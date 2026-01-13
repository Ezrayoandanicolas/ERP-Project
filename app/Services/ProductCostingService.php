<?php

namespace App\Services;

use App\Models\Product;

class ProductCostingService
{
    public static function calculate(Product $product): float
    {
        $total = 0;

        foreach ($product->recipes as $recipe) {

            $ingredient = $recipe->ingredient;

            if (! $ingredient) {
                continue;
            }

            $pricePerUnit = $ingredient->price_per_unit ?? 0;
            $qtyUsed = $recipe->qty;

            // cost = qty digunakan × harga per unit bahan
            $total += $qtyUsed * $pricePerUnit;
        }

        return round($total, 2);
    }

    public static function update(Product $product): void
    {
        $product->cost_price = self::calculate($product);
        $product->save();
    }
}
