<?php

namespace App\Traits;

use Carbon\Carbon;

trait DateFormatTrait
{
    /**
     * Format a given date or datetime.
     *
     * @param  string|\DateTime|null $date
     * @param  string $format
     * @return string|null
     */
    public function formatDate($date, $format = 'd M, Y')
    {
        if (empty($date)) {
            return null;
        }
        
        return Carbon::parse($date)->format($format);
    }

    /**
     * Get the formatted created_at attribute.
     *
     * @param  string $format
     * @return string|null
     */
    public function getFormattedCreatedAt($format = 'd M, Y')
    {
        return $this->formatDate($this->created_at, $format);
    }

    /**
     * Get the formatted updated_at attribute.
     *
     * @param  string $format
     * @return string|null
     */
    public function getFormattedUpdatedAt($format = 'd M, Y')
    {
        return $this->formatDate($this->updated_at, $format);
    }

    /**
     * Get the formatted date for any specified column.
     *
     * @param  string $column
     * @param  string $format
     * @return string|null
     */
    public function getFormattedDate($column, $format = 'd/m/Y'/*'d M, Y'*/)
    {
        // Check if the column exists in the model's attributes
        if (!isset($this->{$column}) || empty($this->{$column})) {
            return null;
        }

        return $this->formatDate($this->{$column}, $format);
    }
}
