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
            'item_code',
            'item_name',
            'category',
            'sub_category',
            'hsnsac',
            'type',
            'sub_type',
            'inventory_uom',
            'sale_price', 
            'cost_price', 
            'status'
        ];

        for ($i = 1; $i <= 10; $i++) {
            array_push($headings,
                "attribute_{$i}_name",
                "attribute_{$i}_value",
                "required_bom_{$i}",
                "all_checked_{$i}"
            );
        }

        $headings[] = 'product_specification_group';

        for ($i = 1; $i <= 10; $i++) {
            array_push($headings,
                "specification_{$i}_name",
                "specification_{$i}_value"
            );
        }

        for ($i = 1; $i <= 10; $i++) {
            array_push($headings,
                "alternate_uom_{$i}",
                "alternate_uom_{$i}_conversion",
                "alternate_uom_{$i}_cost_price",
                "alternate_uom_{$i}_default"
            );
        }

        $headings[] = 'remark';

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
            $item->sell_price ?? 'N/A',
            $item->cost_price ?? 'N/A',
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
        $requiredColumns = range(1, 7); 
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
                ]
            ];
        }
    
        $totalColumns = count($this->headings());
        for ($col = 8; $col <= $totalColumns; $col++) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col); 
            $sheet->getStyle("{$columnLetter}1")->applyFromArray([
                'font' => [
                    'color' => ['argb' => 'FF000000'], 
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['argb' => 'D3D3D3'] 
                ]
            ]);
        }
        return $styles;
    }
    
}
