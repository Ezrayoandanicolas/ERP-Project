<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'store_id',
        'name',
        'unit_id',        // satuan utama bahan
        'price_per_unit', // harga per 1 unit
    ];

    protected $casts = [
        'price_per_unit' => 'float',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);  // FIX: relasi yang benar
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_recipe');
    }

    public function recipes()
    {
        return $this->hasMany(ProductRecipe::class);
    }

    public function stocks()
    {
        return $this->hasMany(IngredientStock::class);
    }

    public function getTotalStockAttribute()
    {
        return $this->stocks->sum(fn ($s) =>
            $s->type === 'in' ? $s->qty : -$s->qty
        );
    }
}
