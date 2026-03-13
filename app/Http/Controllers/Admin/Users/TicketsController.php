<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Billmora;
use Illuminate\Http\Request;

class TicketsController extends Controller
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
     * Display a paginated list of tickets belonging to the specified user with optional search filtering.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function index(Request $request, User $user)
    {
        $query = Ticket::with('user', 'service')->where('user_id', $user->id);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderByDesc('created_at')->paginate(Billmora::getGeneral('misc_admin_pagination'));

        $tickets->appends(['search' => $search]);

        return view('admin::users.tickets', compact('user', 'tickets'));
    }
}
