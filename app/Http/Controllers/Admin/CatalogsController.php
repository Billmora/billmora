<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CatalogsController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing currencies settings.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:catalogs.view')->only(['index']);
        $this->middleware('permission:catalogs.create')->only(['create', 'store']);
        $this->middleware('permission:catalogs.update')->only(['edit', 'update']);
        $this->middleware('permission:catalogs.delete')->only(['destroy']);
    }

    /**
     * Display a paginated list of catalogs.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $catalogs = Catalog::select('id', 'name', 'slug', 'status', 'created_at')->paginate(25);

        return view('admin::catalogs.index', compact('catalogs'));
    }

    /**
     * Show the form for creating a new catalog.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin::catalogs.create');
    }

    /**
     * Store a newly created catalog in the database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'catalog_name' => ['required', 'string', 'max:255'],
            'catalog_slug' => ['required', 'string', 'max:255', Rule::unique('catalogs', 'slug')],
            'catalog_description' => ['required', 'string'],
            'catalog_icon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'catalog_status' => ['required', 'in:visible,hidden'],
        ]);

        if ($request->catalog_icon) {
            $iconPath = $request->file('catalog_icon')->store('public/catalogs');
        }

        $catalog = Catalog::create([
            'name' => $validated['catalog_name'],
            'slug' => $validated['catalog_slug'],
            'description' => $validated['catalog_description'],
            'icon' => $iconPath ?? null,
            'status' => $validated['catalog_status'],
        ]);

        $this->recordCreate('catalog.create', $catalog->toArray());

        return redirect()->route('admin.catalogs')->with('success', __('common.create_success', ['attribute' => $catalog->name]));
    }

    /**
     * Show the form for editing the specified catalog.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $catalog = Catalog::findOrFail($id);

        return view('admin::catalogs.edit', compact('catalog'));
    }

    /**
     * Update the specified catalog in the database.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $catalog = Catalog::findOrFail($id);

        $validated = $request->validate([
            'catalog_name' => ['required', 'string', 'max:255'],
            'catalog_slug' => ['required', 'string', 'max:255', Rule::unique('catalogs', 'slug')->ignore($catalog->id)],
            'catalog_description' => ['required', 'string'],
            'catalog_icon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'catalog_status' => ['required', 'in:visible,hidden'],
        ]);

        if ($request->hasFile('catalog_icon')) {
            $iconPath = $request->file('catalog_icon')->store('public/catalogs');
        }

        $oldCatalog = $catalog->getOriginal();

        $catalog->update([
            'name' => $validated['catalog_name'],
            'slug' => $validated['catalog_slug'],
            'description' => $validated['catalog_description'],
            'icon' => $iconPath ?? $catalog->icon,
            'status' => $validated['catalog_status'],
        ]);

        $this->recordUpdate('catalog.update', $oldCatalog, $catalog->getChanges());

        return redirect()->route('admin.catalogs')->with('success', __('common.update_success', ['attribute' => $catalog->name]));
    }

    /**
     * Remove the specified catalog from the database.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $catalog = Catalog::findOrFail($id);

        $catalog->delete();

        $this->recordDelete('catalog.delete', [
            'name' => $catalog->name,
        ]);

        return redirect()->route('admin.catalogs')->with('success', __('common.delete_success', ['attribute' => $catalog->name]));
    }

}
