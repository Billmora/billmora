<?php

namespace App\Livewire\Admin\Invoices;

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

class InvoiceCreate extends Component
{
    public array $invoice_items = [];

    /**
     * Initialize the component with a default invoice line item,
     * restoring any previously submitted items from old input
     * on a failed form submission.
     *
     * @return void
     */
    public function mount()
    {
        $this->invoice_items = old('invoice_items', [
            [
                'id' => null, 
                'description' => '', 
                'quantity' => 1, 
                'unit_price' => ''
            ]
        ]);
    }

    /**
     * Append a new blank line item to the invoice items list
     * with default quantity of 1 and empty description and unit price.
     *
     * @return void
     */
    public function addItem()
    {
        $this->invoice_items[] = [
            'id' => null, 
            'description' => '', 
            'quantity' => 1, 
            'unit_price' => ''
        ];
    }

    /**
     * Remove a line item at the given index and re-index the array.
     * Automatically appends a blank item if the list becomes empty
     * to ensure at least one line item is always present.
     *
     * @param  int  $index  The zero-based index of the item to remove
     * @return void
     */
    public function removeItem($index)
    {
        unset($this->invoice_items[$index]);
        $this->invoice_items = array_values($this->invoice_items);

        if (empty($this->invoice_items)) {
            $this->addItem();
        }
    }

    /**
     * Retrieve all users as selectable options for the invoice recipient field.
     * Persisted computed property; result is cached across Livewire requests.
     *
     * @return array<int, array{value: int, title: string, subtitle: string}>
     */
    #[Computed(persist: true)]
    public function userOptions()
    {
        return User::select('id', 'first_name', 'last_name', 'email')
            ->get()
            ->map(fn ($user) => [
                'value' => $user->id,
                'title' => $user->fullname,
                'subtitle' => $user->email,
            ])->toArray();
    }

    /**
     * Render the invoice creation Livewire component view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('admin::livewire.invoices.invoice-create');
    }
}