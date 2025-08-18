<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GenericSampleExport implements FromArray, WithHeadings
{
    protected $headers;

    public function __construct(array $headers)
    {
        $this->headers = $headers;
    }

    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return array_values($this->headers);
    }
}
