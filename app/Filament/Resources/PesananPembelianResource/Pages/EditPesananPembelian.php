<?php

namespace App\Filament\Resources\PesananPembelianResource\Pages;

use App\Filament\Resources\PesananPembelianResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPesananPembelian extends EditRecord
{
    protected static string $resource = PesananPembelianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
