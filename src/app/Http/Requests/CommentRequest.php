<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
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
            'comment' => 'required|string|max:255',
        ];
        return $rules;
    }

    public function messages()
    {
        return [
            'comment.required' => '入力必須、最大文字255',
        ];
    }
}
