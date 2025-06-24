<?php
namespace App\Exports;

use App\Helpers\ConstantHelper;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;

class FailedFAExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

    public function collection()
    {
        return $this->items;
    }

    public function headings(): array
    {
        return [
            'Asset Code',
            'Asset Name',
            'Location',
            'Cost Center',
            'Category',
            'Ledger',
            'Capitalize Date',
            'Quantity',
            'Maintenance Schedule',
            'Useful Life',
            'Current Value',
            'Vendor',
            'Currency',
            'Book Date',
            'Import Status',
            'Import Remarks'
        ];
    }

    public function map($item): array
    {
        return [
            $item->asset_code ?? '',
            $item->asset_name ?? '',
            $item->location ?? '',
            $item->cost_center ?? '',
            $item->category ?? '',
            $item->ledger ?? '',
            $item->capitalize_date ?? '',
            $item->quantity ?? '',
            $item->maintenance_schedule ?? '',
            $item->useful_life ?? '',
            $item->current_value ?? '',
            $item->vendor ?? '',
            $item->currency ?? '',
            $item->book_date ?? '',
            $item->import_status ?? 'Failed',
            $item->import_remarks ?? 'No remarks provided'
        ];
    }

    public function styles(Worksheet $sheet)
{
    $styles = [];
    $headings = $this->headings();
    $totalColumns = count($headings);

    for ($col = 1; $col <= $totalColumns; $col++) {
        $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
        $headingText = $headings[$col - 1];

        // Estimate width: length of heading * factor
        $approxWidth = max(strlen($headingText) * 1.2, ConstantHelper::EXCEL_COLUMN_WIDTH_DEFAULT);

        $styles["{$columnLetter}1"] = [
            'font' => [
                'color' => ['argb' => ConstantHelper::EXCEL_FONT_COLOR_BLACK],
                'bold' => ConstantHelper::EXCEL_FONT_BOLD,
            ],
            'fill' => [
                'fillType' => ConstantHelper::EXCEL_FILL_TYPE_SOLID,
                'startColor' => ['argb' => ConstantHelper::EXCEL_FILL_YELLOW],
            ],
            'alignment' => [
                'wrapText' => ConstantHelper::EXCEL_ALIGNMENT_WRAP,
                'vertical' => ConstantHelper::EXCEL_ALIGNMENT_VERTICAL_CENTER,
                'horizontal' => ConstantHelper::EXCEL_ALIGNMENT_HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => ConstantHelper::EXCEL_BORDER_STYLE_THIN,
                    'color' => ['argb' => ConstantHelper::EXCEL_BORDER_COLOR_BLACK],
                ],
            ],
        ];

        // Set dynamic width based on heading length
        $sheet->getColumnDimension($columnLetter)->setWidth($approxWidth);

        // Ensure cell text wraps
        $sheet->getStyle("{$columnLetter}")->getAlignment()->setWrapText(ConstantHelper::EXCEL_ALIGNMENT_WRAP);
    }

    return $styles;
}
}
