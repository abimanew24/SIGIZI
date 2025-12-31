<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PesananPembelian extends Model
{
    protected $table = 'pesanan_pembelian';
    protected $guarded = [];

    // === OTOMATISASI JURNAL AKUNTANSI ===
    protected static function booted()
    {
        // Event: Saat data di-update
        static::updated(function ($po) {
            
            // Cek: Jika status berubah jadi 'Disetujui'
            if ($po->isDirty('status') && $po->status === 'Disetujui') {
                self::catatJurnalOtomatis($po);
            }
        });
    }

    protected static function catatJurnalOtomatis($po)
    {
        // 1. Cari Akun
        $akunPersediaan = Akun::where('kode_akun', '1201')->first(); // Persediaan
        $akunKas = Akun::where('kode_akun', '1101')->first();        // Kas
        
        if (!$akunPersediaan || !$akunKas) return;

        // 2. Tentukan Nominal & Keterangan
        // Kita pakai total_harga yang ada di header PO
        $nominal = $po->total_harga; 
        
        // Buat keterangan dinamis
        // Jika pakai detailPesanan, kita ambil nama barang pertama atau generik
        $namaBarang = 'Berbagai Barang';
        if ($po->bahanBaku) {
             // Kalau Single Item
            $namaBarang = $po->bahanBaku->nama_bahan;
        } elseif ($po->detailPesanan && $po->detailPesanan->first()) {
             // Kalau Multi Item (ambil salah satu contoh)
            $namaBarang = $po->detailPesanan->first()->bahanBaku->nama_bahan . ' dkk';
        }

        // 3. CATAT DEBIT (Stok Nambah)
        Jurnal::create([
            'tanggal' => now(),
            'nomor_referensi' => 'PO-' . $po->id,
            'keterangan' => 'Pembelian Stok: ' . $namaBarang,
            'akun_id' => $akunPersediaan->id,
            'debit' => $nominal,
            'kredit' => 0,
        ]);

        // 4. CATAT KREDIT (Kas Berkurang)
        Jurnal::create([
            'tanggal' => now(),
            'nomor_referensi' => 'PO-' . $po->id,
            'keterangan' => 'Pembayaran ke ' . ($po->pemasok->nama_pemasok ?? 'Supplier'),
            'akun_id' => $akunKas->id,
            'debit' => 0,
            'kredit' => $nominal,
        ]);
    }

    // === RELASI (KEMBALIKAN YANG HILANG) ===
    
    // 1. Jika kamu pakai Multi-Item (Repeater)
    public function detailPesanan(): HasMany
    {
        // Pastikan model DetailPesanan ada, kalau belum ada, buat modelnya
        // atau sesuaikan nama modelnya jika berbeda
        return $this->hasMany(DetailPesanan::class, 'pesanan_pembelian_id');
    }

    // 2. Jika kamu pakai Single-Item (Pilih Bahan di Header)
    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    public function pemasok(): BelongsTo
    {
        return $this->belongsTo(Pemasok::class, 'pemasok_id');
    }
}