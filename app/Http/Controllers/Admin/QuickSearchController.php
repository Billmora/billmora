<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QuickSearchController extends Controller
{
    /**
     * Handle quick search request (AlpineJS).
     * Returns JSON result to be consumed by command bar search UI.
     */
    public function search()
    {
        // TODO: Replace below simulation with real search logic (e.g. database query)
        // Static placeholder data used for prototyping the quick search feature
        $data = [
            [
                'title' => 'Settings',               // Displayed name in search result
                'category' => 'setting',             // Grouping category
                'url' => '/admin/settings',          // Redirect target when clicked
            ],
            [
                'title' => 'General Setting',
                'category' => 'setting',
                'url' => '/admin/settings/general',
            ],
            [
                'title' => 'Authorization Setting',
                'category' => 'setting',
                'url' => '/admin/settings/authorization',
            ],
            [
                'title' => 'Mail Setting',
                'category' => 'setting',
                'url' => '/admin/settings/mail',
            ],
            [
                'title' => 'Users',
                'category' => 'user',
                'url' => '/admin/user',
            ],
            [
                'title' => 'mafly@billmora.com',
                'category' => 'user',
                'url' => '/admin/user/2',
            ],
        ];

        // Return search results as JSON for frontend consumption
        return response()->json($data);
    }
}
