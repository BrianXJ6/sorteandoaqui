<?php

namespace App\Http\Requests\User\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SignInRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->option === 'phone') $this->merge(['field' => onlyNumbers($this->field)]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'option' => ['required', 'in:email,phone'],
            'field' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Get credentials for signIn
     *
     * @return array
     */
    public function credentials(): array
    {
        return array_merge([$this->option => $this->safe()->field], $this->safe()->only('password'));
    }
}
