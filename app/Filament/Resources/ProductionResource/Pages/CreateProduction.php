<?php

namespace App\Filament\Resources\ProductionResource\Pages;

use App\Filament\Resources\ProductionResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Product;
use App\Services\ProductionService;
use Illuminate\Database\Eloquent\Model;

class CreateProduction extends CreateRecord
{
    protected static string $resource = ProductionResource::class;

    /**
     * Gunakan ProductionService untuk membuat produksi.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $product = Product::find($data['product_id']);

        // 🔥 PROSES PRODUKSI (potong stok bahan + tambah stok produk)
        $production = ProductionService::produce(
            product: $product,
            batchQty: $data['batch_qty'],
            storeId: $data['store_id'],
            outletId: $data['outlet_id']
        );

        return $production; // wajib return Model
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
