<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientStock extends Model
{
    protected $table = 'ingredient_stocks';

    protected $fillable = [
        'ingredient_id',
        'qty',
        'type',
        'unit_id',
        'price_total',
        'price_per_base',
        'note',
    ];

    protected $casts = [
        'qty' => 'float',
        'price_total' => 'float',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
