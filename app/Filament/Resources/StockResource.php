<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource\Pages;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Outlet;
use App\Services\ProductStockService;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Components\Placeholder;



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
                ->preload()
                ->live()
                ->default(fn () => session('last_outlet_id'))
                    ->afterStateUpdated(function ($state) {
                        session(['last_outlet_id' => $state]);
                    }),

            Forms\Components\Select::make('product_id')
                ->label('Produk')
                ->required()
                ->searchable()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {

                    if (!$state) return;

                    $product = Product::find($state);

                    if ($product) {
                        $set('unit_id', $product->yield_unit_id);
                        $set('unit_name', $product->yieldUnit?->name);

                        if ($get('type') === 'in' && $get('price_auto')) {
                            $qty = (int) ($get('qty') ?: 1);
                            $set('price_total', $product->cost_price * $qty);
                        }
                    }
                })
                ->options(function (callable $get) {
                    $outletId = $get('outlet_id');
                    if (!$outletId) return [];

                    $outlet = Outlet::find($outletId);
                    if (!$outlet) return [];

                    return Product::where('store_id', $outlet->store_id)
                        ->pluck('name', 'id');
                }),

            // ======= UNIT (AUTO) =======

            Placeholder::make('current_stock')
                ->label('Stok Saat Ini di Outlet')
                ->content(function (callable $get) {
                    $outletId  = $get('outlet_id');
                    $productId = $get('product_id');

                    if (!$outletId || !$productId) {
                        return '-';
                    }

                    $stock = \App\Models\Stock::where('outlet_id', $outletId)
                        ->where('product_id', $productId)
                        ->selectRaw("
                            SUM(
                                CASE 
                                    WHEN type = 'in' THEN qty
                                    WHEN type = 'out' THEN -qty
                                    ELSE 0
                                END
                            ) as total_qty
                        ")
                        ->first();

                    $product = \App\Models\Product::find($productId);

                    $qty  = $stock?->total_qty ?? 0;
                    $unit = $product?->yieldUnit?->name ?? '';

                    return "{$qty} {$unit}";
                })
                ->visible(fn ($get) => $get('product_id') && $get('outlet_id')),


            Forms\Components\Hidden::make('unit_id')
                ->required(),

            Forms\Components\TextInput::make('unit_name')
                ->label('Unit')
                ->disabled()
                ->dehydrated(false),

            // ============================

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
                ->debounce(500) // ⏱️ nunggu 0.5 detik setelah berhenti ngetik
                ->afterStateUpdated(function ($state, callable $set, callable $get) {

                    if ($get('type') !== 'in') return;
                    if (!$get('price_auto')) return;

                    $productId = $get('product_id');
                    if (!$productId) return;

                    $cost = Product::find($productId)?->cost_price ?? 0;
                    $set('price_total', $cost * (int) $state);
                })
                ->required(),

            Forms\Components\Select::make('target_outlet')
                ->label('Outlet Tujuan')
                ->relationship('outlet', 'name')
                ->preLoad()
                ->searchable()
                ->visible(fn ($get) => $get('type') === 'transfer'),

            Forms\Components\Hidden::make('price_auto')
                ->default(true),

            Forms\Components\TextInput::make('price_total')
                ->label('Harga Total Pembelian')
                ->numeric()
                ->visible(fn ($get) => $get('type') === 'in')
                ->required(fn ($get) => $get('type') === 'in')
                ->dehydrated(fn ($get) => $get('type') === 'in')
                ->afterStateUpdated(function ($set, $get, $state) {
                    $set('price_auto', false);
                })
                ->reactive(),


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

            Tables\Columns\TextColumn::make('qty')
                ->label('Qty')
                ->formatStateUsing(fn ($state, $record) =>
                    $state . ' ' . ($record->unit?->name ?? '')
                ),

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
            
            Tables\Columns\TextColumn::make('cost_price')
                ->label('Modal / Unit')
                ->formatStateUsing(fn ($state) =>
                    "Rp " . number_format($state, 0, ',', '.')
                ),


            Tables\Columns\TextColumn::make('created_at')
                ->label('Tanggal')
                ->date('d M Y'),

            Tables\Columns\TextColumn::make('note')
                ->label('Catatan')
                ->limit(20)
                ->tooltip(fn ($record) => $record->note),

        ])
        ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            // 'edit' => Pages\CreateStock::route('/{record}/edit'),
        ];
    }
}
