<?php

namespace App\Http\Requests\User\Auth;

use App\Rules\GoogleRecaptcha;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SendResetLinkEmailRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'recaptcha' => [Rule::requiredIf(config('services.google_recaptcha.enabled')), 'string', new GoogleRecaptcha],
        ];
    }
}
