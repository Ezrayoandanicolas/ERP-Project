<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngredientStockResource\Pages;
use App\Models\IngredientStock;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use App\Services\CostingService;
use Filament\Forms\Components\Placeholder;

class IngredientStockResource extends Resource
{
    protected static ?string $model = IngredientStock::class;

    protected static ?string $navigationGroup = 'Inventory';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $label = 'Stok Bahan';
    protected static ?string $navigationLabel = 'Stok Bahan';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
        ->schema([

            Forms\Components\Select::make('ingredient_id')
                ->relationship('ingredient', 'name')
                ->label('Bahan')
                ->required()
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(function ($state, callable $set) {
                    $ingredient = \App\Models\Ingredient::find($state);

                    if ($ingredient) {
                        $set('unit_id', $ingredient->unit_id);
                    }
                }),


            Forms\Components\Select::make('type')
                ->label('Jenis Transaksi')
                ->options([
                    'in' => 'Stok Masuk (IN)',
                    'out' => 'Stok Keluar (OUT)',
                ])
                ->required()
                ->live() // <— WAJIB supaya bisa re-render
                ->native(false)
                ->helperText('Pilih IN untuk pembelian, OUT untuk pemakaian'),

            Placeholder::make('current_stock')
                ->label('Stok Saat Ini')
                ->content(function (callable $get) {

                    $ingredientId = $get('ingredient_id');

                    if (!$ingredientId) {
                        return '-';
                    }

                    $stock = \App\Models\IngredientStock::where('ingredient_id', $ingredientId)
                        ->selectRaw("
                            SUM(
                                CASE 
                                    WHEN type = 'in' THEN qty
                                    WHEN type = 'out' THEN -qty
                                END
                            ) as total_qty
                        ")
                        ->value('total_qty') ?? 0;

                    $ingredient = \App\Models\Ingredient::with('unit')->find($ingredientId);

                    $unit = $ingredient?->unit?->name ?? '';

                    if ($stock <= 0) {
                        return '⚠️ Stok kosong';
                    }

                    $formattedQty = rtrim(rtrim(number_format($stock, 4, '.', ''), '0'), '.');

                    return "{$formattedQty} {$unit}";

                })
                ->visible(fn ($get) => filled($get('ingredient_id'))),



            Forms\Components\Select::make('unit_id')
                ->relationship('unit', 'name')
                ->label('Satuan')
                ->disabled()
                ->dehydrated() // PENTING: supaya tetap tersimpan
                ->required(),

            Forms\Components\TextInput::make('qty')
                ->numeric()
                ->minValue(0.001)
                ->label('Jumlah')
                ->required(),


            Forms\Components\TextInput::make('price_total')
                ->numeric()
                ->label('Total Harga Pembelian')
                ->visible(fn ($get) => $get('type') === 'in')
                ->required(fn ($get) => $get('type') === 'in')
                ->helperText('Hanya untuk IN')
                ->minValue(0),

            Forms\Components\TextInput::make('note')
                ->label('Catatan')
                ->maxLength(255)
                ->placeholder('Contoh: pembelian 2kg, pemakaian produksi 1 batch')
                ->columnSpanFull(),

        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
        ->columns([

            Tables\Columns\TextColumn::make('ingredient.name')
                ->label('Bahan')
                ->sortable()
                ->searchable(),

            Tables\Columns\BadgeColumn::make('type')
                ->label('Tipe')
                ->colors([
                    'success' => 'in',
                    'danger' => 'out',
                ])
                ->formatStateUsing(function ($state) {
                    return strtoupper($state); // IN / OUT
                }),

            Tables\Columns\TextColumn::make('qty')
                ->label('Qty'),

            Tables\Columns\TextColumn::make('unit.name')
                ->label('Satuan'),

            Tables\Columns\TextColumn::make('price_total')
                ->label('Total Harga')
                ->money('IDR')
                ->visible(fn ($record) => $record?->type === 'in'),

            Tables\Columns\TextColumn::make('price_per_base')
                ->label('Harga Per Base Unit')
                ->numeric(2)
                ->visible(fn ($record) => $record?->type === 'in'),

            Tables\Columns\TextColumn::make('note')
                ->label('Catatan')
                ->wrap()
                ->default('Tidak Ada Note')
                ->toggleable(),


        ])
        ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIngredientStocks::route('/'),
            'create' => Pages\CreateIngredientStock::route('/create'),
            'edit' => Pages\EditIngredientStock::route('/{record}/edit'),
        ];
    }
}
