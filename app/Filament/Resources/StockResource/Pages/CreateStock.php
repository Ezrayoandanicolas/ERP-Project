<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use App\Models\Stock;
use App\Services\StockService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateStock extends CreateRecord
{
    protected static string $resource = StockResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Validasi stok (prevent stok minus)
        if ($data['type'] !== 'in') {
            $current = StockService::getOutletStock($data['outlet_id'], $data['product_id']);

            if ($data['qty'] > $current) {
                Notification::make()
                    ->title('Stok tidak cukup!')
                    ->body("Stok tersisa hanya: {$current}")
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
