<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidAppliNo implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (strpos($value, '-') === 0) {
            return false;
        }

        return preg_match('/^[a-zA-Z0-9-]+$/', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must not start with a negative sign and can only contain letters, numbers, and dashes.';
    }
}
