<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use PragmaRX\Google2FAQRCode\Google2FA;

class TwoFactorService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function generateQRCodeUrl(string $company, string $email, string $secret): string
    {
        return $this->google2fa->getQRCodeInline($company, $email, $secret);
    }

    public function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))->map(fn () => Str::random(10))->toArray();
    }

    public function verifyOtp(string $secret, string $otp): bool
    {
        return $this->google2fa->verifyKey($secret, $otp);
    }
}
