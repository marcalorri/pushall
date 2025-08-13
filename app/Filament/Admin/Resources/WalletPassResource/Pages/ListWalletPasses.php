<?php

namespace App\Filament\Admin\Resources\WalletPassResource\Pages;

use App\Filament\Admin\Resources\WalletPassResource;
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
