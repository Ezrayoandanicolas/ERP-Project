<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\ThermalServices; // Sesuaikan namespace service Anda
use Illuminate\Http\Request;
use Filament\Notifications\Notification;

class ThermalServicesController extends Controller
{
    public function __invoke(Sale $sale, ThermalServices $thermalService)
    {
        try {
            // Panggil method cetak di service Anda
            // Asumsi service Anda punya method seperti printReceipt($sale)
            $thermalService->printReceipt($sale);

            Notification::make()
                ->title('Berhasil')
                ->body('Struk sedang dicetak...')
                ->success()
                ->send();

            return back();
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Mencetak')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return back();
        }
    }
}