<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    use HasFactory;

    // KARENA NAMA TABEL KITA INDONESIA, KITA HARUS DEKLARASIKAN:
    protected $table = 'bahan_baku';

    // Izinkan semua kolom diisi (biar gak error Mass Assignment)
    protected $guarded = [];
}