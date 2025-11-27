<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'outlet_id',
        'product_id',
        'qty',
        'type',
        'note',
        'target_outlet',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function target()
    {
        return $this->belongsTo(Outlet::class, 'target_outlet');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
