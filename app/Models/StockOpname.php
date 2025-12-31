<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    protected $guarded = [];

    // Relasi ke detail barang (Wajib untuk Repeater Filament)
    public function items()
    {
        return $this->hasMany(StockOpnameItem::class);
    }
}
