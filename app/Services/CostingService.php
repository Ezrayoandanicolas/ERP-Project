<?php

namespace App\Services;

use App\Models\Product;

class CostingService
{
    /**
     * Hitung HPP produk berdasarkan resep dan harga pembelian stok terakhir.
     */
    public static function productCost(Product $product)
    {
        $total = 0;

        foreach ($product->recipes as $recipe) {
            $ingredient = $recipe->ingredient;

            if (! $ingredient) continue;

            // Harga bahan = dari stok pembelian terakhir
            $lastStock = $ingredient->stocks()->where('type', 'in')->latest()->first();

            if (! $lastStock) continue;

            $pricePerUnit = $lastStock->price_total / $lastStock->qty;

            // Hitung biaya
            $total += ($recipe->qty * $pricePerUnit);
        }

        // Simpan ke produk
        $product->cost_price = $total;
        $product->save();

        return $total;
    }
}
