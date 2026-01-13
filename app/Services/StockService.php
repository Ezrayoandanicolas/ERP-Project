<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Models\ProductVariant;

class StockService
{
    /**
     * Hitung stok suatu produk di outlet.
     */
    public static function getOutletStock($outletId, $productId)
    {
        return Stock::where('outlet_id', $outletId)
            ->where('product_id', $productId)
            ->selectRaw("
                SUM(CASE WHEN type = 'in' THEN qty ELSE 0 END) -
                SUM(CASE WHEN type = 'out' THEN qty ELSE 0 END)
                AS total
            ")
            ->value('total') ?? 0;
    }

    /**
     * Ambil stok keluar (in/out)
     */
    public static function consume($outletId, $productId, $qty, $note = null)
    {
        $current = self::getOutletStock($outletId, $productId);

        if ($qty > $current) {
            throw new \Exception("Stok tidak cukup (tersisa: $current)");
        }

        return Stock::create([
            'outlet_id'  => $outletId,
            'product_id' => $productId,
            'qty'        => $qty,
            'type'       => 'out',
            'note'       => $note,
        ]);
    }

    /**
     * Tambah stok in.
     */
    public static function add($outletId, $productId, $qty, $note = null)
    {
        return Stock::create([
            'outlet_id'  => $outletId,
            'product_id' => $productId,
            'qty'        => $qty,
            'type'       => 'in',
            'note'       => $note,
        ]);
    }

    /**
     * TRANSFER ANTAR OUTLET — versi paling benar
     */
    public static function transfer(
        int $fromOutlet,
        int $toOutlet,
        int $productId,
        int $qty,
        ?int $unitId = null
    ): void {
        DB::transaction(function () use (
            $fromOutlet,
            $toOutlet,
            $productId,
            $qty,
            $unitId
        ) {

            // 🔴 OUT — outlet asal
            Stock::create([
                'outlet_id'  => $fromOutlet,
                'product_id' => $productId,
                'qty'        => $qty,
                'unit_id'    => $unitId,
                'type'       => 'out',
                'note'       => 'Transfer ke outlet ' . $toOutlet,
            ]);

            // 🟢 IN — outlet tujuan
            Stock::create([
                'outlet_id'  => $toOutlet,
                'product_id' => $productId,
                'qty'        => $qty,
                'unit_id'    => $unitId,
                'type'       => 'in',
                'note'       => 'Transfer dari outlet ' . $fromOutlet,
            ]);
        });
    }

    /**
     * Catat stok untuk penjualan.
     */
    public static function processSale($sale, $productId, $qty)
    {
        self::consume(
            outletId: $sale->outlet_id,
            productId: $productId,
            qty: $qty,
            note: "Penjualan #{$sale->id}"
        );
    }

    public static function process(Stock $stock)
    {
        // hanya stok masuk yg hitung modal
        if ($stock->type !== 'in') return;

        if ($stock->price_total <= 0 || $stock->qty <= 0) return;

        $costPerUnit = $stock->price_total / $stock->qty;

        // Simpan modal per unit ke stok
        $stock->update(['cost_price' => $costPerUnit]);

        // update ke product juga
        Product::where('id', $stock->product_id)
            ->update(['cost_price' => $costPerUnit]);
    }

    public static function consumeVariant(
        int $outletId,
        ProductVariant $variant,
        int $qty
    ): void {
        $totalPcs = $variant->pcs_used * $qty;

        self::consume(
            outletId: $outletId,
            productId: $variant->product_id, // ✅ stok fisik
            qty: $totalPcs,
            note: "Penjualan {$variant->name}"
        );
    }

}
