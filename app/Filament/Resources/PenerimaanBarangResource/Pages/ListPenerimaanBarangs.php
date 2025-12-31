<?php

namespace App\Filament\Resources\PenerimaanBarangResource\Pages;

use App\Filament\Resources\PenerimaanBarangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenerimaanBarangs extends ListRecords
{
    protected static string $resource = PenerimaanBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
