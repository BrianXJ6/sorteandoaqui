<?php

namespace App\Http\Requests\User\Auth;

use App\Rules\GoogleRecaptcha;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class PasswordUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'recaptcha' => [Rule::requiredIf(config('services.google_recaptcha.enabled')), 'string', new GoogleRecaptcha],
        ];
    }

    /**
     * Get credentials
     *
     * @return array
     */
    public function credentials(): array
    {
        return $this->safe()->only('token', 'email', 'password');
    }
}
