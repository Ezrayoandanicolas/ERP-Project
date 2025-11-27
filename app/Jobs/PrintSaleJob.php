<?php

namespace App\Jobs;

use App\Models\Sale;
use App\Services\ThermalPrintService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class PrintSaleJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $saleId) {}

    public function handle(): void
    {
        $sale = Sale::with(['items.product', 'outlet'])->find($this->saleId);
        ThermalPrintService::printSale($sale);
    }
}
