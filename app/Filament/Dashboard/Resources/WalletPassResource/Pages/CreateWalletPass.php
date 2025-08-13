<?php

namespace App\Filament\Dashboard\Resources\WalletPassResource\Pages;

use App\Filament\Dashboard\Resources\WalletPassResource;
use App\Models\WalletPass;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateWalletPass extends CreateRecord
{
    protected static string $resource = WalletPassResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }

    public function mount(): void
    {
        parent::mount();

        // Detect platform similar to resource helper
        $ua = request()->header('User-Agent', '');
        $ua = \Illuminate\Support\Str::of($ua)->lower();
        $platform = ($ua->contains('android') || $ua->contains('linux;') || $ua->contains('crandroid')) ? 'google' : 'apple';

        $existing = WalletPass::query()
            ->where('user_id', Auth::id())
            ->where('platform', $platform)
            ->first();

        if ($existing) {
            $this->redirect(static::getResource()::getUrl('edit', ['record' => $existing]));
        }
    }
}
