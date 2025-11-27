<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngredientResource\Pages;
use App\Models\Ingredient;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;

class IngredientResource extends Resource
{
    protected static ?string $model = Ingredient::class;

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $label = 'Bahan';
    protected static ?string $navigationLabel = 'Bahan';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([

            Forms\Components\Select::make('store_id')
                ->relationship('store', 'name')
                ->label('Toko Induk')
                ->required()
                ->searchable(),

            Forms\Components\TextInput::make('name')
                ->label('Nama Bahan')
                ->required(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('store.name')
                ->label('Toko')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('name')
                ->label('Nama Bahan')
                ->sortable()
                ->searchable(),
        ])
        ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIngredients::route('/'),
            'create' => Pages\CreateIngredient::route('/create'),
            'edit' => Pages\EditIngredient::route('/{record}/edit'),
        ];
    }
}
