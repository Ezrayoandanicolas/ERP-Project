<?php

namespace App\Filament\Resources;

use App\Models\SaleItem;
use App\Models\Product;
use App\Services\StockService;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Notifications\Notification;

class SaleItemResource extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Item Belanja';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_id')
                ->label('Produk')
                ->relationship('product', 'name')
                ->required()
                ->searchable()
                ->live(),

            Forms\Components\TextInput::make('qty')
                ->label('Qty')
                ->numeric()
                ->minValue(1)
                ->required()
                ->live(),

            Forms\Components\TextInput::make('price')
                ->label('Harga Satuan')
                ->numeric()
                ->required()
                ->live(),

            Forms\Components\TextInput::make('subtotal')
                ->label('Subtotal')
                ->numeric()
                ->readOnly()
                ->dehydrated(true)
                ->afterStateUpdated(function ($state, callable $set, $get) {
                    $qty = $get('qty') ?? 0;
                    $set('subtotal', $qty * ($state ?? 0));
                })
                ->reactive(),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('product.name')
                ->label('Produk')
                ->sortable(),

            Tables\Columns\TextColumn::make('qty')
                ->label('Qty'),

            Tables\Columns\TextColumn::make('price')
                ->label('Harga')
                ->money('IDR'),

            Tables\Columns\TextColumn::make('subtotal')
                ->label('Subtotal')
                ->money('IDR'),
        ]);
    }

    protected function afterCreate($record): void
    {
        $sale = $this->ownerRecord;

        StockService::processSale(
            sale: $sale,
            productId: $record->product_id,
            qty: $record->qty,
        );

        // Update total
        $sale->total = $sale->items()->sum('subtotal');
        $sale->save();

        Notification::make()
            ->title('Item Ditambahkan')
            ->success()
            ->send();
    }
}
