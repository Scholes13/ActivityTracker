<?php

namespace App\Filament\Resources\TaskNotificationResource\Pages;

use App\Filament\Resources\TaskNotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaskNotifications extends ListRecords
{
    protected static string $resource = TaskNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
