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
            $localeCode = basename($path);
            
            $name = Locale::getDisplayLanguage($localeCode, $locale);
            $country = strtolower(Locale::getRegion($localeCode));

            return [
                $localeCode => [
                    'name' => $name,
                    'lang' => $localeCode,
                    'country' => $country,
                ]
            ];
        })->toArray();

        View::share([
            'langs' => $langs,
            'langActive' => [
                'name' => Locale::getDisplayLanguage($locale, $locale),
                'lang' => $locale,
                'country' => strtolower(Locale::getRegion($locale)),
            ]
        ]);

        return $next($request);
    }
}
