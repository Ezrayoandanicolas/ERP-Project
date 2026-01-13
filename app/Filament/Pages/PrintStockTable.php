<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Product;
use App\Models\Store;
use App\Models\Category;
use Livewire\Attributes\Computed;

class PrintStockTable extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-printer';
    protected static ?string $navigationLabel = 'Cetak Tabel Stok';
    protected static ?string $title = 'Cetak Tabel Stok Manual';
    protected static string $view = 'filament.pages.print-stock-table';

    public ?int $storeId = null;
    public ?int $outletId = null; 
    public ?int $categoryId = null;

    public function products()
    {
        return Product::query()
            ->when($this->storeId, fn ($q) =>
                $q->where('store_id', $this->storeId)
            )
            ->when($this->outletId, fn ($q) =>
                $q->where('outlet_id', $this->outletId)
            )
            ->when($this->categoryId, fn ($q) =>
                $q->where('category_id', $this->categoryId)
            )
            ->orderBy('name')
            ->get();
    }

    public function updatedStoreId()
    {
        $this->categoryId = null;
    }

    public function updatedOutletId()
    {
        $this->categoryId = null;
    }

    public function getProductsProperty()
    {
        return Product::query()
            ->when($this->storeId, fn ($q) =>
                $q->where('store_id', $this->storeId)
            )
            ->when($this->outletId, fn ($q) =>
                $q->where('outlet_id', $this->outletId)
            )
            ->when($this->categoryId, fn ($q) =>
                $q->where('category_id', $this->categoryId)
            )
            ->orderBy('name')
            ->get();
    }


    public function getStoresProperty()
    {
        return Store::orderBy('name')->get();
    }

    public function getCategoriesProperty()
    {
        return Category::query()
            ->when($this->storeId, fn ($q) =>
                $q->where('store_id', $this->storeId)
            )
            ->when($this->outletId, fn ($q) =>
                $q->where('outlet_id', $this->outletId)
            )
            ->orderBy('name')
            ->get();
    }

}
