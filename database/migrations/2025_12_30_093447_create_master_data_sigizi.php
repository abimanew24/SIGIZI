<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Sekolah
        Schema::create('sekolah', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sekolah');
            $table->text('alamat_lengkap')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('jumlah_siswa')->default(0);
            $table->time('jam_makan')->nullable();
            $table->timestamps();
        });

        // 2. Tabel Pemasok
        // 2. Tabel Pemasok (REVISI)
        Schema::create('pemasok', function (Blueprint $table) {
            $table->id();
            $table->string('nama_perusahaan');
            $table->string('kategori')->nullable();
            
            // KITA TAMBAHKAN KOLOM YANG HILANG DI SINI:
            $table->string('nama_kontak')->nullable(); // Dulu gak ada
            $table->string('telepon')->nullable();     // Dulu namanya 'kontak_telepon'
            
            $table->integer('termin_pembayaran_hari')->default(0);
            $table->timestamps();
        });

        // 3. Tabel Bahan Baku
        Schema::create('bahan_baku', function (Blueprint $table) {
            $table->id();
            $table->string('kode_sku')->unique();
            $table->string('nama_bahan');
            $table->enum('kategori', ['Kering', 'Segar', 'Sayur', 'Beku', 'Bumbu', 'Lainnya']);
            $table->string('satuan_dasar');
            $table->decimal('faktor_konversi', 10, 2)->default(1);
            $table->integer('masa_simpan_hari')->default(0);
            $table->integer('batas_stok_aman_persen')->default(10);
            $table->timestamps();
        });

        // 4. Update Tabel Users (Tambah Kolom Jabatan)
        Schema::table('users', function (Blueprint $table) {
            $table->enum('jabatan', ['super_admin', 'kepala_sppg', 'manajer_ops', 'ahli_gizi', 'staf_gudang', 'driver'])
                  ->default('staf_gudang')->after('email');
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sekolah');
        Schema::dropIfExists('pemasok');
        Schema::dropIfExists('bahan_baku');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['jabatan', 'is_active']);
        });
    }
};