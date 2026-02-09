<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServicesController extends Controller
{
    /**
     * Display a paginated list of user's services.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $services = Service::where('user_id', Auth::id())
            ->with([
                'package.catalog',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('client::services.index', compact('services'));
    }
}
