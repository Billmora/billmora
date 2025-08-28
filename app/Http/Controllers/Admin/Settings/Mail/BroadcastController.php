<?php

namespace App\Http\Controllers\Admin\Settings\Mail;

use App\Models\User;
use App\Mail\TemplateMail;
use Illuminate\Http\Request;
use App\Models\MailBroadcast;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class BroadcastController extends Controller
{

    /**
     * Display the list mail broadcast table.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $broadcasts = MailBroadcast::select('id', 'subject', 'schedule_at', 'created_at')->get();

        return view('admin::settings.mail.broadcast.index', compact('broadcasts'));
    }
}
