<?php

namespace App\Filament\Cashier\Pages;

use App\Filament\Pos\BasePosSale;

class PosSale extends BasePosSale
{
    protected static ?string $navigationGroup = 'Transactions';
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static string $view = 'filament.pages.pos-sale';
    protected static ?string $navigationLabel = 'POS';
    protected static ?string $title = 'Sales';

    protected function setupContext(): void
    {
        $user = auth()->user();
        $this->store_id  = $user->store_id;
        $this->outlet_id = $user->outlet_id;
        $this->loadItems();
    }

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole('cashier');
    }

}
