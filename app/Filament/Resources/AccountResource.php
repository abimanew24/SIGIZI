<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Models\Account;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Master Akun (COA)';
    protected static ?string $modelLabel = 'Akun Rekening';
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Akun')
                    ->description('Masukkan Kode dan Nama Akun Akuntansi.')
                    ->schema([
                        // Input Kode Akun (1101, etc)
                        Forms\Components\TextInput::make('code')
                            ->label('Kode Akun')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->numeric()
                            ->maxLength(10)
                            ->placeholder('Contoh: 1101'),

                        // Input Nama Akun
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Akun')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Kas Tunai'),

                        // Pilihan Tipe
                        Forms\Components\Select::make('type')
                            ->label('Tipe Akun')
                            ->options([
                                'asset' => 'Aset / Harta (Kas, Inventaris)',
                                'liability' => 'Kewajiban / Hutang',
                                'equity' => 'Modal / Ekuitas',
                                'revenue' => 'Pendapatan',
                                'expense' => 'Beban / Pengeluaran',
                            ])
                            ->required(),

                        // Status Aktif
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Matikan jika akun ini tidak digunakan lagi.'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Akun')
                    ->searchable()
                    ->weight('bold'),

                // Menampilkan Badge warna-warni sesuai tipe
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'asset' => 'Aset',
                        'liability' => 'Kewajiban',
                        'equity' => 'Modal',
                        'revenue' => 'Pendapatan',
                        'expense' => 'Beban',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'revenue',
                        'danger' => 'expense',
                        'warning' => 'liability',
                        'info' => 'asset',
                        'primary' => 'equity',
                    ]),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                // Filter biar gampang cari akun Beban saja, atau Aset saja
                Tables\Filters\SelectFilter::make('type')
                    ->label('Filter Tipe')
                    ->options([
                        'asset' => 'Aset',
                        'liability' => 'Kewajiban',
                        'equity' => 'Modal',
                        'revenue' => 'Pendapatan',
                        'expense' => 'Beban',
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
            ])
            ->defaultSort('code', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            // Nanti kita isi ini dengan Relation Manager (Buku Besar)
            // Setelah Anda membuat file RelationManager-nya.
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        // Hanya Super Admin dan Manajer Ops yang boleh kelola Bagan Akun
        return auth()->user()->hasAnyRole(['super_admin', 'manajer_operasional']);
    }
}