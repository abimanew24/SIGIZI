<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AkunSeeder extends Seeder
{
    public function run(): void
    {
        $akun = [
            // KEPALA 1: ASET (Harta)
            ['kode_akun' => '1101', 'nama_akun' => 'Kas Tunai', 'tipe' => 'Aset'],
            ['kode_akun' => '1102', 'nama_akun' => 'Bank BCA', 'tipe' => 'Aset'],
            ['kode_akun' => '1201', 'nama_akun' => 'Persediaan Bahan Baku', 'tipe' => 'Aset'], 
            
            // KEPALA 2: KEWAJIBAN (Utang)
            ['kode_akun' => '2101', 'nama_akun' => 'Utang Usaha', 'tipe' => 'Kewajiban'],

            // KEPALA 5: BEBAN (Biaya)
            ['kode_akun' => '5101', 'nama_akun' => 'Beban Pokok Makanan (HPP)', 'tipe' => 'Beban'],
            ['kode_akun' => '5201', 'nama_akun' => 'Beban Operasional', 'tipe' => 'Beban'],
        ];

        DB::table('akun')->insert($akun);
    }
}