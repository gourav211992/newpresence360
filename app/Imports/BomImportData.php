<?php

namespace App\Imports;

use App\Helpers\BookHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\Helper;
use App\Helpers\ItemHelper;
use App\Models\AttributeGroup;
use App\Models\Attribute;
use App\Models\Bom;
use App\Models\BomUpload;
use App\Models\Customer;
use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\ProductionRouteDetail;
use App\Models\ProductionRoute;
use App\Models\ProductSection;
use App\Models\ProductSectionDetail;
use App\Models\Station;
use App\Models\Vendor;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class BomImportData implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    protected $bookId;
    protected $documentDate;
    protected $moduleTyle;

    public function __construct($bookId, $documentDate, $moduleTyle)
    {
        $this->bookId = $bookId;
        $this->documentDate = $documentDate;
        $this->moduleTyle = $moduleTyle;
    }

    public function collection(Collection $rows)
    {
        $response = BookHelper::fetchBookDocNoAndParameters($this->bookId, $this->documentDate);
        $parameters = json_decode(json_encode($response['data']['parameters']), true) ?? [];
        $consumption_method = isset($parameters['consumption_method']) && $parameters['consumption_method'][0] == 'manual' ? false : true;
        $trimmedRows = $rows->map(function ($row) {
            $rowArray = $row->toArray();
            while (end($rowArray) === null || end($rowArray) === '') {
                array_pop($rowArray);
            }
            return collect($rowArray);
        });
        $user = Helper::getAuthenticatedUser();
        // $filteredRows = $rows->filter(fn($row) => collect($row)->filter()->isNotEmpty());
        // DB::beginTransaction(); // Start Transaction
        // try {
            foreach ($trimmedRows as $index => $row) {
                if($index) {
                    $errors = [];
                    $productItem = Item::where('item_code', @$row['product_code'])->first();
                    if(!$productItem) {
                        $errors[] = "Product not found: {$row['product_code']}";
                    }

                    $productAttributes = [];
                    # Need to valid item attribute length
                    if($productItem?->itemAttributes?->count()) {
                        for ($i = 1; $i <= 5; $i++) {
                            $result = $this->validateAttribute($productItem, $row, $i,'Product');
                            if (!empty($result['error'])) {
                                $errors[] = $result['error'];
                            }
                            if (!empty($result['attribute'])) {
                                $productAttributes[] = $result['attribute'];
                            }
                        }
                        if($productItem?->itemAttributes?->count() != count($productAttributes)) {
                            $errors[] = "Product Attribute length not match";
                        }
                    }

                    $productCode = $productItem?->item_code; 
                    $productName = $productItem?->item_name;
                    $productId = $productItem?->id;
                    $productUomId = $productItem?->uom?->id;
                    $productUomCode = $productItem?->uom?->name;
                    $productionType =  $row['production_type'] ?? 'In-house';
                    if(!in_array(strtolower($productionType), ['in-house','job work'])) {
                        $errors[] = "Invalid Production Type: {$row['production_type']}";
                    }
                    $productionRoute = ProductionRoute::where('name', @$row['production_route'])->first();
                    if(!$productionRoute) {
                        $errors[] = "Production route not found";
                    }
                    $productionRouteId = $productionRoute?->id;

                    $stationName = $row['station'] ?? ''; 
                    $station = Station::where('name', $stationName)->first();
                    if(!$station) {
                        $errors[] = "Station not found";
                    }
                    $stationId = $station?->id;

                    $vendorcode = $row['vendor_code'] ?? ''; 
                    $vendor = Vendor::where('vendor_code', $vendorcode)->first();
                    if(!$vendor) {
                        $errors[] = "Vendor not found";
                    }
                    $vendorId = $vendor?->id;
                    $customercode = $row['customer_code'] ?? ''; 
                    $customer = Customer::where('customer_code', $customercode)->first();
                    if($this->moduleTyle == 'qbom') {
                        if(!$vendor) {
                            $errors[] = "Customer not found";
                        }
                    }
                    $customerId = $customer?->id;
                    
                    $sectionName = $row['section'] ?? ''; 
                    $section = ProductSection::where('name', $sectionName)->first();
                    if(!$section) {
                        $errors[] = "Section not found";
                    }
                    $sectionId = $section?->id;

                    $subSectionName = $row['sub_section'] ?? ''; 
                    $subSection = ProductSectionDetail::where('name', $subSectionName)->first();
                    if(!$subSection) {
                        $errors[] = "Sub section not found";
                    }
                    $subSectionId = $subSection?->id;

                    $checkStationMapped = null;
                    if($productionRouteId && $stationId) {
                       $checkStationMapped = $productionRoute->details()->where('station_id',$stationId)->first();
                    } 
                    if(!$checkStationMapped) {
                        $errors[] = "Station not mapped with Production route";
                    }
                    $customizable = $row['customizable'] ?? 'no';
                    if(!in_array(strtolower($customizable), ['yes','no'])) {
                        $errors[] = "Invalid customizable: {$row['customizable']}";
                    }
                    $item = Item::where('item_code', @$row['item_code'])->first();
                    if(!$item) {
                        $errors[] = "Item not found: {$row['item_code']}";
                    }
                    $itemId = $item?->id; 
                    $itemCode = $item?->item_code; 
                    $itemUomId = $item?->uom?->id;
                    $itemUomCode = $item?->uom?->name;

                    $itemAttributes = [];
                    # Need to valid item attribute length
                    if($item?->itemAttributes?->count()) {
                        for ($i = 1; $i <= 5; $i++) {
                            $result = $this->validateAttribute($item, $row, $i, 'Item');
                            if (!empty($result['error'])) {
                                $errors[] = $result['error'];
                            }
                            if (!empty($result['attribute'])) {
                                $itemAttributes[] = $result['attribute'];
                            }
                        }
                        if($item?->itemAttributes?->count() != count($itemAttributes)) {
                            $errors[] = "Item Attribute length not match";
                        }
                    }

                    $consumptionQty = $row['consumption_qty'] ?? 0; 

                    if(!$consumption_method) {
                        if (is_null($consumptionQty) || $consumptionQty === '') {
                            $errors[] = "Consumption is required.";
                        } elseif (!is_numeric($consumptionQty)) {
                            $errors[] = "Consumption must be a valid number.";
                        } elseif ($consumptionQty < 0) {
                            $errors[] = "Consumption cannot be negative.";
                        } elseif ($consumptionQty == 0) {
                            $errors[] = "Consumption cannot be zero.";
                        }
                    }
                    $consumption_per_unit = 0;
                    $pieces = 0;
                    $std_qty = 0;
                    if($consumption_method) {
                        $consumption_per_unit = $row['consumption_per_unit'] ?? 0;
                        $pieces = $row['pieces'] ?? 0;
                        $std_qty = $row['std_qty'] ?? 0;
                        if ($consumption_per_unit !== null && $consumption_per_unit !== '') {
                            if (!is_numeric($consumption_per_unit)) {
                                $errors[] = "Norms Consumption per unit must be a valid number.";
                            } elseif ($consumption_per_unit < 0) {
                                $errors[] = "Norms Consumption per unit cannot be negative.";
                            } elseif ($consumption_per_unit == 0) {
                                $errors[] = "Norms Consumption per unit cannot be zero.";
                            }
                        }
                        if ($pieces !== null && $pieces !== '') {
                            if (!is_numeric($pieces)) {
                                $errors[] = "Norms pieces must be a valid number.";
                            } elseif ($pieces < 0) {
                                $errors[] = "Norms pieces cannot be negative.";
                            } elseif ($pieces == 0) {
                                $errors[] = "Norms pieces cannot be zero.";
                            }
                        }
                        if ($std_qty !== null && $std_qty !== '') {
                            if (!is_numeric($std_qty)) {
                                $errors[] = "Norms std qty must be a valid number.";
                            } elseif ($std_qty < 0) {
                                $errors[] = "Norms std qty cannot be negative.";
                            } elseif ($std_qty == 0) {
                                $errors[] = "Norms std qty cannot be zero.";
                            }
                        }
                    }
                    

                    $costPerUnit = $row['cost_per_unit'] ?? 0;
                    $currency =  CurrencyHelper::getOrganizationCurrency();
                    $currencyId = $currency?->id ?? null; 
                    $transactionDate = date('Y-m-d');
                    $itemCost = 0;
                    if($itemId) {
                        $itemCost = ItemHelper::getItemCostPrice($itemId, [], $itemUomId, $currencyId, $transactionDate);
                    }
                    if(!floatval($costPerUnit)) {
                        $costPerUnit = $itemCost; 
                    }
                    if(!$costPerUnit) {
                        $errors[] = "Item cost not defined";
                    }
                    
                    BomUpload::create(
                        [
                            'type' => 'bom',
                            'production_route_id' => $productionRouteId,
                            'production_route_name' => @$row['production_route'],
                            'product_item_id' => $productId,
                            'product_item_code' => $productCode,
                            'product_item_name' => $productName,
                            'uom_id' => $productUomId,
                            'uom_code' => $productUomCode,
                            'customizable' => $customizable,
                            'bom_type' => 'fixed',
                            'production_type' => $productionType,
                            'item_id' => $itemId,
                            'item_code' => $itemCode,
                            'item_uom_id' => $itemUomId,
                            'item_uom_code' => $itemUomCode,
                            'item_attributes' => $itemAttributes,
                            'product_attributes' => $productAttributes ?? [],
                            'reason' => $errors,
                            'product_attribute_name_1' => @$row['product_attribute_name_1'],
                            'product_attribute_value_1' => @$row['product_attribute_value_1'],
                            'product_attribute_name_2' => @$row['product_attribute_name_2'],
                            'product_attribute_value_2' => @$row['product_attribute_value_2'],
                            'product_attribute_name_3' => @$row['product_attribute_name_3'],
                            'product_attribute_value_3' => @$row['product_attribute_value_3'],
                            'product_attribute_name_4' => @$row['product_attribute_name_4'],
                            'product_attribute_value_4' => @$row['product_attribute_value_4'],
                            'product_attribute_name_5' => @$row['product_attribute_name_5'],
                            'product_attribute_value_5' => @$row['product_attribute_value_5'],
                            'attribute_name_1' => @$row['attribute_name_1'],
                            'attribute_value_1' => @$row['attribute_value_1'],
                            'attribute_name_2' => @$row['attribute_name_2'],
                            'attribute_value_2' => @$row['attribute_value_2'],
                            'attribute_name_3' => @$row['attribute_name_3'],
                            'attribute_value_3' => @$row['attribute_value_3'],
                            'attribute_name_4' => @$row['attribute_name_4'],
                            'attribute_value_4' => @$row['attribute_value_4'],
                            'attribute_name_5' => @$row['attribute_name_5'],
                            'attribute_value_5' => @$row['attribute_value_5'],
                            'consumption_qty' => $consumptionQty,
                            'consumption_per_unit' => $consumption_per_unit,
                            'pieces' => $pieces,
                            'std_qty' => $std_qty,
                            'cost_per_unit' => $costPerUnit,
                            'station_id' => $stationId,
                            'station_name' => $stationName,
                            'section_id' => $sectionId,
                            'section_name' => $sectionName,
                            'sub_section_id' => $subSectionId,
                            'sub_section_name' => $subSectionName,  
                            'vendor_id' => $vendorId,
                            'vendor_code' => $vendorcode,
                            'vendor_name' => $vendor?->company_name,
                            'customer_id' => $customerId,
                            'customer_code' => $customercode,
                            'customer_name' => $customer?->company_name,
                            'remark' => @$row['remark'],
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );                        
                }
            }

            $groupedItemsTests = BomUpload::where('migrate_status', 0)
                ->where('created_by', $user->auth_user_id)
                ->get();
            
            foreach($groupedItemsTests as $groupedItemsTest) {
                if (empty($groupedItemsTest->calculated_consumption) && empty($groupedItemsTest->consumption_qty)) {
                    $reasons = $groupedItemsTest->reason ?? [];
                    $reasons[] = 'Either calculated consumption or manual consumption must be defined.';
                    $groupedItemsTest->reason = array_unique($reasons);
                    $groupedItemsTest->save();
                }
                if($groupedItemsTest->calculated_consumption || $groupedItemsTest->consumption_qty) {
                    $reasons = $groupedItemsTest->reason ?? [];
                    $reasons = array_filter($reasons, function ($reason) {
                        return !str_contains(strtolower($reason), 'norms');
                    });
                    $groupedItemsTest->reason = array_unique($reasons);
                    $groupedItemsTest->save();
                }
                if($consumption_method) {

                }
            } 

            $groupedItems = BomUpload::select('product_item_id', DB::raw('COUNT(DISTINCT production_route_id) as route_count'))
                ->where('migrate_status', 0)
                ->where('created_by', $user->auth_user_id)
                ->groupBy('product_item_id')
                ->get();
            foreach ($groupedItems as $item) {

                if ($item->route_count > 1) {
                    BomUpload::where('migrate_status', 0)
                        ->where('created_by', $user->auth_user_id)
                        ->where('product_item_id', $item->product_item_id)
                        ->get()
                        ->each(function ($row) {
                            $reasons = $row->reason ?? [];
                            $reasons[] = 'Production route multiple';
                            $row->reason = array_unique($reasons);
                            $row->save();
                        });
                } else {
                    
                    $allData = BomUpload::where('migrate_status', 0)
                    ->where('product_item_id', $item->product_item_id)
                    ->where('created_by', $user->auth_user_id)
                    ->first();

                    $prDetailStationIds = ProductionRouteDetail::where('production_route_id',$allData?->production_route_id)
                                    ->where('consumption', 'yes')
                                    ->pluck('station_id')
                                    ->toArray();

                    $bomStationIds = BomUpload::where('migrate_status', 0)
                                    ->where('product_item_id', $item->product_item_id)
                                    ->where('created_by', $user->auth_user_id)
                                    ->pluck('station_id')
                                    ->toArray();
                    $newDiffArr = array_diff($prDetailStationIds, $bomStationIds);
                    if(count($newDiffArr)) {
                        $reasons = $allData->reason ?? [];
                        $reasons[] = "All station of production route not defined in Bom";
                        $allData->reason = array_unique($reasons);
                        $allData->save();
                    }
                }
            }

        //     DB::commit();
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     \Log::error('BOM Import Error: ' . $e->getMessage());
        //     throw $e;
        // }
    }

    private function validateAttribute($item, $row, int $index, $label): array
    {
        $attribute = null;
        if($label == 'Product') {
            $groupName = $row["product_attribute_name_{$index}"] ?? null;
            $valueName = $row["product_attribute_value_{$index}"] ?? null;
        } else {
            $groupName = $row["attribute_name_{$index}"] ?? null;
            $valueName = $row["attribute_value_{$index}"] ?? null;
        }
        if (!$groupName) return [];
        $group = AttributeGroup::withDefaultGroupCompanyOrg()->where('name', $groupName)->first();
        if (!$group) {
            return ['error' => "Attr {$index} group not found"];
        }
        $attr = Attribute::where('value', $valueName)->where('attribute_group_id', $group->id)->first();
        if (!$attr) {
            return ['error' => "{$label} Attr {$index} value not found"];
        }
        if ($item && $group) {
            $itemAttr = ItemAttribute::where('item_id', $item->id)->where('attribute_group_id', $group->id)->first();
            if (!$itemAttr) {
                return ['error' => "{$label} Attr {$index} not mapped to item"];
            }
            $attrIds = $itemAttr->all_checked
                ? Attribute::where('attribute_group_id', $group->id)->pluck('id')->toArray()
                : (array) $itemAttr->attribute_id;
            if (!in_array($attr->id, $attrIds)) {
                return ['error' => "{$label} Attr {$index} value not mapped with item"];
            }
            $attribute = [
                'item_attribute_id' => $itemAttr->id,
                'attribute_name_id' => $group->id,
                'attribute_value_id' => $attr->id,
            ];
        }
        return ['attribute' => $attribute];
    }
}
