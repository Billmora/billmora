<?php

namespace App\Http\Controllers\Admin\Users\Edit;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SummaryController extends Controller
{
    public function index(Request $request, $id)
    {
        $user = User::with('billing')->findOrFail($id);
        
        return view('admin::users.edit.summary', compact('user'));
    }
}
