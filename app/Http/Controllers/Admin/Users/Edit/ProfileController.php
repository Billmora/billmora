<?php

namespace App\Http\Controllers\Admin\Users\Edit;

use App\Http\Controllers\Controller;
use App\Models\User;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class ProfileController extends Controller
{

    /**
     * Display the profile edit page for a specific user.
     *
     * @param int $id The ID of the user to be edited.
     *
     * @return \Illuminate\View\View The profile edit view with user data.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the user is not found.
     */
    public function index($id)
    {
        $user = User::with('billing')->findOrFail($id);
        $roles = Role::pluck('name', 'id');
        
        return view('admin::users.edit.profile', compact('user', 'roles'));
    }

    /**
     * Update the specified user's profile and billing information.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing profile data.
     * @param int $id The ID of the user being updated.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the user is not found.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'min:3', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
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

        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
            'status' => $validated['status'],
            'currency' => $validated['currency'],
            'language' => $validated['language'],
        ]);

        if ($validated['role'] !== 'root') {
            $user->syncRoles([$validated['role']]);
            $user->update(['is_root_admin' => false]);
        }

        if ($validated['role'] === 'client') {
            $user->syncRoles([]);
            $user->update(['is_root_admin' => false]);
        }

        if ($validated['role'] === 'root' && Auth::user()->isRootAdmin()) {
            $user->syncRoles([]);
            $user->update(['is_root_admin' => true]);
        }

        $user->billing()->updateOrCreate(
            ['user_id' => $user->id],
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

        return redirect()->back()->with('success', __('common.update_success', ['attribute' => __('admin/users/manage.tabs.profile')]));
    }
}
