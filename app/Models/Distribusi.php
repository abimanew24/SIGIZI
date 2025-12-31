<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Distribusi extends Model
{
    protected $table = 'distribusi';
    protected $guarded = [];

    // === MAGIC LOGIC: POTONG STOK OTOMATIS ===
    protected static function booted()
    {
        // 1. Saat data Distribusi dibuat, stok berkurang
        static::created(function ($distribusi) {
            self::kurangiStok($distribusi);
        });

        // 2. Saat data dihapus, stok balik lagi (Undo)
        static::deleted(function ($distribusi) {
            self::kembalikanStok($distribusi);
        });
    }

    // Fungsi: Hitung resep & kurangi stok gudang
    protected static function kurangiStok($distribusi)
    {
        $menu = $distribusi->menu;
        $jumlahPorsi = $distribusi->jumlah_porsi;

        // Cek apakah menu ada & punya bahan baku
        if ($menu && $menu->bahanBaku) {
            foreach ($menu->bahanBaku as $bahan) {
                // Ambil takaran per siswa dari tabel pivot (komposisi_menu)
                $takaran = $bahan->pivot->qty_per_porsi;
                
                // Hitung total pakai: Takaran x Jumlah Siswa
                $totalPakai = $takaran * $jumlahPorsi;

                // Update stok di database
                $bahan->stok_saat_ini -= $totalPakai;
                $bahan->save();
            }
        }
    }

    // Fungsi: Balikin stok kalau salah input/dihapus
    protected static function kembalikanStok($distribusi)
    {
        $menu = $distribusi->menu;
        $jumlahPorsi = $distribusi->jumlah_porsi;

        if ($menu && $menu->bahanBaku) {
            foreach ($menu->bahanBaku as $bahan) {
                $takaran = $bahan->pivot->qty_per_porsi;
                $totalPakai = $takaran * $jumlahPorsi;

                $bahan->stok_saat_ini += $totalPakai;
                $bahan->save();
            }
        }
    }

    // === RELASI KE TABEL LAIN ===
    public function sekolah(): BelongsTo 
    { 
        return $this->belongsTo(Sekolah::class); 
    }
    
    public function menu(): BelongsTo 
    { 
        return $this->belongsTo(Menu::class); 
    }
    
    public function user(): BelongsTo 
    { 
        return $this->belongsTo(User::class); 
    }
}