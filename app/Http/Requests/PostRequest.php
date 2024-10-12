<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Заголовок є обов’язковим.',
            'title.max' => 'Заголовок не повинен перевищувати 255 символів.',
            'content.required' => 'Контент є обов’язковим.',
            'content.min' => 'Контент повинен містити щонайменше 10 символів.',
        ];
    }
}
