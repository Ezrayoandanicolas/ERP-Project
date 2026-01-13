<?php

namespace App\Filament\Resources\ProductRecipeResource\Pages;

use App\Filament\Resources\ProductRecipeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductRecipe extends EditRecord
{
    protected static string $resource = ProductRecipeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $product = $this->record->product;
        \App\Services\ProductCostingService::update($product);
    }

}
