<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing role and permission settings.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.roles.view')->only(['index']);
        $this->middleware('permission:settings.roles.create')->only(['create', 'store']);
        $this->middleware('permission:settings.roles.update')->only(['edit', 'update']);
        $this->middleware('permission:settings.roles.delete')->only(['destroy']);
    }

    /**
     * Display a paginated list of roles with their permissions.
     *
     * @return \Illuminate\View\View The view instance showing the list of roles.
     */
    public function index()
    {
        $roles = Role::with('permissions')->paginate(25);
        
        return view('admin::settings.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     *
     * @return \Illuminate\View\View The view instance for creating a role.
     */
    public function create()
    {
        $permissions = Permission::all();

        return view('admin::settings.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role in the database.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing role data.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects to the roles index with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If the request validation fails.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_name' => [
                'required',
                'string',
                'max:255',
                'unique:roles,name',
                Rule::notIn(['root', 'client'])
            ],
            'role_permissions' => ['nullable', 'array'],
            'role_permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $validated['role_name']]);

        $this->recordCreate('role.create', $role->toArray());

        if (!empty($validated['role_permissions'])) {
            $role->syncPermissions($validated['role_permissions']);
        }

        return redirect()
            ->route('admin.settings.roles')
            ->with('success', __('common.create_success', ['attribute' => $role->name]));
    }

    /**
     * Show the form for editing an existing role.
     *
     * @param \Spatie\Permission\Models\Role $role The ID of the role to edit.
     *
     * @return \Illuminate\View\View The view instance for editing the role.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the role does not exist.
     */
    public function edit(Role $role)
    {
        $role->load('permissions');

        $permissions = Permission::all();

        return view('admin::settings.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update an existing role in the database.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing updated role data.
     * @param \Spatie\Permission\Models\Role $role    The role to update.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects to the roles index with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If the request validation fails.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the role does not exist.
     */
    public function update(Request $request, Role $role)
    {

        $oldRole = [
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name')->toArray(),
        ];

        $validated = $request->validate([
            'role_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($role->id),
                Rule::notIn(['root', 'client']),
            ],
            'role_permissions' => ['nullable', 'array'],
            'role_permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->update(['name' => $validated['role_name']]);

        $role->syncPermissions($validated['role_permissions'] ?? []);
        
        $newRole = [
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name')->toArray(),
        ];

        $this->recordUpdate('role.update', $oldRole, $newRole);

        return redirect()
            ->route('admin.settings.roles')
            ->with('success', __('common.update_success', ['attribute' => $role->name]));
    }

    /**
     * Remove a role from the database.
     *
     * @param \Spatie\Permission\Models\Role $role The ID of the role to delete.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects to the roles index with a success flash message.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the role does not exist.
     */
    public function destroy(Role $role)
    {

        $userCount = $role->users()->count();
        if ($userCount > 0) {
            return back()->with('error', __('validation.role_has_users', [
                'name' => $role->name,
                'count' => $userCount,
            ]));
        }

        $this->recordDelete('role.delete', [
            'name' => $role->name,
        ]);

        $role->delete();

        return redirect()
            ->route('admin.settings.roles')
            ->with('success', __('common.delete_success', ['attribute' => $role->name]));
    }
}
