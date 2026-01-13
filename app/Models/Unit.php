<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'base_slug',
        'multiplier',
    ];

    protected $casts = [
        'multiplier' => 'float',
    ];

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_slug', 'slug');
    }

    public function toBaseMultiplier(): float
    {
        return (float) $this->multiplier;
    }
}
