<?php

namespace App\Http\Requests\Auth;

use App\Domain\User\Enums\ContactType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(ContactType::class)],
            'identifier' => ['required', 'string', 'max:255'],
        ];
    }
}
