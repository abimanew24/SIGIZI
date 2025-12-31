<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KomposisiMenu extends Model
{
    protected $table = 'komposisi_menu';
    protected $guarded = [];

    // Relasi ke Bahan Baku (supaya bisa ambil nama bahannya nanti)
    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }
}