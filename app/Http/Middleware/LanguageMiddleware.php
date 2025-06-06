<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;
use Locale;

class LanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = Session::get('locale', config('app.locale'));
        App::setLocale($locale);

        $langsDirectory = File::directories(resource_path('lang'));

        $langs = collect($langsDirectory)->mapWithKeys(function ($path) use ($locale) {
            $code = basename($path);
            return [$code => Locale::getDisplayName($code, $locale)];
        })->toArray();

        $langActive = Locale::getDisplayName($locale, $locale);

        View::share([
            'langs' => $langs,
            'langActive' => $langActive,
        ]);

        return $next($request);
    }
}
