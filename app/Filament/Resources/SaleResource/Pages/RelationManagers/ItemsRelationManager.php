<?php

namespace App\Filament\Resources\SaleResource\Pages\RelationManagers;

use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Item Belanja';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('qty')
                    ->label('Qty')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->defaultSort('id', 'asc');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            // biasanya tidak boleh edit dari sini, hanya view
        ]);
    }
}
