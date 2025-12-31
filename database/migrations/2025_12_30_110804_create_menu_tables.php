<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Menu (Induk)
        // Harus dibuat DULUAN sebelum tabel 'distribusi' memanggilnya
        Schema::create('menu', function (Blueprint $table) {
            $table->id(); // Ini UnsignedBigInteger (Cocok dengan foreignId)
            $table->string('nama_menu'); 
            $table->integer('urutan_hari_siklus')->default(1); 
            $table->enum('target_usia', ['SD_7-9', 'SD_10-12', 'SMP']);
            
            // Kolom Gizi
            $table->decimal('total_energi_kkal', 8, 2)->default(0);
            $table->decimal('protein_gram', 8, 2)->default(0);
            $table->decimal('lemak_gram', 8, 2)->default(0);
            $table->decimal('karbohidrat_gram', 8, 2)->default(0);
            
            $table->boolean('validasi_ahli_gizi')->default(false);
            $table->timestamps();
        });

        // 2. Tabel Komposisi (Pivot / Anak)
        Schema::create('komposisi_menu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menu')->onDelete('cascade');
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku')->onDelete('cascade');
            $table->decimal('qty_per_porsi', 10, 4); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komposisi_menu');
        Schema::dropIfExists('menu');
    }
};