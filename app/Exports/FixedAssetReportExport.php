<?php

namespace App\Exports;

use App\Helpers\ConstantHelper;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Carbon\Carbon;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Helpers\Helper;
use App\Models\FixedAssetSub;

class FixedAssetReportExport implements FromCollection, WithHeadings, WithMapping, WithEvents, WithStyles
{
    protected $items;

    protected $srNo = 1; // Counter for Sr. No.

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
        return [
            'Sr. No',
            'Asset Code',
            'Asset Name',
            'Sub Asset Code',
            'Asset Category',
            'Type',
            'Date of Acquisition',
            'Vendor Name',
            'Acquisition cost',
            'Salvage Value',
            'Current Location',
            'Assigned User',
            'Estimated Useful Life',
            'Balance Useful Life',
            'Depreciation Method',
            'Depreciation Start Date/ Put to use date',
            'Accumulated Depreciation',
            'Balance as per Books',
            'Insured Value',
            'Insurance Expiry Date',
            'Insurance Policy Reference',
            'Lien / Security Details',
            'Current Status',
            'Sale / Disposal Date',
            'Sale Proceeds / Residual Value',
            'Profit/(Loss) on sale',
            'Last Physical Verification Date',
            'Reconciliation Status with Ledger',
            'Maintenance Schedule',
            'Last Maintenance Date',
            'Condition',
            'Asset Revalued',
            'Revaluation date',
            'Revaluation gain',
            'Asset Impaired',
            'Impairment date',
            'Impairment loss',
        ];
    }

    public function map($item): array
    {

        $use = null;
        $bal_use = null;

        if ($item?->capitalize_date) {
            $capitalizeDate = Carbon::parse($item->capitalize_date);
            $expiryPlusOne =  $capitalizeDate->copy()->addYears($item->asset->useful_life);
            $years = $capitalizeDate->diffInYears($expiryPlusOne);
                $days = $capitalizeDate->diffInDays($expiryPlusOne);
                 $use = "{$years} ({$days} days)";
            
        }

        if ($item?->last_dep_date && $item?->expiry_date) {
            $expiryPlusOne = Carbon::parse($item->expiry_date)->addDay();
            $lastDepDate = Carbon::parse($item->last_dep_date);

            if ($lastDepDate->eq($item->expiry_date)) {
                $bal_use = "0 (0 days)";
            } else {
                $years = $lastDepDate->diffInYears($expiryPlusOne);
                $days = $lastDepDate->diffInDays($expiryPlusOne);

                $bal_use = "{$years} ({$days} days)";
            }
        }

        return [
            $this->srNo++,
            $item?->asset?->asset_code ?? 'N/A',
            $item?->asset?->asset_name ?? 'N/A',
            $item?->sub_asset_code ?? 'N/A',
            $item?->asset?->category?->name ?? 'N/A',
            $item?->asset?->reference_series == ConstantHelper::FIXED_ASSET_MERGER ? 'Merger' : ($item?->asset?->reference_series == ConstantHelper::FIXED_ASSET_SPLIT ? 'Split' : 'Register'),
            $item?->asset?->document_date != null
                ? Carbon::parse($item->asset->document_date)->format('d-m-Y')
                : 'N/A',
            $item?->asset?->vendor?->company_name ?? 'N/A',
            $item?->current_value
                ? Helper::formatIndianNumber($item->current_value)
                : 'N/A',
            Helper::formatIndianNumber($item?->salvage_value) ?? 'N/A',
            $item?->location?->store_name ?? 'N/A',
            $item?->issue?->authorizedPerson?->name ?? 'N/A',
            
            $item?->asset?->useful_life && !empty($item?->capitalize_date) && !empty($item?->expiry_date)
                ? $use : ($item?->asset?->useful_life
                    ? $item->asset->useful_life . ' (' . ($item->asset->useful_life * 365) . ' days)'
                    : 'N/A'),
            $item?->last_dep_date && $item?->expiry_date
                ? $bal_use : ($item?->asset?->useful_life
                    ? $item->asset->useful_life . ' (' . ($item->asset->useful_life * 365) . ' days)'
                    : 'N/A'),
            $item?->asset?->depreciation_method ?? 'N/A',
            $item?->capitalize_date != null
                ? Carbon::parse($item->capitalize_date)->format('d-m-Y')
                : 'N/A',
            Helper::formatIndianNumber($item?->total_depreciation) ?? 'N/A',
            Helper::formatIndianNumber($item?->current_value_after_dep) ?? 'N/A',
            Helper::formatIndianNumber($item?->insurances?->insured_value) ?? 'N/A',
            $item?->insurances?->expiry_date != null
                ? Carbon::parse($item->insurances->expiry_date)->format('d-m-Y')
                : 'N/A',
            $item?->insurances?->policy_no ?? 'N/A',
            $item?->insurances?->lien_security_details ?? 'N/A',
            FixedAssetSub::current_status($item->id)?FixedAssetSub::current_status($item->id):'Active',
            'N/A',
            'N/A',
            'N/A',
            $item?->maintenance?->verf_date != null
                ? Carbon::parse($item->maintenance->verf_date)->format('d-m-Y')
                : 'N/A',
            $item?->reconciliation_status ?? 'Done',
            $item?->asset?->maintenance_schedule ?? 'N/A',
            $item?->maintenance?->created_at != null
                ? Carbon::parse($item->maintenance->created_at)->format('d-m-Y')
                : 'N/A',
            $item?->condition ?? 'N/A',
            $item?->rev?->currentvalue !== null
                ? Helper::formatIndianNumber($item->rev->revaluate)
                : 'N/A',
            $item?->rev?->document_date != null
                ? Carbon::parse($item->rev->document_date)->format('d-m-Y')
                : 'N/A',
            $item?->rev?->revaluate !== null
                ? Helper::formatIndianNumber($item->rev->revaluate - $item->rev->currentvalue)
                : 'N/A',
            $item?->imp?->revaluate !== null
                ? Helper::formatIndianNumber($item->imp->revaluate)
                : 'N/A',
            $item?->imp?->document_date != null
                ? Carbon::parse($item->imp->document_date)->format('d-m-Y')
                : 'N/A',
            $item?->imp?->revaluate !== null
                ? Helper::formatIndianNumber($item->imp->currentvalue - $item->imp->revaluate)
                : 'N/A',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Insert the first row for grouped headings
                $sheet->insertNewRowBefore(1, 1);

                $sheet->fromArray([[
                    '', // Sr. No group header
                    'Asset Identification',
                    '',
                    '',
                    '',
                    '',
                    'Acquisition & Salvage Details:',
                    '',
                    '',
                    '',
                    'Location & Allocation:',
                    '',
                    'Depreciation and Useful Life:',
                    '',
                    '',
                    '',
                    '',
                    '',
                    'Insurance & Security:',
                    '',
                    '',
                    '',
                    'Status Tracking:',
                    '',
                    '',
                    '',
                    'Audit & Verification:',
                    '',
                    'Maintenance & Condition:',
                    '',
                    '',
                    'Revaluation Details:',
                    '',
                    '',
                    'Impairment Details:',
                    '',
                    '',
                ]], null, 'A1');

                $sheet->mergeCells('A1:A1'); // Sr. No
                $sheet->mergeCells('B1:F1');
                $sheet->mergeCells('G1:J1');
                $sheet->mergeCells('K1:L1');
                $sheet->mergeCells('M1:R1');
                $sheet->mergeCells('S1:V1');
                $sheet->mergeCells('W1:Z1');
                $sheet->mergeCells('AA1:AB1');
                $sheet->mergeCells('AC1:AE1');
                $sheet->mergeCells('AF1:AH1');
                $sheet->mergeCells('AI1:AK1');

                $totalColumns = 37; // Updated for Sr. No column
                $lastColumnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalColumns);
                $range = "A1:{$lastColumnLetter}" . $sheet->getHighestRow();

                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                $sheet->getStyle('1:1')->getFont()->setBold(true);
                $sheet->getStyle('1:1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('2:2')->getFont()->setBold(true);

                // Style first two header rows (red background, white bold font)
                $sheet->getStyle("A1:{$lastColumnLetter}2")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'C00000'],
                    ],
                ]);

                for ($col = 0; $col < $totalColumns; $col++) {
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                }

                $rightAlignedColumns = ['I', 'Q', 'J','R','S', 'Y', 'Z', 'AF', 'AH','AI', 'AK'];
                $highestRow = $sheet->getHighestRow();

                foreach ($rightAlignedColumns as $column) {
                    $sheet->getStyle("{$column}2:{$column}{$highestRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                $leftAlignedColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G','H', 'K', 'L', 'M', 'N', 'O','P', 'T', 'U', 'V', 'W', 'X', 'AA', 'AB', 'AC', 'AD','AE', 'AG','AJ'];

                foreach ($leftAlignedColumns as $column) {
                    $sheet->getStyle("{$column}2:{$column}{$highestRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }
            }
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
