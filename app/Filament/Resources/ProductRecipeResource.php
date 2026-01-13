<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductRecipeResource\Pages;
use App\Models\ProductRecipe;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;

class ProductRecipeResource extends Resource
{
    protected static ?string $model = ProductRecipe::class;

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static bool $shouldRegisterNavigation = false; // sembunyikan

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
        ->schema([
            Forms\Components\Select::make('product_id')
            ->relationship('product', 'name')
            ->required(),

        Forms\Components\Select::make('ingredient_id')
            ->relationship('ingredient', 'name')
            ->required(),

        Forms\Components\TextInput::make('qty')
            ->numeric()
            ->required(),

        Forms\Components\Select::make('unit_id')
            ->relationship('unit', 'name')
            ->required(),

        Forms\Components\TextInput::make('yield_qty')
            ->numeric(),

        Forms\Components\Select::make('yield_unit_id')
            ->relationship('yieldUnit', 'name'),
    
        
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('product.name')
                ->label('Produk'),

            Tables\Columns\TextColumn::make('ingredient.name')
                ->label('Bahan'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductRecipes::route('/'),
            'create' => Pages\CreateProductRecipe::route('/create'),
            'edit' => Pages\EditProductRecipe::route('/{record}/edit'),
        ];
    }
}
