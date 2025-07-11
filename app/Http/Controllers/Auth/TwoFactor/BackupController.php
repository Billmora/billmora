<?php

namespace App\Http\Controllers\Auth\TwoFactor;

use App\Http\Controllers\Controller;
use App\Services\BillmoraService as Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BackupController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user->twoFactor) {
            return redirect()->route('client.account.security')->with('error', __('client.2fa_not_setup'));
        } elseif ($user->twoFactor->enabled) {
            return redirect()->route('client.account.security')->with('error', __('client.2fa_has_setup'));
        }

        $codes = $user->twoFactor->recovery_codes;
        return view('client::auth.two-factor.backup', compact('codes'));
    }

    public function store()
    {
        $user = Auth::user();

        if (!$user->twoFactor?->downloaded) {
            return redirect()->back()->with('error',__('client.2fa_backup_not_download'));
        }

        return redirect()->route('client.two-factor.verify');
    }

    public function download()
    {
        $user = Auth::user();

        $user->twoFactor->update([
            'downloaded' => true,
        ]);

        $codes = $user->twoFactor->recovery_codes ?? [];
        
        $companyName = Billmora::getGeneral('company_name');
        $filename = "{$companyName}_backup-codes.txt";

        return response(implode("\n", $codes))
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
