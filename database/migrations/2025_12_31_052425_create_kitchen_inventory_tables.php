<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Master Barang Dapur (Panci, Gas, Ompreng)
        Schema::create('kitchen_items', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('unit')->nullable(); // Pcs, Tabung, Unit
            // Kategori penting: 'consumable' (Gas/Sabun) vs 'fixed_asset' (Panci/Kompor)
            $table->enum('category', ['consumable', 'fixed_asset']); 
            $table->integer('stock')->default(0);
            $table->decimal('avg_price', 15, 2)->default(0);
            $table->timestamps();
        });

        // 2. Tabel Header Opname (Bukti Cek Fisik)
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // No Dokumen
            $table->date('date');
            $table->text('note')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->boolean('is_processed')->default(false); // Kunci agar tidak dijurnal 2x
            $table->timestamps();
        });

        // 3. Tabel Detail Opname (Isi barang-barangnya)
        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            
            // Relasi ke Master Barang Dapur
            $table->foreignId('kitchen_item_id')->constrained('kitchen_items');
            
            $table->integer('system_stock')->default(0);   // Stok di komputer
            $table->integer('physical_stock')->default(0); // Stok hasil hitung
            $table->integer('difference')->default(0);     // Selisih
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
        Schema::dropIfExists('kitchen_items');
    }
};