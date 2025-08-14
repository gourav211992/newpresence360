<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
// use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

// class DynamicExport implements FromArray, WithStyles, WithColumnWidths
class DynamicExport implements FromArray, WithStyles, WithEvents
{
    protected array $exportData;
    protected array $styleMap = [];

    public function __construct(array $exportData)
    {
        $this->exportData = $exportData;
    }

    public function array(): array
    {
        $output = [];
        $rowCounter = 1;

        // Title
        if (!empty($this->exportData['title'])) {
            $title = $this->formatCell($this->exportData['title'], $rowCounter++);
            $output[] = [$title['text']];
            $this->styleMap[$rowCounter - 1] = $title['style'];
        }

        // Org
        if (!empty($this->exportData['org'])) {
            $org = $this->formatCell($this->exportData['org'], $rowCounter++);
            $output[] = [$org['text']];
            $this->styleMap[$rowCounter - 1] = $org['style'];
        }

        // Sections
        foreach ($this->exportData['sections'] as $section) {
            $hasData = !empty($section['data']);
            $hasTable = !empty($section['headers']) && !empty($section['rows']);
            if (!$hasData && !$hasTable) {
                continue;
            }
            $output[] = [''];
            $rowCounter++;
            
            // Section Title
            
            if (!empty($section['section_title'])) {
                $formattedTitle = $this->formatCell([
                    'text' => $section['section_title'],
                    'bold' => $section['bold'] ?? true,
                    'font_size' => $section['font_size'] ?? 12,
                ], $rowCounter++);

                $output[] = [$formattedTitle['text']];
                $this->styleMap[$rowCounter - 1] = $formattedTitle['style'];
            }

            // Key-Value Data
            if (!empty($section['data'])) {
                foreach ($section['data'] as $row) {
                    $output[] = $row;
                    $rowCounter++;
                }
            }

            // Table with Headers & Rows
            if (!empty($section['headers']) && !empty($section['rows'])) {
                
                $output[] = [''];
                $rowCounter++;

                $headers = $this->formatHeaders($section['headers'], $rowCounter++);
                $output[] = $headers['text'];
                $this->styleMap[$rowCounter - 1] = $headers['style'];

                foreach ($section['rows'] as $row) {
                    $output[] = $row;
                    $rowCounter++;
                }
            }
        }

        return $output;
    }

    public function styles(Worksheet $sheet)
    {
        return $this->styleMap;
    }

    protected function formatCell($input, int $rowNumber): array
    {
        // If only a string is given, convert it
        if (is_string($input)) {
            return [
                'text' => $input,
                'style' => [],
            ];
        }

        return [
            'text' => $input['text'] ?? '',
            'style' => [
                'font' => [
                    'bold' => $input['bold'] ?? false,
                    'size' => $input['font_size'] ?? 11,
                ],
            ],
        ];
    }

    protected function formatHeaders($headers, int $rowNumber): array
    {
        // If just array of headers (no styling)
        if (array_is_list($headers)) {
            return [
                'text' => $headers,
                'style' => [
                    'font' => ['bold' => true, 'size' => 11],
                ],
            ];
        }

        // If structured with 'values' and optional styles
        return [
            'text' => $headers['values'] ?? [],
            'style' => [
                'font' => [
                    'bold' => $headers['bold'] ?? true,
                    'size' => $headers['font_size'] ?? 11,
                ],
            ],
        ];
    }

    // public function columnWidths(): array
    // {
    //     return [
    //         'A' => 15,  // Item Code
    //         'B' => 30,  // Description
    //         'C' => 10,  // UOM
    //         'D' => 10,  // Qty
    //         'E' => 8,   // Level
    //         'F' => 40,  // Attributes (if merged as JSON or string)
    //     ];
    // }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                foreach (range('A', 'Z') as $columnID) {
                    $event->sheet->getDelegate()->getColumnDimension($columnID)->setWidth(15);
                }
            },
        ];
    }
}
