<?php

namespace App\Filament\Resources\PosSystemResource\Pages;

use App\Filament\Resources\PosSystemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPosSystems extends ListRecords
{
    protected static string $resource = PosSystemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
