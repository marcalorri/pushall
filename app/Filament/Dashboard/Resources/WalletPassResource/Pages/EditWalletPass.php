<?php

namespace App\Filament\Dashboard\Resources\WalletPassResource\Pages;

use App\Filament\Dashboard\Resources\WalletPassResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWalletPass extends EditRecord
{
    protected static string $resource = WalletPassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $meta = $this->record->meta ?? [];
        $data['welcome_title'] = $meta['welcome']['title'] ?? null;
        $data['welcome_message'] = $meta['welcome']['message'] ?? null;
        $data['welcome_button_text'] = $meta['welcome']['button']['text'] ?? null;
        $data['welcome_button_url'] = $meta['welcome']['button']['url'] ?? null;
        $data['primary_color'] = $meta['theme']['primaryColor'] ?? null;
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $meta = [
            'welcome' => [
                'title' => $data['welcome_title'] ?? null,
                'message' => $data['welcome_message'] ?? null,
                'button' => [
                    'text' => $data['welcome_button_text'] ?? null,
                    'url' => $data['welcome_button_url'] ?? null,
                ],
            ],
            'theme' => [
                'primaryColor' => $data['primary_color'] ?? null,
            ],
        ];
        $data['meta'] = $meta;

        unset(
            $data['welcome_title'],
            $data['welcome_message'],
            $data['welcome_button_text'],
            $data['welcome_button_url'],
            $data['primary_color']
        );
        return $data;
    }
}
