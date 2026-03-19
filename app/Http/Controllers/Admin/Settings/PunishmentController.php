<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Jobs\NotificationJob;
use App\Models\Punishment;
use App\Models\Service;
use App\Models\User;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;

class PunishmentController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing punishments settings.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.punishments.view')->only(['index']);
        $this->middleware('permission:settings.punishments.create')->only(['create', 'store']);
        $this->middleware('permission:settings.punishments.delete')->only(['destroy']);
    }

    /**
     * Display a paginated list of punishment records.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $punishments = Punishment::with('user:id,first_name,last_name,email')
            ->orderBy('created_at', 'desc')
            ->paginate(Billmora::getGeneral('misc_admin_pagination'));
        
        return view('admin::settings.punishments.index', compact('punishments'));
    }

    /**
     * Show the form for creating a new punishment.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $userOptions = User::query()
            ->select('id', 'first_name', 'last_name', 'email')
            ->whereNotIn('status', ['suspended', 'closed'])
            ->get()
            ->map(fn ($user) => [
                'value' => $user->id,
                'title' => $user->fullname,
                'subtitle' => $user->email,
            ])
            ->values()
            ->toArray();
            
        return view('admin::settings.punishments.create', compact('userOptions'));
    }

    /**
     * Validate and persist a new punishment record, update target user status, optionally terminate services and send email notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'status' => ['required', 'in:suspended,closed'],
            'reason' => ['required', 'string', 'max:5000'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'terminate_services' => ['nullable', 'boolean'],
            'notify_user' => ['nullable', 'boolean'],
        ]);

        $user = User::findOrFail($validated['user_id']);

        $punishment = Punishment::create([
            'user_id' => $user->id,
            'status' => $validated['status'],
            'reason' => $validated['reason'],
            'expires_at' => $validated['expires_at'] ?? null,
            'terminate_services' => $request->boolean('terminate_services'),
        ]);

        $user->update(['status' => $validated['status']]);

        if ($request->boolean('terminate_services')) {
            Service::where('user_id', $user->id)
                ->whereNotIn('status', ['terminated', 'cancelled'])
                ->each(fn ($service) => $service->terminate());
        }

        if ($request->boolean('notify_user')) {
            NotificationJob::dispatch(
                $user->email,
                'user_' . $punishment->status,
                [
                    'client_name' => $user->fullname,
                    'company_name' => Billmora::getGeneral('company_name'),
                    'reason' => $punishment->reason,
                ],
                $user->language,
                $user->id
            );
        }

        $this->recordCreate('punishment.create', $punishment->toArray());

        return redirect()
            ->route('admin.settings.punishments')
            ->with('success', __('common.create_success', ['attribute' => 'Punishment for ' . $user->fullname]));
    }

    /**
     * Remove the specified punishment record and restore the user's status if they are still under the respective punishment.
     *
     * @param  \App\Models\Punishment  $punishment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Punishment $punishment)
    {
        $user = $punishment->user;
        
        $this->recordDelete('punishment.delete', $punishment->toArray());

        if ($user && $user->status === $punishment->status) {
            $user->update(['status' => 'active']);
        }

        $punishment->delete();

        return redirect()
            ->route('admin.settings.punishments')
            ->with('success', __('common.delete_success', ['attribute' => 'Punishment record']));
    }
}
