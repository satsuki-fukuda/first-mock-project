<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
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
            'payment_method' => 'required|string|in:konbini,card',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = auth()->user();
            if (empty($user->postal_code) || empty($user->address)) {
                $validator->errors()->add('address', '配送先を登録してください。');
            }
        });
    }


    public function messages()
    {
        return [
            'payment_method.required' => '支払い方法を選択してください',
        ];
    }
}
