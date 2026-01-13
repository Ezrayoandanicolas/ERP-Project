<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'tanggal',
        'kategori',
        'keterangan',
        'jumlah',
        'metode_pembayaran',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
}
