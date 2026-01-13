<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Ingredient;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Storage;

use Spatie\ImageOptimizer\OptimizerChainFactory;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $label = 'Products';
    protected static ?string $navigationLabel = 'Products';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('image_url')
                ->label('Product Image (Optional)')
                ->image()
                ->disk('public')
                ->directory('products')
                ->preserveFilenames()
                ->afterStateUpdated(function (?string $state, callable $set) {

                    // ✅ state null / reset
                    if (! $state) {
                        return;
                    }

                    // ✅ tunggu sampai filenya beneran ada (bukan tmp)
                    if (! Storage::disk('public')->exists($state)) {
                        return;
                    }

                    $fullPath = Storage::disk('public')->path($state);

                    // ✅ extra guard
                    if (! is_file($fullPath)) {
                        return;
                    }

                    // ✅ OPTIMIZE (SAFE)
                    try {
                        \Spatie\ImageOptimizer\OptimizerChainFactory::create()
                            ->optimize($fullPath);
                    } catch (\Throwable $e) {
                        \Log::warning('Image optimize failed: '.$e->getMessage());
                    }

                    // ✅ CONVERT TO WEBP (SAFE)
                    $webpPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $fullPath);

                    // kalau sudah webp, skip
                    if ($webpPath !== $fullPath) {
                        try {
                            exec("cwebp -q 80 ".escapeshellarg($fullPath)." -o ".escapeshellarg($webpPath));
                        } catch (\Throwable $e) {
                            \Log::warning('WebP convert failed: '.$e->getMessage());
                        }
                    }

                    // ✅ simpan relative path
                    $relativeWebp = str_replace(
                        Storage::disk('public')->path(''),
                        '',
                        $webpPath
                    );

                    // ✅ SET nilai field (2 arg wajib)
                    $set('image_url', $relativeWebp);
                }),

            Forms\Components\Section::make('Informasi Produk')
                ->schema([
                    Forms\Components\Select::make('store_id')
                        ->relationship('store', 'name')
                        ->label('Toko Induk')
                        ->required()
                        ->preload()
                        ->searchable(),

                    Forms\Components\Select::make('category_id')
                        ->label('Kategori')
                        ->options(function (callable $get) {
                            $storeId = $get('store_id');
                            return $storeId
                                ? \App\Models\Category::where('store_id', $storeId)->orderBy('name')->pluck('name', 'id')
                                : [];
                        })
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->required(),



                    Forms\Components\TextInput::make('name')
                        ->label('Nama Produk')
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

            Forms\Components\Section::make('Unit Produk (Stock)')
                ->visible(fn ($get) => $get('type') === 'stock')
                ->schema([
                    Forms\Components\Select::make('unit_id')
                        ->relationship('yieldUnit', 'name')
                        ->label('Satuan Produk')
                        ->searchable()
                        ->required(),
                ]),

            Forms\Components\Section::make('Yield Produk')
                ->visible(fn ($get) => $get('type') === 'production')
                ->schema([
                    Forms\Components\TextInput::make('yield_qty')
                        ->label('Hasil Resep (Yield)')
                        ->numeric()
                        ->required(),

                    Forms\Components\Select::make('yield_unit_id')
                        ->relationship('yieldUnit', 'name')
                        ->label('Satuan Hasil')
                        ->searchable()
                        ->required(),
                ]),

            Forms\Components\Section::make('Resep Produk')
                ->visible(fn ($get) => $get('type') === 'production')
                ->schema([
                    Forms\Components\Repeater::make('recipes')
                        ->relationship('recipes')
                        ->schema([

                            // ============================
                            // 1. Ingredient
                            // ============================
                            Forms\Components\Select::make('ingredient_id')
                                ->label('Bahan')
                                ->options(Ingredient::pluck('name', 'id'))
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {

                                    if (!$state) return;

                                    $ingredient = Ingredient::find($state);

                                    if ($ingredient) {
                                        // SET Hidden unit_id
                                        $set('unit_id', $ingredient->unit_id);
                                        // SET unit_name untuk tampilan
                                        $set('unit_name', $ingredient->unit->name);
                                    }
                                })
                                ->required(),

                            // ============================
                            // 2. Hidden field: unit_id (Wajib!)
                            // ============================
                            Forms\Components\Hidden::make('unit_id')
                                ->required(),

                            // ============================
                            // 3. Unit Name tampilan
                            // ============================
                            Forms\Components\TextInput::make('unit_name')
                                ->label('Unit')
                                ->disabled()
                                ->dehydrated(false)
                                ->afterStateHydrated(function ($state, callable $set, callable $get) {

                                    $ingredient = Ingredient::find($get('ingredient_id'));

                                    if ($ingredient) {
                                        $set('unit_id', $ingredient->unit_id);       // isi ulang jika edit
                                        $set('unit_name', $ingredient->unit->name); // tampilkan nama unit
                                    }
                                }),

                            // ============================
                            // 4. Qty
                            // ============================
                            Forms\Components\TextInput::make('qty')
                                ->numeric()
                                ->required(),

                        ])
                        ->columnSpanFull()
                        ->createItemButtonLabel('Tambah Bahan'),

                ]),

            Forms\Components\Section::make('Harga Jual')
                ->schema([
                    Forms\Components\TextInput::make('sell_price')
                        ->label('Harga Jual')
                        ->numeric()
                        ->required(),

                    Forms\Components\Placeholder::make('cost_price')
                        ->label('Modal Otomatis')
                        ->content(fn ($record) => $record?->cost_price
                            ? number_format($record->cost_price, 0, ',', '.').' IDR'
                            : '0'),
                ]),

            Forms\Components\Section::make('Varian Produk')
                ->schema([
                    Forms\Components\Repeater::make('variants')
                        ->relationship('variants')
                        ->label('Varian Penjualan')
                        ->schema([

                            Forms\Components\TextInput::make('name')
                                ->label('Nama Varian')
                                ->required()
                                ->placeholder('Contoh: Dimsum 4 pcs'),

                            Forms\Components\TextInput::make('pcs_used')
                                ->label('Pemakaian Stok (pcs)')
                                ->numeric()
                                ->minValue(1)
                                ->required(),

                            Forms\Components\TextInput::make('price')
                                ->label('Harga Jual Varian')
                                ->numeric()
                                ->required(),

                            Forms\Components\Toggle::make('is_active')
                                ->label('Aktif')
                                ->default(true),

                        ])
                        ->columnSpanFull()
                        ->addActionLabel('Tambah Varian')
                        ->reorderable()
                        ->collapsible(),
                ])

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

            Tables\Columns\TextColumn::make('yield_qty')
                ->label('Yield')
                ->formatStateUsing(fn ($state, $record) =>
                    $state && $record->yieldUnit
                    ? "{$state} {$record->yieldUnit->name}"
                    : '-'
                )
                ->default('-')
                ->sortable(),

            Tables\Columns\TextColumn::make('variants.name')
                ->label('Varian')
                ->formatStateUsing(fn ($state, $record) =>
                    $record->variants->pluck('name')->implode(', ') ?: '-'
                )
                ->wrap(),



            Tables\Columns\TextColumn::make('cost_price')
                ->label('Modal Produk')
                ->formatStateUsing(fn ($state) =>
                    number_format($state ?? 0, 0, ',', '.')
                )
                ->sortable(),

            Tables\Columns\TextColumn::make('sell_price')
                ->label('Harga Jual')
                ->formatStateUsing(fn ($state) =>
                    number_format($state ?? 0, 0, ',', '.')
                )
                ->sortable(),

            // ===============================
            //   STOK PER OUTLET
            // ===============================
            Tables\Columns\TextColumn::make('outlet_stocks')
                ->label('Stok per Outlet')
                ->getStateUsing(function ($record) {

                    $totals = [];

                    foreach ($record->stocks as $s) {

                        $qty = (int) $s->qty;

                        switch ($s->type) {

                            case 'in':
                                $totals[$s->outlet_id] = ($totals[$s->outlet_id] ?? 0) + $qty;
                                break;

                            case 'out':
                                $totals[$s->outlet_id] = ($totals[$s->outlet_id] ?? 0) - $qty;
                                break;
                        }
                    }

                    return collect($totals)
                        ->map(function ($qty, $outletId) {
                            $name = \App\Models\Outlet::find($outletId)?->name ?? 'Unknown';
                            return "<div><strong>{$name}</strong>: {$qty}</div>";
                        })
                        ->implode('');
                })
                ->html()
                ->wrap(),

        ])
        ->defaultSort('name', 'asc')
        ->searchable();
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
