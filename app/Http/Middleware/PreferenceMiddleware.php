<?php

namespace App\Http\Middleware;

use App\Models\Currency;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Locale;
use Symfony\Component\HttpFoundation\Response;

class PreferenceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->handleLanguage();
        $this->handleCurrency();

        return $next($request);
    }

    /**
     * Handle application language preference.
     *
     * @return void
     */
    protected function handleLanguage()
    {
        $locale = Session::get('locale', config('app.locale'));
        App::setLocale($locale);
        
        $langsDirectory = File::directories(base_path('lang'));
        
        $langs = collect($langsDirectory)->mapWithKeys(function ($path) use ($locale) {
            $localeCode = basename($path);

            return [
                $localeCode => [
                    'name' => Locale::getDisplayLanguage($localeCode, $locale),
                    'lang' => $localeCode,
                    'country' => strtolower(Locale::getRegion($localeCode)),
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
    }

    /**
     * Handle application currency preference.
     *
     * @return void
     */
    protected function handleCurrency()
    {
        $currencyCode = Session::get('currency');

        $currencies = Currency::all(); 

        $currency = $currencies->firstWhere('code', $currencyCode)
            ?? $currencies->firstWhere('is_default', true)
            ?? $currencies->first();

        Session::put('currency', $currency->code);

        View::share([
            'currencies' => $currencies,
            'currencyActive' => $currency,
        ]);
    }
}
