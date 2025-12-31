<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Models\Menu;
use App\Models\BahanBaku; 
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Katalog Menu & Resep';
    protected static ?string $pluralModelLabel = 'Data Menu';
    protected static ?string $navigationGroup = 'Operasional Dapur';

    // === EAGER LOADING (Anti Lemot) ===
    // Load data Resep beserta Gizi Bahan Bakunya sekaligus
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['komposisi.bahanBaku']); 
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // KIRI: Data Menu
                Forms\Components\Section::make('Identitas Menu')
                    ->schema([
                        Forms\Components\TextInput::make('nama_menu')
                            ->required()
                            ->placeholder('Misal: Sop Ayam Klaten'),
                        
                        Forms\Components\Select::make('urutan_hari_siklus')
                            ->label('Jadwal Siklus (Hari Ke-)')
                            ->options(array_combine(range(1, 10), range(1, 10))) // Angka 1-10
                            ->required(),
                        
                        Forms\Components\Select::make('target_usia')
                            ->options([
                                'SD_7-9' => 'SD Kelas Bawah (7-9 Thn)',
                                'SD_10-12' => 'SD Kelas Atas (10-12 Thn)',
                                'SMP' => 'SMP',
                            ])
                            ->required(),
                        
                        Forms\Components\FileUpload::make('foto')
                            ->image()
                            ->directory('menu-images')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('deskripsi')
                            ->columnSpanFull(),
                    ])->columns(2),

                // TENGAH: Repeater Bahan Baku (Fitur Utama)
                Forms\Components\Section::make('Komposisi Resep')
                    ->description('Masukkan bahan baku dan takaran per 1 siswa')
                    ->schema([
                        Forms\Components\Repeater::make('komposisi') 
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('bahan_baku_id')
                                    ->label('Pilih Bahan')
                                    ->options(BahanBaku::all()->pluck('nama_bahan', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(2),
                                
                                Forms\Components\TextInput::make('qty_per_porsi')
                                    ->label('Takaran (Kg/Ltr per Siswa)')
                                    ->numeric()
                                    ->placeholder('0.05')
                                    ->required()
                                    ->columnSpan(1),
                            ])
                            ->columns(3)
                            ->addActionLabel('Tambah Bahan Lain'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')->circular(),
                
                Tables\Columns\TextColumn::make('nama_menu')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('target_usia')
                    ->badge(),

                // --- KOLOM KALKULASI GIZI OTOMATIS ---
                // Menghitung Total Kalori dari semua bahan
                Tables\Columns\TextColumn::make('total_kalori')
                    ->label('Total Kalori')
                    ->state(function (Menu $record): string {
                        $total = 0;
                        if ($record->komposisi) {
                            foreach ($record->komposisi as $item) {
                                if ($item->bahanBaku) {
                                    // Rumus: Jumlah Pakai x Kalori Bahan
                                    $total += ($item->qty_per_porsi * $item->bahanBaku->kalori);
                                }
                            }
                        }
                        return number_format($total, 0) . ' kkal';
                    })
                    ->badge()
                    ->color('warning'), // Warna Oranye biar eye-catching

                // Menghitung Detail Protein, Karbo, Lemak
                Tables\Columns\TextColumn::make('detail_gizi')
                    ->label('Detail Nutrisi')
                    ->state(function (Menu $record): string {
                        $prot = 0; $karb = 0; $lem = 0;
                        
                        if ($record->komposisi) {
                            foreach ($record->komposisi as $item) {
                                if ($item->bahanBaku) {
                                    $prot += ($item->qty_per_porsi * $item->bahanBaku->protein);
                                    $karb += ($item->qty_per_porsi * $item->bahanBaku->karbohidrat);
                                    $lem  += ($item->qty_per_porsi * $item->bahanBaku->lemak);
                                }
                            }
                        }
                        // Tampilkan format: P: 10g | K: 50g | L: 5g
                        return "P: " . number_format($prot,1) . "g | K: " . number_format($karb,1) . "g | L: " . number_format($lem,1) . "g";
                    })
                    ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall) // Huruf kecil
                    ->wrap(), 
                // -------------------------------------

                Tables\Columns\TextColumn::make('urutan_hari_siklus')
                    ->label('Hari Ke')
                    ->alignCenter(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}