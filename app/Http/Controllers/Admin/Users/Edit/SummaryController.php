<?php

namespace App\Http\Controllers\Admin\Users\Edit;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SummaryController extends Controller
{

    /**
     * Display the summary information of a specific user.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request.
     * @param int                      $id      The ID of the user to display.
     *
     * @return \Illuminate\View\View The view instance displaying the user summary.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the user does not exist.
     */
    public function index(Request $request, $id)
    {
        $user = User::with('billing')->findOrFail($id);
        
        return view('admin::users.edit.summary', compact('user'));
    }
}
