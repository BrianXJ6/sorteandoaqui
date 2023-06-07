<?php

namespace App\Http\Requests\Admin\Account;

use App\Models\Admin;
use Illuminate\Validation\Rule;
use App\Data\Admin\UpdatePersonalData;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonalDataRequest extends FormRequest
{
    /**
     * Create a new Personal Data Update Request instance
     *
     * @param \App\Models\Admin $admin
     */
    public function __construct(private Admin $admin)
    {
        $this->admin = auth()->user();
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) $this->merge(['phone' => onlyNumbers($this->phone)]);

        $this->replace(array_filter($this->all(), fn ($value, $key) => ($value !== $this->admin->{$key}), true));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $uniqueRule = Rule::unique(Admin::class)->ignore($this->admin->id);

        return [
            'name' => ['sometimes', 'required', 'string', 'max:100', 'regex:/^[a-zA-Z]{1,}[a-zA-Zà-úÀ-Ú]{1,}[a-zA-Zà-úÀ-Ú\.\s]{1,}$/'],
            'email' => ['sometimes', 'required', 'email', 'max:100', $uniqueRule],
            'phone' => ['sometimes', 'required', 'string', 'size:11', 'regex:/^\d{11}$/', $uniqueRule],
        ];
    }

    /**
     * Get data
     *
     * @return \App\Data\Admin\UpdatePersonalData
     */
    public function data(): UpdatePersonalData
    {
        return UpdatePersonalData::from($this->safe());
    }
}
