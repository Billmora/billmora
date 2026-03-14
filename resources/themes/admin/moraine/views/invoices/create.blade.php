@extends('admin::layouts.app')

@section('title', 'Invoice Create')

@section('body')
<form action="{{ route('admin.invoices.store') }}" method="POST" class="flex flex-col gap-5" x-data="invoiceForm()">
    @csrf
    <div class="flex flex-col gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-admin::singleselect
                name="invoice_user"
                label="{{ __('admin/invoices.user_label') }}"
                helper="{{ __('admin/invoices.user_helper') }}"
                :options="$userOptions"
                :selected="old('invoice_user')"
                required
            />
            <x-admin::select
                name="invoice_status"
                label="{{ __('admin/invoices.status_label') }}"
                helper="{{ __('admin/invoices.status_helper') }}"
                required
            >
                <option value="unpaid" {{ old('invoice_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                <option value="paid" {{ old('invoice_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="cancelled" {{ old('invoice_status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                <option value="refunded" {{ old('invoice_status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
            </x-admin::select>
            <x-admin::input
                name="invoice_date"
                type="date"
                label="{{ __('admin/invoices.date_label') }}"
                helper="{{ __('admin/invoices.date_helper') }}"
                required
            />
            <x-admin::input
                name="invoice_due_date"
                type="date"
                label="{{ __('admin/invoices.due_date_label') }}"
                helper="{{ __('admin/invoices.due_date_helper') }}"
                required
            />
            <x-admin::select
                name="invoice_currency"
                label="{{ __('admin/invoices.currency_label') }}"
                helper="{{ __('admin/invoices.currency_helper') }}"
                required
            >
                @foreach ($currencies as $currency)
                    <option value="{{ $currency->code }}" {{ old('invoice_currency') == $currency->code ? 'selected' : '' }}>{{ $currency->code }}</option>
                @endforeach
            </x-admin::select>
            <x-admin::toggle
                name="invoice_email"
                label="{{ __('admin/invoices.email_label') }}"
                helper="{{ __('admin/invoices.email_helper') }}"
                :checked="old('invoice_email')"
            />
        </div>
    </div>
    <div class="flex flex-col sm:flex-row justify-between gap-4 sm:items-center">
        <div>
            <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/invoices.invoice_items.items_label') }}</h4>
            <span class="text-slate-500">{{ __('admin/invoices.invoice_items.items_helper') }}</span>
        </div>
        <button 
            type="button" 
            x-on:click="addItem"
            class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-4 py-2 ml-auto text-white rounded-lg transition-colors duration-150 cursor-pointer"
        >
            {{ __('admin/invoices.add_new_items_label') }}
        </button>
    </div>
    <div id="items-container" class="space-y-4">
        @if(old('invoice_items'))
            @foreach(old('invoice_items') as $index => $item)
                @include('admin::invoices._item_group', [
                    'index' => $index,
                    'description' => $item['description'] ?? '',
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? ''
                ])
            @endforeach
        @else
            @include('admin::invoices._item_group', [
                'index' => 0,
                'description' => '',
                'quantity' => 1,
                'unit_price' => ''
            ])
        @endif
    </div>
    <template id="item-template">
        @include('admin::invoices._item_group', [
            'index' => '__INDEX__',
            'description' => '',
            'quantity' => 1,
            'unit_price' => ''
        ])
    </template>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.invoices') }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.create') }}</button>
    </div>
</form>
<script>
function invoiceForm() {
    return {
        itemIndex: {{ old('invoice_items') ? count(old('invoice_items')) : 1 }},
        addItem() {
            const template = document.getElementById('item-template');
            const container = document.getElementById('items-container');
            
            let templateHTML = template.innerHTML;
            
            templateHTML = templateHTML.replaceAll('__INDEX__', this.itemIndex);
            
            const wrapper = document.createElement('div');
            wrapper.innerHTML = templateHTML.trim();
            container.appendChild(wrapper.firstChild);
            
            this.itemIndex++;
        }
    }
}

function removeItemCard(button) {
    const container = document.getElementById('items-container');
    const itemCard = button.closest('.item-card');
    
    itemCard.remove();
}
</script>
@endsection
