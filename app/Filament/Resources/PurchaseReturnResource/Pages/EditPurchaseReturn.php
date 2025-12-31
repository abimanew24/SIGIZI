<?php

namespace App\Filament\Resources\PurchaseReturnResource\Pages;

use App\Filament\Resources\PurchaseReturnResource;
use App\Services\JournalService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class EditPurchaseReturn extends EditRecord
{
    protected static string $resource = PurchaseReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),

            Actions\Action::make('process_return')
                ->label('Proses Retur')
                ->color('danger')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->requiresConfirmation()
                ->modalHeading('Proses Pengembalian Barang?')
                ->modalDescription('Stok gudang akan dikurangi. Jurnal pembalik akan tercatat otomatis.')
                // REVISI: Cek pakai is_processed (bukan status)
                ->visible(fn ($record) => !$record->is_processed) 
                ->action(function ($record) {
                    
                    DB::transaction(function () use ($record) {
                        
                        $totalReturValue = 0;

                        // 1. UPDATE STOK GUDANG
                        foreach ($record->items as $item) {
                            if ($item->kitchenItem) {
                                // REVISI: Pakai 'qty' (karena tabel detail_pesanan kamu pakai qty)
                                // Gunakan intval biar aman
                                $jumlahRetur = intval($item->qty); 
                                
                                if($jumlahRetur > 0) {
                                    $item->kitchenItem->decrement('stock', $jumlahRetur);
                                }
                            }
                            $totalReturValue += $item->subtotal;
                        }

                        // 2. SIAPKAN DATA JURNAL
                        $jurnalTransactions = [
                            // DEBIT: Hutang Dagang (ID 5)
                            [
                                'account_id' => 5, 
                                'debit'      => $totalReturValue, 
                                'credit'     => 0
                            ],
                            // KREDIT: Persediaan (ID 3)
                            [
                                'account_id' => 3, 
                                'debit'      => 0, 
                                'credit'     => $totalReturValue
                            ]
                        ];

                        // 3. PANGGIL SERVICE JURNAL
                        JournalService::createEntry(
                            $record->date,
                            "Retur Pembelian: " . $record->number,
                            $record->number,
                            $jurnalTransactions
                        );

                        // 4. UPDATE STATUS (REVISI: Pakai is_processed)
                        $record->update(['is_processed' => true]); 
                    });

                    Notification::make()
                        ->title('Retur Berhasil Diproses!')
                        ->body('Stok dikurangi & Jurnal tercatat.')
                        ->success()
                        ->send();
                    
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }
}