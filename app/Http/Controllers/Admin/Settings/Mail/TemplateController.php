<?php

namespace App\Http\Controllers\Admin\Settings\Mail;

use Billmora;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TemplateController extends Controller
{

    /**
     * Display the mail templates table.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.mail.template');
    }
}
