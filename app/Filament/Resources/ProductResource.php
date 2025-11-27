<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Tables;
use App\Models\Stock;
use App\Models\Outlet;
use Filament\Resources\Resource;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $label = 'Produk';
    protected static ?string $navigationLabel = 'Produk';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([

            Forms\Components\Section::make('Informasi Produk')
                ->schema([
                    Forms\Components\Select::make('store_id')
                        ->relationship('store', 'name')
                        ->label('Toko Induk')
                        ->required()
                        ->searchable()
                        ->live(),

                    Forms\Components\Select::make('category_id')
                        ->relationship('category', 'name')
                        ->label('Kategori')
                        ->searchable()
                        ->preload(),

                    Forms\Components\TextInput::make('name')
                        ->label('Nama Produk')
                        ->required(),

                    Forms\Components\TextInput::make('price')
                        ->label('Harga')
                        ->numeric()
                        ->required(),

                    Forms\Components\Select::make('type')
                        ->label('Tipe Produk')
                        ->options([
                            'production' => 'Production (Pakai Resep)',
                            'stock' => 'Stock',
                        ])
                        ->required()
                        ->live(),
                ]),

            Forms\Components\Section::make('Bahan Produk (Production Only)')
                ->schema([
                    Forms\Components\Select::make('ingredients')
                        ->label('Bahan yang Dipakai')
                        ->multiple()
                        ->relationship('ingredients', 'name')
                        ->preload()
                        ->visible(fn ($get) => $get('type') === 'production'),
                ])
                ->visible(fn ($get) => $get('type') === 'production')
                ->collapsed(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([

            Tables\Columns\TextColumn::make('store.name')
                ->label('Toko Induk')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('category.name')
                ->label('Kategori')
                ->toggleable(),

            Tables\Columns\TextColumn::make('name')
                ->label('Nama Produk')
                ->sortable()
                ->searchable(),

            Tables\Columns\BadgeColumn::make('type')
                ->label('Tipe')
                ->colors([
                    'success' => 'production',
                    'warning' => 'stock',
                ])
                ->formatStateUsing(fn ($state) => ucfirst($state)),


            // ===============================
            //   HANYA STOK PER OUTLET
            // ===============================

            Tables\Columns\TextColumn::make('outlet_stocks')
                ->label('Stok per Outlet')
                ->getStateUsing(function ($record) {

                    $stocks = $record->stocks;

                    $totals = [];

                    foreach ($stocks as $s) {

                        if ($s->type === 'in') {
                            $totals[$s->outlet_id] = ($totals[$s->outlet_id] ?? 0) + $s->qty;
                        }

                        if ($s->type === 'out') {
                            $totals[$s->outlet_id] = ($totals[$s->outlet_id] ?? 0) - $s->qty;
                        }

                        if ($s->type === 'transfer') {
                            // kurangi outlet asal
                            $totals[$s->outlet_id] = ($totals[$s->outlet_id] ?? 0) - $s->qty;

                            // tambahkan outlet tujuan
                            if ($s->target_outlet) {
                                $totals[$s->target_outlet] = ($totals[$s->target_outlet] ?? 0) + $s->qty;
                            }
                        }
                    }

                    // Format multi-line
                    return collect($totals)
                        ->map(function ($qty, $outletId) {
                            $name = \App\Models\Outlet::find($outletId)?->name ?? 'Unknown';
                            return "<div><strong>{$name}</strong>: {$qty}</div>";
                        })
                        ->implode('');
                })
                ->html()   // ← penting: biar <div> jalan
                ->wrap(),  // ← supaya pindah baris rapi

        ])
        ->defaultSort('name', 'asc')
        ->searchable();
    }



    public static function getRelations(): array
    {
        return [
            ProductResource\RelationManagers\IngredientsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
