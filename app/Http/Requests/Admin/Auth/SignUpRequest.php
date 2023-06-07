<?php

namespace App\Http\Requests\Admin\Auth;

use App\Models\Admin;
use App\Data\Admin\SignUpData;
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
        $uniqueRule = Rule::unique(Admin::class);

        return [
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z]{1,}[a-zA-Zà-úÀ-Ú]{1,}[a-zA-Zà-úÀ-Ú\.\s]{1,}$/'],
            'email' => ['required', 'email', 'max:100', $uniqueRule],
            'phone' => ['required', 'string', 'size:11', 'regex:/^\d{11}$/', $uniqueRule],
            'password' => ['required', 'string', Password::defaults()],
        ];
    }

    /**
     * Get Data from request
     *
     * @return \App\Data\Admin\SignUpData
     */
    public function data(): SignUpData
    {
        return SignUpData::from($this->safe());
    }
}
