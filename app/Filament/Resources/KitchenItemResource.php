<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KitchenItemResource\Pages;
use App\Models\KitchenItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KitchenItemResource extends Resource
{
    protected static ?string $model = KitchenItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Master Alat & BHP';
    protected static ?string $modelLabel = 'Barang Dapur';
    protected static ?string $navigationGroup = 'Gudang & Logistik';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()->schema([
                    // NAMA BARANG
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Barang')
                        ->required()
                        ->placeholder('Contoh: Tabung Gas 12kg')
                        ->maxLength(255),

                    // SATUAN
                    Forms\Components\TextInput::make('unit')
                        ->label('Satuan')
                        ->placeholder('Pcs / Unit / Tabung')
                        ->required()
                        ->maxLength(50),

                    // KATEGORI (Penting untuk Akuntansi)
                    Forms\Components\Select::make('category')
                        ->label('Kategori')
                        ->options([
                            'consumable' => 'Barang Habis Pakai (Gas, Sabun, Plastik)',
                            'fixed_asset' => 'Aset Tetap (Panci, Kompor, Meja)',
                        ])
                        ->required()
                        ->helperText('Pilih "Habis Pakai" untuk barang rutin belanja, "Aset Tetap" untuk inventaris jangka panjang.'),

                    // HARGA RATA-RATA (Untuk Valuasi jika hilang)
                    Forms\Components\TextInput::make('avg_price')
                        ->label('Estimasi Harga Satuan')
                        ->numeric()
                        ->prefix('Rp')
                        ->required()
                        ->default(0)
                        ->helperText('Digunakan untuk menghitung kerugian jika barang hilang.'),

                    // STOK AWAL
                    Forms\Components\TextInput::make('stock')
                        ->label('Stok Saat Ini')
                        ->numeric()
                        ->default(0)
                        ->helperText('Update otomatis saat ada Opname.'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->colors([
                        'warning' => 'consumable',
                        'success' => 'fixed_asset',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'consumable' => 'Habis Pakai',
                        'fixed_asset' => 'Aset Tetap',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Sisa Stok')
                    ->badge()
                    ->color(fn(int $state): string => $state <= 5 ? 'danger' : 'primary'),

                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan'),

                Tables\Columns\TextColumn::make('avg_price')
                    ->label('Harga/Unit')
                    ->money('IDR'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'consumable' => 'Habis Pakai',
                        'fixed_asset' => 'Aset Tetap',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKitchenItems::route('/'),
            'create' => Pages\CreateKitchenItem::route('/create'),
            'edit' => Pages\EditKitchenItem::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        // Hampir semua level boleh lihat stok, tapi Ahli Gizi/Kepala SPPG biasanya cuma lihat
        return auth()->user()->hasAnyRole(['super_admin', 'staf_logistik', 'kepala_dapur', 'ahli_gizi', 'manajer_operasional', 'kepala_sppg']);
    }

    public static function canCreate(): bool
    {
        // Cuma Logistik dan Admin yang boleh nambah master barang baru
        return auth()->user()->hasAnyRole(['super_admin', 'staf_logistik']);
    }
}