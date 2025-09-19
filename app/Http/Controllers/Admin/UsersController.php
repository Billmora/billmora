<?php

namespace App\Http\Controllers\Admin;

use App\Mail\TemplateMail;
use App\Models\User;
use App\Models\UserEmailVerification;
use App\Models\UserPasswordReset;
use App\Http\Controllers\Controller;
use Billmora;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    
    /**
     * Applies permission-based middleware for accessing users management.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:users.view')->only(['index']);
        $this->middleware('permission:users.create')->only(['create', 'store']);
        $this->middleware('permission:users.update')->only(['verify']);
        $this->middleware('permission:users.delete')->only(['destroy']);
    }
    
    /**
     * Display a paginated list of users with their roles.
     *
     * @return \Illuminate\View\View The view instance displaying the list of users.
     */
    public function index(Request $request)
    {
        $search = $request->input('searchUser');
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc'); 

        $users = User::query()
                    ->select(['id', 'first_name', 'last_name', 'email', 'is_root_admin', 'created_at'])
                    ->with('roles:id,name')
                    ->when($search, fn ($query) => $this->searchUser($query, $search))
                    ->tap(fn ($query) => $this->sortUser($query, $sort, $direction))
                    ->paginate(25)
                    ->withQueryString();
        
        return view('admin::users', compact('users', 'search', 'sort', 'direction'));
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View The view for creating a user.
     */
    public function create()
    {
        $roles = Role::pluck('name', 'id');

        return view('admin::users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing user data.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects to the user list with a success message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     * @throws \Exception If user creation or related operations fail.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'min:3', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => [
                'required',
                Rule::in(array_merge(['client', 'root'], Role::pluck('name')->toArray())),
            ],
            'status' => ['required', 'in:active,inactive,suspended,closed'],
            'currency' => ['required', 'string'], // TODO: Add currency validation rule
            'language' => ['required', 'string', Rule::in(array_map('basename', File::directories(lang_path())))],
            'phone_number' => [
                Rule::requiredIf(Billmora::hasAuth('user_billing_required_inputs', 'phone_number')),
                Rule::prohibitedIf(Billmora::hasAuth('user_registration_disabled_inputs', 'phone_number')),
                'nullable', 'numeric',
            ],
            'company_name' => [
                Rule::requiredIf(Billmora::hasAuth('user_billing_required_inputs', 'company_name')),
                Rule::prohibitedIf(Billmora::hasAuth('user_registration_disabled_inputs', 'company_name')),
                'nullable', 'string',
            ],
            'street_address_1' => [
                Rule::requiredIf(Billmora::hasAuth('user_billing_required_inputs', 'street_address_1')),
                Rule::prohibitedIf(Billmora::hasAuth('user_registration_disabled_inputs', 'street_address_1')),
                'nullable', 'string',
            ],
            'street_address_2' => [
                Rule::requiredIf(Billmora::hasAuth('user_billing_required_inputs', 'street_address_2')),
                Rule::prohibitedIf(Billmora::hasAuth('user_registration_disabled_inputs', 'street_address_2')),
                'nullable', 'string',
            ],
            'city' => [
                Rule::requiredIf(Billmora::hasAuth('user_billing_required_inputs', 'city')),
                Rule::prohibitedIf(Billmora::hasAuth('user_registration_disabled_inputs', 'city')),
                'nullable', 'string',
            ],
            'state' => [
                Rule::requiredIf(Billmora::hasAuth('user_billing_required_inputs', 'state')),
                Rule::prohibitedIf(Billmora::hasAuth('user_registration_disabled_inputs', 'state')),
                'nullable', 'string',
            ],
            'postcode' => [
                Rule::requiredIf(Billmora::hasAuth('user_billing_required_inputs', 'postcode')),
                Rule::prohibitedIf(Billmora::hasAuth('user_registration_disabled_inputs', 'postcode')),
                'nullable', 'string',
            ],
            'country' => [
                Rule::requiredIf(Billmora::hasAuth('user_billing_required_inputs', 'country')),
                Rule::prohibitedIf(Billmora::hasAuth('user_registration_disabled_inputs', 'country')),
                'nullable', 'string',
                Rule::in(array_keys(config('utils.countries'))),
            ],
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'],
            'currency' => $validated['currency'],
            'language' => $validated['language'],
        ]);

        if ($validated['password'] === null) {
            $token = Str::random(64);
            UserPasswordReset::create([
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => now()->addMinutes(60),
            ]);

            Mail::to($user->email)->send(new TemplateMail('user_password_reset', [
                'client_name' => $user->fullname,
                'company_name' => Billmora::getGeneral('company_name'),
                'reset_url' => route('client.password.reset', ['token' => $token]),
                'clientarea_url' => config('app.url'),
            ]));
        }

        if ($validated['role'] === 'root' && Auth::user()->isRootAdmin()) {
            $user->syncRoles([]);
            $user->update(['is_root_admin' => true]);
        } elseif ($validated['role'] === 'client') {
            $user->syncRoles([]);
            $user->update(['is_root_admin' => false]);
        } else {
            $user->syncRoles([$validated['role']]);
            $user->update(['is_root_admin' => false]);
        }

        $user->billing()->create(
            [
                'phone_number' => $validated['phone_number'],
                'company_name' => $validated['company_name'],
                'street_address_1' => $validated['street_address_1'],
                'street_address_2' => $validated['street_address_2'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postcode' => $validated['postcode'],
                'country' => $validated['country'],
            ]
        );

        return redirect()->route('admin.users')->with('success', __('common.create_success', ['attribute' => $user->email]));
    }

    /**
     * Remove the specified user from storage.
     *
     * @param int $id The ID of the user to delete.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with an error message if attempting to delete self, otherwise to the user list with success message.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the user does not exist.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $this->authorize('delete', $user);

        $user->update(
            ['status' => 'closed',
        ]);
        $user->delete();

        return redirect()->route('admin.users')->with('success', __('common.delete_success', ['attribute' => $user->email]));
    }

    /**
     * Manually verify a user's email address.
     *
     * @param int $id The ID of the user whose email should be verified.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success message after verification.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the user is not found.
     * @throws \Throwable If the verification record is not found or cannot be updated.
     */
    public function verify($id)
    {
        $user = User::findOrFail($id);

        $verification = UserEmailVerification::where('user_id', $user->id)
                ->whereNull('verified_at')
                ->latest()
                ->first();

        $verification->update([
            'verified_at' => now(),
        ]);

        $verification->user->update([
            'email_verified_at' => now(),
        ]);

        return redirect()->back()->with('success', __('admin/users/manage.email_verification_alert_success'));
    }

    /**
     * Apply search filters to the user query by email or full name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query  The base query builder for User model.
     * @param string                                $search The search keyword to filter users.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function searchUser(Builder $query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('email', 'like', "%{$search}%")
            ->orWhere(
                DB::raw("CONCAT(first_name, ' ', last_name)"),
                'like',
                "%{$search}%"
            );
        });
    }

    /**
     * Apply sorting to the user query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query     The base query builder for User model.
     * @param string                                $sort      The column to sort by (e.g., "email", "fullname").
     * @param string                                $direction The sorting direction ("asc" or "desc").
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function sortUser(Builder $query, string $sort, string $direction)
    {
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        if ($sort === 'fullname') {
            return $query->orderByRaw("CONCAT(first_name, ' ', last_name) {$direction}");
        }

        if (Schema::hasColumn('users', $sort)) {
            return $query->orderBy($sort, $direction);
        }

        return $query->latest();
    }
}
