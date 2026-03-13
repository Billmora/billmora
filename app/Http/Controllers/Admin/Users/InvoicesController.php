<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use Billmora;
use Illuminate\Http\Request;

class InvoicesController extends Controller
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
     * Display a paginated list of invoices belonging to the specified user with optional search filtering.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function index(Request $request, User $user)
    {
        $query = Invoice::with(['order.items', 'user'])->where('user_id', $user->id);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $invoices = $query->latest('id')->paginate(Billmora::getGeneral('misc_admin_pagination'));

        return view('admin::users.invoices', compact('user', 'invoices'));
    }
}
