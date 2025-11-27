<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\RelationManagers\ItemsRelationManager;
use App\Models\Sale;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationGroup = 'Penjualan';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $label = 'Penjualan';
    protected static ?string $navigationLabel = 'Penjualan';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([

            Forms\Components\Select::make('outlet_id')
                ->label('Outlet')
                ->relationship('outlet', 'name')
                ->required()
                ->searchable(),

            Forms\Components\TextInput::make('customer_name')
                ->label('Nama Pembeli')
                ->placeholder('Opsional'),

            Forms\Components\Select::make('payment_method')
                ->label('Metode Pembayaran')
                ->options([
                    'cash' => 'Cash',
                    'qris' => 'QRIS',
                    'transfer' => 'Transfer',
                    'credit' => 'Credit / Utang',
                ])
                ->required()
                ->default('cash'),

            Forms\Components\TextInput::make('total')
                ->label('Total')
                ->numeric()
                ->readOnly()
                ->default(0),
                
            Forms\Components\Textarea::make('note')
                ->label('Catatan'),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([

            Tables\Columns\TextColumn::make('outlet.name')
                ->label('Outlet')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('customer_name')
                ->label('Pembeli')
                ->sortable(),

            Tables\Columns\TextColumn::make('payment_method')
                ->label('Pembayaran')
                ->formatStateUsing(fn($state) => strtoupper($state)),

            Tables\Columns\TextColumn::make('total')
                ->label('Total')
                ->money('IDR')
                ->sortable(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Tanggal')
                ->dateTime()
                ->sortable(),
        ])
        ->defaultSort('id', 'desc')
        ->searchable();
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
