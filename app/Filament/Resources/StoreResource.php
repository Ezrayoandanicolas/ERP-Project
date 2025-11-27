<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoreResource\Pages;
use App\Models\Store;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Tables\Filters\SelectFilter;

class StoreResource extends Resource
{
    protected static ?string $model = Store::class;

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $label = 'Toko Induk';
    protected static ?string $navigationLabel = 'Toko Induk';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Toko')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('type')
                    ->label('Tipe Toko')
                    ->options([
                        'production' => 'Production (Pakai Resep)',
                        'stock' => 'Stock Biasa',
                    ])
                    ->required(),

                Forms\Components\Toggle::make('status')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Toko')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn ($state) => $state === 'production' ? 'Production' : 'Stock')
                    ->colors([
                        'success' => 'production',
                        'warning' => 'stock',
                    ])
                    ->sortable(),

                Tables\Columns\IconColumn::make('status')
                    ->boolean()
                    ->label('Status')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'production' => 'Production',
                        'stock' => 'Stock',
                    ])
            ])
            ->searchable()
            ->defaultSort('name', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            // TODO: Tambah OutletRelationManager kalau mau
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStores::route('/'),
            'create' => Pages\CreateStore::route('/create'),
            'edit' => Pages\EditStore::route('/{record}/edit'),
        ];
    }
}
