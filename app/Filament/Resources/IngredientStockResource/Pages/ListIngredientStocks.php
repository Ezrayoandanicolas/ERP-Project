<?php

namespace App\Filament\Resources\IngredientStockResource\Pages;

use App\Filament\Resources\IngredientStockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIngredientStocks extends ListRecords
{
    protected static string $resource = IngredientStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
