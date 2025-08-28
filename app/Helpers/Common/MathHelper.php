<?php

namespace App\Helpers\Common;

class MathHelper
{
    /**
     * Safe Division
     *
     * @param float|int $numerator
     * @param float|int $denominator
     * @param float|int $default
     * @return float|int
     */
    public static function safeDivide($numerator, $denominator, $default = 0)
    {
        return ($denominator != 0) ? ($numerator / $denominator) : $default;
    }
}
