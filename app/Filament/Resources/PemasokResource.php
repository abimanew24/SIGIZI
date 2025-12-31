<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PemasokResource\Pages;
use App\Models\Pemasok;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PemasokResource extends Resource
{
    protected static ?string $model = Pemasok::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck'; // Ikon Truk
    protected static ?string $navigationLabel = 'Data Pemasok';
    protected static ?string $pluralModelLabel = 'Pemasok';
    protected static ?string $navigationGroup = 'Gudang & Logistik';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profil Perusahaan')
                    ->schema([
                        Forms\Components\TextInput::make('nama_perusahaan')
                            ->label('Nama PT/CV/UD')
                            ->required(),
                        
                        Forms\Components\Select::make('kategori')
                            ->options([
                                'Sembako' => 'Sembako (Beras, Minyak)',
                                'Sayur' => 'Sayur & Buah Segar',
                                'Daging' => 'Daging & Ikan',
                                'Gas' => 'Gas LPG',
                                'Umum' => 'General Supplier',
                            ]),

                        Forms\Components\TextInput::make('termin_pembayaran_hari')
                            ->label('Termin Pembayaran (TOP)')
                            ->numeric()
                            ->suffix('Hari')
                            ->helperText('Isi 0 jika Cash On Delivery (COD)'),
                    ])->columns(2),

                Forms\Components\Section::make('Kontak Person')
                    ->schema([
                        Forms\Components\TextInput::make('nama_kontak')->label('Nama Sales'),
                        Forms\Components\TextInput::make('telepon')->tel(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_perusahaan')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('kategori')->badge(),
                Tables\Columns\TextColumn::make('termin_pembayaran_hari')->label('TOP (Hari)'),
                Tables\Columns\TextColumn::make('telepon'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPemasoks::route('/'),
            'create' => Pages\CreatePemasok::route('/create'),
            'edit' => Pages\EditPemasok::route('/{record}/edit'),
        ];
    }
}