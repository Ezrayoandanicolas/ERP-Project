<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'name',
        'type',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function outlets()
    {
        return $this->hasMany(Outlet::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }
}
