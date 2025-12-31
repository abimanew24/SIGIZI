<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JournalEntryResource\Pages;
use App\Models\JournalEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;

class JournalEntryResource extends Resource
{
    protected static ?string $model = JournalEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Jurnal Umum';
    protected static ?string $modelLabel = 'Transaksi Jurnal';
    protected static ?string $navigationGroup = 'Keuangan';
    
    protected static ?int $navigationSort = 1; 

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Transaksi')
                    ->schema([
                        Forms\Components\TextInput::make('reference')
                            ->label('No. Referensi')
                            ->default('JE-' . date('Ymd-His'))
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal Transaksi')
                            ->default(now())
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan / Deskripsi')
                            ->required()
                            ->placeholder('Contoh: Pembayaran Listrik Bulan Desember')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Detail Jurnal (Debit / Kredit)')
                    ->description('Pastikan Total Debit dan Kredit seimbang (Balance).')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('account_id')
                                    ->label('Akun')
                                    ->relationship('account', 'name')
                                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->code} - {$record->name}")
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('debit')
                                    ->label('Debit')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(onBlur: true),

                                Forms\Components\TextInput::make('credit')
                                    ->label('Kredit')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(onBlur: true),
                            ])
                            ->columns(4)
                            ->defaultItems(2)
                            ->addActionLabel('Tambah Baris Akun')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateTotals($get, $set);
                            }),
                        
                        Forms\Components\Group::make()->schema([
                            Forms\Components\TextInput::make('total_debit')
                                ->label('Total Debit')
                                ->disabled()
                                ->dehydrated(false)
                                ->prefix('Rp'),
                                
                            Forms\Components\TextInput::make('total_credit')
                                ->label('Total Kredit')
                                ->disabled()
                                ->dehydrated(false)
                                ->prefix('Rp'),
                        ])->columns(2),
                    ]),
            ]);
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        $items = $get('items') ?? [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($items as $item) {
            $totalDebit += floatval($item['debit'] ?? 0);
            $totalCredit += floatval($item['credit'] ?? 0);
        }

        $set('total_debit', number_format($totalDebit, 2));
        $set('total_credit', number_format($totalCredit, 2));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('reference')
                    ->label('No. Ref')
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('items_sum_debit')
                    ->sum('items', 'debit')
                    ->label('Total Nilai')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJournalEntries::route('/'),
            'create' => Pages\CreateJournalEntry::route('/create'),
            'edit' => Pages\EditJournalEntry::route('/{record}/edit'),
        ];
    }

    // =========================================================================
    // HAK AKSES (ROLE PERMISSIONS) - GEMBOK KEAMANAN
    // =========================================================================

    public static function canViewAny(): bool
    {
        // PENTING: Gunakan 'manajer_operasional' sesuai tabel roles yang kita buat
        return auth()->user()->hasAnyRole(['super_admin', 'manajer_operasional', 'kepala_sppg']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'manajer_operasional']);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'manajer_operasional']);
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasRole('super_admin');
    }
}