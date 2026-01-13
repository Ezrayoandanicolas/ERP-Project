<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionItem extends Model
{
    protected $fillable = [
        'production_id',
        'ingredient_id',
        'unit_id',
        'qty_used',
        'cost',
    ];

    protected $casts = [
        'qty_used' => 'float',
        'cost'     => 'float',
    ];

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
