<?php

namespace App\Http\Controllers\Client\Account;

use App\Http\Controllers\Controller;
use App\Services\BillmoraService as Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DetailController extends Controller
{
    public function index() {
        $user = Auth::user()->with('billing')->first();

        return view('client::account.detail', compact('user'));
    }

    public function update(Request $request) {
        $requiredFields = Billmora::getAuth('form_required', []);
        
        $user = Auth::user();

        $validation = [
            'first_name' => 'required|string|min:3|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'nullable|numeric',
            'company_name' => 'nullable|string',
            'street_address_1' => 'nullable|string',
            'street_address_2' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string|in:' . implode(',', array_keys(config('utils.countries'))),
            'state' => 'nullable|string',
            'postcode' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ];

        foreach ($requiredFields as $field) {
            if (isset($validation[$field])) {
                $validation[$field] = str_replace('nullable', 'required', $validation[$field]);
            }
        }

        $request->validate($validation);

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
        ]);

        $user->refresh();

        $user->billing()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone_number' => $request->phone_number,
                'company_name' => $request->company_name,
                'street_address_1' => $request->street_address_1,
                'street_address_2' => $request->street_address_2,
                'city' => $request->city,
                'country' => $request->country,
                'state' => $request->state,
                'postcode' => $request->postcode,
            ]
        );

        return redirect()->back()->with('success', __('client.update_detail_success'));
    }
}
