<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPenerimaan extends Model
{
    protected $table = 'detail_penerimaan';
    protected $guarded = [];

    // === MAGIC LOGIC: AUTO UPDATE STOK ===
    protected static function booted()
    {
        // Saat data detail dibuat (created), jalankan ini:
        static::created(function ($detail) {
            // Cari Bahan Baku terkait
            $bahan = BahanBaku::find($detail->bahan_baku_id);
            if ($bahan) {
                // Tambah Stoknya
                $bahan->stok_saat_ini += $detail->qty_diterima;
                $bahan->save();
            }
        });

        // (Opsional) Kalau data dihapus, stok dikurangi lagi (Undo)
        static::deleted(function ($detail) {
            $bahan = BahanBaku::find($detail->bahan_baku_id);
            if ($bahan) {
                $bahan->stok_saat_ini -= $detail->qty_diterima;
                $bahan->save();
            }
        });
    }

    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }
}