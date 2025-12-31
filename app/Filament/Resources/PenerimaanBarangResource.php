<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenerimaanBarangResource\Pages;
use App\Models\PenerimaanBarang;
use App\Models\PesananPembelian;
use App\Models\BahanBaku;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PenerimaanBarangResource extends Resource
{
    protected static ?string $model = PenerimaanBarang::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Penerimaan Gudang';
    protected static ?string $pluralModelLabel = 'Barang Masuk';
    protected static ?string $navigationGroup = 'Gudang & Logistik';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Surat Jalan')
                    ->schema([
                        // Pilih PO yang sudah disetujui saja
                        Forms\Components\Select::make('pesanan_pembelian_id')
                            ->label('Nomor PO')
                            ->options(PesananPembelian::where('status', 'Disetujui')->pluck('nomor_po', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('nomor_surat_jalan')
                            ->label('No. Surat Jalan (Dari Vendor)')
                            ->required(),

                        Forms\Components\DatePicker::make('tanggal_terima')
                            ->default(now())
                            ->required(),

                        Forms\Components\Hidden::make('user_id')->default(fn() => Auth::id()),
                    ])->columns(2),

                Forms\Components\Section::make('Barang yang Diterima')
                    ->schema([
                        Forms\Components\Repeater::make('detailPenerimaan')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('bahan_baku_id')
                                    ->label('Bahan Baku')
                                    ->options(BahanBaku::all()->pluck('nama_bahan', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('qty_diterima')
                                    ->label('Jumlah Diterima')
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(1),

                                Forms\Components\Select::make('kondisi')
                                    ->options([
                                        'Baik' => 'Baik',
                                        'Rusak' => 'Rusak (Retur)',
                                        'Kurang' => 'Kurang Qty',
                                    ])
                                    ->default('Baik')
                                    ->required()
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->addActionLabel('Scan Barang Lain'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pesananPembelian.nomor_po')->label('Ref PO')->searchable(),
                Tables\Columns\TextColumn::make('nomor_surat_jalan')->searchable(),
                Tables\Columns\TextColumn::make('tanggal_terima')->date(),
                Tables\Columns\TextColumn::make('detail_penerimaan_count')->counts('detailPenerimaan')->label('Item'),
            ])
            ->actions([
                // View saja, jangan Edit kalau sudah masuk stok biar aman
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenerimaanBarangs::route('/'),
            'create' => Pages\CreatePenerimaanBarang::route('/create'),
            // Kita matikan edit agar data stok tidak kacau (SOP Gudang Ketat)
            // 'edit' => Pages\EditPenerimaanBarang::route('/{record}/edit'), 
        ];
    }
}