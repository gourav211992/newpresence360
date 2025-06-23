<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class BomExport implements FromArray, WithTitle
{
    protected $bom;

    public function __construct($bomId)
    {
        $this->bom = \App\Models\Bom::findOrFail($bomId);
    }

    public function title(): string
    {
        return 'BOM-' . $this->bom->book_code;
    }

    public function array(): array
    {
        $rows = [];

        // Section: Header
        $rows[] = ['BOM Header'];
        $rows[] = ['BOM No:', $this?->bom?->book_code];
        $rows[] = ['Item Name:', optional($this?->bom?->item)?->item_name];
        $rows[] = ['UOM:', $this?->bom?->uom?->name];
        $rows[] = ['Description:', $this?->bom?->remarks];
        $rows[] = ['']; // empty row

        // Section: Components
        $rows[] = ['Components'];
        $rows[] = ['Component Name', 'Qty', 'UOM', 'Cost'];
        foreach ($this->bom->bomItems as $component) {
            $rows[] = [
                optional($component?->item)?->name,
                $component->qty ?? 0,
                $component?->uom?->name,
                $component->item_value ?? 0,
            ];
        }

        $rows[] = ['']; // empty row

        // Section: Instructions
        $rows[] = ['Instructions'];
        $rows[] = ['Step No', 'Description'];
        foreach ($this->bom->bomInstructions as $step) {
            $rows[] = [$step->step_no, $step->instructions];
        }

        return $rows;
    }
}
