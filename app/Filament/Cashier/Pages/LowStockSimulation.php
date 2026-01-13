<?php

namespace App\Filament\Cashier\Pages;

use Filament\Pages\Page;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Outlet;

class LowStockSimulation extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel = 'Simulasi Stock';
    protected static ?string $title = 'Simulasi Produk Stok Rendah';
    protected static string $view = 'filament.cashier.pages.low-stock-simulation';

    public ?int $outletId = null;
    public int $minStock = 10;

    /**
     * Auto set outlet untuk cashier
     */
    public function mount(): void
    {
        if (Filament::getCurrentPanel()->getId() === 'cashier') {
            $this->outletId = auth()->user()->outlet_id;
        }
    }

    public function getProducts()
    {
        $stockSub = DB::table('stocks')
            ->selectRaw("
                product_id,
                SUM(
                    CASE
                        WHEN type = 'in' THEN qty
                        WHEN type = 'out' THEN -qty
                        ELSE 0
                    END
                ) as stock
            ")
            ->when($this->outletId, fn ($q) =>
                $q->where('outlet_id', $this->outletId)
            )
            ->groupBy('product_id');


        return Product::query()
            ->joinSub($stockSub, 's', fn ($join) =>
                $join->on('products.id', '=', 's.product_id')
            )
            ->where('s.stock', '<', $this->minStock)
            ->orderBy('s.stock')
            ->select('products.*', 's.stock')
            ->get();
    }

    public function getOutlets()
    {
        if (Filament::getCurrentPanel()->getId() === 'cashier') {
            return Outlet::where('id', auth()->user()->outlet_id)->get();
        }

        return Outlet::orderBy('name')->get();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(
            Filament::getCurrentPanel()->getId(),
            ['admin', 'cashier']
        );
    }
}
