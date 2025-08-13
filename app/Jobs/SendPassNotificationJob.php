<?php

namespace App\Jobs;

use App\Models\PassNotification;
use App\Services\Wallet\ApplePassService;
use App\Services\Wallet\GooglePassService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPassNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $notificationId)
    {
    }

    public function handle(): void
    {
        $notification = PassNotification::query()->with('walletPass')->find($this->notificationId);
        if (!$notification || !$notification->walletPass) {
            return;
        }

        $pass = $notification->walletPass;
        try {
            $notification->update(['status' => 'queued']);
            // Notify both platforms if configured/available
            // Apple available if serial_number or Apple meta/certs exist
            try {
                app(ApplePassService::class)->notify($pass);
            } catch (\Throwable $e) {
                Log::warning('Apple notify failed (continuing to Google)', [
                    'pass_id' => $pass->id,
                    'error' => $e->getMessage(),
                ]);
            }
            try {
                app(GooglePassService::class)->notify($pass);
            } catch (\Throwable $e) {
                Log::warning('Google notify failed (Apple may have succeeded)', [
                    'pass_id' => $pass->id,
                    'error' => $e->getMessage(),
                ]);
            }
            $notification->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (\Throwable $e) {
            Log::error('SendPassNotificationJob failed', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
            $notification->update(['status' => 'failed', 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
