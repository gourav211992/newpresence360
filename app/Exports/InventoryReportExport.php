<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $customHeader;
    protected $filterHeader;
    protected $headers;
    protected $data;

    public function __construct($customHeader, $filterHeader, $headers, $data)
    {
        $this->customHeader = $customHeader;
        $this->filterHeader = $filterHeader;
        $this->headers = $headers;
        $this->data = $data;
    }

    // Clean the data to remove unwanted spaces or HTML tags and return the collection
    public function collection()
    {
        // Clean the data: remove unwanted spaces and strip HTML tags
        $cleanData = array_map(function ($row) {
            return array_map(function ($cell) {
                if (is_string($cell)) {
                    $cleaned = strip_tags(trim($cell));
                    $cleaned = preg_replace('/\s+/', ' ', $cleaned); // Normalize whitespace
                    return $cleaned;
                }
                return $cell;
            }, $row);
        }, $this->data);

        $collection = collect([$this->customHeader]);
        $collection = $collection->merge(collect([$this->filterHeader]));
        $collection = $collection->merge(collect([$this->headers]));
        $collection = $collection->merge(collect($cleanData));

        return $collection;
    }

    public function headings(): array
    {
        return [];
    }

    public function map($row): array
    {
        return (array) $row;
    }

    // Apply styles to the sheet
    public function styles(Worksheet $sheet)
    {
        $columnCount = count($this->headers);
        $lastColumnLetter = Coordinate::stringFromColumnIndex($columnCount);

        // Style the custom header (row 1)
        $sheet->getStyle("A1:{$lastColumnLetter}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
            ],
        ]);
        $sheet->getStyle("A2:{$lastColumnLetter}2")->applyFromArray([
            'font' => [
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF28A745'],
            ],
        ]);

        // Style the actual table headers (row 2)
        $sheet->getStyle("A3:{$lastColumnLetter}3")->applyFromArray([
            'font' => [
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF6B12B7'],
            ],
        ]);
    }
}
