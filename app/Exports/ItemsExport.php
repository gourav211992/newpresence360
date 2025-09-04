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
            'Type',
            'Group',
            'Sub-Type',
            'Item Code',
            'Item Name',
            'HSN/SAC',
            'Inventory UOM',
            'Cost Price', 
            'Cost Price Currency',
            'Sale Price', 
            'Sell Price Currency',
            'Min Stocking Level',
            'Max Stocking Level',
            'Reorder Level',
            'Min Order Qty',
            'Lead Days',
            'Safety Days',
            'Shelf Life Days',
            'PO Positive Tolerance',
            'PO Negative Tolerance',
            'SO Positive Tolerance',
            'SO Negative Tolerance',
            'Is Serial No',
            'Is Batch No',
            'Is Expiry',
            'Is Inspection',
            'Inspection Checklist',
            'Storage UOM',
            'Storage UOM Conversion',
            'Storage UOM Count',
            'Storage Weight',
            'Storage Volume',
            'Asset Category',
            'Brand Name',
            'Model No',
        ];

        for ($i = 1; $i <= 10; $i++) {
            array_push($headings,
                "Attribute {$i} Name",
                "Attribute {$i} Value",
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
            $item->type ?? 'N/A',
            $item->subcategory->name ?? 'N/A',
            $item->subTypes->pluck('subType.name')->implode(', ') ?? 'N/A',
            $item->item_code,
            $item->item_name,
            $item->hsn->code ?? 'N/A',
            $item->uom->name ?? 'N/A',
            $item->cost_price ?? 'N/A',
            $item->costCurrency->short_name ?? 'N/A',
            $item->sell_price ?? 'N/A',
            $item->sellCurrency->short_name ?? 'N/A',
            $item->min_stocking_level ?? 'N/A',
            $item->max_stocking_level ?? 'N/A',
            $item->reorder_level ?? 'N/A',
            $item->minimum_order_qty ?? 'N/A',
            $item->lead_days ?? 'N/A',
            $item->safety_days ?? 'N/A',
            $item->shelf_life_days ?? 'N/A',
            $item->po_positive_tolerance ?? 'N/A',
            $item->po_negative_tolerance ?? 'N/A',
            $item->so_positive_tolerance ?? 'N/A',
            $item->so_negative_tolerance ?? 'N/A',
            ($item->is_serial_no ?? 0 == 1 ? 'Y' : 'N'), 
            ($item->is_batch_no ?? 0 == 1 ? 'Y' : 'N'),
            ($item->is_expiry ?? 0 == 1 ? 'Y' : 'N'),
            ($item->is_inspection ?? 0 == 1 ? 'Y' : 'N'), 
            $item->inspectionChecklist->name ?? 'N/A',
            $item->storageUom->name ?? 'N/A',
            $item->storage_uom_conversion ?? 'N/A',
            $item->storage_uom_count ?? 'N/A',
            $item->storage_weight ?? 'N/A',
            $item->storage_volume ?? 'N/A',
            $item->assetCategory->name ?? 'N/A',
            $item->brand_name ?? 'N/A', 
            $item->model_no ?? 'N/A',
        ];

        $attributes = $item->itemAttributes;
        $groupedAttributes = $attributes->groupBy(function($attribute) {
            return $attribute->attributeGroup->name ?? '';
        });
        
        for ($i = 0; $i < 10; $i++) {
            $groupName = $groupedAttributes->keys()[$i] ?? '';
            $groupAttributes = $groupedAttributes->get($groupName, collect()); 
            if ($groupAttributes->isNotEmpty()) {
                 $attributeValues = $groupAttributes
                ->flatMap(function($attr) {
                    return $attr->selectedAttributes()->pluck('value');
                })
                ->implode(', ');

                $requiredBom = $groupAttributes->first()->required_bom ?? '';
                $allChecked = $groupAttributes->first()->all_checked ?? '';
                $data = array_merge($data, [
                    $groupName,      
                    $attributeValues,  
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
