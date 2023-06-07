<?php

namespace App\Rules;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Contracts\Validation\ValidationRule;

class FirstAndLastName implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param \Closure $fail
     *
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $brokenName = Str::of($value)->squish()->split('/[\s ]+/');

        if ($brokenName->count() < 2) $fail(__('validation.custom.first_and_last_name'));
    }
}
