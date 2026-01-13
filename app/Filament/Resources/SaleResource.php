<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\Pages\RelationManagers\ItemsRelationManager;
use App\Models\Sale;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;

use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Support\Enums\ActionSize;

use Filament\Infolists;
use Filament\Infolists\Infolist;

use Illuminate\Support\HtmlString;
use App\Services\ThermalPrintService;
use Filament\Notifications\Notification;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationGroup = 'Transactions';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $label = 'Penjualan';
    protected static ?string $navigationLabel = 'Penjualan';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([

            Forms\Components\Select::make('outlet_id')
                ->label('Outlet')
                ->relationship('outlet', 'name')
                ->required()
                ->searchable(),

            Forms\Components\TextInput::make('customer_name')
                ->label('Nama Pembeli')
                ->placeholder('Opsional'),

            Forms\Components\Select::make('payment_method')
                ->label('Metode Pembayaran')
                ->options([
                    'cash' => 'Cash',
                    'qris' => 'QRIS',
                    'transfer' => 'Transfer',
                    'credit' => 'Credit / Utang',
                ])
                ->required()
                ->default('cash'),

            Forms\Components\TextInput::make('total')
                ->label('Total')
                ->numeric()
                ->readOnly()
                ->default(0),
                
            Forms\Components\Textarea::make('note')
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

            Tables\Columns\TextColumn::make('customer_name')
                ->label('Pembeli')
                ->sortable(),

            Tables\Columns\TextColumn::make('payment_method')
                ->label('Pembayaran')
                ->formatStateUsing(fn($state) => strtoupper($state)),

            Tables\Columns\TextColumn::make('total')
                ->label('Total')
                ->money('IDR')
                ->sortable(),

            Tables\Columns\TextColumn::make('discount')
                ->label('Diskon')
                ->money('IDR')
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Tanggal')
                ->dateTime()
                ->sortable(),
        ])
        ->defaultSort('id', 'desc')
        ->searchable()
        ->actions([
            // Tombol Detail (Modal)
            ViewAction::make()
                ->label('Detail')
                ->color('info')
                ->modalHeading('Detail Penjualan'),

            // Tombol Print
            Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->color('success')
                // Menjalankan print langsung tanpa pindah halaman
                ->action(function (Sale $record) {
                    try {
                        // Memanggil service thermal Anda
                        ThermalPrintService::printSale($record);

                        Notification::make()
                            ->title('Print Berhasil')
                            ->body('Struk sedang dicetak.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Print Gagal')
                            ->body('Terjadi kesalahan: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            // 'create' => Pages\CreateSale::route('/create'),
            // 'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Transaksi')
                    ->schema([
                        Infolists\Components\TextEntry::make('customer_name')->label('Customer'),
                        Infolists\Components\TextEntry::make('payment_method')->badge()->formatStateUsing(fn($state) => strtoupper($state)),
                        Infolists\Components\TextEntry::make('created_at')->label('Waktu Transaksi')->dateTime(),
                    ])->columns(3),

                Infolists\Components\Section::make('Item Belanja')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('product.name')
                                    ->label('Produk')
                                    ->formatStateUsing(function ($record) {
                                        $productName = $record->product->name;
                                        $variantName = $record->variant?->name;
                                        $pcs = $record->variant?->pcs_used;

                                        if ($variantName) {
                                            return new HtmlString("
                                                <div class='font-medium text-gray-950 dark:text-white'>{$productName}</div>
                                                <div class='text-xs text-gray-500'>Varian: {$variantName} ({$pcs} pcs)</div>
                                            ");
                                        }

                                        return $productName;
                                    }),

                                Infolists\Components\TextEntry::make('qty')
                                    ->label('Jml'),
                                    // ->alignCenter(),

                                Infolists\Components\TextEntry::make('price')
                                    ->label('Harga Satuan')
                                    ->money('IDR'),

                                Infolists\Components\TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money('IDR')
                                    ->weight('bold'),
                            ])
                            ->columns(4)
                    ]),

                Infolists\Components\Section::make('Ringkasan Biaya')
                    ->schema([
                        Infolists\Components\TextEntry::make('discount')->label('Potongan Diskon')->money('IDR')->color('danger'),
                        Infolists\Components\TextEntry::make('total')->label('Grand Total')->money('IDR')->weight('bold')->size('lg'),
                    ])->columns(2),
            ]);
    }

}
