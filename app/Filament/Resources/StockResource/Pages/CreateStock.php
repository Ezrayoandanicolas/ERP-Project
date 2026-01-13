<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use App\Models\Stock;
use App\Models\Product;
use App\Services\StockService;
use App\Services\ProductStockService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateStock extends CreateRecord
{
    protected static string $resource = StockResource::class;

    protected function handleRecordCreation(array $data): Stock
    {
        if ($data['type'] === 'transfer') {

            $current = StockService::getOutletStock(
                $data['outlet_id'],
                $data['product_id']
            );

            if ($data['qty'] > $current) {
                Notification::make()
                    ->title('Stok tidak cukup!')
                    ->body("Sisa stok: {$current}")
                    ->danger()
                    ->send();

                $this->halt();
            }

            // ✅ 1️⃣ CATAT TRANSFER (LOG)
            $transfer = Stock::create([
                'outlet_id'  => $data['outlet_id'],          // asal
                'product_id' => $data['product_id'],
                'qty'        => $data['qty'],
                'unit_id'    => $data['unit_id'],
                'type'       => 'transfer',
                'note'       => $data['note'] ?: 'Transfer ke outlet ' . $data['target_outlet'],
                'target_outlet' => $data['target_outlet'], // kalau ada kolomnya
            ]);

            // ✅ 2️⃣ PANGGIL SERVICE → buat OUT + IN
            StockService::transfer(
                fromOutlet: $data['outlet_id'],
                toOutlet: $data['target_outlet'],
                productId: $data['product_id'],
                qty: $data['qty'],
                unitId: $data['unit_id'],
            );

            // ✅ 3️⃣ Filament pegang RECORD TRANSFER
            return $transfer;
        }

        // ✅ IN / OUT normal
        return Stock::create($data);
    }



    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Untuk stok keluar dan transfer: cek stok
        if ($data['type'] !== 'in') {
            $current = StockService::getOutletStock($data['outlet_id'], $data['product_id']);

            if ($data['qty'] > $current) {
                Notification::make()
                    ->title('Stok tidak cukup!')
                    ->body("Sisa stok: {$current}")
                    ->danger()
                    ->send();

                $this->halt();
            }
        }

        // Untuk stok masuk, wajib isi harga total
        if ($data['type'] === 'in') {
            if (!isset($data['price_total']) || $data['price_total'] <= 0) {
                Notification::make()
                    ->title('Harga Tidak Valid')
                    ->body("Harga total wajib diisi untuk stok masuk.")
                    ->danger()
                    ->send();

                $this->halt();
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        try {
            StockService::process($this->record);

            Notification::make()
                ->title('Berhasil')
                ->body('Stok berhasil diproses')
                ->success()
                ->send();

        } catch (\Exception $e) {

            Notification::make()
                ->title('Error Stok')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
