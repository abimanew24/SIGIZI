<?php

namespace App\Filament\Widgets;

use App\Models\BahanBaku;
use App\Models\Distribusi;
use App\Models\PesananPembelian;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    // Mengatur agar widget ini update otomatis setiap 15 detik (Realtime-ish)
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        // 1. HITUNG STOK KRITIS (Kurang dari 10 unit)
        $stokKritis = BahanBaku::where('stok_saat_ini', '<', 10)->count();

        // 2. HITUNG PENGELUARAN BULAN INI (Dari PO yang Disetujui)
        $pengeluaranBulanIni = PesananPembelian::where('status', 'Disetujui')
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('total_harga');

        // 3. HITUNG PORSI DIKIRIM HARI INI
        $porsiHariIni = Distribusi::whereDate('tanggal_kirim', Carbon::today())
            ->sum('jumlah_porsi');

        return [
            // KARTU 1: PENGELUARAN
            Stat::make('Belanja Bulan Ini', 'Rp ' . number_format($pengeluaranBulanIni, 0, ',', '.'))
                ->description('Total PO Disetujui')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('danger') // Merah karena uang keluar
                ->chart([7, 2, 10, 3, 15, 4, 17]), // Grafik hiasan

            // KARTU 2: PORSI MAKANAN
            Stat::make('Porsi Dikirim Hari Ini', $porsiHariIni . ' Porsi')
                ->description('Total distribusi ke sekolah')
                ->descriptionIcon('heroicon-m-truck')
                ->color('success') // Hijau
                ->chart([10, 20, 15, 30, 20]),

            // KARTU 3: PERINGATAN STOK
            Stat::make('Stok Menipis', $stokKritis . ' Item')
                ->description('Segera lakukan Restock!')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($stokKritis > 0 ? 'danger' : 'success'), // Merah kalau ada yg kritis
        ];
    }
}