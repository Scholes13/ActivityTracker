<?php

namespace App\Filament\Resources\TaskNotificationResource\Pages;

use App\Filament\Resources\TaskNotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaskNotification extends EditRecord
{
    protected static string $resource = TaskNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
