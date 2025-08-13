<?php

namespace App\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Models\WalletPass;
use App\Services\Wallet\ApplePassService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ApplePassController extends Controller
{
    public function download(WalletPass $walletPass, ApplePassService $service): Response
    {
        // Ensure the authenticated user owns the pass or is admin
        Gate::authorize('view', $walletPass);
        return $service->download($walletPass);
    }
}
