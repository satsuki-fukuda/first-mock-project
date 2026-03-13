<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'image' => 'nullable|image|mimes:jpeg,png',
            'name' => 'required|string|max:20',
            'postal_code' => 'required',
            'address' => 'required|string|max:255',
        ];
        return $rules;
    }

    public function messages()
    {
        return [
            'image.required' => '拡張子が.jpegもしくは.png',
            'name.required' => 'お名前を入力してください',
            'postal_code.required' => '郵便番号はハイフンありの形式（000-0000）で入力してください',
            'address.required' => '住所を入力してください',
        ];
    }
}
