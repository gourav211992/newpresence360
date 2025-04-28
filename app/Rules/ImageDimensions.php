<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ImageDimensions implements Rule
{
    private $width;
    private $height;

    public function __construct($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function passes($attribute, $value)
    {
        // Get image dimensions
        list($imgWidth, $imgHeight) = getimagesize($value->getRealPath());

        // Compare the actual dimensions with the expected dimensions
        return $imgWidth == $this->width && $imgHeight == $this->height;
    }

    public function message()
    {
        return 'The :attribute must be exactly ' . $this->width . 'px by ' . $this->height . 'px.';
    }
}
