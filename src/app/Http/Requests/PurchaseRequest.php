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
            // 文字列であること、かつ指定の選択肢内であることをバリデート
            'payment_method' => 'required|string|in:konbini,card',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = auth()->user();
            // ユーザーの住所情報が空でないかチェック
            if (empty($user->postal_code) || empty($user->address)) {
                // 'address' というキー名でエラーを登録
                $validator->errors()->add('address', '配送先を登録してください。');
            }
        });
    }


    public function messages()
    {
        return [
            'payment_method.required' => '支払い方法を選択してください',
            'payment_method.in' => '有効な支払い方法を選択してください',
        ];
    }
}
