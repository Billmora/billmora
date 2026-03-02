<?php

namespace App\Http\Controllers\Admin\Packages;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScalingController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing packages scaling.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:packages.update')->only(['index', 'update']);
    }

    /**
     * Display the scaling configuration page for the specified package.
     *
     * @param  \App\Models\Package  $package
     * @return \Illuminate\View\View
     */
    public function index(Package $package)
    {
        $availablePackages = Package::with('catalog')
            ->where('id', '!=', $package->id)
            ->get();

        $selectedTargets = $package->scalablePackages()->pluck('packages.id')->toArray();

        return view('admin::packages.scaling.index', compact('package', 'availablePackages', 'selectedTargets'));
    }

    /**
     * Sync the scalable target packages for the specified package.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'scaling_target_packages' => ['nullable', 'array'],
            'scaling_target_packages.*' => ['integer', Rule::exists('packages', 'id')],
        ]);

        $changes = $package->scalablePackages()->sync($validated['scaling_target_packages'] ?? []);

        if (!empty($changes['attached']) || !empty($changes['detached'])) {
            $this->recordUpdate(
                'package.scaling.update', 
                ['removed_targets' => $changes['detached']],
                ['added_targets' => $changes['attached']]
            );
        }

        return back()->with('success', __('common.update_success', ['attribute' => $package->name]));
    }
}
