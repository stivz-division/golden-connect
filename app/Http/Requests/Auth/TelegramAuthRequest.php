<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class TelegramAuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user' => ['required', 'string'],
            'hash' => ['required', 'string'],
            'auth_date' => ['required', 'integer'],
        ];
    }
}
