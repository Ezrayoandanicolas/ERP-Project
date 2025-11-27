<?php

namespace App\Filament\Resources\ProductRecipeResource\Pages;

use App\Filament\Resources\ProductRecipeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductRecipe extends CreateRecord
{
    protected static string $resource = ProductRecipeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
