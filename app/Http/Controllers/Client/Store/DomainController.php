<?php

namespace App\Http\Controllers\Client\Store;

use App\Http\Controllers\Controller;
use App\Models\Tld;

class DomainController extends Controller
{
    /**
     * Display the domain search and pricing page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('client::store.domains.index');
    }
    
    /**
     * Display the domain configuration page.
     *
     * @param string $domain_name
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function show(string $domain_name, \Illuminate\Http\Request $request)
    {
        $type = $request->query('type', 'register');
        
        if (!in_array($type, ['register', 'transfer'])) {
            $type = 'register';
        }

        return view('client::store.domains.show', compact('domain_name', 'type'));
    }
}
