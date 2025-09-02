<?php

namespace App\Services;

use App\Helpers\BookHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Models\Bom;
use App\Models\Organization;
use Illuminate\Support\Str;
class BomExportService
{
    public function getExportData(int $id): array
    {
        $bomColumns = [
            'id', 'type', 'book_id', 'book_code', 'document_number', 'document_date', 'item_id', 'uom_id',
            'production_type', 'production_route_id', 'safety_buffer_perc', 'customizable', 'customer_id',
            'remarks', 'total_item_value', 'header_overhead_amount'
        ];

        $bomItemColumns = [
            'id', 'bom_id', 'item_id', 'uom_id', 'qty', 'item_value', 'overhead_amount',
            'total_amount', 'station_name', 'vendor_id', 'section_name', 'sub_section_name', 'remark'
        ];

        $instructionColumns = [
            'id', 'bom_id', 'station_id', 'section_id', 'sub_section_id', 'instructions'
        ];

        $bom = Bom::with([
            'item:id,item_code,item_name',
            'uom:id,name',
            'productionRoute:id,name',
            'bomAttributes.headerAttribute:id,name',
            'bomAttributes.headerAttributeValue:id,value',
            'bomItems' => fn ($q) => $q->select($bomItemColumns),
            'bomItems.item:id,item_code,item_name',
            'bomItems.uom:id,name',
            'bomItems.vendor:id,company_name',
            'bomItems.attributes.headerAttribute:id,name',
            'bomItems.attributes.headerAttributeValue:id,value',
            'bomInstructions' => fn ($q) => $q->select($instructionColumns),
            'bomInstructions.station:id,name',
            'bomInstructions.section:id,name',
            'bomInstructions.subSection:id,name',
        ])->findOrFail($id, $bomColumns);

        $response = BookHelper::fetchBookDocNoAndParameters($bom->book_id, $bom->document_date);
        $parameters = data_get($response, 'data.parameters', []);
        $isNorm = isset($parameters->consumption_method) && is_array($parameters->consumption_method) && in_array('norms', $parameters->consumption_method);
        $parentUrl = request()->segments()[0];
        $servicesAliasParam = $parentUrl === 'quotation-bom' ? ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS : ConstantHelper::BOM_SERVICE_ALIAS;

        $canView = request()->user()?->hasPermission(
            $servicesAliasParam === ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS
                ? 'quotation_bom.item_cost_view'
                : 'production_bom.item_cost_view'
        ) ?? true;

        $user = request()->user() ?? Helper::getAuthenticatedUser();
        $organization = Organization::find($user?->organization_id);
        $orgName = $organization?->name ?? '';

        $headerData = $this->getHeaderRows($bom);
        $sections = [];

        // Section 1: Header
        if (!empty($headerData)) {
            $sections[] = [
                'section_title' => '',
                'bold' => true,
                'font_size' => 12,
                'data' => $headerData,
            ];
        }

        // Section 2: Components
        if ($bom->bomItems->isNotEmpty()) {
            $componentColumn = $this->getComponentHeaders($parameters, $isNorm, $canView);
            $componentColumnValues = [];

            foreach ($bom->bomItems as $component) {
                $componentColumnValues[] = $this->getComponentRow($parameters, $isNorm, $canView, $component);
            }

            $sections[] = [
                'section_title' => 'Components',
                'bold' => true,
                'font_size' => 12,
                'headers' => [
                    'values' => $componentColumn,
                    'bold' => true,
                    'font_size' => 11,
                ],
                'rows' => $componentColumnValues,
            ];
        }

        // Section 3: Instructions
        if ($bom->bomInstructions->isNotEmpty()) {
            $instructionColumn = $this->getInstructionHeaders($parameters);
            $instructionData = [];

            foreach ($bom->bomInstructions as $step) {
                $instructionData[] = $this->getInstructionRow($parameters, $step);
            }

            $sections[] = [
                'section_title' => 'Instructions',
                'bold' => true,
                'font_size' => 12,
                'headers' => [
                    'values' => $instructionColumn,
                    'bold' => true,
                    'font_size' => 11,
                ],
                'rows' => $instructionData,
            ];
        }

        // Section 4: Summary
        $bomSummary = [];
        if ($canView) {
            $bomSummary[] = ['Item Total', $bom->total_item_value ?? 0];
            $bomSummary[] = ['Header Overheads', $bom->header_overhead_amount ?? 0];
            $bomSummary[] = ['Grand Total', $bom->total_value ?? 0];

            $sections[] = [
                'section_title' => 'Summary',
                'bold' => true,
                'font_size' => 12,
                'data' => $bomSummary,
            ];
        }
        // Section 4: Multiple Attachment
        $attachments = $bom->getDocuments()->filter(fn($doc) => Str::contains($doc->mime_type, 'image'))->values();

        $bomAttachment = [];
        if ($canView && $attachments->isNotEmpty()) {
            foreach ($attachments as $keys=>$file) {
                $fileName = 'Attachment '.$keys+1;
                $fileType = Str::contains($file->mime_type, 'pdf') ? 'PDF' : 'Image';
                $downloadUrl = $bom->getPdfDocumentUrl($file);

                $bomAttachment[] = [
                            'text' => $fileName ,
                            'link' => $downloadUrl
                            // 'link' => "=HYPERLINK(\"{$downloadUrl}\", \"Download\")",
                        ];
            }

            $sections[] = [
                'section_title' => 'Attachment',
                'bold' => true,
                'font_size' => 12,
                'data' => $bomAttachment,
              
            ];
        }

        return [
            'title' => ['text' => 'BOM Export', 'bold' => true, 'font_size' => 14],
            'org' => ['text' => $orgName, 'bold' => true, 'font_size' => 12],
            'sections' => $sections,
        ];
    }

