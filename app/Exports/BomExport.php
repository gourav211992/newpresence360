<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Helpers\BookHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Models\Bom;
use App\Models\Organization;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BomExport implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    protected $bom;
    protected $parameters;

    protected $sectionRequired;
    protected $subSectionRequired;
    protected $componentOverheadRequired;
    protected $isNorm;
    protected $canView;
    protected $orgName;

    public function __construct($bomId)
    {
        $bomColumns = [
            'id','type', 'book_id', 'book_code', 'document_number', 'document_date', 'item_id', 'uom_id',
            'production_type', 'production_route_id', 'safety_buffer_perc', 'customizable','customer_id',
            'remarks', 'total_item_value', 'header_overhead_amount'
        ];
    
        $bomItemColumns = [
            'id', 'bom_id', 'item_id', 'uom_id', 'qty', 'item_value', 'overhead_amount',
            'total_amount', 'station_name', 'vendor_id', 'section_name', 'sub_section_name', 'remark'
        ];
    
        $instructionColumns = [
            'id', 'bom_id', 'station_id', 'section_id', 'sub_section_id', 'instructions'
        ];

        $this->bom = Bom::with([
            'item:id,item_code,item_name',
            'uom:id,name',
            'productionRoute:id,name',
            'bomAttributes.headerAttribute:id,name',
            'bomAttributes.headerAttributeValue:id,value',
            'bomItems' => fn($q) => $q->select($bomItemColumns),
            'bomItems.item:id,item_code,item_name',
            'bomItems.uom:id,name',
            'bomItems.vendor:id,company_name',
            'bomItems.attributes.headerAttribute:id,name',
            'bomItems.attributes.headerAttributeValue:id,value',
            'bomInstructions' => fn($q) => $q->select($instructionColumns),
            'bomInstructions.station:id,name',
            'bomInstructions.section:id,name',
            'bomInstructions.subSection:id,name',
        ])->findOrFail($bomId, $bomColumns);
        
        $response = BookHelper::fetchBookDocNoAndParameters($this->bom->book_id, $this->bom->document_date);
        $this->parameters = data_get($response, 'data.parameters', []);
        $this->sectionRequired = $this->isEnabled('section_required');
        $this->subSectionRequired = $this->isEnabled('sub_section_required');
        $this->componentOverheadRequired = $this->isEnabled('component_overhead_required');
        $this->isNorm = isset($this->parameters->consumption_method) && is_array($this->parameters->consumption_method) && in_array('norms', $this->parameters->consumption_method);

        $parentUrl = request()->segments()[0];
        $servicesAliasParam = $parentUrl === 'quotation-bom' ? ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS : ConstantHelper::BOM_SERVICE_ALIAS;
        if ($servicesAliasParam === ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS) {
            $this->canView = request()->user()?->hasPermission('quotation_bom.item_cost_view') ?? true;
        } else {
            $this->canView = request()->user()?->hasPermission('production_bom.item_cost_view') ?? true;
        }

        $user = request()->user() ?? Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user?->organization_id)->first();
        $orgName = $organization?->name ?? ''; 

        $this->orgName = $orgName; 
    }

    public function title(): string
    {
        return 'BOM-' . $this->bom->book_code;
    }

    public function array(): array
    {
        $rows = [];
        // Header Section
        $rows[] = ['BOM Export'];
        $rows[] = [$this->orgName];
        $rows[] = [''];
        $rows = array_merge($rows, $this->getHeaderRows());
        $rows[] = [''];

        // Components Section
        $rows[] = ['Components'];
        $rows[] = $this->getComponentHeaders();
        foreach ($this->bom->bomItems as $component) {
            $rows[] = $this->getComponentRow($component);
        }

        if($this->bom->bomInstructions->isNotEmpty()) {
            $rows[] = ['']; // spacing row
            // Instructions Section
            $rows[] = ['Instructions'];
            $rows[] = $this->getInstructionHeaders();
            foreach ($this->bom->bomInstructions as $step) {
                $rows[] = $this->getInstructionRow($step);
            }
        }

        $rows[] = ['']; // spacing row

        // Summary
        if($this->canView) {
            $rows[] = ['Summary'];
            $rows[] = ['Item Total', $this->bom->total_item_value ?? 0];
            $rows[] = ['Header Overheads', $this->bom->header_overhead_amount ?? 0];
            $rows[] = ['Grand Total', ($this->bom->total_value  ?? 0)];
        }

        return $rows;
    }

    private function getHeaderRows(): array
    {
        $rows = [
            ['BOM Code:', $this->bom->book_code],
            ['BOM No:', $this->bom->document_number],
            ['Product Code:', optional($this->bom->item)->item_code],
            ['Product Name:', optional($this->bom->item)->item_name],
        ];

        // Attributes - break into separate rows
        // $attributes = $this->bom->bomAttributes ?? collect();
        // if ($attributes->isNotEmpty()) {
        //     $rows[] = ['Attributes:'];
        //     foreach ($attributes as $attr) {
        //         $name = optional($attr->headerAttribute)->name;
        //         $value = optional($attr->headerAttributeValue)->value;
        //         if ($name && $value) {
        //             $rows[] = ["- {$name}", $value];
        //         }
        //     }
        // }

        // if ($this->bom->bomAttributes->isNotEmpty()) {
        //     $rows[] = ['Specifications', $this->formatSpecifications($this?->bom?->item?->specifications)];
        // }

        if ($this->bom->bomAttributes->isNotEmpty()) {
            $rows[] = ['Attributes:', $this->formatAttributes($this->bom->bomAttributes)];
        }
        $rows[] = ['UOM:', optional($this->bom->uom)->name];
        // Specifications - break into separate rows
        $specs = $this->bom->item->specifications ?? [];
        if (!empty($specs)) {
            foreach ($specs as $spec) {
                $name = $spec?->specification_name;
                $value = $spec?->value;
                if ($name && $value) {
                    $rows[] = ["{$name}:", $value];
                }
            }
        }

        if($this?->bom->type == ConstantHelper::BOM_SERVICE_ALIAS) {
            $rows[] = ['Production Type:', $this->bom->production_type];
        }
        if($this?->bom?->customer) {
            $rows[] = ['Customer Code:', optional($this->bom->customer)->customer_code];
            $rows[] = ['Customer Name:', optional($this->bom->customer)->company_name];
        }
        $rows[] = ['Production Route:', optional($this->bom->productionRoute)->name];

        $saftyb = $this->bom->safety_buffer_perc ? $this->bom->safety_buffer_perc : $this->bom?->productionRoute?->safety_buffer_perc;
        if($saftyb) {
            $rows[] = ['Safety Buffer:', $saftyb];
        }
        $rows[] = ['Customizable:', ucfirst($this->bom->customizable ?? 'no')];
        $rows[] = ['Remarks:', $this->bom->remarks];
        return $rows;
    }

    private function getComponentHeaders(): array
    {
        $headers = [];
        if ($this->sectionRequired) {
            $headers[] = 'Section';
        }
        if ($this->subSectionRequired) {
            $headers[] = 'Sub Section';
        }   

        $qtyLebel = $this->isNorm ? 'Norms' : 'Consumption';
        if($this->canView) {
            $baseHeaders = [
                'Item Code', 'Item Name', 'Attributes', 'UOM',
                $qtyLebel,
                'Item Value', 'Overhead Cost',
                'Total Cost', 'Station', 'Vendor Name', 'Remark'
            ];
        } else {
            $baseHeaders = [
                'Item Code', 'Item Name', 'Attributes', 'UOM',
                'Consumption', 'Station', 'Vendor Name', 'Remark'
            ];
        }
        $headers = array_merge($headers, $baseHeaders);
        $insertIndex = array_search($qtyLebel, $headers);
        $dynamicColumns = [];
        if ($this->isNorm) {
            $dynamicColumns[] = 'Component per unit';
            $dynamicColumns[] = 'Pieces';
            $dynamicColumns[] = 'Std Qty';
        }
        // Insert dynamic columns after 'Consumption'
        if ($insertIndex !== false && !empty($dynamicColumns)) {
            array_splice($headers, $insertIndex + 1, 0, $dynamicColumns);
        }
        return $headers;
    }

    private function getComponentRow($component): array
    {
        $row = [];
        if ($this->sectionRequired) {
            $row[] = $component->section_name ?? '';
        }
        if ($this->subSectionRequired) {
            $row[] = $component->sub_section_name ?? '';
        }

        if($this->canView) {
            $baseRow = [
                optional($component->item)->item_code,
                optional($component->item)->item_name,
                $this->formatComponentAttributes($component->attributes ?? []),
                // $this->formatAttributes($component->attributes),
                optional($component->uom)->name,
                $component->qty ?? 0, // 'Consumption'
                $component->item_value ?? 0,
                $component->overhead_amount ?? 0,
                $component->total_amount ?? 0,
                $component->station_name ?? '',
                optional($component->vendor)->company_name,
                $component->remark,
            ];
        } else {
            $baseRow = [
                optional($component->item)->item_code,
                optional($component->item)->item_name,
                $this->formatAttributes($component->attributes),
                optional($component->uom)->name,
                $component->qty ?? 0,
                $component->station_name ?? '',
                optional($component->vendor)->company_name,
                $component->remark,
            ];
        }
        $dynamicValues = [];
        if ($this->isNorm) {
            $dynamicValues[] = $component?->norm?->qty_per_unit ?? 0;
            $dynamicValues[] = $component?->norm?->total_qty ?? 0;
            $dynamicValues[] = $component?->norm?->std_qty ?? 0;
        }
        $consumptionIndex = 4;
        array_splice($baseRow, $consumptionIndex + 1, 0, $dynamicValues);
        return array_merge($row, $baseRow);
    }

    private function getInstructionHeaders(): array
    {
        $headers = ['Station'];
        if ($this->sectionRequired) {
            $headers[] = 'Section';
        }
        if ($this->subSectionRequired) {
            $headers[] = 'Sub Section';
        }
        $headers[] = 'Description';
        return $headers;
    }

    private function getInstructionRow($step): array
    {
        $row = [
            optional($step->station)->name,
        ];
        if ($this->sectionRequired) {
            $row[] = optional($step->section)->name;
        }
        if ($this->subSectionRequired) {
            $row[] = optional($step->subSection)->name;
        }
        $row[] = $step->instructions;
        return $row;
    }

    private function formatComponentAttributes($attributes): string
    {
        if (empty($attributes)) {
            return '';
        }
        $lines = [];
        foreach ($attributes as $attr) {
            $name = optional($attr->headerAttribute)->name;
            $value = optional($attr->headerAttributeValue)->value;
            if ($name && $value) {
                $lines[] = "{$name}: {$value}";
            }
        }
        // Join each attribute on a new line
        return implode("\n", $lines);
    }

    /**
     * Format attributes as a string like: Color: Red, Size: 1
     */
    private function formatAttributes($attributes) : string
    {
        $formatted = [];
        foreach ($attributes as $attribute) {
            $name = optional($attribute->headerAttribute)->name;
            $value = optional($attribute->headerAttributeValue)->value;
            if ($name && $value) {
                $formatted[] = "{$name}: {$value}";
            }
        }
        return implode(', ', $formatted);
    }
    
    /**
     * Format attributes as a string like: Color: Red, Size: 1
     */
    // private function formatSpecifications($attributes): string
    // {
    //     $formatted = [];
    //     foreach ($attributes as $attribute) {
    //         $name = $attribute?->specification_name;
    //         $value = $attribute?->value;
    //         if ($name && $value) {
    //             $formatted[] = "{$name}: {$value}";
    //         }
    //     }
    //     return implode(', ', $formatted);
    // }

    /**
     * Check if a parameter is enabled
     */
    private function isEnabled(string $key): bool
    {
        return isset($this->parameters->{$key}) &&
            is_array($this->parameters->{$key}) &&
            in_array('yes', array_map('strtolower', $this->parameters->{$key}));
    }

    public function styles(Worksheet $sheet)
    {
        $boldRows = [];
        $titlesToBold = ['BOM Export'];
        foreach ($sheet->toArray() as $rowNumber => $rowContent) {
            if (!empty($rowContent[0]) && in_array($rowContent[0], $titlesToBold)) {
                $boldRows[$rowNumber + 1] = ['font' => ['bold' => true, 'size' => 14]];
            }
        }
        $subtTitlesToBold = ['Components', 'Instructions', 'Summary', $this->orgName];
        foreach ($sheet->toArray() as $rowNumber => $rowContent) {
            if (!empty($rowContent[0]) && in_array($rowContent[0], $subtTitlesToBold)) {
                $boldRows[$rowNumber + 1] = ['font' => ['bold' => true, 'size' => 12]];
            }
        }

        foreach ($sheet->toArray() as $rowNumber => $rowContent) {
            if (!empty($rowContent[0]) && in_array($rowContent[0], $this->getComponentHeaders())) {
                $boldRows[$rowNumber + 1] = ['font' => ['bold' => true]];
            }
        }
        return $boldRows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 15,
            'C' => 25,
            'D' => 10,
            'E' => 5,
            'F' => 10,
            'G' => 15,
            'H' => 15,
            'I' => 10,
            'J' => 10,
            'K' => 10,
            'L' => 10,
        ];
    }
}
