<?php

namespace App\Exports;

use App\Helpers\ConstantHelper;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Services\LedgerImportExportService;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FixedAssetReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $items;
    protected $service;

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
            'Code',
            'Name',
            'Group',
            'Status',
            'tds_section',
            'tds_percentage',
            'tcs_section',
            'tcs_percentage',
            'tax_type',
            'tax_percentage',
            'Remarks',
        ];

        return $headings;
    }

    public function map($item): array
    {
        $groupNames = $this->service->getGroupNamesByIds($item->ledger_group_id);
        $tdsSections = ConstantHelper::getTdsSections();
        $tcsSections = ConstantHelper::getTcsSections();
        $taxTypes    = ConstantHelper::getTaxTypes();
        $status = 
        $data = [
            $item->code,
            $item->name,
            implode(', ', $groupNames),
            ($item->status == 1) ? ConstantHelper::STATUS[0] : ConstantHelper::STATUS[1],
            $tdsSections[$item->tds_section] ?? 'N/A',
            $item->tds_percentage ?? 'N/A',
            $tcsSections[$item->tcs_section] ?? 'N/A',
            $item->tcs_percentage ?? 'N/A',
            $taxTypes[$item->tax_type] ?? 'N/A',
            $item->tax_percentage ?? 'N/A',
            'Success',
        ];

        return $data;
    }

    
}
