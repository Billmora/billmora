<?php

namespace App\Http\Controllers\Client\Registrants;

use App\Http\Controllers\Controller;
use App\Models\Registrant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutoRenewController extends Controller
{
    public function show(Registrant $registrant)
    {
        if ($registrant->user_id !== Auth::id()) {
            abort(404);
        }

        return view('client::registrants.workspaces.autorenew', compact('registrant'));
    }

    public function update(Request $request, Registrant $registrant)
    {
        if ($registrant->user_id !== Auth::id()) {
            abort(404);
        }

        $validated = $request->validate([
            'auto_renew' => 'required|boolean',
        ]);
        
        $enabled = $validated['auto_renew'];
        $registrant->update(['auto_renew' => $enabled]);
        
        $msgKey = $enabled ? 'client/registrants.auto_renew.enabled' : 'client/registrants.auto_renew.disabled';
        return redirect()->back()->with('success', __($msgKey));
    }
}
