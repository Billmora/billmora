<?php

namespace App\Livewire\Admin\Packages;

use App\Models\Package;
use App\Models\PackageField;
use Illuminate\Support\Str;
use Livewire\Component;

class PackageFields extends Component
{
    public Package $package;

    public $fields = [];



    public function mount(Package $package)
    {
        $this->package = $package;
        $this->loadFields();
    }

    public function loadFields()
    {
        $this->fields = $this->package->fields()->orderBy('sort_order', 'asc')->get()->toArray();
    }

    public function updateSortOrder($orderedIds)
    {
        foreach ($orderedIds as $index => $id) {
            $this->package->fields()->where('id', $id)->update(['sort_order' => $index]);
        }
        $this->loadFields();
    }

    public function render()
    {
        return view('admin::livewire.packages.package-fields');
    }
}
