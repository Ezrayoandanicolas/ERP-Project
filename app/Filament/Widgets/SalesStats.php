<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\DB;

class SalesStats extends BaseWidget
{
    protected function getCards(): array
    {
        // Penjualan hari ini
        $todaySales = Sale::whereDate('created_at', today())
            ->sum('total');

        // Penjualan bulan ini
        $monthSales = Sale::whereMonth('created_at', now()->month)
            ->sum('total');

        // Profit bulan ini: sum(qty * price) - total modal
        // Jika belum ada modal, profit = sum(subtotal)
        $monthProfit = SaleItem::join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereMonth('sale_items.created_at', now()->month)
            ->whereYear('sale_items.created_at', now()->year)
            ->select(DB::raw('SUM((sale_items.price - products.cost_price) * sale_items.qty) as profit'))
            ->value('profit');

        return [
            Card::make('Penjualan Hari Ini', number_format($todaySales))
                ->description('Total transaksi hari ini')
                ->descriptionColor('primary')
                ->color('primary'),

            Card::make('Penjualan Bulan Ini', number_format($monthSales))
                ->description('Total transaksi bulan berjalan')
                ->descriptionColor('success')
                ->color('success'),

            Card::make('Profit Bulan Ini', number_format($monthProfit))
                ->description('Keuntungan bulan ini')
                ->descriptionColor('warning')
                
                ->color('warning'),
        ];
    }

    protected int|string|array $columnSpan = 'full';


}
