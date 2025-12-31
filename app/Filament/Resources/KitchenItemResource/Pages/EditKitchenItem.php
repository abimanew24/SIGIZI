<?php

namespace App\Filament\Resources\KitchenItemResource\Pages;

use App\Filament\Resources\KitchenItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKitchenItem extends EditRecord
{
    protected static string $resource = KitchenItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
