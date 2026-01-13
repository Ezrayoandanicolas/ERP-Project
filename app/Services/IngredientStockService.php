<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\IngredientStock;
use Illuminate\Support\Facades\DB;

class IngredientStockService
{
    /**
     * Total stok bahan di seluruh store/outlet (jumlah di semua ingredient_stocks)
     * Mengembalikan qty dalam satuan stock record (tidak melakukan convert).
     */
    public static function getTotalStock(Ingredient $ingredient): float
    {
        return IngredientStock::where('ingredient_id', $ingredient->id)
            ->selectRaw("
                SUM(CASE WHEN type = 'in' THEN qty ELSE 0 END) -
                SUM(CASE WHEN type = 'out' THEN qty ELSE 0 END)
                AS total
            ")
            ->value('total') ?? 0;
    }

    public static function getAvgPricePerUnit(\App\Models\Ingredient $ingredient): float
    {
        $inStocks = \App\Models\IngredientStock::where('ingredient_id', $ingredient->id)
            ->where('type', 'in')
            ->get();

        if ($inStocks->isEmpty()) {
            throw new \Exception("Bahan {$ingredient->name} belum punya histori pembelian.");
        }

        $totalQty   = $inStocks->sum('qty');
        $totalPrice = $inStocks->sum('price_total');

        if ($totalQty <= 0) {
            throw new \Exception("Qty pembelian bahan {$ingredient->name} tidak valid.");
        }

        return round($totalPrice / $totalQty, 2);
    }


    /**
     * Cek apakah cukup (menggunakan total stok global bahan)
     */
    public static function hasEnough(Ingredient $ingredient, float $neededQty): bool
    {
        return self::getTotalStock($ingredient) >= $neededQty;
    }

    /**
     * Konsumsi bahan (create ingredient_stocks type out)
     * qty harus sesuai dengan unit dari bahan/stock (kita anggap qty sudah dalam unit stock yang sama)
     */
     public static function consume(Ingredient $ingredient, float $qtyBase, string $note = null)
    {
        return IngredientStock::create([
            'ingredient_id' => $ingredient->id,
            'qty'           => $qtyBase,
            'type'          => 'out',
            'unit_id'       => $ingredient->unit_id,
            'note'          => $note,
        ]);
    }

    /**
     * Tambah stok bahan (pembelian)
     */
    public static function add(Ingredient $ingredient, float $qtyBase, float $priceTotal = 0, string $note = null)
    {
        return IngredientStock::create([
            'ingredient_id' => $ingredient->id,
            'qty'           => $qtyBase,
            'type'          => 'in',
            'price_total'   => $priceTotal,
            'unit_id'       => $ingredient->unit_id,
            'note'          => $note,
        ]);
    }

    /**
     * Hitung nilai stok bahan (berdasarkan price_per_unit terakhir)
     */
    public static function getStockValue(Ingredient $ingredient): float
    {
        $total = self::getTotalStock($ingredient);
        return $total * ($ingredient->price_per_unit ?? 0);
    }
}
