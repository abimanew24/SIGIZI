<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BahanBakuResource\Pages;
use App\Models\BahanBaku;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BahanBakuResource extends Resource
{
    protected static ?string $model = BahanBaku::class;

    // Ikon di sidebar
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    
    // Label di Sidebar
    protected static ?string $navigationLabel = 'Data Bahan Baku';
    protected static ?string $pluralModelLabel = 'Bahan Baku';
    protected static ?string $navigationGroup = 'Gudang & Logistik';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // BAGIAN 1: IDENTITAS BARANG & HARGA
                Forms\Components\Section::make('Informasi Dasar & Harga')
                    ->schema([
                        Forms\Components\TextInput::make('kode_sku')
                            ->label('Kode SKU / Barcode')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('Contoh: RM-001'),
                        
                        Forms\Components\TextInput::make('nama_bahan')
                            ->label('Nama Bahan')
                            ->required()
                            ->placeholder('Contoh: Beras Premium'),

                        Forms\Components\Select::make('kategori')
                            ->options([
                                'Kering' => 'Bahan Kering (Beras, Gula)',
                                'Segar' => 'Bahan Segar (Daging, Ikan)',
                                'Sayur' => 'Sayuran & Buah',
                                'Beku' => 'Frozen Food',
                                'Bumbu' => 'Bumbu & Rempah',
                            ])
                            ->required(),

                        // Input Harga
                        Forms\Components\TextInput::make('harga_satuan')
                            ->label('Harga Per Unit (Estimasi)')
                            ->prefix('Rp')
                            ->numeric()
                            ->default(0)
                            ->required(),

                    ])->columns(2),

                // BAGIAN 2: LOGIKA STOK & KONVERSI
                Forms\Components\Section::make('Pengaturan Stok & Satuan')
                    ->schema([
                        Forms\Components\TextInput::make('satuan_dasar')
                            ->label('Satuan Pakai (Ecer)')
                            ->placeholder('Kg, Liter, Pcs')
                            ->required(),

                        Forms\Components\TextInput::make('faktor_konversi')
                            ->label('Faktor Konversi (Isi 1 jika tidak ada)')
                            ->numeric()
                            ->default(1)
                            ->helperText('Contoh: 1 Karung = 25 Kg, maka isi 25.'),

                        Forms\Components\TextInput::make('masa_simpan_hari')
                            ->label('Masa Simpan (Hari)')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('batas_stok_aman_persen')
                            ->label('Alert Stok Minimum (%)')
                            ->numeric()
                            ->default(10),
                    ])->columns(2),

                // === BAGIAN 3: INFORMASI GIZI (BARU) ===
                // Ini form khusus untuk Ahli Gizi
                Forms\Components\Section::make('Informasi Nilai Gizi (Per Satuan)')
                    ->description('Isi nilai gizi sesuai satuan dasar barang (Misal: Per Kg atau Per Butir)')
                    ->schema([
                        Forms\Components\TextInput::make('kalori')
                            ->label('Energi (Kkal)')
                            ->numeric()
                            ->default(0)
                            ->suffix('kkal'),
                        
                        Forms\Components\TextInput::make('protein')
                            ->label('Protein')
                            ->numeric()
                            ->default(0)
                            ->suffix('gram'),

                        Forms\Components\TextInput::make('karbohidrat')
                            ->label('Karbohidrat')
                            ->numeric()
                            ->default(0)
                            ->suffix('gram'),

                        Forms\Components\TextInput::make('lemak')
                            ->label('Lemak')
                            ->numeric()
                            ->default(0)
                            ->suffix('gram'),
                    ])->columns(4), // Tampil sejajar 4 kolom
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('nama_bahan')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Kering' => 'gray',
                        'Segar' => 'warning',
                        'Sayur' => 'success',
                        'Beku' => 'info',
                        default => 'primary',
                    }),

                Tables\Columns\TextColumn::make('harga_satuan')
                    ->label('Harga/Unit')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('satuan_dasar')
                    ->label('Satuan'),

                Tables\Columns\TextColumn::make('stok_saat_ini')
                    ->label('Stok Gudang')
                    ->numeric()
                    ->sortable()
                    ->color(fn (string $state): string => $state < 10 ? 'danger' : 'success') 
                    ->weight('bold'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->options([
                        'Kering' => 'Kering',
                        'Segar' => 'Segar',
                        'Sayur' => 'Sayur',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBahanBakus::route('/'),
            'create' => Pages\CreateBahanBaku::route('/create'),
            'edit' => Pages\EditBahanBaku::route('/{record}/edit'),
        ];
    }
}