<?php

namespace App\Imports;

use App\Helpers\GenericImport\GenericImportHelper;
use App\Models\Unit;
use App\Models\Vendor;
use Carbon\Traits\Units;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;

class GenericItemImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithChunkReading
{
    protected string $alias;
    protected array $validRows = [];
    protected array $invalidRows = [];

    public function __construct(string $alias)
    {
        $this->alias = $alias;
    }

    public function collection(Collection $rows)
    {
        $headerMap = GenericImportHelper::getHeaderMap($this->alias);
        $requiredKeys = ['item_code', 'item_name'];

        // Normalize labels for reverse lookup
        // Normalize headerMap keys for reverse lookup
        $reverseMap = [];
        foreach ($headerMap as $key => $label) {
            $normalized = strtolower(preg_replace('/\s+/', '', $key));
            $reverseMap[$normalized] = $key;
        }
        foreach ($rows as $index => $row) {
            $parsedRow = [];
            $errors = [];
            $item = null;
            foreach ($row as $column => $value) {
                $normalizedColumn = strtolower(preg_replace('/\s+/', '', str_replace("\xc2\xa0", ' ', $column)));
                $key = $reverseMap[$normalizedColumn] ?? null;
                if ($key) {
                    $value = is_string($value) ? trim($value) : $value;
                    $parsedRow[$key] = $value;

                    if (in_array($key, $requiredKeys) && empty($value)) {
                        $errors[] = "$column is required";
                    }

                    if ($key === 'item_code' && !empty($value)) {
                        $item = \App\Models\Item::where('item_code', $value)->first();
                        if (!$item) {
                            $errors[] = "Item Code '$value' not found";
                        } else {
                            $uom = Unit::find($item->uom_id);
                            $parsedRow['item_id'] = $item->id;
                            $parsedRow['item_name'] = $item->item_name;
                            $parsedRow['uom_id'] = $item->uom_id;
                            $parsedRow['uom_name'] = $uom->name;
                            $parsedRow['rate'] = $item->rate ?? 0;
                            $parsedRow['specifications'] = $item->specifications ?? 0;
                            $parsedRow['hsn_code'] = $item->hsn->code ?? 0;
                        }
                    }
                    if($key ==='item_name') {
                        if($item)
                        {
                            $parsedRow['item_name'] = $item->item_name;
                        }
                        else
                        {
                            $item = \App\Models\Item::where('item_name', $value)->first();
                            if (!$item) {
                                $errors[] = "Item Name '$value' not found";
                            } else {
                                $parsedRow['item_id'] = $item->id;
                                $parsedRow['item_name'] = $item->item_name;
                                $parsedRow['uom_id'] = $item->uom_id;
                                $parsedRow['uom_name'] = $item->uom->name ?? '';
                                $parsedRow['rate'] = $item->rate ?? 0;
                                $parsedRow['specifications'] = $item->specifications ?? '';
                                $parsedRow['hsn_code'] = $item->hsn->code ?? '';
                                $parsedRow['hsn_id'] = $item->hsn->id ?? '';
                            }
                        }
                    }
                    if ($key === 'attribute') {
                        $attrArray = isset($item) ? $item->item_attributes_array() : [];
                        $parsedRow['item_attribute_array'] = $attrArray; // always send back

                        if (!empty($value) && strpos($value, ':') !== false) {
                            [$group, $attributeValue] = array_map('trim', explode(':', $value, 2));

                            $groupModel = \App\Models\AttributeGroup::whereRaw('LOWER(name) = ?', [strtolower($group)])->first();

                            if (!$groupModel) {
                                $errors[] = "Attribute group '$group' not found";
                            } elseif (!$groupModel->attributes()->whereRaw('LOWER(value) = ?', [strtolower($attributeValue)])->exists()) {
                                $errors[] = "Attribute '$attributeValue' not found in group '$group'";
                            } else {
                                $parsedRow['short_name']         = $groupModel->short_name ?? '';
                                $parsedRow['group_name']         = $groupModel->name ?? '';
                                $parsedRow['attribute_group_id'] = $groupModel->id;
                                $parsedRow['attribute_value']    = $attributeValue;

                                $matchFound = false;

                                foreach ($attrArray as &$groupAttr) {
                                    if ((int) $groupAttr['attribute_group_id'] === (int) $groupModel->id) {
                                        foreach ($groupAttr['values_data'] as &$valueObj) {
                                            $val = is_object($valueObj) ? $valueObj->value : ($valueObj['value'] ?? null);

                                            if (strtolower($val) === strtolower($attributeValue)) {
                                                $matchFound = true;
                                                if (is_object($valueObj)) {
                                                    $valueObj->selected = true;
                                                    $parsedRow['attribute_id'] = $valueObj->id;
                                                } else {
                                                    $valueObj['selected'] = true;
                                                    $parsedRow['attribute_id'] = $valueObj['id'] ?? null;
                                                }
                                            } else {
                                                if (is_object($valueObj)) {
                                                    $valueObj->selected = false;
                                                } else {
                                                    $valueObj['selected'] = false;
                                                }
                                            }
                                        }
                                    }
                                }

                                if (!$matchFound) {
                                    $parsedRow['attribute_id'] = null; // no match, still return all attributes
                                }
                            }
                        }
                    }
                    if($key === 'uom_code' && !empty($value)) {
                        $uom = Unit::where('name', $value)->first();
                        if (!$uom) {
                            $errors[] = "UOM Code '$value' not found";
                        } else {
                            $parsedRow['uom_id'] = $uom->id;
                            $parsedRow['uom_name'] = $uom->name;
                        }
                    }
                    if($key === 'vendor_name' && !empty($value)) {
                        $vendor = Vendor::where('company_name', $value)->orWhere('vendor_code',$value)->first();
                        if (!$vendor) {
                            $errors[] = "Vendor Name '$value' not found";
                        } else {
                            $parsedRow['vendor_id'] = $vendor->id;
                            $parsedRow['vendor'] = $vendor;
                            $parsedRow['vendor_name'] = $vendor->name;
                        }
                    }
                    if (stripos($key, 'date') !== false || in_array($key, ['effective_from', 'effective_upto'])) {
                        if (empty($value)) {
                            $parsedRow[$key] = null;
                            continue;
                        }

                        try {
                            if (is_numeric($value)) {
                                // ✅ Excel serial → Carbon
                                $carbonDate = \Carbon\Carbon::instance(
                                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                                );
                            } else {
                                // Normalize strings (replace "-" with "/")
                                $cleanValue = trim(str_replace('-', '/', $value));

                                if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $cleanValue)) {
                                    // dd/mm/yyyy
                                    $carbonDate = \Carbon\Carbon::createFromFormat('d/m/Y', $cleanValue);
                                } elseif (preg_match('/^\d{4}\/\d{1,2}\/\d{1,2}$/', $cleanValue)) {
                                    // yyyy/mm/dd
                                    $carbonDate = \Carbon\Carbon::createFromFormat('Y/m/d', $cleanValue);
                                } else {
                                    // fallback parse
                                    $carbonDate = \Carbon\Carbon::parse($cleanValue);
                                }
                            }

                            // Always store normalized format
                            $parsedRow[$key] = $carbonDate->format('Y-m-d');

                            // Extra validation: effective_upto >= effective_from
                            if ($key === 'effective_upto' && !empty($parsedRow['effective_from'])) {
                                $fromDate = \Carbon\Carbon::createFromFormat('Y-m-d', $parsedRow['effective_from']);
                                if ($carbonDate->lt($fromDate)) {
                                    $parsedRow[$key] = null;
                                    $errors[] = "Effective Upto date cannot be before Effective From date";
                                }
                            }
                        } catch (\Exception $e) {
                            $parsedRow[$key] = null;
                            $errors[] = "Invalid date format in column $key";
                        }
                    }


                    else{
                        $parsedRow[$key] = $value;
                    }

                }
                
            }
            
            $parsedRow['row_number'] = $index + 2;
            $parsedRow['errors'] = $errors;
            if (empty($errors)) {
                $this->validRows[] = $parsedRow;
            } else {
                $this->invalidRows[] = $parsedRow;
            }
        }
    }

    public function getValidRows(): array
    {
        return $this->validRows;
    }

    public function getInvalidRows(): array
    {
        return $this->invalidRows;
    }

    public function chunkSize(): int
    {
        return 100; // ✅ process 100 rows per chunk
    }
    public function getParsedRows(): array
    {
        return [
            'valid' => $this->validRows,
            'invalid' => $this->invalidRows,
        ];
    }
}