<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use App\Jobs\PrintSaleJob;
use App\Models\Product;
use App\Models\ProductVariant; // 👈 WAJIB
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Services\StockService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class PosSale extends Page
{
    protected static ?string $navigationGroup = 'Transactions';
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static string $view = 'filament.pages.pos-sale';
    protected static ?string $navigationLabel = 'POS';
    protected static ?string $title = 'Sales';

    // state
    public ?int $store_id = null;
    public ?int $outlet_id = null;
    public ?string $customer_name = null;
    public string $payment_method = 'cash';
    public array $cart = [];
    public int|float $total = 0;
    public int|float $discount = 0;
    public ?int $lastSaleId = null;
    public ?string $sale_date = null;
    public $category_id = null;
    public array $items = [];


    public function mount(): void
    {
        $this->store_id = null;
        $this->outlet_id = null;
        $this->customer_name = 'Umum';
        $this->payment_method = 'cash';
        $this->cart = [];
        $this->total = 0;
        $this->discount = 0;
        $this->sale_date = now()->toDateString();
    }

    public function loadItems(): void
{
    if (! $this->store_id || ! $this->outlet_id) {
        $this->items = [];
        return;
    }

    $this->items = [];

    $products = Product::with([
        'variants' => fn ($q) => $q->where('is_active', true),
        'stocks'
    ])
    ->where('store_id', $this->store_id)
    ->when($this->category_id, fn ($q) =>
        $q->where('category_id', $this->category_id)
    )
    ->orderBy('name')
    ->get();

    foreach ($products as $product) {
        $stocks = $product->stocks->where('outlet_id', $this->outlet_id);
        $stockReal = $stocks->where('type','in')->sum('qty')
                    - $stocks->where('type','out')->sum('qty');

        $this->items[] = [
            'key'   => 'product-' . $product->id,
            'type'  => 'product',
            'id'    => $product->id,
            'name'  => $product->name,
            'price' => $product->sell_price,
            'image' => $product->image_url,
            'unit'  => $product->yieldUnit?->name ?? 'pcs',
            'stock' => $stockReal,
        ];

        foreach ($product->variants as $variant) {
            $this->items[] = [
                'key'   => 'variant-' . $variant->id,
                'type'  => 'variant',
                'id'    => $variant->id,
                'name'  => $variant->name,
                'price' => $variant->price,
                'pcs'   => $variant->pcs_used,
                'image' => $product->image_url,
                'stock' => $variant->pcs_used > 0
                            ? floor($stockReal / $variant->pcs_used)
                            : 0,
            ];
        }
    }
}


    public function updatedStoreId(): void
    {
        $this->outlet_id = null;
        $this->cart = [];
        $this->total = 0;
        $this->discount = 0;
    }

    public function updatedOutletId(): void
    {
        // $this->cart = [];
        // $this->total = 0;
        // $this->discount = 0;
        $this->resetCart();
        $this->loadItems();
    }

    public function updatedCategoryId() {
        $this->loadItems();
    }

    private function resetCart() {
        $this->cart = [];
        $this->total = 0;
        $this->discount = 0;
    }

    public function addItem(string $type, int $id): void
    {
        if ($type === 'product') {
            $this->addToCart($id);
        } else {
            $this->addVariantToCart($id);
        }
    }


    /**
     * Produk normal (tanpa varian) — pakai product_id sebagai key (int).
     */
    public function addToCart(int $productId): void
    {
        if (! $this->store_id || ! $this->outlet_id) {
            Notification::make()->title('Pilih toko & outlet dulu')->danger()->send();
            return;
        }

        $product = Product::find($productId);
        if (! $product) {
            Notification::make()->title('Produk tidak ditemukan')->danger()->send();
            return;
        }

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['qty']++;
            $this->updateSubtotal($productId);
            return;
        }

        $price = (int) ($product->sell_price ?? 0);


        $key = 'product-'.$productId;

        if (isset($this->cart[$key])) {
            $this->cart[$key]['qty']++;
            $this->updateSubtotal($key);
            return;
        }

        $this->cart[$key] = [
            'key'        => $key,
            'product_id' => $productId,
            'variant_id' => null,
            'pcs_used'   => 1, // 1 pcs per qty
            'name'       => $product->name,
            'unit'       => $product->yieldUnit?->name ?? 'pcs',
            'unit_id'    => $product->yield_unit_id,
            'qty'        => 1,
            'price'      => $price,
            'subtotal'   => $price,
            'image'      => $product->image_url,
        ];

        $this->calculateTotal();
    }

    /**
     * Varian — key pakai string "variant_{id}" biar nggak tabrakan.
     */
    public function addVariantToCart(int $variantId): void
    {
        if (! $this->store_id || ! $this->outlet_id) {
            Notification::make()
                ->title('Pilih toko & outlet dulu')
                ->danger()
                ->send();
            return;
        }

        $variant = ProductVariant::with('product.yieldUnit')->find($variantId);

        if (! $variant) {
            Notification::make()
                ->title('Variant tidak ditemukan')
                ->danger()
                ->send();
            return;
        }

        // ✅ CEK APAKAH VARIANT SUDAH ADA DI CART
        foreach ($this->cart as $index => $item) {
            if (($item['variant_id'] ?? null) === $variant->id) {
                $this->cart[$index]['qty']++;
                $this->cart[$index]['subtotal'] =
                    $this->cart[$index]['qty'] * $this->cart[$index]['price'];

                $this->calculateTotal();
                return;
            }
        }

        $baseProduct = $variant->product;

        $key = 'variant-'.$variant->id;

        if (isset($this->cart[$key])) {
            $this->cart[$key]['qty']++;
            $this->updateSubtotal($key);
            return;
        }

        // ✅ PUSH KE ARRAY NUMERIK (INI KUNCI UTAMA)
        $this->cart[$key] = [
            'key'        => $key,
            'product_id' => $variant->product_id,        // stok fisik
            'variant_id' => $variant->id,                // 🔥 PENTING
            'pcs_used'   => $variant->pcs_used ?? 1,
            'name'       => $variant->name,
            'unit'       => $baseProduct->yieldUnit->name ?? 'pcs',
            'unit_id'    => $baseProduct->yield_unit_id,
            'qty'        => 1,
            'price'      => (int) $variant->price,
            'subtotal'   => (int) $variant->price,
            'image'      => $baseProduct->image_url,
        ];

        $this->calculateTotal();
    }


    /**
     * Update subtotal untuk item dengan key apapun (int / string).
     */
    public function updateSubtotal(int|string $key): void
    {
        if (! isset($this->cart[$key])) {
            return;
        }

        $qty   = (int) Arr::get($this->cart, "{$key}.qty", 1);
        $price = (int) Arr::get($this->cart, "{$key}.price", 0);

        $this->cart[$key]['qty']      = max(1, $qty);
        $this->cart[$key]['price']    = max(0, $price);
        $this->cart[$key]['subtotal'] = $this->cart[$key]['qty'] * $this->cart[$key]['price'];

        $this->calculateTotal();
    }

    public function calculateTotal(): void
    {
        $subtotal = collect($this->cart)->sum(fn($i) => ($i['subtotal'] ?? 0));
        $discount = (int) ($this->discount ?? 0);
        $this->total = max(0, $subtotal - $discount);
    }

    public function removeFromCart(int|string $key): void
    {
        if (isset($this->cart[$key])) {
            unset($this->cart[$key]);
            $this->calculateTotal();
        }
    }

    public function save(): void
    {
        if (! $this->store_id) {
            Notification::make()->title('Pilih Toko Induk terlebih dahulu')->danger()->send();
            return;
        }
        if (! $this->outlet_id) {
            Notification::make()->title('Pilih Outlet terlebih dahulu')->danger()->send();
            return;
        }

        if (empty($this->cart)) {
            Notification::make()->title('Keranjang kosong')->danger()->send();
            return;
        }

        // ✅ Cek stok: pakai qty * pcs_used
        try {
            foreach ($this->cart as $item) {

                $pcsUsed = $item['pcs_used'] ?? 1;
                $needed  = $item['qty'] * $pcsUsed;

                $available = StockService::getOutletStock(
                    $this->outlet_id,
                    $item['product_id']
                );

                if ($needed > $available) {
                    throw ValidationException::withMessages([
                        'stock' => [
                            "Stok {$item['name']} tidak cukup (butuh {$needed}, sisa {$available})"
                        ],
                    ]);
                }
            }

        } catch (ValidationException $e) {
            Notification::make()
                ->title('Stok tidak cukup')
                ->body(collect($e->errors())->flatten()->first() ?? 'Stok tidak mencukupi')
                ->danger()
                ->send();
            return;
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error pengecekan stok')
                ->body($e->getMessage())
                ->danger()
                ->send();
            return;
        }

        try {
            $sale = Sale::create([
                'store_id'      => $this->store_id,
                'outlet_id'     => $this->outlet_id,
                'customer_name' => $this->customer_name,
                'payment_method'=> $this->payment_method,
                'discount'      => (int) $this->discount,
                'total'         => $this->total,
                'sale_date'      => Carbon::parse($this->sale_date)->toDateString(),
                'created_at'      => Carbon::parse($this->sale_date)->toDateString(),
            ]);

            $productIds = collect($this->cart)->pluck('product_id')->unique();
            $products = Product::whereIn('id', $productIds)
                ->select('id', 'cost_price')
                ->get()
                ->keyBy('id');

            foreach ($this->cart as $item) {
                // \Log::info('CART ITEM: ' . json_encode($item));
                $pcsUsed = $item['pcs_used'] ?? 1;
                // dd($this->cart);

                $costUnit = (int) ($products[$item['product_id']]->cost_price ?? 0);

                // HPP per qty jual
                $realCostPrice = $costUnit * $pcsUsed;
                
                // Di sale item, qty bisa kamu simpan sebagai "pack" / "porsi"
                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id'=> $item['variant_id'] ?? null,
                    'qty'        => $item['qty'], // qty jual (misal 1 box 4 pcs)
                    'price'      => $item['price'],
                    'cost_price' => $realCostPrice, 
                    // 'subtotal'   => $item['subtotal'],
                    'subtotal'   => $item['qty'] * $item['price'],
                ]);

                // Stok fisik: keluar sesuai pcs_used
                Stock::create([
                    'outlet_id'  => $this->outlet_id,
                    'product_id' => $item['product_id'],
                    'qty'        => $item['qty'] * $pcsUsed,
                    'unit_id'    => $item['unit_id'] ?? null,
                    'type'       => 'out',
                    'note'       => 'Penjualan POS #' . $sale->id,
                ]);
            }

            $this->cart = [];
            $this->customer_name = null;
            $this->discount = 0;
            $this->total = 0;

            Notification::make()
                ->title('Transaksi berhasil')
                ->success()
                ->send();

            $this->lastSaleId = $sale->id;

            try {
                \App\Services\ThermalPrintService::printSale($sale);

                Notification::make()
                    ->title('Struk sedang dicetak...')
                    ->success()
                    ->send();

            } catch (\Throwable $e) {
                Notification::make()
                    ->title('Gagal auto-print')
                    ->body('Silakan tekan tombol Print Struk.')
                    ->danger()
                    ->send();
            }
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal menyimpan transaksi')
                ->body($e->getMessage())
                ->danger()
                ->send();
            \Log::error('POS Save Error: '.$e->getMessage());
        }
    }

    public function updateQty(int|string $key, int $change = 1): void
    {
        if (! isset($this->cart[$key])) {
            return;
        }

        $newQty = $this->cart[$key]['qty'] + $change;
        if ($newQty < 1) {
            $newQty = 1;
        }

        $this->cart[$key]['qty'] = $newQty;
        $this->cart[$key]['subtotal'] = $newQty * $this->cart[$key]['price'];

        $this->calculateTotal();
    }

    public function printLastSale(): void
    {
        if (! $this->lastSaleId) {
            Notification::make()
                ->title("Tidak ada transaksi untuk dicetak.")
                ->danger()
                ->send();
            return;
        }

        $sale = \App\Models\Sale::find($this->lastSaleId);

        if (! $sale) {
            Notification::make()
                ->title("Transaksi tidak ditemukan.")
                ->danger()
                ->send();
            return;
        }

        try {
            \App\Services\ThermalPrintService::printSale($sale);

            Notification::make()
                ->title("Struk sedang dicetak...")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title("Gagal mencetak")
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function updatedDiscount(): void
    {
        $this->calculateTotal();
    }

    public function updateQtyManual(int|string $key, $value): void
    {
        if (! isset($this->cart[$key])) {
            return;
        }

        $qty = (int) $value;
        if ($qty < 1) {
            $qty = 1;
        }

        $this->cart[$key]['qty'] = $qty;
        $this->cart[$key]['subtotal'] = $qty * $this->cart[$key]['price'];

        $this->calculateTotal();
    }
}
