<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Carbon\Carbon;

class Age implements Rule
{
    public function passes($attribute, $value)
    {
        if (empty($value)) {
            return true;
        }

        $dob = Carbon::createFromFormat('Y-m-d', $value);
        $today = Carbon::now();

        return $today->diffInYears($dob) >= 18;
    }

    public function message()
    {
        return 'You must be at least 18 years old.';
    }
}
