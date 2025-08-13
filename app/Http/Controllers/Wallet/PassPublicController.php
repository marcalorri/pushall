<?php

namespace App\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Models\WalletPass;
use App\Services\Wallet\ApplePassService;
use App\Services\Wallet\GooglePassService;
use Illuminate\Http\Request;

class PassPublicController extends Controller
{
    public function smart(Request $request, WalletPass $pass)
    {
        $ua = strtolower($request->userAgent() ?? '');
        if (str_contains($ua, 'iphone') || str_contains($ua, 'ipad') || str_contains($ua, 'ipod') || str_contains($ua, 'ios') || str_contains($ua, 'macintosh')) {
            return redirect()->route('public.pass.apple', $pass);
        }
        if (str_contains($ua, 'android')) {
            return redirect()->route('public.pass.google', $pass);
        }
        return redirect()->route('public.pass.choose', $pass);
    }

    public function choose(WalletPass $pass)
    {
        return view('pass.landing', compact('pass'));
    }

    public function apple(WalletPass $pass, ApplePassService $apple)
    {
        // Serve/download .pkpass publicly
        return $apple->download($pass);
    }

    public function google(WalletPass $pass, GooglePassService $google)
    {
        $url = $google->getAddToWalletUrl($pass);
        return redirect()->away($url);
    }
}
