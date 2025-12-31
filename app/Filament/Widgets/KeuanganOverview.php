<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\JournalItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KeuanganOverview extends BaseWidget
{
    protected static ?int $sort = 1; // Tampil paling atas

    protected function getStats(): array
    {
        // 1. Hitung Saldo Kas (Kode Akun 1101 - Sesuaikan dengan data Anda)
        // Rumus: Total Debit - Total Kredit (Karena Kas adalah Aset, saldo normal Debit)
        $kasAccount = Account::where('code', '1101')->first();
        $saldoKas = 0;
        
        if($kasAccount) {
            $debit = JournalItem::where('account_id', $kasAccount->id)->sum('debit');
            $credit = JournalItem::where('account_id', $kasAccount->id)->sum('credit');
            $saldoKas = $debit - $credit;
        }

        // 2. Hitung Total Beban Bulan Ini (Kode Kepala 5)
        $totalBeban = JournalItem::whereHas('account', function($q) {
                $q->where('code', 'like', '5%'); // Semua akun depannya 5
            })
            ->whereHas('journalEntry', function($q) {
                $q->whereMonth('date', now()->month); // Hanya bulan ini
            })
            ->sum('debit');

        return [
            Stat::make('Saldo Kas Tunai', 'Rp ' . number_format($saldoKas, 0, ',', '.'))
                ->description('Uang fisik tersedia')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Pengeluaran Bulan Ini', 'Rp ' . number_format($totalBeban, 0, ',', '.'))
                ->description('Total akun Beban (5xxx)')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger'),
                
            // Anda bisa tambah widget lain, misal Total Nilai Stok
        ];
    }
}