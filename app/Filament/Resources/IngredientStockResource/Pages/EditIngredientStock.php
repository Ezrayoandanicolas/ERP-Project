<?php

namespace App\Filament\Resources\IngredientStockResource\Pages;

use App\Filament\Resources\IngredientStockResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIngredientStock extends EditRecord
{
    protected static string $resource = IngredientStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $stock = \App\Models\IngredientStock::find($this->record->id);

        if (!$stock || !$stock->ingredient) {
            return;
        }

        // =======================
        // UPDATE COST PRODUK
        // Jika ingredient dipakai di produk mana pun
        // =======================
        foreach ($stock->ingredient->recipes as $recipe) {
            $product = $recipe->product;
            \App\Services\ProductCostingService::update($product);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

            $old = $this->record;
            if ($old->type === 'out') {
                $totalStock += $old->qty;
            }

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
