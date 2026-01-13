<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRecipe extends Model
{
    protected $table = 'product_recipe';

    protected $fillable = [
        'product_id',
        'ingredient_id',
        'qty',
        'unit_id',
        'yield_qty',
        'yield_unit_id',
    ];

    protected $casts = [
        'qty'        => 'float',
        'yield_qty'  => 'float',
    ];

    protected static function booted()
    {
        static::saving(function ($recipe) {
            if ($recipe->unit_id != $recipe->ingredient->unit_id) {
                throw new \Exception("Unit resep tidak boleh berbeda dari unit stok bahan: " . $recipe->ingredient->name);
            }
        });
    }


    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function yieldUnit()
    {
        return $this->belongsTo(Unit::class, 'yield_unit_id');
    }
}
