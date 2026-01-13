<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'outlet_id',     // OUTLET ASAL (Boleh null jika stok masuk dari luar)
        'product_id',
        'unit_id',
        'qty',
        'type',          // in / out / transfer
        'target_outlet', // OUTLET TUJUAN (jika transfer)
        'cost_price',
        'price_total',
        'note',
    ];

    protected $casts = [
        'qty' => 'float',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    // Jika pengiriman ke outlet lain
    public function target()
    {
        return $this->belongsTo(Outlet::class, 'target_outlet');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

}
