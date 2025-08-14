<?php

namespace App\Exports;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Models\UploadItemMaster;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FailedPendingPaymentExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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
        $headings = [
            'S.No',
            'Ledger Name',
            'Ledger Group',
            'Series',
            'Voucher No.',
            'Balance',
            'Settle Amount',
            'Remarks',
        ];

        return $headings;
    }

    public function map($item): array
    {
        static $index = 1;
        $data = [
            $index++,
            $item->ledger_name,
            $item->ledger_group,
            $item->series,
            $item->voucher_no,
            Helper::formatIndianNumber($item->balance),
            Helper::formatIndianNumber($item->settle_amount),
            $item->import_remarks,
        ];


        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [];
        $requiredColumns = range(1, 6);
        $totalColumns = count($this->headings());
        $remarksColIndex = $totalColumns;
        foreach ($requiredColumns as $col) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
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
            $sheet->getColumnDimension($columnLetter)->setWidth(ConstantHelper::EXCEL_COLUMN_WIDTH_DEFAULT);
                $sheet->getStyle("{$columnLetter}")->getAlignment()->setWrapText(ConstantHelper::EXCEL_ALIGNMENT_WRAP);
        }


        $totalColumns = count($this->headings());
        for ($col = 6; $col <= $totalColumns; $col++) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getStyle("{$columnLetter}1")->applyFromArray([
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
            ]);
            $sheet->getColumnDimension($columnLetter)->setWidth(ConstantHelper::EXCEL_COLUMN_WIDTH_DEFAULT);
                $sheet->getStyle("{$columnLetter}")->getAlignment()->setWrapText(ConstantHelper::EXCEL_ALIGNMENT_WRAP);
        }
        return $styles;
    }
}
