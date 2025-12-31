<?php

namespace App\Filament\Widgets;

use App\Models\PesananPembelian;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class TrenPengeluaranChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Pengeluaran (7 Hari Terakhir)';
    protected static ?int $sort = 2; // Tampil di urutan kedua (bawah kartu stats)
    protected static string $color = 'danger'; // Merah (karena uang keluar)

    protected function getData(): array
    {
        // 1. Ambil Data PO yang Disetujui 7 Hari Terakhir
        $data = Trend::model(PesananPembelian::class)
            ->between(
                start: now()->subDays(7),
                end: now(),
            )
            ->perDay()
            ->sum('total_harga'); // Jumlahkan total belanja

        // 2. Format Data untuk Grafik
        return [
            'datasets' => [
                [
                    'label' => 'Total Belanja (Rp)',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->format('d M')),
        ];
    }

    protected function getType(): string
    {
        return 'line'; // Pilih model garis (bisa ganti 'bar', 'bubble', dll)
    }
}