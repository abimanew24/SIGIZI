<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Master Akun (Chart of Accounts)
        // Kita ganti nama tabel jadi 'accounts' (Inggris) biar standar Laravel
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Contoh: 1101, 5101
            $table->string('name'); // Contoh: Kas Tunai, Persediaan
            // Tipe akun kita buat bahasa Inggris biar gampang dicoding
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->boolean('is_active')->default(true); // Biar bisa non-aktifkan akun lama
            $table->timestamps();
        });

        // 2. Tabel Header Jurnal (Menyimpan Tanggal & Keterangan Transaksi)
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique(); // No Bukti: JE-202412-001
            $table->date('date');
            $table->text('description')->nullable();
            // User ID (nullable biar kalau user dihapus, jurnal gak error)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); 
            $table->timestamps();
        });

        // 3. Tabel Detail Jurnal (Menyimpan Angka Debit/Kredit)
        Schema::create('journal_items', function (Blueprint $table) {
            $table->id();
            // Relasi ke Header Jurnal
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            // Relasi ke Akun
            $table->foreignId('account_id')->constrained('accounts');
            
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Urutan hapus harus dibalik karena ada relasi (Foreign Key)
        Schema::dropIfExists('journal_items');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounts');
    }
};