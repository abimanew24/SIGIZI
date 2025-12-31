<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SekolahResource\Pages;
use App\Models\Sekolah;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SekolahResource extends Resource
{
    protected static ?string $model = Sekolah::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap'; // Ikon Topi Toga
    protected static ?string $navigationLabel = 'Data Sekolah';
    protected static ?string $pluralModelLabel = 'Sekolah';
    protected static ?string $navigationGroup = 'Operasional Dapur';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas Sekolah')
                    ->schema([
                        Forms\Components\TextInput::make('nama_sekolah')
                            ->label('Nama Sekolah')
                            ->required()
                            ->placeholder('Contoh: SD Negeri 1 Kota'),

                        Forms\Components\TextInput::make('jumlah_siswa')
                            ->label('Jumlah Siswa')
                            ->numeric()
                            ->default(0)
                            ->suffix('Siswa'),

                        Forms\Components\TimePicker::make('jam_makan')
                            ->label('Jam Makan Siang')
                            ->seconds(false), // Tidak butuh detik
                    ])->columns(3),

                Forms\Components\Section::make('Lokasi & Koordinat')
                    ->description('Penting untuk pelacakan pengiriman (Gunakan Format Desimal)')
                    ->schema([
                        Forms\Components\Textarea::make('alamat_lengkap')
                            ->columnSpanFull(),
                            
                        // PERBAIKAN: Tambah Validasi Latitude (-90 s/d 90)
                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->minValue(-90)
                            ->maxValue(90)
                            ->required()
                            ->placeholder('Contoh: -7.4301 (Pakai Titik)'),
                            
                        // PERBAIKAN: Tambah Validasi Longitude (-180 s/d 180)
                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->minValue(-180)
                            ->maxValue(180)
                            ->required()
                            ->placeholder('Contoh: 109.2345 (Pakai Titik)'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_sekolah')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('jumlah_siswa')->label('Total Siswa')->sortable(),
                Tables\Columns\TextColumn::make('jam_makan')->label('Jam Makan'),
                Tables\Columns\TextColumn::make('alamat_lengkap')->limit(30),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSekolahs::route('/'),
            'create' => Pages\CreateSekolah::route('/create'),
            'edit' => Pages\EditSekolah::route('/{record}/edit'),
        ];
    }
}