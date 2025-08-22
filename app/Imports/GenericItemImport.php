<?php

namespace App\Imports;

use App\Helpers\GenericImport\GenericImportHelper;
use App\Models\Unit;
use App\Models\Vendor;
use Carbon\Traits\Units;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;

class GenericItemImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
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
                    if ($key === 'attribute' && !empty($value) && strpos($value, ':') !== false) {
                        [$group, $attributeValue] = array_map('trim', explode(':', $value, 2));
                        $groupModel = \App\Models\AttributeGroup::whereRaw('LOWER(name) = ?', [strtolower($group)])->first();
                        if (!$groupModel) {
                            $errors[] = "Attribute group '$group' not found";
                        } elseif (!$groupModel->attributes()->whereRaw('LOWER(value) = ?', [strtolower($attributeValue)])->exists()) {
                            $errors[] = "Attribute '$attributeValue' not found in group '$group'";
                        } else {
                            $parsedRow['short_name'] = $groupModel->short_name ?? '';
                            $parsedRow['group_name'] = $groupModel->name ?? '';
                            $parsedRow['attribute_group_id'] = $groupModel->id;
                            $parsedRow['attribute_value'] = $attributeValue;

                            if (isset($item)) {
                                $attrArray = $item->item_attributes_array();
                                
                                foreach ($attrArray as &$groupAttr) {
                                    if ((int)$groupAttr['attribute_group_id'] === (int)$groupModel->id) {
                                        foreach ($groupAttr['values_data'] as &$valueObj) {
                                            // Handle object or array
                                            $val = is_object($valueObj) ? $valueObj->value : ($valueObj['value'] ?? null);
                                            if (strtolower($val) == strtolower($attributeValue)) {
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

                                $parsedRow['item_attribute_array'] = $attrArray;
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
                    if (stripos($key, 'date') !== false) {
                        if (empty($value)) {
                            $parsedRow[$key] = null;
                            // ✅ Empty date cell → just ignore
                            continue;
                        }
                        try {
                            if (is_numeric($value)) {
                                // ✅ Real Excel date cell
                                $carbonDate = Carbon::instance(ExcelDate::excelToDateTimeObject($value));
                                $parsedRow[$key] = $carbonDate->format('Y/m/d');
                            } elseif (is_string($value) && preg_match('/^\d{4}[\/\-](0[1-9]|1[0-2])[\/\-](0[1-9]|[12][0-9]|3[01])$/', trim($value))) {
                                // ✅ Strict string in yyyy/mm/dd or yyyy-mm-dd
                                $carbonDate = Carbon::createFromFormat('Y/m/d', str_replace('-', '/', trim($value)));
                                $parsedRow[$key] = $carbonDate->format('Y/m/d');
                            } else {
                                // ❌ Not a valid date
                                $parsedRow[$key] = null;
                                $errors[] = "Invalid date format in column $key";
                            }
                        } catch (\Exception $e) {
                            $parsedRow[$key] = null;
                            $errors[] = "Error parsing date in column $key";
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

    public function getParsedRows(): array
    {
        return [
            'valid' => $this->validRows,
            'invalid' => $this->invalidRows,
        ];
    }
}
