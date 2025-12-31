<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PesananPembelianResource\Pages;
use App\Models\PesananPembelian;
use App\Models\BahanBaku;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class PesananPembelianResource extends Resource
{
    protected static ?string $model = PesananPembelian::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?int $navigationSort = 1; 
    protected static ?string $navigationLabel = 'Belanja Bahan (PO)';
    protected static ?string $pluralModelLabel = 'Purchase Order';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['pemasok']); 
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pesanan')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_po')
                            ->default('PO-' . date('Ymd') . '-' . rand(100, 999))
                            ->readOnly()
                            ->required(),

                        Forms\Components\Select::make('pemasok_id')
                            ->relationship('pemasok', 'nama_perusahaan')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\DatePicker::make('tanggal_pesan')
                            ->default(now())
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'Draft' => 'Draft (Konsep)',
                                'Menunggu_Acc' => 'Ajukan Approval',
                                'Disetujui' => 'Disetujui (Approved)',
                                'Selesai' => 'Selesai (Barang Diterima)',
                                'Batal' => 'Batalkan',
                            ])
                            ->default('Draft')
                            ->required()
                            // GEMBOK STATUS: Hanya level atas yang bisa merubah status ke 'Disetujui'
                            ->disabled(fn () => !auth()->user()->hasAnyRole(['super_admin', 'manajer_operasional', 'kepala_sppg'])),

                        Forms\Components\Hidden::make('user_id')
                            ->default(fn() => Auth::id()),
                    ])->columns(2),

                Forms\Components\Section::make('Daftar Barang Belanjaan')
                    ->schema([
                        Forms\Components\Repeater::make('detailPesanan')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('bahan_baku_id')
                                    ->label('Pilih Bahan')
                                    ->options(BahanBaku::all()->pluck('nama_bahan', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(
                                        fn($state, Set $set) =>
                                        $set('satuan', BahanBaku::find($state)?->satuan_dasar ?? '-')
                                    )
                                    ->columnSpan(3),

                                Forms\Components\TextInput::make('satuan')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('qty')
                                    ->numeric()
                                    ->default(1)
                                    ->reactive()
                                    ->afterStateUpdated(
                                        fn($state, Get $get, Set $set) =>
                                        $set('subtotal', (int) $state * (int) ($get('harga_satuan') ?? 0))
                                    )
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('harga_satuan')
                                    ->label('Harga/Satuan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->reactive()
                                    ->afterStateUpdated(
                                        fn($state, Get $get, Set $set) =>
                                        $set('subtotal', (int) ($get('qty') ?? 0) * (int) $state)
                                    )
                                    ->columnSpan(3),

                                Forms\Components\TextInput::make('subtotal')
                                    ->numeric()
                                    ->readOnly()
                                    ->prefix('Rp')
                                    ->columnSpan(3),
                            ])
                            ->columns(12)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateTotals($get, $set);
                            }),
                    ]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('total_harga')
                            ->label('TOTAL TAGIHAN')
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly()
                            ->extraInputAttributes(['style' => 'font-size: 1.5rem; font-weight: bold; color: green']),
                    ]),
            ]);
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        $items = $get('detailPesanan');
        $sum = 0;

        if ($items) {
            foreach ($items as $item) {
                $qty = (int) ($item['qty'] ?? 0);
                $harga = (int) ($item['harga_satuan'] ?? 0);
                $sum += ($qty * $harga);
            }
        }

        $set('total_harga', $sum);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_po')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('pemasok.nama_perusahaan')->searchable(),
                Tables\Columns\TextColumn::make('tanggal_pesan')->date(),
                Tables\Columns\TextColumn::make('total_harga')->money('IDR'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Draft' => 'gray',
                        'Menunggu_Acc' => 'warning',
                        'Disetujui' => 'success',
                        'Ditolak' => 'danger',
                        'Selesai' => 'info',
                        default => 'secondary'
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPesananPembelians::route('/'),
            'create' => Pages\CreatePesananPembelian::route('/create'),
            'edit' => Pages\EditPesananPembelian::route('/{record}/edit'),
        ];
    }

    // =========================================================================
    // HAK AKSES (ROLE PERMISSIONS) - GEMBOK KEAMANAN
    // =========================================================================

    public static function canViewAny(): bool
    {
        // Siapa yang boleh melihat menu PO? Semua kecuali Ahli Gizi (biasanya gizi cuma menu)
        return auth()->user()->hasAnyRole(['super_admin', 'staf_logistik', 'kepala_dapur', 'manajer_operasional', 'kepala_sppg']);
    }

    public static function canCreate(): bool
    {
        // Hanya Logistik dan Kepala Dapur (Request) yang boleh buat PO
        return auth()->user()->hasAnyRole(['super_admin', 'staf_logistik', 'kepala_dapur']);
    }

    public static function canEdit(Model $record): bool
    {
        // Semua level operasional & admin bisa edit (untuk proses ACC)
        return auth()->user()->hasAnyRole(['super_admin', 'staf_logistik', 'manajer_operasional', 'kepala_sppg']);
    }

    public static function canDelete(Model $record): bool
    {
        // Sangat berbahaya menghapus data belanja, kunci hanya untuk Super Admin
        return auth()->user()->hasRole('super_admin');
    }
}