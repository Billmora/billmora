<?php

namespace App\Services;

use Illuminate\Support\Str;
use PragmaRX\Google2FAQRCode\Google2FA;

class TwoFactorService
{

    /**
     * The Two-Factor Authentication google instance.
     *
     * @var \PragmaRX\Google2FAQRCode\Google2FA
     */
    protected $google2fa;

    /**
     * Initialize the TwoFactorService with Google2FA instance.
     */
    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate a new secret key for Two-Factor Authentication.
     *
     * @return string
     */
    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Generate a QR code URL (inline image data) for setting up 2FA in authenticator apps such as Google Authenticator or Authy.
     *
     * @param  string  $company  The company or application name.
     * @param  string  $email    The user's email address.
     * @param  string  $secret   The generated secret key.
     * @return string  Base64-encoded inline QR code image.
     */
    public function generateQRCodeUrl(string $company, string $email, string $secret): string
    {
        return $this->google2fa->getQRCodeInline($company, $email, $secret);
    }

    /**
     * Generate an array of recovery codes for 2FA fallback.
     *
     * @return array<string>  A list of recovery codes.
     */
    public function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))->map(fn () => Str::random(10))->toArray();
    }

    /**
     * Verify a given One-Time Password (OTP) against the user's secret key.
     *
     * @param  string  $secret  The secret key associated with the user.
     * @param  string  $otp     The 6-digit OTP entered by the user.
     * @return bool
     */
    public function verifyOtp(string $secret, string $otp): bool
    {
        return $this->google2fa->verifyKey($secret, $otp);
    }
}