<?php

namespace App\Livewire\Admin\Registrants;

use App\Models\Plugin;
use App\Models\Registrant;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RegistrantEdit extends Component
{
    public Registrant $registrant;

    public string $plugin_id = '';

    /**
     * Initialize component state from the given registrant.
     */
    public function mount(Registrant $registrant): void
    {
        $this->registrant = $registrant;
        $this->plugin_id  = old('plugin_id', (string) ($registrant->plugin_id ?? ''));
    }

    /**
     * Available active registrar plugins.
     */
    #[Computed(persist: true)]
    public function registrars()
    {
        return Plugin::where('type', 'registrar')
            ->where('is_active', true)
            ->get();
    }

    /**
     * Render the registrant edit Livewire component view.
     */
    public function render()
    {
        return view('admin::livewire.registrants.registrant-edit');
    }
}
