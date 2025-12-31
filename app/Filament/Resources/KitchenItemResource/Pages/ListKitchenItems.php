<?php

namespace App\Filament\Resources\KitchenItemResource\Pages;

use App\Filament\Resources\KitchenItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKitchenItems extends ListRecords
{
    protected static string $resource = KitchenItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
