<?php

namespace App\Exports;

use App\Models\UploadItemMaster;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FailedItemsExport implements FromCollection, WithHeadings, WithMapping,WithStyles
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
            'Item Code',
            'Item Name',
            'Category',
            'Sub-Category',
            'HSN/SAC',
            'Type',
            'Sub-Type',
            'Inventory UOM',
            'Cost Price',
            'Cost Price Currency',
            'Sale Price', 
            'Sell Price Currency',
            'Status'
        ];

        for ($i = 1; $i <= 10; $i++) {
            array_push($headings,
                "Attribute {$i} Name",
                "Attribute {$i} Value",
                "Required BOM {$i}",
                "All Checked {$i}"
            );
        }

        $headings[] = 'Product Specification Group';

        for ($i = 1; $i <= 10; $i++) {
            array_push($headings,
                  "Specification {$i} Name",
                "Specification {$i} Value"
            );
        }

        for ($i = 1; $i <= 10; $i++) {
            array_push($headings,
                "Alternate UOM {$i}",
                "Alternate UOM {$i} Conversion",
                "Alternate UOM {$i} Cost Price",
                "Alternate UOM {$i} Default?"
            );
        }

        $headings[] = 'Remark';

        return $headings;
    }

    public function map($item): array
    {
        $data = [
            $item->item_code,
            $item->item_name,
            $item->category?? 'N/A',
            $item->subcategory?? 'N/A',
            $item->hsn?? 'N/A',
            $item->type ?? 'N/A',
            $item->sub_type ?? 'N/A',
            $item->uom?? 'N/A',
            $item->cost_price ?? 'N/A',
            $item->cost_price_currency?? 'N/A',
            $item->sell_price ?? 'N/A',
            $item->sell_price_currency?? 'N/A',
            $item->status ?? 'N/A',
        ];

        $attributes = json_decode($item->attributes, true);
        for ($i = 0; $i < 10; $i++) {
            $attr = $attributes[$i] ?? null;
            $data = array_merge($data, [
                $attr['name'] ?? '',
                $attr['value'] ?? '',
                $attr['required_bom'] ?? '',
                $attr['all_checked'] ?? '',
            ]);
        }

        $specs = json_decode($item->specifications, true);
        $data[] = $specs[0]['group_name'] ?? '';

        for ($i = 0; $i < 10; $i++) {
            $spec = $specs[0]['specifications'][$i] ?? null;
            $data = array_merge($data, [
                $spec['name'] ?? '',
                $spec['value'] ?? '',
            ]);
        }

        $uoms = json_decode($item->alternate_uoms, true);
        for ($i = 0; $i < 10; $i++) {
            $uom = $uoms[$i] ?? null;
            $data = array_merge($data, [
                $uom['uom'] ?? '',
                $uom['conversion'] ?? '',
                $uom['cost_price'] ?? '',
                $uom['default'] ?? '',
            ]);
        }

        $data[] = $item->remarks ?? 'N/A';

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [];
        $requiredColumns = range(1, 10); 
        $totalColumns = count($this->headings());
        $remarksColIndex = $totalColumns; 
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
            if ($col !== $remarksColIndex) {
                $sheet->getStyle("{$columnLetter}")->getAlignment()->setWrapText(true);
            }
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
            if ($col !== $remarksColIndex) {
                $sheet->getStyle("{$columnLetter}")->getAlignment()->setWrapText(true);
            }
        }
        return $styles;
    }
    
}
