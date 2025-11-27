<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\Product;
use App\Models\Outlet;
use Illuminate\Support\Facades\DB;

class StockService
{
    // hitung stok total per outlet
    public static function getOutletStock($outletId, $productId)
    {
        return Stock::where('outlet_id', $outletId)
            ->where('product_id', $productId)
            ->selectRaw("
                SUM(CASE WHEN type = 'in' THEN qty ELSE 0 END) -
                SUM(CASE WHEN type IN ('out','transfer') THEN qty ELSE 0 END)
                AS total
            ")
            ->value('total') ?? 0;
    }

    // proses logika stok otomatis
    public static function process(Stock $stock)
    {
        if ($stock->type === 'in') {
            // stok masuk → tidak butuh tambahan logic, sudah otomatis dihitung
            return;
        }

        if ($stock->type === 'out') {
            $current = self::getOutletStock($stock->outlet_id, $stock->product_id);

            if ($stock->qty > $current) {
                throw new \Exception("Stok tidak cukup untuk diambil (stok tersisa: $current)");
            }
            return;
        }

        // transfer
        if ($stock->type === 'transfer') {
            $current = self::getOutletStock($stock->outlet_id, $stock->product_id);

            if ($stock->qty > $current) {
                throw new \Exception("Stok tidak cukup untuk ditransfer (stok tersisa: $current)");
            }

            // tambah stok ke outlet tujuan
            Stock::create([
                'outlet_id'     => $stock->target_outlet,
                'product_id'    => $stock->product_id,
                'qty'           => $stock->qty,
                'type'          => 'in',
                'note'          => 'Transfer dari outlet ' . $stock->outlet_id,
            ]);
        }
    }

    public static function processSale($sale, $productId, $qty)
    {
        $current = self::getOutletStock($sale->outlet_id, $productId);

        if ($qty > $current) {
            throw new \Exception("Stok tidak cukup untuk penjualan (tersisa: $current)");
        }

        // catat stok keluar
        \App\Models\Stock::create([
            'outlet_id'  => $sale->outlet_id,
            'product_id' => $productId,
            'qty'        => $qty,
            'type'       => 'out',
            'note'       => 'Penjualan #' . $sale->id,
        ]);
    }

}
