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
            'visible_on_order' => 'nullable|boolean',
            'visible_on_invoice' => 'nullable|boolean',
            'helper' => 'nullable|string|max:255',
            'default' => 'nullable|string|max:255',
            'options' => 'nullable|string',
            'condition_target' => 'nullable|string|in:configuration,fields',
            'condition_field' => 'nullable|string|required_with:condition_target',
            'condition_operator' => 'nullable|string|in:=,!=,in,not_in,truthy|required_with:condition_target',
            'condition_value' => 'nullable|string',
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
            'visible_on_order' => __('admin/packages.fields.visible_on_order'),
            'visible_on_invoice' => __('admin/packages.fields.visible_on_invoice'),
            'helper' => __('admin/packages.fields.helper'),
            'default' => __('admin/packages.fields.default'),
            'options' => __('admin/packages.fields.options'),
            'condition_target' => __('admin/packages.fields.condition_target'),
            'condition_field' => __('admin/packages.fields.condition_field'),
            'condition_operator' => __('admin/packages.fields.condition_operator'),
            'condition_value' => __('admin/packages.fields.condition_value'),
        ];
    }
}
