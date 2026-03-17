<form 
    action="{{ route('admin.invoices.update', ['invoice' => $invoice->id]) }}" 
    method="POST" 
    class="flex flex-col gap-5"
>
    @csrf
    @method('PUT')
    <div class="flex flex-col gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-admin::singleselect
                name="invoice_user"
                label="{{ __('admin/invoices.user_label') }}"
                helper="{{ __('admin/invoices.user_helper') }}"
                :options="$this->userOptions"
                :selected="old('invoice_user', $invoice->user->id)"
                required
            />
            <x-admin::select
                name="invoice_status"
                label="{{ __('admin/invoices.status_label') }}"
                helper="{{ __('admin/invoices.status_helper') }}"
                required
            >
                @foreach (['unpaid', 'paid', 'cancelled', 'refunded'] as $status)
                    <option value="{{ $status }}" {{ old('invoice_status', $invoice->status) === $status ? 'selected' : '' }}>{{ ucwords($status) }}</option>
                @endforeach
            </x-admin::select>
            <x-admin::input
                name="invoice_date"
                type="date"
                label="{{ __('admin/invoices.date_label') }}"
                helper="{{ __('admin/invoices.date_helper') }}"
                value="{{ old('invoice_date', $invoice->created_at->format('Y-m-d')) }}"
                required
            />
            <x-admin::input
                name="invoice_due_date"
                type="date"
                label="{{ __('admin/invoices.due_date_label') }}"
                helper="{{ __('admin/invoices.due_date_helper') }}"
                value="{{ old('invoice_due_date', $invoice->due_date->format('Y-m-d')) }}"
                required
            />
            <x-admin::select
                name="invoice_currency"
                label="{{ __('admin/invoices.currency_label') }}"
                helper="{{ __('admin/invoices.currency_helper') }}"
                required
            >
                @foreach ($currencies as $currency)
                    <option value="{{ $currency->code }}" {{ old('invoice_currency', $invoice->currency) === $currency->code ? 'selected' : '' }}>
                        {{ $currency->code }}
                    </option>
                @endforeach
            </x-admin::select>
        </div>
    </div>
    <div class="flex flex-col sm:flex-row justify-between gap-4 sm:items-center">
        <div>
            <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/invoices.invoice_items.items_label') }}</h4>
            <span class="text-slate-500">{{ __('admin/invoices.invoice_items.items_helper') }}</span>
        </div>
        <button 
            type="button" 
            wire:click="addItem"
            class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-4 py-2 ml-auto text-white rounded-lg transition-colors duration-150 cursor-pointer"
        >
            {{ __('admin/invoices.add_new_items_label') }}
        </button>
    </div>
    <div class="space-y-4">
        @foreach($invoice_items as $index => $item)
            <div 
                wire:key="invoice-item-{{ $index }}" 
                class="flex flex-col gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl relative"
            >
                <div class="flex justify-end">
                    <button 
                        type="button" 
                        wire:click="removeItem({{ $index }})" 
                        class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-2 py-1 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
                    >
                        {{ __('common.delete') }}
                    </button>
                </div>
                @if (!empty($item['id']))
                    <input type="hidden" name="invoice_items[{{ $index }}][id]" value="{{ $item['id'] }}">
                @endif
                <x-admin::textarea
                    name="invoice_items[{{ $index }}][description]"
                    wire:model="invoice_items.{{ $index }}.description"
                    label="{{ __('admin/invoices.invoice_items.description_label') }}"
                    helper="{{ __('admin/invoices.invoice_items.description_helper') }}"
                    rows="3"
                    required
                >{{ old('invoice_items.'.$index.'.description', $item['description']) }}</x-admin::textarea>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-admin::input
                        name="invoice_items[{{ $index }}][quantity]"
                        wire:model="invoice_items.{{ $index }}.quantity"
                        type="number"
                        min="1"
                        label="{{ __('admin/invoices.invoice_items.quantity_label') }}"
                        helper="{{ __('admin/invoices.invoice_items.quantity_helper') }}"
                        value="{{ old('invoice_items.'.$index.'.quantity', $item['quantity']) }}"
                        required
                    />
                    <x-admin::input
                        name="invoice_items[{{ $index }}][unit_price]"
                        wire:model="invoice_items.{{ $index }}.unit_price"
                        type="number"
                        step="0.01"
                        label="{{ __('admin/invoices.invoice_items.unit_price_label') }}"
                        helper="{{ __('admin/invoices.invoice_items.unit_price_helper') }}"
                        value="{{ old('invoice_items.'.$index.'.unit_price', $item['unit_price']) }}"
                        required
                    />
                </div>
            </div>
        @endforeach
    </div>
    <div class="flex gap-4 ml-auto">
        <a 
            href="{{ route('admin.invoices') }}" 
            class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
        >
            {{ __('common.cancel') }}
        </a>
        <button 
            type="submit" 
            class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
        >
            {{ __('common.update') }}
        </button>
    </div>
</form>