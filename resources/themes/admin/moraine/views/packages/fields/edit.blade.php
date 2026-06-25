@extends('admin::layouts.app')

@section('title', "Package Field Edit")

@section('body')
    @php
        $opts = $field->options ?? [];
        $lines = [];
        foreach ($opts as $k => $v) {
            if ($k === $v || is_numeric($k)) {
                $lines[] = $v;
            } else {
                $lines[] = "{$k}|{$v}";
            }
        }
        $optionsString = implode("\n", $lines);
    @endphp

    <form action="{{ route('admin.packages.fields.update', ['package' => $package->id, 'field' => $field->id]) }}"
        method="POST" class="flex flex-col gap-5">
        @csrf
        @method('PUT')
        <div class="flex flex-col lg:flex-row gap-5">
            <div class="w-full lg:w-2/3 h-fit grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-admin::input name="label" label="{{ __('admin/packages.fields.label') }}"
                        helper="{{ __('admin/packages.fields.label_helper') }}" placeholder="E.g. Character Name"
                        value="{{ old('label', $field->label) }}" required />
                    <x-admin::input name="name" label="{{ __('admin/packages.fields.name') }}"
                        placeholder="E.g. character_name" helper="{{ __('admin/packages.fields.name_helper') }}"
                        value="{{ old('name', $field->name) }}" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-admin::input name="helper" label="{{ __('admin/packages.fields.helper') }}"
                        helper="{{ __('admin/packages.fields.helper_helper') }}"
                        placeholder="Optional helper text below the field" value="{{ old('helper', $field->helper) }}" />
                    <x-admin::input name="default" label="{{ __('admin/packages.fields.default') }}"
                        helper="{{ __('admin/packages.fields.default_helper') }}" placeholder="Optional default value"
                        value="{{ old('default', $field->default) }}" />
                </div>

                <x-admin::textarea name="options" label="{{ __('admin/packages.fields.options') }}"
                    placeholder="value|Label
value2|Label 2" helper="{{ __('admin/packages.fields.options_helper') }}"
                    rows="4">{{ old('options', implode("\n", $lines)) }}</x-admin::textarea>
            </div>

            <div class="w-full lg:w-1/3 h-fit grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                <x-admin::select name="type" label="{{ __('admin/packages.fields.type') }}"
                    helper="{{ __('admin/packages.fields.type_helper') }}" required>
                    <option value="text" @selected(old('type', $field->type) == 'text')>Text</option>
                    <option value="number" @selected(old('type', $field->type) == 'number')>Number</option>
                    <option value="email" @selected(old('type', $field->type) == 'email')>Email</option>
                    <option value="url" @selected(old('type', $field->type) == 'url')>URL</option>
                    <option value="password" @selected(old('type', $field->type) == 'password')>Password</option>
                    <option value="textarea" @selected(old('type', $field->type) == 'textarea')>Textarea</option>
                    <option value="select" @selected(old('type', $field->type) == 'select')>Select (Dropdown)</option>
                    <option value="radio" @selected(old('type', $field->type) == 'radio')>Radio Buttons</option>
                    <option value="toggle" @selected(old('type', $field->type) == 'toggle')>Toggle (Checkbox)</option>
                </x-admin::select>

                <div class="flex flex-col gap-4 mt-2">
                    <x-admin::toggle name="required" label="{{ __('admin/packages.fields.required') }}"
                        helper="{{ __('admin/packages.fields.required_helper') }}" :checked="(bool) old('required', $field->required)" />
                    <x-admin::toggle name="visible_on_order" label="{{ __('admin/packages.fields.visible_on_order') }}"
                        helper="{{ __('admin/packages.fields.visible_on_order_helper') }}" :checked="(bool) old('visible_on_order', $field->visible_on_order)" />
                    <x-admin::toggle name="visible_on_invoice" label="{{ __('admin/packages.fields.visible_on_invoice') }}"
                        helper="{{ __('admin/packages.fields.visible_on_invoice_helper') }}" :checked="(bool) old('visible_on_invoice', $field->visible_on_invoice)" />
                </div>
            </div>
        </div>

        <div class="flex gap-4 ml-auto">
            <a href="{{ route('admin.packages.fields', ['package' => $package->id]) }}"
                class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 
                   px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors 
                   ease-in-out duration-150 cursor-pointer">
                {{ __('common.cancel') }}
            </a>
            <button type="submit"
                class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white 
                   rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.update') }}
            </button>
        </div>
    </form>
@endsection
