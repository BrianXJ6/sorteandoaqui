<?php

namespace App\Http\Requests\User\Account;

use App\Data\User\UpdatePersonalData;
use App\Models\User;
use App\Rules\FirstAndLastName;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonalDataRequest extends FormRequest
{
    /**
     * Create a new Personal Data Update Request instance
     *
     * @param \App\Models\User $user
     */
    public function __construct(private User $user)
    {
        $this->user = auth()->user();
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) $this->merge(['phone' => onlyNumbers($this->phone)]);
        if ($this->has('cpf')) $this->merge(['cpf' => onlyNumbers($this->cpf)]);

        $this->replace(
            array_filter($this->all(), function ($value, $key) {
                switch (true) {
                    case ($key === 'cpf' && !empty($this->user->cpf)):
                    case ($value === $this->user->{$key}):
                        return;
                        break;

                    default:
                        return $value;
                        break;
                }
            }, true)
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $cpfRequiredRule = Rule::requiredIf(empty($this->user->cpf));
        $uniqueRule = Rule::unique(User::class)->ignore($this->user->id);

        return [
            'cpf' => [$cpfRequiredRule, 'string', 'size:11', 'regex:/^\d{11}$/', $uniqueRule],
            'nick' => ['sometimes', 'required', 'string', 'between:3,10'],
            'name' => ['sometimes', 'required', 'string', 'max:100', 'regex:/^[a-zA-Z]{1,}[a-zA-Zà-úÀ-Ú]{1,}[a-zA-Zà-úÀ-Ú\.\s]{1,}$/', new FirstAndLastName],
            'email' => ['sometimes', 'required', 'email', 'max:100', $uniqueRule],
            'phone' => ['sometimes', 'required', 'string', 'size:11', 'regex:/^\d{11}$/', $uniqueRule],
            'referral_code' => ['sometimes', 'required', 'string', 'size:10', 'regex:/^\w{10}$/', $uniqueRule],
        ];
    }

    /**
     * Get data
     *
     * @return \App\Data\User\UpdatePersonalData
     */
    public function data(): UpdatePersonalData
    {
        return UpdatePersonalData::from($this->safe());
    }
}
