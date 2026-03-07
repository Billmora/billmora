<div class="flex flex-col gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl relative item-card">
    <div class="flex justify-end">
        <button type="button" onclick="removeItemCard(this)" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-2 py-1 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.delete') }}
        </button>
    </div>
    @if (!empty($id))
        <input type="hidden" name="invoice_items[{{ $index }}][id]" value="{{ $id }}">
    @endif
    <x-admin::textarea
        name="invoice_items[{{ $index }}][description]"
        label="{{ __('admin/invoices.invoice_items.description_label') }}"
        helper="{{ __('admin/invoices.invoice_items.description_helper') }}"
        rows="3"
        required
    >{{ $description ?? '' }}</x-admin::textarea>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-admin::input
            name="invoice_items[{{ $index }}][quantity]"
            type="number"
            min="1"
            label="{{ __('admin/invoices.invoice_items.quantity_label') }}"
            helper="{{ __('admin/invoices.invoice_items.quantity_helper') }}"
            value="{{ $quantity ?? 1 }}"
            required
        />
        <x-admin::input
            name="invoice_items[{{ $index }}][unit_price]"
            type="number"
            label="{{ __('admin/invoices.invoice_items.unit_price_label') }}"
            helper="{{ __('admin/invoices.invoice_items.unit_price_helper') }}"
            value="{{ $unit_price ?? '' }}"
            required
        />
    </div>
</div>