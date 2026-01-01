<?php

namespace App\Http\Controllers\Admin\Variants;

use App\Http\Controllers\Controller;
use App\Models\Variant;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    public function index($id)
    {
        $variant = Variant::findOrFail($id);

        return view('admin::variants.option.index', compact('variant'));
    }
}
