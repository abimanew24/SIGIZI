<?php

namespace App\Filament\Resources\StockOpnameResource\Pages;

use App\Filament\Resources\StockOpnameResource;
use App\Services\JournalService; // Pastikan Service ini ada (dari langkah awal)
use App\Models\KitchenItem;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditStockOpname extends EditRecord
{
    protected static string $resource = StockOpnameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                // Jangan boleh hapus kalau sudah diposting ke akuntansi
                ->hidden(fn ($record) => $record->is_processed),

            // TOMBOL INTEGRASI
            Actions\Action::make('save_adjustment')
                ->label('Simpan & Update Stok')
                ->color('success') // Warna Hijau
                ->icon('heroicon-o-check-badge')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Posting?')
                ->modalDescription('Stok di Master Barang akan diupdate sesuai Fisik. Selisih akan dicatat ke Jurnal Akuntansi otomatis.')
                ->visible(fn ($record) => !$record->is_processed) // Tombol hilang kalau sudah diproses
                ->action(function ($record) {
                    
                    $jurnalTransactions = [];
                    $totalAdjustment = 0;
                    
                    // 1. Loop semua barang yang di-opname
                    foreach($record->items as $item) {
                        $barang = KitchenItem::find($item->kitchen_item_id);
                        if(!$barang) continue;

                        // Update Stok Master Real-time
                        $barang->stock = $item->physical_stock; 
                        $barang->save();

                        // Cek Selisih untuk Akuntansi
                        $selisihQty = $item->difference; // Misal: -2 (Hilang 2)
                        
                        if($selisihQty != 0) {
                            $nilaiUang = abs($selisihQty) * $barang->avg_price;
                            
                            // Tentukan Kode Akun Beban
                            // Jika Consumable (Gas) -> Beban Perlengkapan (5202)
                            // Jika Aset (Panci) -> Beban Kerusakan/Kehilangan (5299)
                            // Catatan: Pastikan kode akun ini ada di Master Akun (COA)
                            $akunBeban = ($barang->category == 'consumable') ? '5202' : '5299';
                            $akunAset = '1201'; // Kode Akun Perlengkapan/Peralatan Dapur

                            if ($selisihQty < 0) {
                                // BARANG KURANG (Expense Debit, Asset Kredit)
                                $jurnalTransactions[] = ['code' => $akunBeban, 'debit' => $nilaiUang, 'credit' => 0];
                                $jurnalTransactions[] = ['code' => $akunAset, 'debit' => 0, 'credit' => $nilaiUang];
                            } else {
                                // BARANG LEBIH (Asset Debit, Pendapatan Lain Kredit)
                                $jurnalTransactions[] = ['code' => $akunAset, 'debit' => $nilaiUang, 'credit' => 0];
                                $jurnalTransactions[] = ['code' => '4201', 'debit' => 0, 'credit' => $nilaiUang];
                            }
                        }
                    }

                    // 2. Catat ke Jurnal (Jika ada selisih)
                    if(count($jurnalTransactions) > 0) {
                        JournalService::createEntry(
                            $record->date,
                            "Selisih Opname: " . $record->code,
                            auth()->id(),
                            $jurnalTransactions
                        );
                    }

                    // 3. Kunci Dokumen Opname
                    $record->update(['is_processed' => true]);

                    Notification::make()
                        ->title('Berhasil!')
                        ->body('Stok telah diupdate & Jurnal tercatat.')
                        ->success()
                        ->send();
                    
                    // Refresh halaman agar tombol hilang
                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $record]));
                }),
        ];
    }
}