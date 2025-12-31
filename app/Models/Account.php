<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $guarded = [];

    // Helper untuk menampilkan "1101 - Kas Tunai" di Dropdown Filament
    public function getCodeNameAttribute()
    {
        return "{$this->code} - {$this->name}";
    }
}