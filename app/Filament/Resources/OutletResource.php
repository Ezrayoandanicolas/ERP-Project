<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutletResource\Pages;
use App\Models\Outlet;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;

class OutletResource extends Resource
{
    protected static ?string $model = Outlet::class;

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $label = 'Outlet';
    protected static ?string $navigationLabel = 'Outlet';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('store_id')
                    ->label('Toko Induk')
                    ->relationship('store', 'name')
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('name')
                    ->label('Nama Outlet')
                    ->required(),

                Forms\Components\Toggle::make('status')
                    ->label('Aktif')
                    ->default(true)
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('store.name')
                    ->label('Toko Induk')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Outlet')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('status')
                    ->label('Status')
                    ->boolean(),
            ])
            ->filters([])
            ->defaultSort('store_id', 'asc')
            ->searchable();
    }

    public static function getRelations(): array
    {
        return [
            // Nanti ada relation ke sales dan stock
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOutlets::route('/'),
            'create' => Pages\CreateOutlet::route('/create'),
            'edit' => Pages\EditOutlet::route('/{record}/edit'),
        ];
    }
}
