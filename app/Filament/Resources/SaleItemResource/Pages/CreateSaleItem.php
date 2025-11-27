<?php

namespace App\Filament\Resources\SaleItemResource\Pages;

use App\Filament\Resources\SaleItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSaleItem extends CreateRecord
{
    protected static string $resource = SaleItemResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
