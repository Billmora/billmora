<?php

namespace App\Http\Requests\Admin\Packages;

use Illuminate\Foundation\Http\FormRequest;

class FieldRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'label' => 'required|string|max:255',
            'name' => 'nullable|string|max:255|alpha_dash',
            'type' => 'required|string|in:text,number,email,url,password,textarea,select,radio,toggle',
            'required' => 'nullable|boolean',
            'admin_only' => 'nullable|boolean',
            'visible_on_order' => 'nullable|boolean',
            'visible_on_invoice' => 'nullable|boolean',
            'helper' => 'nullable|string|max:255',
            'default' => 'nullable|string|max:255',
            'options' => 'nullable|string',
        ];
    }
    
    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'label' => __('admin/packages.fields.label'),
            'name' => __('admin/packages.fields.name'),
            'type' => __('admin/packages.fields.type'),
            'required' => __('admin/packages.fields.required'),
            'admin_only' => __('admin/packages.fields.admin_only'),
            'visible_on_order' => __('admin/packages.fields.visible_on_order'),
            'visible_on_invoice' => __('admin/packages.fields.visible_on_invoice'),
            'helper' => __('admin/packages.fields.helper'),
            'default' => __('admin/packages.fields.default'),
            'options' => __('admin/packages.fields.options'),
        ];
    }
}
