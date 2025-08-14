<?php

namespace App\Exports;

use App\Models\ErpAttribute;
use App\Models\AttributeGroup;
use App\Models\TransactionUploadItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FailedTransactionItemsExport implements FromCollection, WithHeadings, WithMapping,WithStyles
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
            'uom_code',
            'order_qty',
            'item_rate', 
            'store_code', 
            'status',
        ];

        for ($i = 1; $i <= 10; $i++) {
            array_push($headings,
                "attribute_{$i}_name",
                "attribute_{$i}_value"
            );
        }

        $headings[] = 'remark';

        return $headings;
    }

    public function map($item): array
    {
        $data = [
            $item?->item_code,
            $item->uom_code ?? 'N/A',
            $item->order_qty ?? 'N/A',
            $item->rate ?? 'N/A',
            $item->store_code ?? 'N/A',
            $item->status ?? 'N/A',
        ];

        $attributes = $item->attributes;
        if (is_string($attributes)) {
            $attributes = json_decode($attributes, true);
        }
        
        for ($i = 1; $i <= 10; $i++) {
            $attribute = $attributes[$i - 1] ?? null;
            if ($attribute) {
                $attributeGroup  = AttributeGroup::find($attribute['attribute_name_id']);
                $attributeGroupName = $attributeGroup ? $attributeGroup->name : 'N/A';
                $attributeValue  = ErpAttribute::find($attribute['attribute_value_id']);
                $attributeVal = $attributeValue ? $attributeValue->value : 'N/A';
                $data[] = $attributeGroupName ?? '';
                $data[] = $attributeVal ?? '';
            } else {
                $data[] = '';
                $data[] = '';
            }
        }

        $data[] = $item->reason ?? 'N/A';

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
