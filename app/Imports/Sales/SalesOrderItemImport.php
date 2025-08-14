<?php

namespace App\Imports\Sales;
use App\Helpers\BookHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\Helper;
use App\Helpers\ItemHelper;
use App\Models\Attribute;
use App\Models\Book;
use App\Models\Customer;
use App\Models\ErpSaleOrder;
use App\Models\ErpStore;
use App\Models\Item;
use App\Models\SaleOrderImport;
use App\Models\SoItemImport;
use App\Models\SubType;
use App\Models\Unit;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use stdClass;

class SalesOrderItemImport implements ToArray, WithHeadingRow, SkipsEmptyRows, WithChunkReading
{
    // private $bookId = null;
    // private $locationId = null;
    private $authUserId = null;
    public function __construct(int $authUserId)
    {
        // //Assign Book and Location Id
        // $this -> bookId = $bookId;
        // $this -> locationId = $locationId;
        $this -> authUserId = $authUserId;
    }
    public function array(array $rows)
    {
        //Book and Location Validation
        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex) {
                $orderDetail = new stdClass();
                $errors = [];
                //Item
                $orderDetail -> item_code = $row['item_code'];
                $subTypeIds = SubType::whereIn('name', [ConstantHelper::FINISHED_GOODS, ConstantHelper::TRADED_ITEM, 
                ConstantHelper::ASSET,ConstantHelper::WIP_SEMI_FINISHED])
                -> get() -> pluck('id') -> toArray();
                $item = Item::withDefaultGroupCompanyOrg() -> where('status', ConstantHelper::ACTIVE) -> whereHas('subTypes', function ($subTypeQuery) use($subTypeIds) {
                    $subTypeQuery -> whereIn('sub_type_id', $subTypeIds);
                }) -> where('type', ConstantHelper::GOODS) -> where('item_code', $orderDetail -> item_code) -> first();
                $orderDetail -> item_id = $item ?-> id;
                if (!isset($item)) {
                    $errors[] = "Item not found or invalid item specified";
                } else {
                    //Attributes
                    $actualItemAttributes = $item -> itemAttributes;
                    $attributesArray = [];
                    //Continue with attribute validation if present
                    if ($actualItemAttributes && count($actualItemAttributes) > 0) {
                        $attributesString = $row['attributes'];
                        if (!$attributesString) {
                            $errors[] = "Item Attributes not specified";
                        }
                        //Explode the string to make the attributes array
                        $attributesArrayRaw = explode(',', $attributesString);
                        if (count($attributesArrayRaw) !== count($actualItemAttributes)) {
                            $errors[] = "All Attributes of item not specified";
                        }
                        //Make the attributes array
                        $attributeNameValues = [];
                        foreach ($attributesArrayRaw as $attribute) {
                            $attributeKeyValue = explode(':', $attribute);
                            if (count($attributeKeyValue) == 2) {
                                //Trim the attribute name and value for flexible checking
                                $attributeNameValues[trim(strtolower($attributeKeyValue[1]))] = trim(strtolower($attributeKeyValue[0]));
                            }
                        }
                        foreach ($actualItemAttributes as $actualItemAttribute) {
                            $attribute = $actualItemAttribute -> attributeGroup;
                            $attributeName = $attribute ?-> name;
                            $attributeNameLower = strtolower($attribute ?-> name);
                            $index = array_search($attributeNameLower, $attributeNameValues);
                            //Attribute Value is matched and found
                            if ($index && $attributeNameLower == $attributeNameValues[$index]) {
                                //Now check for the attribute Name
                                $attributeValue = $index;
                                $attributeVal = Attribute::whereIn('id', $actualItemAttribute -> attribute_id) 
                                -> whereRaw('LOWER(value) = ?', [$attributeValue]) -> first();
                                //Both Value and name are matched -> add the item attribute
                                if (isset($attributeVal)) {
                                    array_push($attributesArray, [
                                        'item_attribute_id' => $actualItemAttribute -> id,
                                        'attribute_name' => $attributeName,
                                        'attr_name' => $attribute ?-> id,
                                        'attribute_value' => $attributeVal -> value,
                                        'attr_value' => $attributeVal -> id,
                                        'attribute_id' => $attributeVal -> id,
                                    ]);
                                } else {
                                    $errors[] = "Invalid Item Attribute Value - $attributeValue specified for $attributeName";
                                    break;
                                }
                            } else {
                                $errors[] = "Invalid Item Attribute Name specified";
                                break;
                            }
                        }
                    }
                    $orderDetail -> attributes = $attributesArray;
                    //Check if Item Bom Exists
                    $bomDetails = ItemHelper::checkItemBomExists($orderDetail -> item_id, $attributesArray);
                    if (!isset($bomDetails['bom_id'])) {
                        $errors[] = "Bom not found";
                    }
                    $orderDetail -> qty = floatval($row['qty']);
                    if ($orderDetail -> qty <= 0) {
                        $errors[] = "Item Quantity not specified";
                    }
                }
                //UOM
                $orderDetail -> uom_code = $row['uom'];
                if ($orderDetail -> uom_code) {
                    $uom = Unit::withDefaultGroupCompanyOrg() -> where('status', ConstantHelper::ACTIVE) 
                    -> where('name', $orderDetail -> uom_code) -> first();
                    $orderDetail -> uom_id = $uom ?-> id;
                    if (!$uom) {
                        $errors[] = "UOM not found";
                    }
                }
                //Rate
                if ($row['rate']) {
                    $orderDetail -> rate = $row['rate'];
                } else {
                    $orderDetail -> rate = $item ?-> sell_price;
                }
                if (!$orderDetail -> rate) {
                    $errors[] = "Item Rate not specified";
                }
                //Delivery Date
                if ($row['delivery_date']) {
                    $orderDetail -> delivery_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['delivery_date'])->format('Y-m-d');;
                } else {
                    $orderDetail -> delivery_date = Carbon::now() -> format("Y-m-d");
                }
                $orderDetail -> created_by = $this -> authUserId;
                $orderDetail -> is_migrated = "0";
                $orderDetail -> created_at = Carbon::now() -> format('Y-m-d');
                $orderDetail -> updated_at = Carbon::now() -> format('Y-m-d');
                $orderDetail -> reason = $errors;
                //Sales Order Insertion
                SoItemImport::create((array) $orderDetail);
            }
        }
        
    }
    public function chunkSize() : int
    {
        return 100;
    }
}
