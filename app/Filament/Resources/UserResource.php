<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Store;
use App\Models\Outlet;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Users';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([

            Forms\Components\TextInput::make('name')
                ->label('Name')
                ->required(),

            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->required(),

            Forms\Components\TextInput::make('password')
                ->password()
                ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                ->dehydrated(fn($state) => filled($state))
                ->label('Password (optional)')
                ->required(fn($context) => $context === 'create'),

            // ROLE SELECTOR
            Forms\Components\Select::make('role')
                ->label('Role')
                ->options([
                    'superadmin' => 'Superadmin',
                    'admin_store'      => 'Admin Store',
                    'cashier'    => 'Cashier',
                    'member'     => 'Member',
                ])
                ->reactive() // <--- penting agar UI langsung berubah
                ->required()
                ->afterStateHydrated(function ($component, $state, $record) {
                    if ($record && $record->roles()->exists()) {
                        $component->state($record->roles->first()->name);
                    }
                })
                ->afterStateUpdated(function ($state, $record) {
                    if (! $record) {
                        return;
                    }

                    $currentRole = $record->roles->first()?->name;

                    // ✅ JANGAN sync kalau role tidak berubah
                    if ($currentRole === $state) {
                        return;
                    }

                    $role = Role::where('name', $state)
                        ->where('guard_name', 'web')
                        ->first();

                    if ($role) {
                        $record->syncRoles([$role]);
                    }

                    if ($state !== 'admin_store') {
                        $record->store_id = null;
                    }

                    if ($state !== 'cashier') {
                        $record->outlet_id = null;
                    }

                    $record->save();
                }),


            // STORE SELECTOR (ADMIN ONLY)
            Forms\Components\Select::make('store_id')
                ->label('Store')
                ->options(function () {
                    $auth = auth()->user();

                    // SUPERADMIN → bisa pilih semua store
                    if ($auth->hasRole('superadmin')) {
                        return Store::pluck('name', 'id');
                    }

                    // ADMIN → hanya store dia sendiri
                    if ($auth->hasRole('admin_store')) {
                        return Store::where('id', $auth->store_id)
                            ->pluck('name', 'id');
                    }

                    return [];
                })
                ->visible(fn ($get) => $get('role') === 'admin')
                ->required(fn ($get) => $get('role') === 'admin')
                ->searchable(),


            // OUTLET SELECTOR (CASHIER ONLY)
            // OUTLET SELECTOR (CASHIER ONLY)
            Forms\Components\Select::make('outlet_id')
                ->label('Outlet')
                ->options(function () {
                    $auth = auth()->user();

                    // SUPERADMIN → bisa lihat semua outlet
                    if ($auth->hasRole('superadmin')) {
                        return Outlet::pluck('name', 'id');
                    }

                    // ADMIN → hanya outlet milik STORE dia
                    if ($auth->hasRole('admin_store')) {
                        return Outlet::where('store_id', $auth->store_id)
                            ->pluck('name', 'id');
                    }

                    // CASHIER dan lainnya tidak perlu akses
                    return [];
                })
                ->visible(fn ($get) => $get('role') === 'cashier')
                ->required(fn ($get) => $get('role') === 'cashier')
                ->searchable(),

        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Name'),
                Tables\Columns\TextColumn::make('email')->label('Email'),
                Tables\Columns\TextColumn::make('roles.name')->label('Role'),
                Tables\Columns\TextColumn::make('store.name')
                    ->label('Store')
                    ->formatStateUsing(fn($state) => $state ?? '-'),
                Tables\Columns\TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->formatStateUsing(fn($state) => $state ?? '-'),
            ])
            ->defaultSort('name')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
