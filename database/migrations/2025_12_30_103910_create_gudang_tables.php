<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. UPDATE TABEL BAHAN BAKU (Tambah kolom stok)
        Schema::table('bahan_baku', function (Blueprint $table) {
            // Kita tambah kolom stok_saat_ini jika belum ada
            if (!Schema::hasColumn('bahan_baku', 'stok_saat_ini')) {
                $table->decimal('stok_saat_ini', 15, 2)->default(0)->after('satuan_dasar');
            }
        });

        // 2. TABEL PENERIMAAN BARANG (Header)
        Schema::create('penerimaan_barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_pembelian_id')->constrained('pesanan_pembelian');
            $table->foreignId('user_id')->constrained('users'); // Siapa yang terima
            $table->string('nomor_surat_jalan')->nullable(); // Dari kertas supplier
            $table->date('tanggal_terima')->default(now());
            $table->timestamps();
        });

        // 3. TABEL DETAIL PENERIMAAN (Isi Barang)
        Schema::create('detail_penerimaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerimaan_barang_id')->constrained('penerimaan_barang')->onDelete('cascade');
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku');
            
            $table->decimal('qty_diterima', 15, 2);
            $table->enum('kondisi', ['Baik', 'Rusak', 'Kurang'])->default('Baik');
            $table->text('catatan')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_penerimaan');
        Schema::dropIfExists('penerimaan_barang');
        Schema::table('bahan_baku', function (Blueprint $table) {
            $table->dropColumn('stok_saat_ini');
        });
    }
};