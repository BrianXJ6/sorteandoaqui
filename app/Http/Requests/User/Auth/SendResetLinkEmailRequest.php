<?php

namespace App\Http\Requests\User\Auth;

use App\Rules\GoogleRecaptcha;
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
            'recaptcha' => ['required', 'string', new GoogleRecaptcha],
        ];
    }
}
