<?php

namespace App\Http\Controllers\Client\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SecurityController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('client::account.security', compact('user'));
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => __('auth.password_current_mismatch')])->withInput();
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', __('client.update_security_success'));
    }

    public function updateEmail(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'new_email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required',
        ]);

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => __('auth.password_current_mismatch')])->withInput();
        }

        $user->email = $request->new_email;
        $user->save();

        return back()->with('success', __('client.update_security_success'));
    }
}