<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GenericItemExport implements FromCollection, WithHeadings
{
    protected array $invalidRows;
    protected array $headerMap;

    public function __construct(array $invalidRows, array $headerMap)
    {
        $this->invalidRows = $invalidRows;
        $this->headerMap   = $headerMap;
    }

    /**
     * Build the rows for export
     */
  public function collection()
    {
        $rows = [];

        foreach ($this->invalidRows as $row) {
            $errorMessage = !empty($row['errors']) ? implode('; ', $row['errors']) : '';

            $dataRow = [];
            foreach ($this->headerMap as $key => $label) {
                $dataRow[] = $row[$key] ?? ''; // Use [] to make numeric array
            }

            $dataRow[] = $errorMessage; // Append Errors column
            $rows[] = $dataRow;
        }

        return new Collection($rows);
    }

    public function headings(): array
    {
        return array_merge(array_values($this->headerMap), ['Errors']);
    }
}