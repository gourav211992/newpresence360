<?php

namespace App\Exports;

use App\Helpers\ConstantHelper;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Carbon\Carbon;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use App\Helpers\Helper;

class FixedAssetReportExport implements FromCollection, WithHeadings, WithMapping, WithEvents, WithStyles
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

    // Row 2: Actual column headers
    public function headings(): array
    {
        return [
            'Asset Code',
            'Asset Name',
            'Sub Asset Code',
            'Sub Asset Name',
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
        return [
            $item?->asset?->asset_code ?? 'N/A',
            $item?->asset?->asset_name ?? 'N/A',
            $item?->sub_asset_code ?? 'N/A',
            $item?->asset?->asset_name ?? 'N/A',
            $item?->asset?->category?->name ?? 'N/A',
            $item?->asset?->reference_series == ConstantHelper::FIXED_ASSET_MERGER ? 'Merger' : ($item?->asset?->reference_series == ConstantHelper::FIXED_ASSET_SPLIT ? 'Split' : 'Register'),
            $item?->asset?->document_date != null
                ? Carbon::parse($item->asset->document_date)->format('d-m-Y')
                : 'N/A',
            $item?->asset?->vendor?->company_name ?? 'N/A',
            $item?->asset?->purchase_amount && $item?->asset?->quantity
                ? Helper::formatIndianNumber($item->asset->purchase_amount / $item->asset->quantity)
                : 'N/A',
            Helper::formatIndianNumber($item?->salvage_value) ?? 'N/A',
            $item?->location?->store_name ?? 'N/A',
            $item?->issue?->authorizedPerson?->name ?? 'N/A',

            $item?->asset?->useful_life && !empty($item?->capitalize_date) && !empty($item?->expiry_date)
                ? Carbon::parse($item->capitalize_date)->diffInYears(Carbon::parse($item->expiry_date)) .
                ' (' . Carbon::parse($item->capitalize_date)->diffInDays(Carbon::parse($item->expiry_date)) . ' days)'
                : ($item?->asset?->useful_life
                    ? $item->asset->useful_life . ' (' . ($item->asset->useful_life * 365) . ' days)'
                    : 'N/A'),

            $item?->last_dep_date && $item?->expiry_date
                ? Carbon::parse($item->last_dep_date)->diffInYears(Carbon::parse($item->expiry_date)) .
                ' (' . Carbon::parse($item->last_dep_date)->diffInDays(Carbon::parse($item->expiry_date)) . ' days)'
                : ($item?->asset?->useful_life
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
            $item?->insurances?->expiry_date
                ? (Carbon::parse($item->insurances->expiry_date)->lt(Carbon::today()) ? 'Expired' : 'Active')
                : 'N/A',
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
                ? Helper::formatIndianNumber($item->rev->currentvalue)
                : 'N/A',
            $item?->rev?->document_date != null
                ? Carbon::parse($item->rev->document_date)->format('d-m-Y')
                : 'N/A',
            $item?->rev?->revaluate !== null
                ? Helper::formatIndianNumber($item->rev->revaluate)
                : 'N/A',
            $item?->imp?->currentvalue !== null
                ? Helper::formatIndianNumber($item->imp->currentvalue)
                : 'N/A',
            $item?->imp?->document_date != null
                ? Carbon::parse($item->imp->document_date)->format('d-m-Y')
                : 'N/A',
            $item?->imp?->revaluate !== null
                ? Helper::formatIndianNumber($item->imp->revaluate)
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
                    'Asset Identification',
                    '',
                    '',
                    '',
                    '',
                    '',                    // A-F: empty
                    'Acquisition & Salvage Details:',
                    '',
                    '',
                    '', // G-J: 4 cells for this header
                    'Location & Allocation:',
                    '',               // K-L: 2 cells
                    'Depreciation and Useful Life:',
                    '',
                    '',
                    '',
                    '',
                    '', // M-R: 6 cells
                    'Insurance & Security:',
                    '',
                    '',
                    '',        // S-V: 4 cells
                    'Status Tracking:',
                    '',
                    '',
                    '',              // W-Z: 4 cells
                    'Audit & Verification:',
                    '',                 // AA-AB: 2 cells
                    'Maintenance & Condition:',
                    '',
                    '',          // AC-AE: 3 cells
                    'Revaluation Details:',
                    '',
                    '',               // AF-AH: 3 cells
                    'Impairment Details:',
                    '',
                    ''                  // AI-AK: 3 cells
                ]], null, 'A1');

                // Merging matching cells
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('G1:J1');   // Acquisition & Salvage Details (4 columns)
                $sheet->mergeCells('K1:L1');   // Location & Allocation (2 columns)
                $sheet->mergeCells('M1:R1');   // Depreciation and Useful Life (6 columns)
                $sheet->mergeCells('S1:V1');   // Insurance & Security (4 columns)
                $sheet->mergeCells('W1:Z1');   // Status Tracking (4 columns)
                $sheet->mergeCells('AA1:AB1'); // Audit & Verification (2 columns)
                $sheet->mergeCells('AC1:AE1'); // Maintenance & Condition (3 columns)
                $sheet->mergeCells('AF1:AH1'); // Revaluation Details (3 columns)
                $sheet->mergeCells('AI1:AK1'); // Impairment Details (3 columns)
                $totalRows = $sheet->getHighestRow(); // Includes your inserted rows + data rows
                $totalColumns = 37; // Adjust if your columns change

                // Get column letter for last column
                $lastColumnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalColumns);

                $range = "A1:{$lastColumnLetter}{$totalRows}";
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'], // black color
                        ],
                    ],
                ]);

                $sheet->getStyle('1:1')->getFont()->setBold(true);
                $sheet->getStyle('1:1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // For second row just bold
                $sheet->getStyle('2:2')->getFont()->setBold(true);


                // Total columns count - count your headings array


                // Loop through columns A, B, C ... up to the last needed column and set auto size
                for ($col = 0; $col < $totalColumns; $col++) {
                    // Convert number to letter: 0 => A, 1 => B, etc.
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                }
                $rightAlignedColumns = ['I', 'J', 'Q', 'R', 'S', 'Y', 'Z', 'AF', 'AH', 'AI', 'AK'];
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Apply right alignment
                foreach ($rightAlignedColumns as $column) {
                    $sheet->getStyle("{$column}2:{$column}{$highestRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
                $leftAlignedColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'K', 'L', 'M', 'N', 'O', 'P', 'T', 'U', 'V', 'W', 'X', 'AA', 'AB', 'AC', 'AD', 'AE', 'AG', 'AJ']
;
                // Apply left alignment + force text format
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
        // Columns to be right-aligned

        // Bold the first row (headers)
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
