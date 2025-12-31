<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribusi', function (Blueprint $table) {
            $table->id();
            
            // Nomor Surat Jalan (Unik, misal: SJ-20250101-001)
            $table->string('nomor_surat_jalan')->unique(); 
            
            // Relasi ke tabel Sekolah (Kirim ke mana?)
            $table->foreignId('sekolah_id')->constrained('sekolah')->onDelete('cascade');
            
            // Relasi ke tabel Menu (Masak apa?)
            $table->foreignId('menu_id')->constrained('menu')->onDelete('cascade');
            
            // Relasi ke User (Siapa admin/driver yang input?)
            $table->foreignId('user_id')->constrained('users'); 
            
            $table->date('tanggal_kirim');
            $table->integer('jumlah_porsi')->default(0); // Nanti otomatis ambil dari jumlah siswa
            
            // Status pengiriman
            $table->enum('status', ['Persiapan', 'Dikirim', 'Diterima', 'Batal'])->default('Persiapan');
            $table->text('catatan')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribusi');
    }
};