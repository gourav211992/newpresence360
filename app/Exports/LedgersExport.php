<?php

namespace App\Exports;

use App\Helpers\ConstantHelper;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Services\LedgerImportExportService;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LedgersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $items;
    protected $service;

    public function __construct($items, LedgerImportExportService $service)
    {
        $this->items = $items;
        $this->service = $service;
    }

    public function collection()
    {
        return $this->items;
    }

    public function headings(): array
    {
        $headings = [
            'S.No',
            'Code',
            'Name',
            'Group',
            'Status',
            // 'tds_section',
            // 'tds_percentage',
            // 'tcs_section',
            // 'tcs_percentage',
            // 'tax_type',
            // 'tax_percentage',
            'Remarks',
        ];

        return $headings;
    }

    public function map($item): array
    {
        static $index = 1;
        $groupNames = $this->service->getGroupNamesByIds($item->ledger_group_id);
        $tdsSections = ConstantHelper::getTdsSections();
        $tcsSections = ConstantHelper::getTcsSections();
        $taxTypes    = ConstantHelper::getTaxTypes();
        $status = 
        $data = [
            $index++,
            $item->code,
            $item->name,
            implode(', ', $groupNames),
            ($item->status == 1) ? ConstantHelper::STATUS[0] : ConstantHelper::STATUS[1],
            // $tdsSections[$item->tds_section] ?? 'N/A',
            // $item->tds_percentage ?? 'N/A',
            // $tcsSections[$item->tcs_section] ?? 'N/A',
            // $item->tcs_percentage ?? 'N/A',
            // $taxTypes[$item->tax_type] ?? 'N/A',
            // $item->tax_percentage ?? 'N/A',
            'Success',
        ];

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [];
        $requiredColumns = range(1, 6);
        foreach ($requiredColumns as $col) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $styles["{$columnLetter}1"] = [
               'font' => [
                    'color' => ['argb' => ConstantHelper::EXCEL_FONT_COLOR_BLACK],
                    'bold' => ConstantHelper::EXCEL_FONT_BOLD,
                ],
                'fill' => [
                    'fillType' => ConstantHelper::EXCEL_FILL_TYPE_SOLID,
                    'startColor' => ['argb' => ConstantHelper::EXCEL_FILL_YELLOW]
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
                    'startColor' => ['argb' => ConstantHelper::EXCEL_FILL_YELLOW]
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
