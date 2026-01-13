<?php

namespace App\Filament\AdminStore\Resources;

use App\Filament\AdminStore\Resources\UserResource\Pages;
use App\Filament\AdminStore\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\Outlet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Users';
    protected static ?string $navigationGroup = 'Management';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Forms\Form $form): Forms\Form
    {
        $admin = auth()->user(); // admin store

        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required(),

            Forms\Components\TextInput::make('email')
                ->required(),

            Forms\Components\TextInput::make('password')
                ->password()
                ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                ->dehydrated(fn($state) => filled($state))
                ->label('Password (optional)')
                ->required(fn($context) => $context === 'create'),

            // AdminStore hanya boleh membuat CASHIER
            Forms\Components\Hidden::make('role')
                ->default('cashier'),

            // Store otomatis mengikuti store admin
            Forms\Components\Hidden::make('store_id')
                ->default($admin->store_id),

            Forms\Components\Select::make('outlet_id')
                ->label('Outlet')
                ->options(
                    Outlet::where('store_id', $admin->store_id)->pluck('name', 'id')
                )
                ->required()
                ->searchable(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        $admin = auth()->user();

        return $table
            ->modifyQueryUsing(function ($query) use ($admin) {
                // Admin hanya melihat user dari store dia
                return $query->where('store_id', $admin->store_id)
                             ->where('id', '!=', $admin->id); // tidak tampilkan diri sendiri
            })
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('outlet.name')->label('Outlet'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}