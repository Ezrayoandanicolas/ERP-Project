<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    protected $fillable = [
        'product_id',
        'store_id',
        'outlet_id',
        'batch_qty',
        'total_output',
        'total_cost',
    ];

    protected $casts = [
        'batch_qty' => 'float',
        'total_output' => 'float',
        'total_cost' => 'float',
    ];

    // Produk yang diproduksi
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Store (toko induk)
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // Outlet tempat produksi
    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    // Daftar bahan yang dipakai
    public function items()
    {
        return $this->hasMany(ProductionItem::class);
    }
}
