<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InspectionChecklistExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithCustomStartCell
{
    protected $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return $this->rows;
    }

    public function startCell(): string
    {
        return 'A1';
    }


    public function headings(): array
    {
        return [
            [
                'Checklist Name',
                'Checklist Description',
                'Detail Name',
                'Data Type',
                'Description',
                'Mandatory',
                'Values',
            ],

            [
                'Enter the name of the checklist',
                'Enter the name of the description',
                'Enter the detail name of the checklist',
                'text/number/list/date',
                'Short description of detail',
                'Y/N if required',
                'Comma-separated values if Data Type = list',
            ]
        ];
    }

    /**
     * Mapping for each row
     */
    public function map($row): array
    {
        return [
            $row->name ?? '',
            $row->description ?? '',
            $row->detail_name ?? '',
            $row->data_type ?? '',
            $row->detail_description ?? '',
            isset($row->mandatory) ? ($row->mandatory ? 'Y' : 'N') : '',
            is_array($row->values) ? implode(',', $row->values) : '',
        ];
    }

    /**
     * Styling
     */
    public function styles(Worksheet $sheet)
    {
        $columns = range('A', 'G'); 

        foreach ($columns as $col) {
            $styleArray = [
                'font' => ['bold' => true, 'color' => ['argb' => 'FF000000']],
                'alignment' => [
                    'wrapText' => true,
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['argb' => ($col === 'A' || $col === 'C') ? 'FFFF00' : 'D3D3D3'],
                ],
            ];

            $sheet->getStyle("{$col}1")->applyFromArray($styleArray);
            $sheet->getColumnDimension($col)->setWidth(25);
        }

        $sheet->getStyle("A2:G2")->applyFromArray([
          'font' => ['color' => ['argb' => 'FF000000']],
            'alignment' => [
                'wrapText' => true,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);
    }
}
