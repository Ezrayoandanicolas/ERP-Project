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
        'price',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Resep (Liza’s Cookies)
    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'product_recipe');
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

}
