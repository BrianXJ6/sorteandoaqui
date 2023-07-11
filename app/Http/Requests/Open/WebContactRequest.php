<?php

namespace App\Http\Requests\Open;

use App\Rules\GoogleRecaptcha;
use Illuminate\Validation\Rule;
use App\Data\Open\WebContactData;
use Illuminate\Foundation\Http\FormRequest;

class WebContactRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'phone' => ['required', 'string', 'size:11', 'regex:/^\d{11}$/'],
            'subject' => ['required', 'string', 'in:Dúvidas,Elogios,Criticas,Sugestões'],
            'message' => ['required', 'string', 'between:20,1024'],
            'recaptcha' => [Rule::requiredIf(config('services.google_recaptcha.enabled')), 'string', new GoogleRecaptcha],
        ];
    }

    /**
     * Get Data from request
     *
     * @return \App\Data\Open\WebContactData
     */
    public function data(): WebContactData
    {
        return WebContactData::from($this->safe());
    }
}
