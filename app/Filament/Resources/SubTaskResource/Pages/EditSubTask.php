<?php

namespace App\Filament\Resources\SubTaskResource\Pages;

use App\Filament\Resources\SubTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubTask extends EditRecord
{
    protected static string $resource = SubTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
