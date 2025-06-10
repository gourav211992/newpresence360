<?php

namespace App\Exports;

use App\Helpers\ConstantHelper;
use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Services\ItemImportExportService;
use App\Services\LedgerImportExportService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LedgersExport implements FromCollection, WithHeadings, WithMapping,WithStyles
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
            'Code',
            'Name',
            'Group',
            'Status',
            'tds_section',
            'tds_percentage',
            'tcs_section',
            'tcs_percentage',
            'tax_type', 
            'tax_percentage',
        ];

        // for ($i = 1; $i <= 10; $i++) {
        //     array_push($headings,
        //         "Attribute {$i} Name",
        //         "Attribute {$i} Value",
        //         "Required BOM {$i}",
        //         "All Checked {$i}"
        //     );
        // }

        // $headings[] = 'Product Specification Group';

        // for ($i = 1; $i <= 10; $i++) {
        //     array_push($headings,
        //         "Specification {$i} Name",
        //         "Specification {$i} Value"
        //     );
        // }

        // for ($i = 1; $i <= 10; $i++) {
        //     array_push($headings,
        //         "Alternate UOM {$i}",
        //         "Alternate UOM {$i} Conversion",
        //         "Alternate UOM {$i} Cost Price",
        //         "Alternate UOM {$i} Default?"
        //     );
        // }

        return $headings;
    }

    public function map($item): array
    {
    $groupNames = $this->service->getGroupNamesByIds($item->ledger_group_id);
    $tdsSections = ConstantHelper::getTdsSections();
    $tcsSections = ConstantHelper::getTcsSections();
    $taxTypes    = ConstantHelper::getTaxTypes();
        $data = [
            $item->code,
            $item->name,
            implode(', ', $groupNames),
            $item->status ?? 'N/A',
            $tdsSections[$item->tds_section] ?? 'N/A',
            $item->tds_percentage ?? 'N/A',
            $tcsSections[$item->tcs_section] ?? 'N/A',
            $item->tcs_percentage ?? 'N/A',
            $taxTypes[$item->tax_type] ?? 'N/A',
            $item->tax_percentage ?? 'N/A',
        ];

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [];
        $requiredColumns = range(1, 10);
        foreach ($requiredColumns as $col) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $styles["{$columnLetter}1"] = [
                'font' => [
                    'color' => ['argb' => 'FF000000'],
                    'bold' => true, 
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['argb' => 'FFFF00'] 
                ],
                'alignment' => [
                    'wrapText' => true, 
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ];
            $sheet->getColumnDimension($columnLetter)->setWidth(15);
            $sheet->getStyle("{$columnLetter}")->getAlignment()->setWrapText(true);
        }
    
        $totalColumns = count($this->headings());
        for ($col = 11; $col <= $totalColumns; $col++) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col); 
            $sheet->getStyle("{$columnLetter}1")->applyFromArray([
                'font' => [
                    'color' => ['argb' => 'FF000000'], 
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['argb' => 'D3D3D3'] 
                ],
                'alignment' => [
                    'wrapText' => true, 
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ]);
            $sheet->getColumnDimension($columnLetter)->setWidth(15);
            $sheet->getStyle("{$columnLetter}")->getAlignment()->setWrapText(true);
        }
        return $styles;
    }
}
