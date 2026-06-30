<?php

namespace App\Http\Controllers\Admin\Packages;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Packages\FieldRequest;
use App\Models\Package;
use App\Models\PackageField;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FieldsController extends Controller
{
    /**
     * Applies permission-based middleware for accessing packages fields.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:packages.update')->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of fields for a specific package.
     *
     * @param  \App\Models\Package  $package  Package ID
     * @return \Illuminate\View\View
     */
    public function index(Package $package)
    {
        return view('admin::packages.fields.index', compact('package'));
    }

    /**
     * Show the form for creating a new field for the given package.
     *
     * @param  \App\Models\Package  $package  Package ID
     * @return \Illuminate\View\View
     */
    public function create(Package $package)
    {
        return view('admin::packages.fields.create', compact('package'));
    }

    /**
     * Store a newly created field.
     *
     * @param  \App\Http\Requests\Admin\Packages\FieldRequest  $request
     * @param  \App\Models\Package  $package  Package ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(FieldRequest $request, Package $package)
    {
        $validated = $request->validated();
        
        $name = empty($validated['name']) 
            ? Str::slug($validated['label'], '_') 
            : Str::slug($validated['name'], '_');
            
        $options = null;
        if (in_array($validated['type'], ['select', 'radio']) && !empty($validated['options'])) {
            $parsedOptions = [];
            foreach (explode("\n", $validated['options']) as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                if (str_contains($line, '|')) {
                    [$val, $lbl] = explode('|', $line, 2);
                    $parsedOptions[trim($val)] = trim($lbl);
                } else {
                    $parsedOptions[$line] = $line;
                }
            }
            $options = $parsedOptions;
        }

        $maxSort = $package->fields()->max('sort_order') ?? 0;

        $package->fields()->create([
            'label' => $validated['label'],
            'name' => $name,
            'type' => $validated['type'],
            'required' => (bool) ($validated['required'] ?? false),
            'visible_on_order' => (bool) ($validated['visible_on_order'] ?? false),
            'visible_on_invoice' => (bool) ($validated['visible_on_invoice'] ?? false),
            'helper' => $validated['helper'] ?? null,
            'default' => $validated['default'] ?? null,
            'options' => $options,
            'condition' => $this->buildCondition($validated),
            'sort_order' => $maxSort + 1,
        ]);

        return redirect()
            ->route('admin.packages.fields', ['package' => $package->id])
            ->with('success', __('common.create_success', ['attribute' => $validated['label']]));
    }

    /**
     * Show the form for editing the specified field.
     *
     * @param  \App\Models\Package  $package
     * @param  \App\Models\PackageField  $field
     * @return \Illuminate\View\View
     */
    public function edit(Package $package, PackageField $field)
    {
        if ($field->package_id !== $package->id) {
            abort(404);
        }

        return view('admin::packages.fields.edit', compact('package', 'field'));
    }

    /**
     * Update the specified field in storage.
     *
     * @param  \App\Http\Requests\Admin\Packages\FieldRequest  $request
     * @param  \App\Models\Package  $package
     * @param  \App\Models\PackageField  $field
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(FieldRequest $request, Package $package, PackageField $field)
    {
        if ($field->package_id !== $package->id) {
            abort(404);
        }

        $validated = $request->validated();
        
        $name = empty($validated['name']) 
            ? Str::slug($validated['label'], '_') 
            : Str::slug($validated['name'], '_');
            
        $options = null;
        if (in_array($validated['type'], ['select', 'radio']) && !empty($validated['options'])) {
            $parsedOptions = [];
            foreach (explode("\n", $validated['options']) as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                if (str_contains($line, '|')) {
                    [$val, $lbl] = explode('|', $line, 2);
                    $parsedOptions[trim($val)] = trim($lbl);
                } else {
                    $parsedOptions[$line] = $line;
                }
            }
            $options = $parsedOptions;
        }
        
        $field->update([
            'label' => $validated['label'],
            'name' => $name,
            'type' => $validated['type'],
            'required' => (bool) ($validated['required'] ?? false),
            'visible_on_order' => (bool) ($validated['visible_on_order'] ?? false),
            'visible_on_invoice' => (bool) ($validated['visible_on_invoice'] ?? false),
            'helper' => $validated['helper'] ?? null,
            'default' => $validated['default'] ?? null,
            'options' => $options,
            'condition' => $this->buildCondition($validated),
        ]);

        return redirect()
            ->route('admin.packages.fields', ['package' => $package->id])
            ->with('success', __('common.update_success', ['attribute' => $validated['label']]));
    }

    /**
     * Remove the specified field from storage.
     *
     * @param  \App\Models\Package  $package
     * @param  \App\Models\PackageField  $field
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Package $package, PackageField $field)
    {
        if ($field->package_id !== $package->id) {
            abort(404);
        }

        $field->delete();

        return redirect()
            ->route('admin.packages.fields', ['package' => $package->id])
            ->with('success', __('common.delete_success', ['attribute' => $field->label]));
    }
}
