<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Akun extends Model
{
    use HasFactory;

    // Ini kuncinya: Biarpun nama filenya Akun, dia tetap baca tabel 'accounts'
    protected $table = 'accounts'; 

    protected $guarded = [];
}