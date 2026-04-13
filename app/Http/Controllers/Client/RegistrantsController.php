<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Registrant;
use App\Services\RegistrarService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrantsController extends Controller
{
    /**
     * Display a listing of the user's registered domains.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Registrant::where('user_id', Auth::id())
            ->with(['tld']);

        if ($search) {
            $query->where('domain', 'LIKE', "%{$search}%");
        }

        $registrants = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('client::registrants.index', compact('registrants', 'search'));
    }

    /**
     * Display the specified domain registrant details.
     *
     * @param  \App\Models\Registrant  $registrant
     * @param  \App\Services\RegistrarService  $registrarService
     * @return \Illuminate\View\View
     */
    public function show(Registrant $registrant, RegistrarService $registrarService)
    {

        if ($registrant->user_id !== Auth::id()) {
            abort(404);
        }

        $registrant->load('tld');
        $clientActions = [];

        if ($registrant->status === 'active' && $registrant->plugin_id) {
            try {
                [$plugin] = $registrarService->bootPluginFor($registrant);

                if (method_exists($plugin, 'getClientAction')) {
                    $clientActions = $plugin->getClientAction($registrant);
                }
            } catch (Exception $e) {

                session()->now('error', __('client/registrants.action.failed', ['message' => $e->getMessage()]));
            }
        }

        return view('client::registrants.workspaces.overview', compact('registrant', 'clientActions'));
    }
}
