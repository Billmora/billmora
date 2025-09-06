<?php

namespace App\Http\Controllers\Client\Account;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SecurityController extends Controller
{
    public function index()
    {
        $user = Auth::user()->with('billing')->first();

        return view('client::account.security', compact('user'));
    }

    public function updateEmail(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'new_email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore(Auth::id())],
            'confirm_password' => ['required', 'string'],
        ]);

        if (!Hash::check($validated['confirm_password'], $user->password)) {
            return back()->withErrors(['confirm_password' => __('auth.password.current_mismatch')])->withInput();
        }

        $user->email = $validated['new_email'];
        $user->save();

        return redirect()->back()->with('success', __('common.update_success', ['attribute' => __('common.email')]));
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => __('auth.password.current_mismatch')])->withInput();
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return back()->with('success', __('common.update_success', ['attribute' => __('common.password')]));
    }
}
