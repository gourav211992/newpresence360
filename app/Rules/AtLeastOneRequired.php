<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AtLeastOneRequired implements Rule
{
    protected $errors = []; 

    public function passes($attribute, $value)
{
    if (!is_array($value)) {
        return true; // If not an array, consider it valid
    }
 
    
    foreach ($value as $index => $attr) {
        // Use null coalescing operator to avoid undefined index notice
        $attributeGroupId = $attr['attribute_group_id'] ?? null;
        
        // Debugging output
        dd($attributeGroupId);

        // Only validate if attribute_group_id is not null
        if (!is_null($attributeGroupId)) {
            // Check if both attribute_id and all_checked are empty
            if (empty($attr['attribute_id']) && empty($attr['all_checked'])) {
                $this->addError($index, 'Both attribute_id and all_checked must not be null when attribute_group_id is not null.');
            }
        }
        // If attribute_group_id is null, do not check anything
    }

    return empty($this->errors); // Return true if no errors
}


    public function message()
    {
        return implode(' ', $this->errors); // Return all error messages as a single string
    }

    protected function addError($index, $message)
    {
        // Add an error message for the specific index
        $this->errors[] = "Index $index: $message";
    }
}
