<?php

namespace App\Livewire\Verify;

use App\Services\SessionManager;
use App\Services\UserVerificationManager;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class SmsVerification extends Component
{
    public $phone;

    public $code;

    public function render(SessionManager $sessionManager)
    {
        $dto = $sessionManager->getSmsVerificationDto();

        $this->phone ??= $dto?->phoneNumber;

        return view(
            'livewire.verify.sms-verification', [
            ]
        );
    }

    public function sendVerificationCode(UserVerificationManager $userVerificationManager)
    {
        // remove spaces from phone number
        $this->phone = preg_replace('/\s+/', '', $this->phone);

        $this->validate([
            'phone' => 'phone:INTERNATIONAL',
        ], [
            'phone' => __('Invalid phone number. Make sure to include the country code with +.'),
        ]);

        $user = auth()->user();

        $executed = RateLimiter::attempt(
            'send-verification-code:'.$user->id,
            10,
            function () use ($userVerificationManager, $user) {

                if ($userVerificationManager->phoneAlreadyExists($user, $this->phone)) {
                    $this->addError('phone', __('Phone number already exists.'));

                    return;
                }
                $result = $userVerificationManager->generateAndSendSmsVerificationCode($this->phone);

                if (! $result) {
                    $this->addError('phone', __('Failed to send verification code.'));
                }
            }
        );

        if (! $executed) {
            $this->addError('phone', __('Too many attempts. Please wait a minute.'));
        }
    }

    public function verifyCode(UserVerificationManager $userVerificationManager)
    {
        $this->validate([
            'code' => 'required|digits:6',
        ]);

        $user = auth()->user();

        $result = false;

        $executed = RateLimiter::attempt(
            'verify-phone:'.$user->id,
            10,
            function () use ($userVerificationManager, $user, &$result) {
                $result = $userVerificationManager->verifyCode($user, $this->code);
            }
        );

        if (! $executed) {
            $this->addError('code', __('Too many attempts. Please wait a minute.'));

            return;
        }

        if (! $result) {
            $this->addError('code', __('Invalid verification code.'));

            return;
        }

        $this->redirect(route('user.phone-verified'));
    }

    public function editPhone(SessionManager $sessionManager)
    {
        $sessionManager->clearSmsVerificationDto();

        $this->phone = null;

    }
}
