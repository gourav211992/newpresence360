<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Helpers\BookHelper;
use App\Helpers\ConstantHelper;
use App\Models\Bom;
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

    public function __construct($bomId)
    {
        $bomColumns = [
            'id','type', 'book_id', 'book_code', 'document_number', 'document_date', 'item_id', 'uom_id',
            'production_type', 'production_route_id', 'safety_buffer_perc', 'customizable','customer_id',
            'remarks', 'total_item_value', 'header_overhead_amount'
        ];
    
        $bomItemColumns = [
            'id', 'bom_id', 'item_id', 'uom_id', 'qty', 'item_value', 'overhead_amount',
            'total_amount', 'station_id', 'vendor_id', 'section_id', 'sub_section_id', 'remark'
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
            'bomItems.station:id,name',
            'bomItems.vendor:id,company_name',
            'bomItems.attributes.headerAttribute:id,name',
            'bomItems.attributes.headerAttributeValue:id,value',
            'bomItems.section:id,name',
            'bomItems.subSection:id,name',
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
    }

    public function title(): string
    {
        return 'BOM-' . $this->bom->book_code;
    }

    public function array(): array
    {
        $rows = [];
        // Header Section
        $rows[] = ['BOM Header'];
        $rows = array_merge($rows, $this->getHeaderRows());
        $rows[] = ['']; // spacing row

        // Components Section
        $rows[] = ['Components'];
        $rows[] = $this->getComponentHeaders();
        foreach ($this->bom->bomItems as $component) {
            $rows[] = $this->getComponentRow($component);
        }

        $rows[] = ['']; // spacing row

        // Instructions Section
        $rows[] = ['Instructions'];
        $rows[] = $this->getInstructionHeaders();
        foreach ($this->bom->bomInstructions as $step) {
            $rows[] = $this->getInstructionRow($step);
        }

        $rows[] = ['']; // spacing row

        // Summary
        $rows[] = ['BOM Summary'];
        $rows[] = ['Item Total', $this->bom->total_item_value ?? 0];
        $rows[] = ['Header Overheads', $this->bom->header_overhead_amount ?? 0];
        $rows[] = ['Grand Total', ($this->bom->total_item_value ?? 0)];

        return $rows;
    }

    private function getHeaderRows(): array
    {
        $rows = [
            ['BOM Code:', $this->bom->book_code],
            ['BOM No:', $this->bom->document_number],
            ['Product Code:', optional($this->bom->item)->item_code],
            ['Product Name:', optional($this->bom->item)->item_name],
            ['UOM:', optional($this->bom->uom)->name],
        ];

        if ($this->bom->bomAttributes->isNotEmpty()) {
            $rows[] = ['Specifications', $this->formatSpecifications($this?->bom?->item?->specifications)];
        }

        if ($this->bom->bomAttributes->isNotEmpty()) {
            $rows[] = ['Attributes', $this->formatAttributes($this->bom->bomAttributes)];
        }
        if($this?->bom->type == ConstantHelper::BOM_SERVICE_ALIAS) {
            $rows[] = ['Production Type :', $this->bom->production_type];
        }
        if($this?->bom?->customer) {
            $rows[] = ['Customer Code :', optional($this->bom->customer)->customer_code];
            $rows[] = ['Customer Name :', optional($this->bom->customer)->company_name];
        }
        $rows[] = ['Production Route :', optional($this->bom->productionRoute)->name];
        $rows[] = ['Safety Buffer :', $this->bom->safety_buffer_perc];
        $rows[] = ['Customizable :', ucfirst($this->bom->customizable ?? 'no')];
        $rows[] = ['Description:', $this->bom->remarks];

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

        return array_merge($headers, [
            'Item Code', 'Item Name', 'Attributes', 'UOM',
            'Consumption', 'Item Value', 'Overhead Cost',
            'Total Cost', 'Station', 'Vendor Name', 'Remark'
        ]);
    }

    private function getComponentRow($component): array
    {
        $row = [];

        if ($this->sectionRequired) {
            $row[] = optional($component->section)->name;
        }

        if ($this->subSectionRequired) {
            $row[] = optional($component->subSection)->name;
        }

        return array_merge($row, [
            optional($component->item)->item_code,
            optional($component->item)->item_name,
            $this->formatAttributes($component->attributes),
            optional($component->uom)->name,
            $component->qty ?? 0,
            $component->item_value ?? 0,
            $component->overhead_amount ?? 0,
            $component->total_amount ?? 0,
            optional($component->station)->name,
            optional($component->vendor)->company_name,
            $component->remark,
        ]);
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

    /**
     * Format attributes as a string like: Color: Red, Size: 1
     */
    private function formatAttributes($attributes): string
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
    private function formatSpecifications($attributes): string
    {
        $formatted = [];
        foreach ($attributes as $attribute) {
            $name = $attribute?->specification_name;
            $value = $attribute?->value;
            if ($name && $value) {
                $formatted[] = "{$name}: {$value}";
            }
        }
        return implode(', ', $formatted);
    }

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
        $titlesToBold = ['BOM Header', 'Components', 'Instructions', 'BOM Summary'];
        foreach ($sheet->toArray() as $rowNumber => $rowContent) {
            if (!empty($rowContent[0]) && in_array($rowContent[0], $titlesToBold)) {
                $boldRows[$rowNumber + 1] = ['font' => ['bold' => true, 'size' => 14]];
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
