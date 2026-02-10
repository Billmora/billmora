<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    /**
     * Display a paginated list of services with search functionality.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = Service::with([
            'user:id,email,first_name,last_name',
            'package:id,name,slug,catalog_id', 
            'package.catalog:id,name',
            'packagePrice:id,package_id,name,type,billing_period',
            'provisioning:id,name,driver'
        ]);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('email', 'like', "%{$search}%")
                              ->orWhere('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        }

        $services = $query->latest()->paginate(25);

        return view('admin::services.index', compact('services'));
    }
}
