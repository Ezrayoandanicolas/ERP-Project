<?php

namespace App\Filament\Cashier\Resources;

use App\Filament\Cashier\Resources\ExpenseResource\Pages;
use App\Filament\Cashier\Resources\ExpenseResource\RelationManagers;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $label = 'Pengeluaran';
    protected static ?string $navigationLabel = 'Pengeluaran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('outlet_id')
                    ->relationship(
                        'outlet',
                        'name',
                        modifyQueryUsing: fn ($query) =>
                            auth()->user()->outlet_id
                                ? $query->where('id', auth()->user()->outlet_id)
                                : $query
                    )
                    ->default(fn () => auth()->user()->outlet_id)
                    ->required()
                    ->disabled()
                    ->dehydrated(true),

                Forms\Components\DatePicker::make('tanggal')
                    ->required()
                    ->default(now()),

                Forms\Components\TextInput::make('kategori')
                    ->required()
                    ->default('Bahan Baku')
                    ->placeholder('Contoh: Listrik, Gas, Bahan Baku')
                    ->maxLength(100),

                Forms\Components\Textarea::make('keterangan')
                    ->required()
                    ->rows(3)
                    ->placeholder('Jelaskan detail pengeluaran, misalnya pembayaran token listrik bulan Januari')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('jumlah')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                Forms\Components\Select::make('metode_pembayaran')
                    ->options([
                        'Cash' => 'Cash',
                        'Transfer' => 'Transfer',
                        'QRIS' => 'QRIS',
                    ])
                    ->default('Cash')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->searchable(),

                Tables\Columns\TextColumn::make('kategori')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('keterangan')
                    ->limit(40)
                    ->wrap()
                    ->placeholder('Tidak ada keterangan'),

                Tables\Columns\TextColumn::make('jumlah')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('metode_pembayaran')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('outlet_id')
                    ->relationship('outlet', 'name'),

                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('tanggal', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('tanggal', '<=', $data['until']));
                    }),
            ])
            ->defaultSort('tanggal', 'desc')
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
