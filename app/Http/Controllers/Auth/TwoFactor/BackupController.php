<?php

namespace App\Http\Controllers\Auth\TwoFactor;

use App\Http\Controllers\Controller;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BackupController extends Controller
{

    /**
     * Show the backup codes page for Two-Factor Authentication.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->twoFactor) {
            return redirect()->route('client.account.security')->with('error', __('auth.2fa.setup.not_setup'));
        } elseif ($user->twoFactor->isActive()) {
            return redirect()->route('client.account.security')->with('error', __('auth.2fa.setup.has_setup'));
        }

        $codes = $user->twoFactor->recovery_codes;
        return view('client::auth.two-factor.backup', compact('codes'));
    }

    /**
     * Mark the backup codes as confirmed and continue to verification.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        $user = Auth::user();

        if (!$user->twoFactor?->isDownloaded()) {
            return redirect()->back()->with('error',__('auth.2fa.backup.not_downloaded'));
        }

        return redirect()->route('client.two-factor.verify');
    }

    /**
     * Download the user's recovery codes as a plain text file.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download()
    {
        $user = Auth::user();

        $user->twoFactor->update([
            'downloaded_at' => now(),
        ]);

        $codes = $user->twoFactor->recovery_codes ?? [];
        
        $companyName = Billmora::getGeneral('company_name');
        $filename = "{$companyName}_backup-codes.txt";

        return response(implode("\n", $codes))
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}