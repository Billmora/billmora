<?php

namespace App\Http\Controllers\Admin;

use Billmora;
use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Models\Registrant;
use App\Models\User;
use App\Models\Tld;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegistrantsController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing registrants management.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:registrants.view')->only(['index']);
        $this->middleware('permission:registrants.create')->only(['create', 'store']);
        $this->middleware('permission:registrants.update')->only(['edit', 'update']);
        $this->middleware('permission:registrants.delete')->only(['destroy']);
    }

    /**
     * Display a paginated list of registrants with search functionality.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = Registrant::with([
            'user:id,email,first_name,last_name',
            'tld:id,tld',
            'plugin:id,name',
        ]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('domain', 'like', "%{$search}%")
                  ->orWhere('registrant_number', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('email', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $registrants = $query->latest()->paginate(Billmora::getGeneral('misc_admin_pagination'));

        return view('admin::registrants.index', compact('registrants'));
    }

    /**
     * Show the form for creating a new registrant.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $users = User::select('id', 'email', 'first_name', 'last_name')->get()->map(fn($u) => [
            'value' => $u->id,
            'title' => $u->first_name . ' ' . $u->last_name,
            'subtitle' => $u->email,
        ]);
        $tlds = Tld::select('id', 'tld')->where('status', 'visible')->get()->map(fn($t) => [
            'value' => $t->id,
            'title' => $t->tld,
        ]);
        $registrars = Plugin::where('type', 'registrar')->where('is_active', true)->get();

        return view('admin::registrants.create', compact('users', 'tlds', 'registrars'));
    }

    /**
     * Store a newly created registrant in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255', 'unique:registrants,domain'],
            'user_id' => ['required', 'exists:users,id'],
            'tld_id' => ['nullable', 'exists:tlds,id'],
            'plugin_id' => ['nullable', 'exists:plugins,id'],
            'status' => ['required', Rule::in([
                'pending', 'active', 'expired', 'suspended',
                'pending_transfer', 'transferred_away', 'cancelled',
                'redemption', 'terminated'
            ])],
            'registration_type' => ['required', Rule::in(['register', 'transfer'])],
            'years' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'auto_renew' => ['boolean'],
            'whois_privacy' => ['boolean'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $validated['auto_renew'] = array_key_exists('auto_renew', $validated) ? (bool) $validated['auto_renew'] : false;
        $validated['whois_privacy'] = array_key_exists('whois_privacy', $validated) ? (bool) $validated['whois_privacy'] : false;
        $validated['currency'] = Billmora::getGeneral('default_currency') ?? 'USD';

        $registrant = Registrant::create($validated);

        $this->recordCreate('registrant.create', $registrant->toArray());

        return redirect()->route('admin.registrants.edit', $registrant)
            ->with('success', __('common.create_success', ['attribute' => $registrant->domain]));
    }

    /**
     * Show the form for editing the specified registrant.
     *
     * @param \App\Models\Registrant $registrant
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Registrant $registrant)
    {
        $registrant->load(['user', 'tld', 'plugin', 'order']);
        $registrars = Plugin::where('type', 'registrar')->where('is_active', true)->get();

        return view('admin::registrants.edit', compact('registrant', 'registrars'));
    }

    /**
     * Update the specified registrant in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Registrant $registrant
     * @return \Illuminate\Http\RedirectResponse
     * 
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Registrant $registrant)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                'pending', 'active', 'expired', 'suspended',
                'pending_transfer', 'transferred_away', 'cancelled',
                'redemption', 'terminated'
            ])],
            'plugin_id' => ['nullable', 'exists:plugins,id'],
            'auto_renew' => ['boolean'],
            'whois_privacy' => ['boolean'],
            'expires_at' => ['nullable', 'date'],
            'nameservers' => ['nullable', 'array'],
            'nameservers.*' => ['nullable', 'string'],
        ]);

        $oldRegistrant = $registrant->getOriginal();

        $registrant->update([
            'status' => $validated['status'],
            'plugin_id' => array_key_exists('plugin_id', $validated) ? $validated['plugin_id'] : $registrant->plugin_id,
            'auto_renew' => array_key_exists('auto_renew', $validated) ? (bool) $validated['auto_renew'] : $registrant->auto_renew,
            'whois_privacy' => array_key_exists('whois_privacy', $validated) ? (bool) $validated['whois_privacy'] : $registrant->whois_privacy,
            'expires_at' => array_key_exists('expires_at', $validated) ? $validated['expires_at'] : $registrant->expires_at,
            'nameservers' => array_values(array_filter($validated['nameservers'] ?? [], fn($n) => $n !== null)),
        ]);

        $this->recordUpdate('registrant.update', $oldRegistrant, $registrant->getChanges());

        return redirect()->route('admin.registrants.edit', $registrant)
            ->with('success', __('common.update_success', ['attribute' => $registrant->domain]));
    }

    /**
     * Remove the specified registrant from database with status validation.
     *
     * @param \App\Models\Registrant $registrant
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Registrant $registrant)
    {
        if ($registrant->status === 'active') {
            return back()->with('error', __('admin/registrants.delete.active_registrant'));
        }

        $registrant->delete();

        $this->recordDelete('registrant.delete', $registrant->toArray());

        return redirect()->route('admin.registrants')
            ->with('success', __('common.delete_success', ['attribute' => $registrant->domain]));
    }
}
