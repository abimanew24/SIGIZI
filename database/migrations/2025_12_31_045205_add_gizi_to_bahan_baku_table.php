<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bahan_baku', function (Blueprint $table) {
            // Nilai gizi per 1 Satuan Dasar (misal per 1 Kg atau per 1 Pcs)
            $table->float('kalori')->default(0)->after('harga_satuan');      // kkal
            $table->float('protein')->default(0)->after('kalori');           // gram
            $table->float('karbohidrat')->default(0)->after('protein');      // gram
            $table->float('lemak')->default(0)->after('karbohidrat');        // gram
        });
    }

    public function down(): void
    {
        Schema::table('bahan_baku', function (Blueprint $table) {
            $table->dropColumn(['kalori', 'protein', 'karbohidrat', 'lemak']);
        });
    }
};