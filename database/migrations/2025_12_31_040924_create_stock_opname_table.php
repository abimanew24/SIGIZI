<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opname', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            
            // Barang apa yang dicek?
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku');
            
            // Kondisi Stok
            $table->integer('stok_di_sistem'); // Stok menurut komputer sebelum dicek
            $table->integer('stok_fisik');     // Stok nyata yang dihitung manual
            $table->integer('selisih');        // Fisik - Sistem (Bisa minus kalau hilang)
            
            $table->text('keterangan')->nullable(); // Alasan (Hilang, Rusak, dll)
            
            // Siapa yang ngecek
            $table->foreignId('user_id')->constrained('users');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname');
    }
};