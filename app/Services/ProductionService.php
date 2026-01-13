<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Production;
use App\Models\ProductionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductionService
{
    /**
     * Proses produksi sejumlah batch.
     *
     * @param  Product  $product
     * @param  float    $batchQty   jumlah batch (contoh: 1, 1.5)
     * @param  int      $storeId    id store (tempat menyimpan bahan)
     * @param  int      $outletId   id outlet (tempat menyimpan produk jadi)
     * @return Production
     *
     * @throws Exception
     */
    public static function produce(Product $product, float $batchQty, int $storeId, int $outletId): Production
    {
        return DB::transaction(function () use ($product, $batchQty, $storeId, $outletId) {

            if ($batchQty <= 0) {
                throw new Exception("Jumlah batch harus lebih besar dari 0.");
            }

            // total output (contoh yield 240 pcs * batch 1 = 240)
            $totalOutput = (float) ($product->yield_qty * $batchQty);

            // tulis header produksi
            $production = Production::create([
                'product_id'   => $product->id,
                'store_id'     => $storeId,
                'outlet_id'    => $outletId,
                'batch_qty'    => $batchQty,
                'total_output' => $totalOutput,
                'total_cost'   => 0, // update setelah semua bahan diproses
            ]);

            $totalCost = 0.0;

            // loop setiap recipe/ingredient
            // 4️⃣ Loop setiap bahan di resep
            foreach ($product->recipes as $recipe) {

                $ingredient = $recipe->ingredient;

                // qty resep × batch
                $qtyUsed = $recipe->qty * $batchQty;

                // 1. Ambil stok bahan (dari ingredient_stocks)
                $currentStock = \App\Services\IngredientStockService::getTotalStock($ingredient);

                if ($currentStock <= 0) {
                    throw new \Exception("Stok bahan '{$ingredient->name}' kosong!");
                }

                if ($qtyUsed > $currentStock) {
                    throw new \Exception("Stok bahan '{$ingredient->name}' tidak cukup! (tersisa {$currentStock})");
                }

                // 2. Kurangi stok (catat OUT ke ingredient_stocks)
                \App\Services\IngredientStockService::consume(
                    ingredient: $ingredient,
                    qtyBase: $qtyUsed,
                    note: "Produksi #{$production->id}"
                );

                // 3. Hitung cost bahan
                $costPerUnit = \App\Services\IngredientStockService::getAvgPricePerUnit($ingredient);

                $cost = $costPerUnit * $qtyUsed;

                $totalCost += $cost;

                // 4. Simpan detail bahan yang dipakai
                ProductionItem::create([
                    'production_id' => $production->id,
                    'ingredient_id' => $ingredient->id,
                    'qty_used'      => $qtyUsed,
                    'unit_id'       => $ingredient->unit_id,
                    'cost'          => $cost,
                ]);
            }


            // tambahkan stok produk jadi (pakai yield_unit_id dari product)
            ProductStockService::add(
                outletId: $outletId,
                productId: $product->id,
                qty: $totalOutput,
                unitId: $product->yield_unit_id,
                note: "Hasil Produksi #{$production->id}"
            );

            // update total cost di header
            $production->update([
                'total_cost' => round($totalCost, 2),
            ]);

            // update product costing (optional)
            try {
                \App\Services\ProductCostingService::update($product);
            } catch (\Throwable $e) {
                // jangan gagalkan produksi jika costing gagal; cukup log
                Log::warning("ProductCostingService::update error for product {$product->id}: ".$e->getMessage());
            }

            return $production;
        });
    }
}
