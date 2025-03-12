<?php

namespace App\Filament\Resources\PosSystemResource\Pages;

use App\Filament\Resources\PosSystemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPosSystem extends EditRecord
{
    protected static string $resource = PosSystemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
