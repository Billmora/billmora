@extends('admin::layouts.app')

@section('title', "Package Field Create")

@section('body')
    <form action="{{ route('admin.packages.fields.store', ['package' => $package->id]) }}" method="POST"
        class="flex flex-col gap-5">
        @csrf
        <div class="flex flex-col lg:flex-row gap-5">
            <div class="w-full lg:w-2/3 h-fit grid gap-4 bg-white p-8 border-2 border-billmora-neutral-100 rounded-2xl">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-admin::input name="label" label="{{ __('admin/packages.fields.label') }}"
                        helper="{{ __('admin/packages.fields.label_helper') }}" placeholder="E.g. Character Name"
                        value="{{ old('label') }}" required />
                    <x-admin::input name="name" label="{{ __('admin/packages.fields.name') }}"
                        placeholder="E.g. character_name" helper="{{ __('admin/packages.fields.name_helper') }}"
                        value="{{ old('name') }}" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-admin::input name="helper" label="{{ __('admin/packages.fields.helper') }}"
                        helper="{{ __('admin/packages.fields.helper_helper') }}"
                        placeholder="Optional helper text below the field" value="{{ old('helper') }}" />
                    <x-admin::input name="default" label="{{ __('admin/packages.fields.default') }}"
                        helper="{{ __('admin/packages.fields.default_helper') }}" placeholder="Optional default value"
                        value="{{ old('default') }}" />
                </div>

                <x-admin::textarea name="options" label="{{ __('admin/packages.fields.options') }}"
                    placeholder="value|Label
value2|Label 2" helper="{{ __('admin/packages.fields.options_helper') }}"
                    rows="4">{{ old('options') }}</x-admin::textarea>

                <div x-data="{ conditionTarget: '{{ old('condition_target') }}' }" class="grid gap-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-admin::select name="condition_target" label="{{ __('admin/packages.fields.condition_target') }}" helper="{{ __('admin/packages.fields.condition_target_helper') }}" x-model="conditionTarget">
                            <option value="configuration" @selected(old('condition_target') == 'configuration')>Additional Configuration</option>
                            <option value="fields" @selected(old('condition_target') == 'fields')>Package Fields</option>
                        </x-admin::select>
                        
                        <x-admin::input name="condition_field" label="{{ __('admin/packages.fields.condition_field') }}"
                            placeholder="E.g. os" helper="{{ __('admin/packages.fields.condition_field_helper') }}" value="{{ old('condition_field') }}" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-admin::select name="condition_operator" label="{{ __('admin/packages.fields.condition_operator') }}" helper="{{ __('admin/packages.fields.condition_operator_helper') }}">
                            <option value="=" @selected(old('condition_operator') == '=')>Equals (=)</option>
                            <option value="!=" @selected(old('condition_operator') == '!=')>Not Equals (!=)</option>
                            <option value="in" @selected(old('condition_operator') == 'in')>In List (in)</option>
                            <option value="not_in" @selected(old('condition_operator') == 'not_in')>Not In List (not_in)</option>
                            <option value="truthy" @selected(old('condition_operator') == 'truthy')>Truthy (Has Value)</option>
                        </x-admin::select>

                        <x-admin::input name="condition_value" label="{{ __('admin/packages.fields.condition_value') }}"
                            placeholder="E.g. windows (comma separate for in/not_in)" helper="{{ __('admin/packages.fields.condition_value_helper') }}" value="{{ old('condition_value') }}" />
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-1/3 h-fit grid gap-4 bg-white p-8 border-2 border-billmora-neutral-100 rounded-2xl">
                <x-admin::select name="type" label="{{ __('admin/packages.fields.type') }}"
                    helper="{{ __('admin/packages.fields.type_helper') }}" required>
                    <option value="text" @selected(old('type') == 'text')>Text</option>
                    <option value="number" @selected(old('type') == 'number')>Number</option>
                    <option value="email" @selected(old('type') == 'email')>Email</option>
                    <option value="url" @selected(old('type') == 'url')>URL</option>
                    <option value="password" @selected(old('type') == 'password')>Password</option>
                    <option value="textarea" @selected(old('type') == 'textarea')>Textarea</option>
                    <option value="select" @selected(old('type') == 'select')>Select (Dropdown)</option>
                    <option value="radio" @selected(old('type') == 'radio')>Radio Buttons</option>
                    <option value="toggle" @selected(old('type') == 'toggle')>Toggle (Checkbox)</option>
                </x-admin::select>

                <div class="flex flex-col gap-4 mt-2">
                    <x-admin::toggle name="required" label="{{ __('admin/packages.fields.required') }}"
                        helper="{{ __('admin/packages.fields.required_helper') }}" :checked="(bool) old('required')" />
                    <x-admin::toggle name="visible_on_order" label="{{ __('admin/packages.fields.visible_on_order') }}"
                        helper="{{ __('admin/packages.fields.visible_on_order_helper') }}" :checked="old('visible_on_order') !== null ? (bool) old('visible_on_order') : true" />
                    <x-admin::toggle name="visible_on_invoice" label="{{ __('admin/packages.fields.visible_on_invoice') }}"
                        helper="{{ __('admin/packages.fields.visible_on_invoice_helper') }}" :checked="(bool) old('visible_on_invoice')" />
                </div>
            </div>
        </div>

        <div class="flex gap-4 ml-auto">
            <a href="{{ route('admin.packages.fields', ['package' => $package->id]) }}"
                class="bg-billmora-neutral-50 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 
                   px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors 
                   ease-in-out duration-150 cursor-pointer">
                {{ __('common.cancel') }}
            </a>
            <button type="submit"
                class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white 
                   rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.create') }}
            </button>
        </div>
    </form>
@endsection
