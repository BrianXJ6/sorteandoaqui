<?php

namespace App\Http\Requests\User\Auth;

use App\Rules\{
    GoogleRecaptcha,
    FirstAndLastName,
};

use App\Models\User;
use App\Data\User\SignUpData;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class SignUpRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['phone' => onlyNumbers($this->phone)]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z]{1,}[a-zA-Zà-úÀ-Ú]{1,}[a-zA-Zà-úÀ-Ú\.\s]{1,}$/', new FirstAndLastName],
            'email' => ['required', 'email', 'max:100', Rule::unique(User::class)],
            'phone' => ['required', 'string', 'size:11', 'regex:/^\d{11}$/', Rule::unique(User::class)],
            'password' => ['required', 'string', Password::defaults()],
            'referral_code' => ['sometimes', 'required', 'string', Rule::exists(User::class)],
            'recaptcha' => ['required', 'string', new GoogleRecaptcha],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['referral_code.exists' => __('validation.custom.url_referral_code')];
    }

    /**
     * Get Data from request
     *
     * @return \App\Data\User\SignUpData
     */
    public function data(): SignUpData
    {
        return SignUpData::from($this->safe());
    }
}
