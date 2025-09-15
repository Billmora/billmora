<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    
    /**
     * Display a paginated list of users with their roles.
     *
     * @return \Illuminate\View\View The view instance displaying the list of users.
     */
    public function index()
    {
        $users = User::query()
                    ->select(['id', 'first_name', 'last_name', 'email', 'is_root_admin', 'created_at'])
                    ->with('roles:id,name')
                    ->latest()
                    ->paginate(25);
        
        return view('admin::users', compact('users'));
    }
}
