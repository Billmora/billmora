<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReorderController extends Controller
{
    /**
     * Update the sort order of the given model.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'model' => ['required', 'string'],
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer'],
            'items.*.order' => ['required', 'integer'],
        ]);

        $modelClass = match($validated['model']) {
            'Catalog' => \App\Models\Catalog::class,
            'Package' => \App\Models\Package::class,
            'Variant' => \App\Models\Variant::class,
            'Tld' => \App\Models\Tld::class,
            default => null,
        };

        if (!$modelClass) {
            return response()->json(['error' => 'Invalid model'], 400);
        }

        foreach ($validated['items'] as $item) {
            $modelClass::where('id', $item['id'])->update(['sort_order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }
}
