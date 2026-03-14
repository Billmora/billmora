@extends('admin::layouts.app')

@section('title', 'Variant Options - Edit')

@section('body')
<form
    action="#"
    method="POST"
    class="flex flex-col gap-5"
    x-data="{
        currentIndex: {{ count($pricings ?? []) }},
        addPrice() {
            let template = document.getElementById('pricing-template').innerHTML;
            template = template.replace(/__INDEX__/g, this.currentIndex);
            this.$refs.container.insertAdjacentHTML('beforeend', template);
            this.currentIndex++;
        }
    }"
>
    @csrf
    @method('PUT')
    <div class="w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl space-y-4">
        <div class="grid grid-cols-none md:grid-cols-2 gap-5">
            <x-admin::input
                name="variant_options_name"
                type="text"
                label="{{ __('admin/variants.options.name_label') }}"
                helper="{{ __('admin/variants.options.name_helper') }}"
                value="{{ $option->name }}"
                required
            />
            <x-admin::input
                name="variant_options_value"
                type="text"
                label="{{ __('admin/variants.options.value_label') }}"
                helper="{{ __('admin/variants.options.value_helper') }}"
                value="{{ $option->value }}"
                required
            />
        </div>
    </div>
    <div class="flex flex-col sm:flex-row justify-between gap-4 sm:items-center">
        <div>
            <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/variants.options.pricing_label') }}</h4>
            <span class="text-slate-500">{{ __('admin/variants.options.pricing_helper') }}</span>
        </div>
        <button
            type="button"
            x-on:click="addPrice()"
            class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-4 py-2 ml-auto text-white rounded-lg transition-colors duration-150 cursor-pointer"
        >
            {{ __('admin/variants.options.add_new_price_label') }}
        </button>
    </div>
    <div id="pricing-container" x-ref="container" class="flex flex-col gap-4">
        @foreach(($pricings ?? []) as $index => $pricing)
            @include('admin::variants.option._pricing_group', [
                'index' => $index,
                'pricing' => $pricing,
                'currencies' => $currencies,
                'canDelete' => $index > 0,
            ])
        @endforeach
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.variants.options', ['variant' => $variant->id]) }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors duration-150">{{ __('common.cancel') }}</a>
        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors duration-150 cursor-pointer">{{ __('common.update') }}</button>
    </div>
    <template id="pricing-template">
        @include('admin::variants.option._pricing_group', [
            'index' => '__INDEX__',
            'pricing' => ['name' => '', 'type' => 'free', 'rates' => []],
            'currencies' => $currencies,
            'canDelete' => true,
        ])
    </template>
</form>
@endsection
