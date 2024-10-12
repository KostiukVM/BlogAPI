<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string|min:5',
            'user_id' => 'required|exists:users,id',
            'post_id' => 'required|exists:posts,id',
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Контент коментаря є обов’язковим.',
            'content.min' => 'Контент коментаря має містити щонайменше 5 символів.',
            'user_id.required' => 'Необхідно вказати ідентифікатор користувача.',
            'user_id.exists' => 'Користувача з таким ID не знайдено.',
            'post_id.required' => 'Необхідно вказати ідентифікатор посту.',
            'post_id.exists' => 'Пост із таким ID не знайдено.',
        ];
    }
}
