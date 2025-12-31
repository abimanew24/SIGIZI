<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany; // Tambah ini

class Menu extends Model
{
    use HasFactory;
    protected $table = 'menu';
    protected $guarded = [];

    // --- TAMBAHKAN INI (Solusi Error) ---
    // Kita anggap satu Menu punya BANYAK baris komposisi
    public function komposisi(): HasMany
    {
        return $this->hasMany(KomposisiMenu::class, 'menu_id');
    }

    // Relasi lama (bahanBaku) biarkan saja, mungkin nanti butuh buat report
    public function bahanBaku(): BelongsToMany
    {
        return $this->belongsToMany(BahanBaku::class, 'komposisi_menu', 'menu_id', 'bahan_baku_id')
                    ->withPivot('qty_per_porsi')
                    ->withTimestamps();
    }
}