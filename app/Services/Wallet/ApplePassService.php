<?php

namespace App\Services\Wallet;

use App\Models\WalletPass;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ApplePassService
{
    /**
     * Build and sign a .pkpass for the given WalletPass.
     * NOTE: Placeholder - integrate PassKit signing with certs from .env.
     */
    public function create(WalletPass $pass): string
    {
        // TODO: Build pass.json + assets; sign with certificate (.p12/.pem)
        // Store file at storage/app/passes/{uuid}.pkpass and return its storage path
        $path = 'passes/'. $pass->id .'.pkpass';
        // Placeholder file creation for flow wiring
        if (!\Storage::disk('local')->exists($path)) {
            \Storage::disk('local')->put($path, 'PKPASS_PLACEHOLDER');
        }
        return storage_path('app/'. $path);
    }

    /**
     * Update the pass content and trigger an APNs notification to devices.
     */
    public function update(WalletPass $pass): void
    {
        // TODO: Regenerate and re-sign pass if fields changed
        Log::info('ApplePassService.update', ['wallet_pass_id' => $pass->id]);
    }

    /**
     * Notify devices via APNs push.
     */
    public function notify(WalletPass $pass): void
    {
        // TODO: Implement APNs HTTP/2 notification to registered devices
        Log::info('ApplePassService.notify', [
            'wallet_pass_id' => $pass->id,
            'device_library_identifier' => $pass->device_library_identifier,
        ]);
    }

    /**
     * Return a Response to download the .pkpass file.
     */
    public function download(WalletPass $pass): Response
    {
        $path = $this->create($pass);
        return response()->file($path, [
            'Content-Type' => 'application/vnd.apple.pkpass',
            'Content-Disposition' => 'attachment; filename="pass.pkpass"',
        ]);
    }
}
