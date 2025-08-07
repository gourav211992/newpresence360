<?php

namespace App\Traits;

use Maatwebsite\Excel\Facades\Excel;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use App\Interfaces\Exportable;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

trait DataTableExportable
{
    public function exportDataTable(Request $request, $query, $exportable, $exportType, $title)
    {
        if (!($exportable instanceof Exportable)) {
            throw new \InvalidArgumentException('Model must implement the Exportable interface.');
        }

        $columns = $exportable->getExportColumns();
        $filenameBase = $exportable->getExportFileName();
        $search = $request->input('search', '');
        $order = $request->input('order', null);
        $title = preg_replace('/[^a-zA-Z0-9\s]/', '', $title);

        $exportableRelationships = $exportable->exportableRelationships ?? [];

        if ($order) {
            if (is_array($order) && count($order) > 0 && isset($order[0]['column']) && isset($order[0]['dir'])) {
                $columnIndex = $order[0]['column'];
                if (!empty($columns) && isset(array_keys($columns)[$columnIndex])) {
                    $dir = $order[0]['dir'];
                    $column = array_values($columns)[$columnIndex] ?? 'id'; 
                    $query->orderBy($column, $dir);
                } else {
                    $query->orderBy('id', 'asc');
                }
            } else {
                $query->orderBy('id', 'asc');
            }
        } else {
            $query->orderBy('id', 'asc');
        }


        $finalData = [];
        $chunkSize = 500;

        $query->chunk($chunkSize, function ($results) use ($columns, &$finalData) {
            foreach ($results as $item) {
                $rowData = [];

                foreach ($columns as $header => $dbColumn) {
                    if (strpos($dbColumn, '.') !== false) {
                        $parts = explode('.', $dbColumn);
                        $relationName = $parts[0];
                        $attributeName = $parts[1] ?? null;
                        $related = $item->{$relationName} ?? null;
                        if ($related && $attributeName) {
                            $rowData[$header] = $related->{$attributeName} ?? null;
                        } else {
                            $rowData[$header] = null;
                        }
                    } else {
                        if ($dbColumn === 'sub_types_list') {
                            $subTypeNames = $item->subTypes->pluck('subType.name')->toArray();
                            if ($item->is_traded_item) {
                                $subTypeNames[] = 'Traded Item';
                            }

                            if ($item->is_asset) {
                                $subTypeNames[] = 'Asset';
                            }

                            $rowData[$header] = !empty($subTypeNames) ? implode(', ', $subTypeNames) : 'N/A';
                        } else {
                            $rowData[$header] = $item->{$dbColumn} ?? null;
                        }               
                    }
                }
               // -- Start Dynamic Headings --
                // Item Attributes
                if (method_exists($item, 'itemAttributes') && $item->itemAttributes?->count()) {
                    $itemAttributes = $item->itemAttributes;
                        for ($i = 1; $i <= 5; $i++) {
                            if (isset($itemAttributes[$i - 1])) {
                                $attribute = $itemAttributes[$i - 1];

                                $rowData["Attribute {$i} Group Name"] = $attribute?->attributeGroup?->name;

                                $attributeIds = $attribute?->attribute_id ?? [];
                                if (is_array($attributeIds)) {
                                    $attributeNames = \App\Models\Attribute::whereIn('id', $attributeIds)->pluck('value')->toArray();
                                    $rowData["Attribute {$i} Attribute Name"] = implode(', ', $attributeNames);
                                } else {
                                    $rowData["Attribute {$i} Attribute Name"] = null;
                                }

                                $rowData["Attribute {$i} All Checked"] = $attribute?->all_checked ? 'Yes' : 'No';

                            } else {
                                $rowData["Attribute {$i} Group Name"] = null;
                                $rowData["Attribute {$i} Attribute Name"] = null;
                                $rowData["Attribute {$i} All Checked"] = null;
                            }
                        }
                    }
                
                //specification
                if (method_exists($item, 'specifications') && $item->specifications?->count()) {
                $specifications = $item->specifications;

                    $specGroupName = $specifications[0]?->group?->name ?? null;
                    $rowData["Product Specification Group"] = $specGroupName;

                    for ($i = 1; $i <= 5; $i++) {
                        if (isset($specifications[$i - 1])) {
                            $spec = $specifications[$i - 1];
                            $rowData["Specification {$i} Name"] = $spec->specification?->name;
                            $rowData["Specification {$i} Value"] = $spec->value;
                        } else {
                            $rowData["Specification {$i} Name"] = null;
                            $rowData["Specification {$i} Value"] = null;
                        }
                    }
                }
              if (method_exists($item, 'alternateUOMs') && $item->alternateUOMs?->count()) {
                    $alternateUOMs = $item->alternateUOMs;

                    for ($i = 1; $i <= 5; $i++) {
                        if (isset($alternateUOMs[$i - 1])) {
                            $uom = $alternateUOMs[$i - 1];

                            $rowData["Alternate UOM {$i} UOM"] = $uom?->uom?->name ?? null;
                            $rowData["Alternate UOM {$i} Conversion To Inventory"] = $uom?->conversion_to_inventory ?? null;
                            $rowData["Alternate UOM {$i} Cost Price"] = $uom?->cost_price ?? null;
                            $rowData["Alternate UOM {$i} Sell Price"] = $uom?->sell_price ?? null;
                            
                            $isSelling = $uom?->is_selling;
                            $isPurchasing = $uom?->is_purchasing;

                            if ($isSelling && $isPurchasing) {
                                $usage = 'S & P';
                            } elseif ($isSelling) {
                                $usage = 'S';
                            } elseif ($isPurchasing) {
                                $usage = 'P';
                            } else {
                                $usage = '';
                            }

                            $rowData["Alternate UOM {$i} Usage"] = $usage;

                        } else {
                            $rowData["Alternate UOM {$i} UOM"] = null;
                            $rowData["Alternate UOM {$i} Conversion To Inventory"] = null;
                            $rowData["Alternate UOM {$i} Cost Price"] = null;
                            $rowData["Alternate UOM {$i} Sell Price"] = null;
                            $rowData["Alternate UOM {$i} Usage"] = null;
                        }
                    }
                }
             
                // -- End Dynamic Headings --
                $finalData[] = (object) $rowData;
            }
        });
  
        if (!empty($finalData)) {
            $columns = [];
            foreach (array_keys((array)$finalData[0]) as $key) {
                $columns[$key] = $key;
            }
        }
        
        $timestamp = date('YmdHis');
        $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '', $filenameBase) . "_export_{$timestamp}";

