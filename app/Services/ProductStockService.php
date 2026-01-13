<?php

namespace App\Services;

use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class ProductStockService
{
    /**
     * Get stok produk di outlet tertentu
     */
    public static function getOutletStock(int $outletId, int $productId): float
    {
        return Stock::where('outlet_id', $outletId)
            ->where('product_id', $productId)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'in' THEN qty ELSE 0 END),0) -
                COALESCE(SUM(CASE WHEN type = 'out' THEN qty ELSE 0 END),0)
                AS total
            ")
            ->value('total') ?? 0;
    }

    /**
     * Tambah stok produk (in) — wajib sertakan unitId (yield unit)
     */
    public static function add(int $outletId, int $productId, float $qty, ?int $unitId = null, ?string $note = null)
    {
        return Stock::create([
            'outlet_id' => $outletId,
            'product_id' => $productId,
            'qty' => $qty,
            'unit_id' => $unitId,
            'type' => 'in',
            'note' => $note,
        ]);
    }

    /**
     * Kurangi stok produk (out)
     */
    public static function consume(int $outletId, int $productId, float $qty, ?int $unitId = null, ?string $note = null)
    {
        $current = self::getOutletStock($outletId, $productId);

        if ($qty > $current) {
            throw new \Exception("Stok produk tidak cukup (tersisa: $current)");
        }

        return Stock::create([
            'outlet_id' => $outletId,
            'product_id' => $productId,
            'qty' => $qty,
            'unit_id' => $unitId,
            'type' => 'out',
            'note' => $note,
        ]);
    }

    /**
     * Transfer produk antar outlet
     */
    public static function transfer(int $fromOutlet, int $toOutlet, int $productId, float $qty, ?int $unitId = null)
    {
        DB::transaction(function () use ($fromOutlet, $toOutlet, $productId, $qty, $unitId) {
            self::consume($fromOutlet, $productId, $qty, $unitId, "Transfer ke outlet {$toOutlet}");
            self::add($toOutlet, $productId, $qty, $unitId, "Transfer dari outlet {$fromOutlet}");
        });
    }

    /**
     * Dipanggil saat penjualan
     */
    public static function processSale($sale, int $productId, float $qty)
    {
        // gunakan outlet dari sale
        return self::consume($sale->outlet_id, $productId, $qty, null, "Penjualan #{$sale->id}");
    }

    public static function updateCost(Stock $stock)
    {
        $product = $stock->product;

        // ambil semua stok masuk produk ini
        $stocks = Stock::where('product_id', $product->id)
            ->where('type', 'in')
            ->get();

        if ($stocks->count() === 0) return;

        $totalQty = $stocks->sum('qty');
        $totalCost = $stocks->sum(fn ($s) => $s->qty * $s->cost_price);

        // HPP rata-rata
        $averageCost = $totalCost / max($totalQty, 1);

        $product->cost_price = $averageCost;
        $product->save();
    }

    public static function recalc(Product $product)
    {
        // Dapatkan semua stok masuk untuk produk
        $in = Stock::where('product_id', $product->id)
            ->where('type', 'in')
            ->whereNotNull('price_total')
            ->get();

        if ($in->count() === 0) {
            return;
        }

        // Total qty & total modal
        $totalQty   = $in->sum('qty');
        $totalPrice = $in->sum('price_total');

        if ($totalQty <= 0) return;

        // Hitung modal per unit
        $modalPerUnit = $totalPrice / $totalQty;

        // Update produk
        $product->update([
            'cost_price' => $modalPerUnit
        ]);
    }

}
