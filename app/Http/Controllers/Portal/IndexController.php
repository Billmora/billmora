<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Locale;

class IndexController extends Controller
{
    public function index()
    {
        $langsDirectory = File::directories(resource_path('lang'));
    
        $langs = collect($langsDirectory)->mapWithKeys(function ($path) {
            $locale = basename($path);
            return [$locale => Locale::getDisplayName($locale, app()->getLocale())];
        })->toArray();

        return view('portal.index', compact(['langs']));
    }
}
