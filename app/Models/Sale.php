<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'outlet_id',
        'customer_name',
        'payment_method',
        'total',
        'note',
        'discount',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
