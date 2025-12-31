<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    use HasFactory;
    
    // Wajib definisikan nama tabel karena Bahasa Indonesia
    protected $table = 'sekolah';
    protected $guarded = [];
}