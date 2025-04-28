<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BomImportErrorExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Product Item Code',
            'Production Type',
            'Production Route',
            'Customizable',
            'Item Code',
            'Consumption Qty',
            'Cost per unit',
            'Station',
            'Error Reasons',
            'Created At',
        ];
    }

    public function map($row): array
    {
        return [
            $row->product_item_code,
            $row->production_type,
            $row->production_route_name,
            $row->customizable,
            $row->item_code,
            $row->consumption_qty,
            $row->cost_per_unit,
            $row->station_name,
            implode(', ', $row->reason ?? []),
            $row->created_at->toDateTimeString(),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 15,
            'C' => 15,
            'D' => 15,
            'E' => 10,
            'F' => 15,
            'G' => 10,
            'H' => 10,
            'I' => 50,
            'J' => 10
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('I')->getAlignment()->setWrapText(true);
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
