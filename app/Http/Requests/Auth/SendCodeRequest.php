<?php

namespace App\Http\Requests\Auth;

use App\Domain\User\Enums\ContactType;
use App\Rules\RecaptchaRule;
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
        $identifierRules = ['required', 'string', 'max:255'];

        if ($this->input('type') === ContactType::Email->value) {
            $identifierRules[] = 'email';
            $identifierRules[] = 'indisposable';

            if ($this->routeIs('register.send-code')) {
                $identifierRules[] = Rule::unique('users', 'email');
            }
        }

        if ($this->input('type') === ContactType::Phone->value && $this->routeIs('register.send-code')) {
            $identifierRules[] = Rule::unique('users', 'phone');
        }

        $rules = [
            'type' => ['required', Rule::enum(ContactType::class)],
            'identifier' => $identifierRules,
        ];

        if (! $this->session()->get('telegram_linked') && config('services.recaptcha.site_key')) {
            $rules['recaptcha_token'] = ['required', new RecaptchaRule('send_code')];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'identifier.indisposable' => __('validation.indisposable'),
            'identifier.unique' => __('validation.identifier_already_registered'),
        ];
    }
}
