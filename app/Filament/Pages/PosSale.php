<?php

namespace App\Filament\Pages;

use App\Jobs\PrintSaleJob;
use App\Models\Product;
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
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Penjualan';
    protected static string $view = 'filament.pages.pos-sale';
    protected static ?string $title = 'POS Penjualan';

    // state
    public ?int $store_id = null;      // <<< added: toko induk
    public ?int $outlet_id = null;
    public ?string $customer_name = null;
    public string $payment_method = 'cash';
    public array $cart = [];
    public int|float $total = 0;
    public int|float $discount = 0;
    public ?int $lastSaleId = null;


    public function mount(): void
    {
        $this->store_id = null;
        $this->outlet_id = null;
        $this->customer_name = null;
        $this->payment_method = 'cash';
        $this->cart = [];
        $this->total = 0;
        $this->discount = 0;
    }

    /**
     * Called automatically by Livewire when store_id is updated in the frontend.
     * Reset outlet and cart so user chooses outlet again for that store.
     */
    public function updatedStoreId(): void
    {
        $this->outlet_id = null;
        $this->cart = [];
        $this->total = 0;
        $this->discount = 0;
    }

    /**
     * Optionally when outlet changes we clear cart as well (optional).
     */
    public function updatedOutletId(): void
    {
        $this->cart = [];
        $this->total = 0;
        $this->discount = 0;
    }

    /**
     * Add product to cart. Price is taken from product->price
     */
    public function addToCart(int $productId): void
    {
        // require store and outlet chosen before adding
        if (! $this->store_id) {
            Notification::make()->title('Pilih Toko Induk dulu')->danger()->send();
            return;
        }
        if (! $this->outlet_id) {
            Notification::make()->title('Pilih Outlet dulu')->danger()->send();
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

        $price = (int) ($product->price ?? 0);

        $this->cart[$productId] = [
            'product_id' => $productId,
            'name'       => $product->name,
            'qty'        => 1,
            'price'      => $price,
            'subtotal'   => $price,
        ];

        $this->calculateTotal();
    }

    /**
     * Update subtotal for a single cart item (qty * price)
     */
    public function updateSubtotal(int $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        $qty = (int) Arr::get($this->cart, "{$productId}.qty", 1);
        $price = (int) Arr::get($this->cart, "{$productId}.price", 0);

        $this->cart[$productId]['qty'] = max(1, $qty);
        $this->cart[$productId]['price'] = max(0, $price);
        $this->cart[$productId]['subtotal'] = $this->cart[$productId]['qty'] * $this->cart[$productId]['price'];

        $this->calculateTotal();
    }

    /**
     * Recalculate grand total (subtotal - discount)
     */
    public function calculateTotal(): void
    {
        $subtotal = collect($this->cart)->sum(fn($i) => ($i['subtotal'] ?? 0));
        $discount = (int) ($this->discount ?? 0);
        $this->total = max(0, $subtotal - $discount);
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(int $productId): void
    {
        if (isset($this->cart[$productId])) {
            unset($this->cart[$productId]);
            $this->calculateTotal();
        }
    }

    /**
     * Save sale: create Sale, SaleItems, create Stock (out) records and dispatch print job
     */
    public function save(): void
    {
        // validation
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

        // check stock availability for each item (using StockService)
        try {
            foreach ($this->cart as $item) {
                $available = StockService::getOutletStock($this->outlet_id, $item['product_id']);
                if ($item['qty'] > $available) {
                    throw ValidationException::withMessages([
                        'stock' => ["Stok untuk produk {$item['name']} tidak cukup (tersisa: {$available})"],
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

        // create sale header
        try {
            $sale = Sale::create([
                'store_id'      => $this->store_id,       // optional if you store relation
                'outlet_id'     => $this->outlet_id,
                'customer_name' => $this->customer_name,
                'payment_method'=> $this->payment_method,
                'discount'      => (int) $this->discount,
                'total'         => $this->total,
            ]);

            // create sale items & stock records
            foreach ($this->cart as $item) {
                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $item['product_id'],
                    'qty'        => $item['qty'],
                    'price'      => $item['price'],
                    'subtotal'   => $item['subtotal'],
                ]);

                // record stock keluar
                Stock::create([
                    'outlet_id'  => $this->outlet_id,
                    'product_id' => $item['product_id'],
                    'qty'        => $item['qty'],
                    'type'       => 'out',
                    'note'       => 'Penjualan POS #' . $sale->id,
                ]);
            }

            // reset cart
            $this->cart = [];
            $this->customer_name = null;
            $this->discount = 0;
            $this->total = 0;

            Notification::make()
                ->title('Transaksi berhasil')
                ->success()
                ->send();
            
            $this->lastSaleId = $sale->id;

            // === AUTO PRINT LANGSUNG ===
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

    public function updateQty($productId, $change = 1)
    {
        if (!isset($this->cart[$productId])) {
            return;
        }

        // Hitung qty baru
        $newQty = $this->cart[$productId]['qty'] + $change;

        // Mencegah qty minimum < 1
        if ($newQty < 1) {
            $newQty = 1;
        }

        // Set qty baru
        $this->cart[$productId]['qty'] = $newQty;

        // Recalculate subtotal & total
        $this->cart[$productId]['subtotal'] = $newQty * $this->cart[$productId]['price'];

        // Update total
        $this->calculateTotal();
    }

    public function printLastSale()
    {
        if (!$this->lastSaleId) {
            Notification::make()
                ->title("Tidak ada transaksi untuk dicetak.")
                ->danger()
                ->send();
            return;
        }

        $sale = \App\Models\Sale::find($this->lastSaleId);

        if (!$sale) {
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

    public function updatedDiscount()
    {
        $this->calculateTotal();
    }



}
