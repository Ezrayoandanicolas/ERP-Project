<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionResource\Pages;
use App\Models\Production;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;

class ProductionResource extends Resource
{
    protected static ?string $model = Production::class;
    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-fire';
    protected static ?string $label = 'Productions';
    protected static ?string $navigationLabel = 'Productions';
    

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_id')
                ->relationship('product', 'name')
                ->label('Produk')
                ->required()
                ->searchable()
                ->preload(),

            Forms\Components\TextInput::make('batch_qty')
                ->numeric()
                ->minValue(0.1)
                ->required()
                ->label('Jumlah Batch'),

            Forms\Components\Select::make('store_id')
                ->relationship('store', 'name')
                ->label('Toko Induk')
                ->required()
                ->preload()
                ->searchable(),

            Forms\Components\Select::make('outlet_id')
                ->relationship('outlet', 'name')
                ->label('Outlet')
                ->required()
                ->preload()
                ->searchable(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('product.name')->label('Produk'),
            Tables\Columns\TextColumn::make('batch_qty')->label('Batch'),
            Tables\Columns\TextColumn::make('product.yield_qty')->label('Hasil (Gram/Pcs)'),
            Tables\Columns\TextColumn::make('total_cost')->money('IDR')->label('Total Biaya'),
            Tables\Columns\TextColumn::make('created_at')->label('Tanggal')->dateTime(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductions::route('/'),
            'create' => Pages\CreateProduction::route('/create'),
            'edit' => Pages\EditProduction::route('/{record}/edit'),
        ];
    }
}
