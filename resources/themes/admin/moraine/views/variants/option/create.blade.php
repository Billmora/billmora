@extends('admin::layouts.app')

@section('title', 'Variant Options - Create')

@section('body')
<form
    action="{{ route('admin.variants.options.store', ['variant' => $variant->id]) }}"
    method="POST"
    class="flex flex-col gap-5"
    x-data="{
        currentIndex: {{ count(old('pricings', [['name' => 'Free', 'type' => 'free']])) }},
        addPrice() {
            let template = document.getElementById('pricing-template').innerHTML;
            template = template.replace(/__INDEX__/g, this.currentIndex);
            this.$refs.container.insertAdjacentHTML('beforeend', template);
            this.currentIndex++;
        }
    }"
>
    @csrf
    @php
        $pricings = old('pricings', [['name' => 'Free', 'type' => 'free']]);
    @endphp
    <div class="w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl space-y-4">
        <div class="grid grid-cols-none md:grid-cols-2 gap-5">
            <x-admin::input
                name="variant_options_name"
                type="text"
                label="{{ __('admin/variants.options.name_label') }}"
                helper="{{ __('admin/variants.options.name_helper') }}"
                value="{{ old('variant_options_name') }}"
                required
            />
            <x-admin::input
                name="variant_options_value"
                type="text"
                label="{{ __('admin/variants.options.value_label') }}"
                helper="{{ __('admin/variants.options.value_helper') }}"
                value="{{ old('variant_options_value') }}"
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
            class="bg-billmora-primary hover:bg-billmora-primary-hover px-4 py-2 ml-auto text-white rounded-lg transition-colors duration-150 cursor-pointer"
        >
            {{ __('admin/variants.options.add_new_price_label') }}
        </button>
    </div>
    <div id="pricing-container" x-ref="container" class="flex flex-col gap-4">
        @foreach($pricings as $index => $pricing)
            @include('admin::variants.option._pricing_group', [
                'index' => $index,
                'pricing' => $pricing,
                'currencies' => $currencies,
                'canDelete' => $index > 0,
            ])
        @endforeach
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.variants.options', ['variant' => $variant->id]) }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors duration-150">{{ __('common.cancel') }}</a>
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors duration-150 cursor-pointer">{{ __('common.create') }}</button>
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