    private function getHeaderRows($bom): array
    {
        $rows = [
            ['BOM Code:', $bom->book_code],
            ['BOM No:', $bom->document_number],
            ['Product Code:', optional($bom->item)->item_code],
            ['Product Name:', optional($bom->item)->item_name],
        ];

        // Attributes - break into separate rows
        $attributes = $bom->bomAttributes ?? collect();
        if ($attributes->isNotEmpty()) {
            foreach ($attributes as $attr) {
                $name = optional($attr->headerAttribute)->name;
                $value = optional($attr->headerAttributeValue)->value;
                if ($name && $value) {
                    $rows[] = ["{$name}", $value];
                }
            }
        }

        $rows[] = ['UOM:', optional($bom->uom)->name];
        $specs = $bom->item->specifications ?? [];
        if (!empty($specs)) {
            foreach ($specs as $spec) {
                $name = $spec?->specification_name;
                $value = $spec?->value;
                if ($name && $value) {
                    $rows[] = ["{$name}:", $value];
                }
            }
        }

        if($bom->type == ConstantHelper::BOM_SERVICE_ALIAS) {
            $rows[] = ['Production Type:', $bom->production_type];
        }
        if($bom?->customer) {
            $rows[] = ['Customer Code:', optional($bom->customer)->customer_code];
            $rows[] = ['Customer Name:', optional($bom->customer)->company_name];
        }
        $rows[] = ['Production Route:', optional($bom->productionRoute)->name];

        $saftyb = $bom->safety_buffer_perc ? $bom->safety_buffer_perc : $bom?->productionRoute?->safety_buffer_perc;
        if($saftyb) {
            $rows[] = ['Safety Buffer:', $saftyb];
        }
        $rows[] = ['Customizable:', ucfirst($bom->customizable ?? 'no')];
        $rows[] = ['Remarks:', $bom->remarks];
        return $rows;
    }

    private function getComponentHeaders($parameters, $isNorm, $canView): array
    {
        $headers = [];
        if ($this->isEnabled('section_required', $parameters)) {
            $headers[] = 'Section';
        }
        if ($this->isEnabled('sub_section_required', $parameters)) {
            $headers[] = 'Sub Section';
        }   
        $qtyLebel = $isNorm ? 'Norms' : 'Consumption';
        if($canView) {
            $baseHeaders = [
                'Item Code', 'Item Name', 'Attributes', 'UOM',
                $qtyLebel,
                'Item Value', 'Overhead Cost',
                'Total Cost', 'Station', 'Vendor Name', 'Remark'
            ];
        } else {
            $baseHeaders = [
                'Item Code', 'Item Name', 'Attributes', 'UOM',
                $qtyLebel,
                'Station', 'Vendor Name', 'Remark'
            ];
        }
        $headers = array_merge($headers, $baseHeaders);
        $insertIndex = array_search($qtyLebel, $headers);
        $dynamicColumns = [];
        if ($isNorm) {
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

    private function getComponentRow($parameters, $isNorm, $canView, $component): array
    {
        $row = [];
        if ($this->isEnabled('section_required', $parameters)) {
            $row[] = $component->section_name ?? '';
        }
        if ($this->isEnabled('sub_section_required', $parameters)) {
            $row[] = $component->sub_section_name ?? '';
        }

        if($canView) {
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
                $this->formatComponentAttributes($component->attributes ?? []),
                // $this->formatAttributes($component->attributes),
                optional($component->uom)->name,
                $component->qty ?? 0,
                $component->station_name ?? '',
                optional($component->vendor)->company_name,
                $component->remark,
            ];
        }
        $dynamicValues = [];
        if ($isNorm) {
            $dynamicValues[] = $component?->norm?->qty_per_unit ?? 0;
            $dynamicValues[] = $component?->norm?->total_qty ?? 0;
            $dynamicValues[] = $component?->norm?->std_qty ?? 0;
        }
        $consumptionIndex = 4;
        array_splice($baseRow, $consumptionIndex + 1, 0, $dynamicValues);
        return array_merge($row, $baseRow);
    }

    private function getInstructionHeaders($parameters): array
    {
        $headers = ['Station'];
        if ($this->isEnabled('section_required', $parameters)) {
            $headers[] = 'Section';
        }
        if ($this->isEnabled('sub_section_required', $parameters)) {
            $headers[] = 'Sub Section';
        }
        $headers[] = 'Description';
        return $headers;
    }

    private function getInstructionRow($parameters, $step): array
    {
        $row = [
            optional($step->station)->name,
        ];
        if ($this->isEnabled('section_required', $parameters)) {
            $row[] = optional($step->section)->name;
        }
        if ($this->isEnabled('sub_section_required', $parameters)) {
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

    private function isEnabled(string $key, $parameters): bool
    {
        return isset($parameters->{$key}) &&
            is_array($parameters->{$key}) &&
            in_array('yes', array_map('strtolower', $parameters->{$key}));
    }
}
