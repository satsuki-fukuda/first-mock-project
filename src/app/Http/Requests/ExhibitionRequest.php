<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png',
            'categories' => 'required|array|min:1',
            'condition' => 'required|integer',
            'price' => 'required|integer|min:0|max:10000',
        ];
        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => '入力必須',
            'description.required' => '入力必須、最大文字数255',
            'image.required' => 'アップロード必須、拡張子が.jpegもしくは.png',
            'categories.required' => '選択必須',
            'condition.required' => '選択必須',
            'price.required' => '入力必須、数字で入力、0円以上',
        ];
    }
}
