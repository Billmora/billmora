<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class PreferenceController extends Controller
{

    /**
     * Update user preference settings.
     *
     * Valid preferences are stored in the session.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        if ($request->filled('language')) {
            $langsAllowed = collect(File::directories(base_path('lang')))
                ->map(fn ($path) => basename($path))
                ->toArray();

            if (in_array($request->language, $langsAllowed)) {
                Session::put('locale', $request->language);
            }
        }

        if ($request->filled('currency')) {
            $currencyExists = Currency::where('code', $request->currency)->exists();

            if ($currencyExists) {
                Session::put('currency', $request->currency);
            }
        }

        return redirect()->back()->with('success', __('common.update_success', ['attribute' => __('preference.title')]));
    }
}
