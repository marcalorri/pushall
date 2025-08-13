<?php

namespace App\Services\Wallet;

use App\Models\WalletPass;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GooglePassService
{
    /**
     * Create Google Wallet class/object or JWT for Add to Google Wallet.
     * NOTE: Placeholder - integrate with Google Wallet API using service account.
     */
    public function create(WalletPass $pass): array
    {
        // Single shared object per pass: derive stable ids from issuer + pass id
        $issuer = config('services.google_wallet.issuer_id')
            ?: env('GOOGLE_WALLET_ISSUER_ID');
        $issuer = (string) $issuer;

        if (!$issuer) {
            Log::warning('GooglePassService: missing issuer id');
        }

        // Compute default class/object ids if missing
        if (empty($pass->class_id) && $issuer) {
            $pass->class_id = $issuer . '.pushall_class_' . Str::slug($pass->type ?: 'generic');
        }
        if (empty($pass->object_id) && $issuer) {
            $pass->object_id = $issuer . '.pushall_object_' . $pass->id; // shared object id per pass
        }

        // Persist ids if we set them now
        if ($pass->isDirty(['class_id', 'object_id'])) {
            $pass->save();
        }

        // TODO: Ensure class/object exist in Google Wallet; generate JWT add link
        $link = url('/google-wallet/placeholder/' . $pass->object_id);
        return [
            'addToWalletUrl' => $link,
        ];
    }

    /**
     * Update object via REST v1.
     */
    public function update(WalletPass $pass): void
    {
        // TODO: PATCH object fields per $pass changes
        Log::info('GooglePassService.update', ['wallet_pass_id' => $pass->id]);
    }

    /**
     * Google notifies users on object updates; we just ensure PATCH happens.
     */
    public function notify(WalletPass $pass): void
    {
        // For Google, notify is typically implicit via object update
        $this->update($pass);
    }

    public function getAddToWalletUrl(WalletPass $pass): string
    {
        return $this->create($pass)['addToWalletUrl'];
    }
}
