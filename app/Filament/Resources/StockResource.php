<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource\Pages;
use App\Models\Stock;
use App\Services\StockService;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static ?string $navigationGroup = 'Inventory';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-right';
    protected static ?string $label = 'Stok';
    protected static ?string $navigationLabel = 'Stok Barang';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([

            Forms\Components\Select::make('outlet_id')
                ->relationship('outlet', 'name')
                ->label('Outlet')
                ->required()
                ->searchable()
                ->live(),

            Forms\Components\Select::make('product_id')
                ->relationship('product', 'name')
                ->label('Produk')
                ->required()
                ->searchable()
                ->preload(),

            Forms\Components\Select::make('type')
                ->label('Jenis Transaksi')
                ->required()
                ->options([
                    'in' => 'Stok Masuk',
                    'out' => 'Stok Keluar',
                    'transfer' => 'Transfer ke Outlet',
                ])
                ->live(),

            Forms\Components\TextInput::make('qty')
                ->numeric()
                ->minValue(1)
                ->required(),

            Forms\Components\Select::make('target_outlet')
                ->label('Outlet Tujuan')
                ->relationship('outlet', 'name')
                ->searchable()
                ->visible(fn ($get) => $get('type') === 'transfer'),

            Forms\Components\TextInput::make('note')
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

            Tables\Columns\TextColumn::make('product.name')
                ->label('Produk')
                ->sortable()
                ->searchable(),

            Tables\Columns\BadgeColumn::make('type')
                ->label('Jenis')
                ->colors([
                    'success' => 'in',
                    'danger' => 'out',
                    'warning' => 'transfer',
                ])
                ->formatStateUsing(fn ($state) => match ($state) {
                    'in' => 'Masuk',
                    'out' => 'Keluar',
                    'transfer' => 'Transfer',
                }),

            Tables\Columns\TextColumn::make('qty')
                ->label('Qty')
                ->sortable(),

            Tables\Columns\TextColumn::make('note')
                ->label('Catatan')
                ->limit(20)
                ->tooltip(fn ($record) => $record->note),
        ])
        ->defaultSort('id', 'desc')
        ->filters([])
        ->searchable();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
        ];
    }
}