       if ($exportType === 'excel' || $exportType === 'csv') {
            $extension = $exportType === 'excel' ? 'xlsx' : 'csv';

            $exportData = collect($finalData)->map(function ($item) {
                return (array) $item;
            })->toArray();

            $allKeys = [];
            foreach ($exportData as $row) {
                $allKeys = array_merge($allKeys, array_keys($row));
            }
            $headings = array_unique($allKeys);

            $normalizedData = [];
            foreach ($exportData as $row) {
                $normalizedRow = [];
                foreach ($headings as $heading) {
                    $normalizedRow[] = $row[$heading] ?? '';
                }
                $normalizedData[] = $normalizedRow;
            }

            // Step 4: Excel Download
            if ($exportType === 'excel') {
                array_unshift($normalizedData, array_fill(0, count($headings), '')); 

                return $this->buildExcelExportDownload($normalizedData, $headings, $title, $filename, $extension);
            }

            // Step 5: CSV Download
            return Excel::download(new class($normalizedData, $headings) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithHeadings {
                private $data;
                private $headings;

                public function __construct(array $data, array $headings) {
                    $this->data = $data;
                    $this->headings = $headings;
                }

                public function array(): array {
                    return $this->data;
                }

                public function headings(): array {
                    return $this->headings;
                }
            }, "$filename.$extension");
        }

        // Export to PDF
        if ($exportType === 'pdf') {
            $html = View::make('exports.datatable_print', compact('finalData', 'columns', 'title'))->render();
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->render();
            return $dompdf->stream("$filename.pdf");
        }

        // 10. Export to Print
        if ($exportType === 'print') {
            return response(View::make('exports.datatable_print', compact('finalData', 'columns', 'title')));
        }
        // Export to Copy
        if ($exportType === 'copy') {
            return response()->json($finalData);
        }

        return response('Invalid export type.', 400);
    }

    protected function buildExcelExportDownload(array $exportData, array $headings, string $title, string $filename, string $extension) {
        return Excel::download(new class($exportData, $headings, $title) implements
            \Maatwebsite\Excel\Concerns\FromArray,
            \Maatwebsite\Excel\Concerns\WithHeadings,
            \Maatwebsite\Excel\Concerns\WithTitle,
            \Maatwebsite\Excel\Concerns\WithStyles {

            private $data;
            private $headings;
            private $title;

            public function __construct(array $data, array $headings, string $title) {
                $this->data = $data;
                $this->headings = $headings;
                $this->title = $title;
            }

           public function array(): array {
                return array_map(function ($row) {
                    return array_map(function ($value) {
                        return ($value === null || $value === '') ? '-' : $value;
                    }, $row);
                }, $this->data);
            }

            public function headings(): array {
                return $this->headings;
            }

            public function title(): string {
                return $this->title;
            }

            public function styles(Worksheet $sheet) {
                $sheet->setCellValue('A1', $this->title);
                $sheet->getStyle('A1')->getFont()->setBold(true);
                $sheet->setTitle($this->title);
                $columnCount = count($this->headings);
                $sheet->mergeCells('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCount) . '1');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->fromArray([$this->headings], null, 'A2');
                $headingRange = 'A2:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCount) . '2';
                $sheet->getStyle($headingRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($headingRange)->getFont()->setBold(true);
                $contentRange = 'A3:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCount) . $sheet->getHighestRow();
                $sheet->getStyle($contentRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $defaultWidth = 15;
                for ($i = 1; $i <= $columnCount; $i++) {
                    $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setWidth($defaultWidth);
                }

                return [];
            }
        }, "$filename.$extension");
    }
}
