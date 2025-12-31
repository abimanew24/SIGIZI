<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JournalItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'journalItems';

    protected static ?string $title = 'Buku Besar (Mutasi Transaksi)';
    protected static ?string $icon = 'heroicon-o-clipboard-document-list';

    public function form(Form $form): Form
    {
        return $form->schema([
            // Kita buat Read Only saja, karena edit harus lewat Jurnal Induk
            Forms\Components\TextInput::make('debit')
                ->prefix('Rp')
                ->numeric(),
            Forms\Components\TextInput::make('credit')
                ->prefix('Rp')
                ->numeric(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                // Mengambil Tanggal dari Header Jurnal (Parent)
                Tables\Columns\TextColumn::make('journalEntry.date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                // No Bukti
                Tables\Columns\TextColumn::make('journalEntry.reference_number')
                    ->label('No. Ref')
                    ->searchable(),

                // Keterangan Transaksi
                Tables\Columns\TextColumn::make('journalEntry.description')
                    ->label('Keterangan')
                    ->wrap() // Biar teks panjang turun ke bawah
                    ->limit(60),

                // Angka Debit
                Tables\Columns\TextColumn::make('debit')
                    ->money('IDR')
                    ->color('danger') // Merah biar jelas
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Debit')),

                // Angka Kredit
                Tables\Columns\TextColumn::make('credit')
                    ->money('IDR')
                    ->color('success') // Hijau
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Kredit')),
            ])
            ->defaultSort('created_at', 'desc') // Transaksi terbaru di atas
            ->filters([
                // Filter Tanggal (Opsional, biar bisa cek per bulan)
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (Builder $query, $date) => $query->whereHas('journalEntry', fn ($q) => $q->whereDate('date', '>=', $date)),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query, $date) => $query->whereHas('journalEntry', fn ($q) => $q->whereDate('date', '<=', $date)),
                            );
                    })
            ])
            ->headerActions([
                // Tidak perlu tombol Create di sini
            ])
            ->actions([
                // Tombol lihat detail jurnal aslinya
                Tables\Actions\Action::make('Lihat Jurnal')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => \App\Filament\Resources\JournalEntryResource::getUrl('edit', ['record' => $record->journal_entry_id])),
            ])
            ->bulkActions([
                //
            ]);
    }
}