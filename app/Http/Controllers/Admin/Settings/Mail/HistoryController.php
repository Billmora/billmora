<?php

namespace App\Http\Controllers\Admin\Settings\Mail;

use App\Http\Controllers\Controller;
use App\Models\AuditEmail;
use Illuminate\Http\Request;

class HistoryController extends Controller
{

    /**
     * Display a paginated list of email audit histories.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = $request->query('searchHistoryMail');

       $histories = AuditEmail::select('id', 'event', 'user_id', 'to', 'status', 'created_at')
                                ->when($search, function ($query, $search) {
                                    $query->where(function ($q) use ($search) {
                                        $q->where('event', 'like', "%{$search}%")
                                        ->orWhere('to', 'like', "%{$search}%")
                                        ->orWhere('status', 'like', "%{$search}%");
                                    });
                                })
                                ->latest()
                                ->paginate(25)
                                ->withQueryString();
        
        return view('admin::settings.mail.history.index', compact('histories'));
    }

    /**
     * Display the details of a specific email audit history.
     *
     * @param int $id The ID of the email audit record.
     *
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function show($id)
    {
        $history = AuditEmail::findOrFail($id);

        return view('admin::settings.mail.history.show', compact('history'));
    }
}
