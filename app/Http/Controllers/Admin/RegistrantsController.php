<?php

namespace App\Http\Controllers\Admin;

use Billmora;
use App\Http\Controllers\Controller;
use App\Models\Registrant;
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
     * Show the form for editing the specified registrant.
     *
     * @param \App\Models\Registrant $registrant
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Registrant $registrant)
    {
        $registrant->load(['user', 'tld', 'plugin', 'order']);

        return view('admin::registrants.edit', compact('registrant'));
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
            'plugin_id'  => ['nullable', 'exists:plugins,id'],
            'auto_renew' => ['boolean'],
            'expires_at' => ['nullable', 'date'],
            'registered_at' => ['nullable', 'date'],
            'years'      => ['required', 'integer', 'min:1'],
            'price'      => ['required', 'numeric', 'min:0'],
            'nameservers'   => ['nullable', 'array'],
            'nameservers.*' => ['nullable', 'string'],
        ]);

        $oldRegistrant = $registrant->getOriginal();

        $registrant->update([
            'status'       => $validated['status'],
            'plugin_id'    => $validated['plugin_id'] ?? $registrant->plugin_id,
            'auto_renew'   => isset($validated['auto_renew']) ? (bool) $validated['auto_renew'] : $registrant->auto_renew,
            'expires_at'   => $validated['expires_at'] ?? $registrant->expires_at,
            'registered_at' => $validated['registered_at'] ?? $registrant->registered_at,
            'years'        => $validated['years'] ?? $registrant->years,
            'price'        => $validated['price'] ?? $registrant->price,
            'nameservers'  => array_values(array_filter($validated['nameservers'] ?? [], fn($n) => $n !== null)),
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
