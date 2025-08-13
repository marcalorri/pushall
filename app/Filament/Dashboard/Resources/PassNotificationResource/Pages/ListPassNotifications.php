<?php

namespace App\Filament\Dashboard\Resources\PassNotificationResource\Pages;

use App\Filament\Dashboard\Resources\PassNotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPassNotifications extends ListRecords
{
    protected static string $resource = PassNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
