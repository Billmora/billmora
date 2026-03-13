<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\User;
use Billmora;
use Illuminate\Http\Request;

class ServiceController extends Controller
{

    /**
     * Applies permission-based middleware for accessing users management.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:users.view')->only(['index']);
    }

    /**
     * Display a paginated list of services belonging to the specified user with optional search filtering.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function index(Request $request, User $user)
    {
        $query = Service::with([
            'user:id,email,first_name,last_name',
            'package:id,name,slug,catalog_id', 
            'package.catalog:id,name',
            'packagePrice:id,package_id,name,type,billing_period',
            'provisioning:id,name'
        ])->where('user_id', $user->id);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('service_number', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $services = $query->latest()->paginate(Billmora::getGeneral('misc_admin_pagination'));

        return view('admin::users.services', compact('user', 'services'));
    }
}
