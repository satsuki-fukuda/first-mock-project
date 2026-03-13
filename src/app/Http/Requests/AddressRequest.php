<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
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
        $rules = [
            'postal_code' => 'required|string|max:8',
            'address' => 'required|string|max:255',
        ];
        return $rules;
    }

    public function messages()
    {
        return [
            'postal_code.required' => '入力必須、ハイフンありの8文字',
            'address.required' => '入力必須',
        ];
    }
}
