<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Services\ItemImportExportService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemsExport implements FromCollection, WithHeadings, WithMapping,WithStyles
{
    protected $items;
    protected $service;

    public function __construct($items, ItemImportExportService $service)
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
            'status',
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

        return $headings;
    }

    public function map($item): array
    {
        $data = [
            $item->item_code,
            $item->item_name,
            $item->category->name ?? 'N/A',
            $item->subcategory->name ?? 'N/A',
            $item->hsn->code ?? 'N/A',
            $item->type ?? 'N/A',
            $item->subTypes->pluck('subType.name')->implode(', ') ?? 'N/A',
            $item->uom->name ?? 'N/A',
            $item->sell_price ?? 'N/A',
            $item->cost_price ?? 'N/A',
            $item->status ?? 'N/A',
        ];

        $attributes = $item->itemAttributes;
        $groupedAttributes = $attributes->groupBy(function($attribute) {
            return $attribute->attributeGroup->name ?? '';
        });
        
        for ($i = 0; $i < 10; $i++) {
            $groupName = $groupedAttributes->keys()[$i] ?? '';
            $groupAttributes = $groupedAttributes->get($groupName, collect()); 
        
            if ($groupAttributes->isNotEmpty()) {
                $attributeValues = $groupAttributes->pluck('attribute.value')->unique()->implode(', ');
                $requiredBom = $groupAttributes->first()->required_bom ?? '';
                $allChecked = $groupAttributes->first()->all_checked ?? '';
                $data = array_merge($data, [
                    $groupName,      
                    $attributeValues, 
                    $requiredBom,     
                    $allChecked,      
                ]);
            } else {
                $data = array_merge($data, ['', '', '', '']);
            }
        }
        
        $specifications = $item->specifications;
        $groupName = $specifications->first()->group->name ?? ''; 
        $data[] = $groupName;

        for ($i = 0; $i < 10; $i++) {
            $spec = $specifications[$i] ?? null; 

            $data = array_merge($data, [
                $spec->specification->name ?? '',  
                $spec->value ?? '',  
            ]);
        }

        $alternateUoms = $item->alternateUOMs;

        for ($i = 0; $i < 10; $i++) {
            $uom = $alternateUoms[$i] ?? null; 
            
            if ($uom) {
                $data = array_merge($data, [
                    $uom->uom->name ?? '', 
                    $uom->conversion_to_inventory ?? '',  
                    $uom->cost_price ?? '',  
                    $uom->is_selling ? 'S' : ($uom->is_purchasing ? 'P' : null),  
                ]);
            } else {
                $data = array_merge($data, ['', '', '', null]);
            }
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [];
        $requiredColumns = range(1, 8); 
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
        for ($col = 9; $col <= $totalColumns; $col++) {
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
