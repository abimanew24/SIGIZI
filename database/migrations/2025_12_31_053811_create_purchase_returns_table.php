<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    // 1. Header Retur
    Schema::create('purchase_returns', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique(); // RET-202512-001
        $table->date('date');
        // Opsi: Cash atau Potong Hutang
        $table->enum('refund_method', ['cash', 'debt_reduction'])->default('cash'); 
        $table->text('reason')->nullable(); // Alasan: Rusak/Salah Kirim
        $table->boolean('is_processed')->default(false); // Kunci jurnal
        $table->foreignId('user_id')->constrained('users');
        $table->timestamps();
    });

    // 2. Detail Barang Retur
    Schema::create('purchase_return_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('purchase_return_id')->constrained('purchase_returns')->cascadeOnDelete();
        
        // Kita hubungkan ke Kitchen Item (Alat/Gas) 
        // *Catatan: Jika mau retur Bahan Baku (Sayur), buat kolom bahan_baku_id nullable juga
        $table->foreignId('kitchen_item_id')->constrained('kitchen_items');
        
        $table->integer('quantity');
        $table->decimal('price_per_unit', 15, 2); // Harga saat beli (untuk hitung nilai retur)
        $table->decimal('total_price', 15, 2); // qty * price
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};
