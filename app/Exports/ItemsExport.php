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
            'Item Code',
            'Item Name',
            'Category',
            'Sub-Category',
            'HSN/SAC',
            'Type',
            'Sub-Type',
            'Inventory UOM',
            'Currency',
            'Cost Price', 
            'Sale Price', 
            'Status',
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
            $item->currency->short_name ??'N/A', 
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
        $requiredColumns = range(1, 9);
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
        for ($col = 10; $col <= $totalColumns; $col++) {
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
