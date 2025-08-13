<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\WalletPass;
use App\Services\Wallet\ApplePassService;

class ApplePushJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $walletPassId)
    {
    }

    /**
     * Create a new job instance.
     */
    

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $pass = WalletPass::query()->find($this->walletPassId);
        if (!$pass) {
            return;
        }
        app(ApplePassService::class)->notify($pass);
    }
}
