<?php

namespace App\Filament\Resources\IngredientStockResource\Pages;

use App\Filament\Resources\IngredientStockResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\CostingService;

class CreateIngredientStock extends CreateRecord
{
    protected static string $resource = IngredientStockResource::class;

    protected function afterCreate(): void
    {
        $stock = \App\Models\IngredientStock::find($this->record->id);

        if (! $stock || ! $stock->ingredient) {
            return;
        }

        // Sistem baru TIDAK memakai computeIngredientCostPerBase.
        // Cukup update HPP product yang memakai bahan ini jika diperlukan.

        foreach ($stock->ingredient->recipes as $recipe) {
            $product = $recipe->product;

            // Hitung ulang HPP berdasarkan harga pembelian terakhir
            \App\Services\CostingService::productCost($product);
        }
    }



    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['type'] === 'in') {
            $qty = (float) ($data['qty'] ?? 0);
            $total = (float) ($data['price_total'] ?? 0);

            $data['price_per_base'] = ($qty > 0 && $total > 0)
                ? round($total / $qty, 2)
                : 0;
        } else {
            $data['price_per_base'] = 0;
        }

        // ===== VALIDASI OUT (punyamu, TETAP) =====
        if ($data['type'] === 'out') {
            $ingredient = \App\Models\Ingredient::find($data['ingredient_id']);
            $totalStock = \App\Services\IngredientStockService::getTotalStock($ingredient);

            if ($data['qty'] > $totalStock) {
                \Filament\Notifications\Notification::make()
                    ->title('Stok tidak cukup!')
                    ->body("Sisa stok: {$totalStock} {$ingredient->unit->name}")
                    ->danger()
                    ->send();

                $this->halt();
            }
        }

        return $data;
    }



}
