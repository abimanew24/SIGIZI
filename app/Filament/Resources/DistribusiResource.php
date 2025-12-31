<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistribusiResource\Pages;
use App\Models\Distribusi;
use App\Models\Sekolah;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class DistribusiResource extends Resource
{
    protected static ?string $model = Distribusi::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Distribusi & Masak';
    protected static ?string $navigationGroup = 'Operasional Dapur';

    // === TAMBAHAN PENTING: EAGER LOADING ===
    // Ini yang bikin loading halaman ngebut (Anti N+1 Query)
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['sekolah', 'menu']); // Load data Sekolah & Menu sekalian
    }
    // ======================================

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Surat Jalan')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_surat_jalan')
                            ->default('SJ-' . date('Ymd') . '-' . rand(1000, 9999))
                            ->readOnly()
                            ->required(),
                        
                        // Tanggal Kirim boleh diedit kalau salah input tanggal
                        Forms\Components\DatePicker::make('tanggal_kirim')
                            ->default(now())
                            ->required(),

                        // STATUS
                        Forms\Components\Select::make('status')
                            ->options([
                                'Persiapan' => 'Persiapan (Dapur)',
                                'Dikirim' => 'Sedang Dikirim (Driver)',
                                'Diterima' => 'Sudah Diterima Sekolah',
                            ])
                            ->default('Persiapan')
                            ->required(),

                        Forms\Components\Hidden::make('user_id')->default(fn()=>Auth::id()),
                    ])->columns(3),

                Forms\Components\Section::make('Tujuan & Menu')
                    ->description('Data ini dikunci saat Edit agar stok tidak error')
                    ->schema([
                        Forms\Components\Select::make('sekolah_id')
                            ->label('Sekolah Tujuan')
                            ->options(Sekolah::all()->pluck('nama_sekolah', 'id'))
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => 
                                $set('jumlah_porsi', Sekolah::find($state)?->jumlah_siswa ?? 0)
                            )
                            ->disabledOn('edit'), 

                        Forms\Components\Select::make('menu_id')
                            ->label('Menu Masakan')
                            ->options(Menu::all()->pluck('nama_menu', 'id'))
                            ->searchable()
                            ->required()
                            ->disabledOn('edit'),

                        Forms\Components\TextInput::make('jumlah_porsi')
                            ->label('Jumlah Porsi')
                            ->numeric()
                            ->required()
                            ->disabledOn('edit'),
                            
                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Penerimaan')
                            ->placeholder('Misal: Diterima oleh Pak Budi (Satpam)')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_surat_jalan')->searchable(),
                Tables\Columns\TextColumn::make('tanggal_kirim')->date(),
                Tables\Columns\TextColumn::make('sekolah.nama_sekolah')->searchable(),
                Tables\Columns\TextColumn::make('menu.nama_menu')->label('Menu'),
                Tables\Columns\TextColumn::make('jumlah_porsi')->label('Porsi'),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Persiapan' => 'warning',
                        'Dikirim' => 'info',
                        'Diterima' => 'success',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // Tombol Cetak PDF
                Tables\Actions\Action::make('cetak_pdf')
                    ->label('Cetak SJ')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (Distribusi $record) => route('cetak.surat-jalan', $record->id))
                    ->openUrlInNewTab(),

                // Tombol Update Status
                Tables\Actions\EditAction::make()
                    ->label('Update Status'),
                    
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDistribusis::route('/'),
            'create' => Pages\CreateDistribusi::route('/create'),
            'edit' => Pages\EditDistribusi::route('/{record}/edit'),
        ];
    }
}