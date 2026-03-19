<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    /**
     * Display a paginated listing of users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->search, fn($q, $search) => $q->where('email', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%"))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate($request->input('per_page', 15));

        return UserResource::collection($users);
    }

    /**
     * Display the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \App\Http\Resources\UserResource
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Store a newly created user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\UserResource
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'status' => ['nullable', 'string', 'in:active,suspended,closed'],
            'currency' => ['nullable', 'string', 'max:10'],
            'language' => ['nullable', 'string', 'max:10'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \App\Http\Resources\UserResource
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'password' => ['sometimes', 'string', 'min:8'],
            'status' => ['sometimes', 'string', 'in:active,suspended,closed'],
            'currency' => ['sometimes', 'string', 'max:10'],
            'language' => ['sometimes', 'string', 'max:10'],
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return new UserResource($user->fresh());
    }

    /**
     * Remove the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.'], 200);
    }
}
