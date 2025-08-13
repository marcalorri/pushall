<?php

namespace App\Filament\Dashboard\Resources\WalletPassResource\Pages;

use App\Filament\Dashboard\Resources\WalletPassResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWalletPasses extends ListRecords
{
    protected static string $resource = WalletPassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
