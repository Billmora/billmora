<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class PreferenceController extends Controller
{
    public function setLanguage(Request $request)
    {
        $language = $request->language;
        $langsAllowed = collect(File::directories(resource_path('lang')))
        ->map(fn ($path) => basename($path))
        ->toArray();

        if (!in_array($language, $langsAllowed)) {
            return redirect()->back();
        }

        Session::put('locale', $language);
        
        return redirect()->back();
    }
}
