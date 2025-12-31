<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseReturnResource\Pages;
use App\Models\PurchaseReturn;
use App\Models\KitchenItem; // Import Model Barang
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PurchaseReturnResource extends Resource
{
    protected static ?string $model = PurchaseReturn::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square'; // Ikon Retur
    protected static ?string $navigationLabel = 'Retur Pembelian';
    protected static ?string $modelLabel = 'Retur Barang';
    protected static ?string $navigationGroup = 'Gudang & Logistik';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // === BAGIAN ATAS: INFO RETUR ===
                Forms\Components\Group::make()->schema([
                    Forms\Components\TextInput::make('code')
                        ->label('No. Dokumen')
                        ->default('RET-' . date('YmdHis'))
                        ->readOnly()
                        ->required(),

                    Forms\Components\DatePicker::make('date')
                        ->label('Tanggal Retur')
                        ->default(now())
                        ->required(),

                    Forms\Components\Select::make('refund_method')
                        ->label('Metode Pengembalian')
                        ->options([
                            'cash' => 'Uang Tunai (Cash Refund)',
                            'debt_reduction' => 'Potong Hutang (Credit Memo)',
                        ])
                        ->default('debt_reduction')
                        ->required()
                        ->helperText('Pilih "Potong Hutang" jika tagihan belum lunas.'),

                    Forms\Components\Textarea::make('reason')
                        ->label('Alasan Retur')
                        ->placeholder('Contoh: Barang Rusak / Salah Kirim / Expired')
                        ->columnSpanFull(),
                        
                    Forms\Components\Hidden::make('user_id')
                        ->default(fn () => Auth::id()),
                ])->columns(2),

                // === BAGIAN BAWAH: ITEM YANG DIRETUR ===
                Forms\Components\Section::make('Daftar Barang Retur')
                    ->description('Masukkan barang dan jumlah yang dikembalikan ke Supplier.')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                // 1. Pilih Barang
                                Forms\Components\Select::make('kitchen_item_id')
                                    ->label('Barang')
                                    ->options(KitchenItem::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->reactive() // Trigger update harga
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        // Ambil harga beli rata-rata dari master barang
                                        $barang = KitchenItem::find($state);
                                        $set('price_per_unit', $barang?->avg_price ?? 0);
                                        $set('quantity', 1);
                                        $set('total_price', $barang?->avg_price ?? 0);
                                    })
                                    ->columnSpan(3),

                                // 2. Jumlah Retur
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jml')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->reactive() // Trigger hitung total
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        $price = floatval($get('price_per_unit'));
                                        $set('total_price', $state * $price);
                                    }),

                                // 3. Harga Satuan (Read Only)
                                Forms\Components\TextInput::make('price_per_unit')
                                    ->label('Harga Beli (@)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly()
                                    ->dehydrated(), // Tetap simpan ke DB

                                // 4. Subtotal (Read Only)
                                Forms\Components\TextInput::make('total_price')
                                    ->label('Total Nilai')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly()
                                    ->dehydrated(),
                            ])
                            ->columns(6)
                            ->addActionLabel('Tambah Barang')
                            ->defaultItems(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('No. Ref')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Jml Item')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('refund_method')
                    ->label('Metode')
                    ->badge()
                    ->colors([
                        'success' => 'cash',
                        'warning' => 'debt_reduction',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'debt_reduction' => 'Potong Hutang',
                        default => $state,
                    }),

                // Status apakah sudah diproses Jurnal/Stok-nya
                Tables\Columns\IconColumn::make('is_processed')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn (PurchaseReturn $record) => $record->is_processed), // Sembunyikan Edit jika sudah diproses
                
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (PurchaseReturn $record) => $record->is_processed),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseReturns::route('/'),
            'create' => Pages\CreatePurchaseReturn::route('/create'),
            'edit' => Pages\EditPurchaseReturn::route('/{record}/edit'),
        ];
    }
}