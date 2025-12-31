<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Header Pesanan (PO)
        Schema::create('pesanan_pembelian', function (Blueprint $table) {
            $table->id();
            // Format Nomor PO (PO-202501-001) kita generate di kodingan nanti
            $table->string('nomor_po')->unique(); 
            
            $table->foreignId('pemasok_id')->constrained('pemasok');
            $table->foreignId('user_id')->constrained('users'); // Siapa yang buat
            
            $table->date('tanggal_pesan');
            $table->enum('status', ['Draft', 'Menunggu_Acc', 'Disetujui', 'Ditolak', 'Selesai', 'Batal'])
                  ->default('Draft');
            
            $table->decimal('total_harga', 15, 2)->default(0); // Bisa simpan triliunan
            $table->text('catatan')->nullable();
            
            $table->timestamps();
        });

        // 2. Tabel Detail Barang (Keranjang)
        Schema::create('detail_pesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_pembelian_id')->constrained('pesanan_pembelian')->onDelete('cascade');
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku');
            
            $table->integer('qty');
            $table->decimal('harga_satuan', 15, 2); // Harga per kg saat beli
            $table->decimal('subtotal', 15, 2); // qty * harga_satuan
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pesanan');
        Schema::dropIfExists('pesanan_pembelian');
    }
};