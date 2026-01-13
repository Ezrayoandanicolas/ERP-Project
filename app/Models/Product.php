<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'type',
        'cost_price',
        'manual_cost',
        'sell_price',
        'yield_qty',
        'yield_unit_id',
        'image_url',
    ];

    protected $casts = [
        'price' => 'float',
        'cost_price' => 'float',
        'manual_cost' => 'float',
        'sell_price' => 'float',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function recipes()
    {
        return $this->hasMany(ProductRecipe::class);
    }

    public function yieldUnit()
    {
        return $this->belongsTo(Unit::class, 'yield_unit_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

}
