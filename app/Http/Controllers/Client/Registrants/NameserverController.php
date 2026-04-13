<?php

namespace App\Http\Controllers\Client\Registrants;

use App\Http\Controllers\Controller;
use App\Models\Registrant;
use App\Services\RegistrarService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NameserverController extends Controller
{
    public function show(Registrant $registrant, RegistrarService $registrarService)
    {
        if ($registrant->user_id !== Auth::id()) {
            abort(404);
        }

        $nameservers = [];

        if ($registrant->status === 'active' && $registrant->plugin_id) {
            try {
                [$plugin] = $registrarService->bootPluginFor($registrant);
                $nameservers = $plugin->getNameservers($registrant);
            } catch (Exception $e) {
                session()->now('error', __('client/registrants.nameservers.failed', ['message' => $e->getMessage()]));
            }
        }

        return view('client::registrants.workspaces.nameservers', compact('registrant', 'nameservers'));
    }

    public function update(Request $request, Registrant $registrant, RegistrarService $registrarService)
    {
        if ($registrant->user_id !== Auth::id()) {
            abort(404);
        }

        $validated = $request->validate([
            'nameservers' => 'required|array|max:4',
            'nameservers.*' => 'nullable|string',
        ]);

        $nameservers = array_filter(array_values($validated['nameservers']));

        if ($registrant->status !== 'active' || !$registrant->plugin_id) {
            return redirect()->back()->with('error', 'Domain must be active to update nameservers.');
        }

        try {
            [$plugin] = $registrarService->bootPluginFor($registrant);
            $plugin->setNameservers($registrant, $nameservers);


            $registrant->update(['nameservers' => $nameservers]);

            return redirect()->back()->with('success', __('client/registrants.nameservers.updated'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', __('client/registrants.nameservers.failed', ['message' => $e->getMessage()]));
        }
    }
}
