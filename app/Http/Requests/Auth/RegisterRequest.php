<?php

namespace App\Http\Requests\Auth;

use App\Domain\User\Enums\ContactType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
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
        $identifierRules = ['required', 'string', 'max:255'];

        if ($this->input('type') === ContactType::Email->value) {
            $identifierRules[] = 'email';
            $identifierRules[] = 'indisposable';
            $identifierRules[] = Rule::unique('users', 'email');
        }

        if ($this->input('type') === ContactType::Phone->value) {
            $identifierRules[] = Rule::unique('users', 'phone');
        }

        return [
            'type' => ['required', Rule::enum(ContactType::class)],
            'identifier' => $identifierRules,
            'code' => ['required', 'string', 'size:6'],
            'ref' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'identifier.indisposable' => __('validation.indisposable'),
            'identifier.unique' => __('validation.identifier_already_registered'),
        ];
    }
}
