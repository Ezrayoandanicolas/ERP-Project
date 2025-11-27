<?php

namespace App\Filament\Resources\ProductRecipeResource\Pages;

use App\Filament\Resources\ProductRecipeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductRecipes extends ListRecords
{
    protected static string $resource = ProductRecipeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
