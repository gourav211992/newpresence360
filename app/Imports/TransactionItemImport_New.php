<?php
namespace App\Imports;

use App\Models\Item;
use App\Models\Unit;
use App\Helpers\Helper;
use App\Models\ErpStore;
use App\Models\Attribute;
use App\Models\MrnHeader;
use App\Models\MrnDetail;
use App\Models\ErpSubStore;
use App\Helpers\ItemHelper;
use App\Models\MrnAttribute;
use App\Models\ItemAttribute;
use App\Models\AttributeGroup;
use App\Helpers\CurrencyHelper;
use App\Models\ErpSubStoreParent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Models\TransactionUploadItem;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class TransactionItemImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    private $storeId;
    private $mrnType;
    private $mrnHeaderId;


    public function chunkSize(): int
    {
        return 10000; 
    }
    public function __construct($storeId = null, $mrnType = null, $mrnHeaderId = null)
    {
        $this->storeId = $storeId;
        $this->mrnType = $mrnType;
        $this->mrnHeaderId = $mrnHeaderId;
    }

    public function collection(Collection $rows)
    {
        $user = Helper::getAuthenticatedUser();
        $filteredRows = $rows->filter(fn($row) => collect($row)->filter()->isNotEmpty());
        foreach ($filteredRows as $index => $row) {
            if($index) {
                $item = NULL;
                $uom = NULL;
                $store = NULL;
                $orderQty = NULL;
                $itemRate = NULL;
                $location = NULL;
                $errors = [];
                $itemAttributes = [];

                if (empty($row['item_code'])) {
                    $errors[] = "Item Code is mandatory";
                } else{
                    $item = Item::where('item_code', @$row['item_code'])->first();
                    if(!$item) {
                        $errors[] = "Item not found: {$row['item_code']}";
                    }
                }
                if (empty($row['uom_code'])) {
                    $errors[] = "UOM Code is mandatory";
                } else{
                    $uom = Unit::where('name', @$row['uom_code'])->first();
                    if(!$uom) {
                        $errors[] = "UOM not found: {$row['uom_code']}";
                    }
                }
                if ($row['store_code']) {
                    $location = ErpStore::find($this->storeId);
                    $store = ErpSubStoreParent::where('store_id', $this->storeId)
                    ->with(['store', 'sub_store'])
                    ->whereHas('sub_store', function ($query) use ($row) {
                        $query->where('code', $row['store_code']);
                    })
                    ->first();
                    if(!$store) {
                        $errors[] = "Store : {$row['store_code']} not found in location : {$location->name}";
                    }
                }
                if (empty($row['order_qty'])) {
                    $errors[] = "Order Qty is mandatory";
                } else{
                    $orderQty = $row['order_qty'] ?? null;
                    if (!is_numeric($orderQty) || $orderQty < 0) {
                        $errors[] = "Order Qty must be a non-negative number";
                    }
                }
                if (empty($row['item_rate'])) {
                    $errors[] = "Item Rate is mandatory";
                } else{
                    $itemRate = $row['item_rate'] ?? null;
                    if (!is_numeric($itemRate) || $itemRate < 0) {
                        $errors[] = "Item Rate must be a non-negative number";
                    }
                }

                $itemId = $item?->id; 
                $itemCode = $item?->item_code;
                $itemName = $item?->item_name; 
                $itemHsnId = $item?->hsn?->id;
                $itemHsnCode = $item?->hsn?->code;
                $itemUomId = $item?->uom?->id;
                $itemUomCode = $item?->uom?->name;
                $storeId = $store?->sub_store_id;
                $storeCode = $store?->sub_store?->code;
                $itemAttributes = [];
                # Need to valid item attribute length
                if($item?->itemAttributes?->count()) {
                    for ($i = 1; $i <= 5; $i++) {
                        $result = $this->validateAttribute($item, $row, $i);
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
                
                self::checkDuplicateRecords($itemCode, $uom, $itemAttributes, $errors, $this->mrnType, $this->mrnHeaderId);
                
                $is_error = 0;
                $status = 'success';

                if(!empty($errors)) {
                    $is_error = 1;
                    $status = 'Failed';
                } else {
                    $is_error = 0;
                    $status = 'Success';
                }
                
                TransactionUploadItem::create(
                    [
                        'type' => 'mrn',
                        'item_id' => $itemId,
                        'item_code' => $itemCode,
                        'item_name' => $itemName,
                        'hsn_id' => $itemHsnId,
                        'hsn_code' => $itemHsnCode,
                        'uom_id' => $itemUomId,
                        'uom_code' => $itemUomCode,
                        'order_qty' => $orderQty,
                        'rate' => $itemRate,
                        'store_id' => $storeId,
                        'store_code' => $storeCode,
                        'reason' => json_encode($errors),
                        'created_by' => $user->id,
                        'status' => $status,
                        'attributes' => json_encode($itemAttributes),
                        'is_error' => $is_error,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );                        
            }
        }
    }

    // Validate Attributes
    private function validateAttribute($item, $row, int $index): array
    {
        $attribute = null;
        $groupName = $row["attribute_name_{$index}"] ?? null;
        $valueName = $row["attribute_value_{$index}"] ?? null;
        if (!$groupName) return [];
        $group = AttributeGroup::withDefaultGroupCompanyOrg()->where('name', $groupName)->first();
        if (!$group) {
            return ['error' => "Attr {$index} group not found"];
        }
        $attr = Attribute::where('value', $valueName)->where('attribute_group_id', $group->id)->first();
        if (!$attr) {
            return ['error' => "Attr {$index} value not found"];
        }
        if ($item && $group) {
            $itemAttr = ItemAttribute::where('item_id', $item->id)->where('attribute_group_id', $group->id)->first();
            if (!$itemAttr) {
                return ['error' => "Attr {$index} not mapped to item"];
            }
            $attrIds = $itemAttr->all_checked
                ? Attribute::where('attribute_group_id', $group->id)->pluck('id')->toArray()
                : (array) $itemAttr->attribute_id;
            if (!in_array($attr->id, $attrIds)) {
                return ['error' => "Attr {$index} value not mapped with item"];
            }
            $attribute = [
                'attribute_name_id' => $group->id,
                'attribute_value_id' => $attr->id,
            ];
        }
        return ['attribute' => $attribute];
    }

    // Check for duplicate records in TransactionUploadItem table
    public static function checkDuplicateRecords($itemCode, $uom, $itemAttributes, &$errors, $mrnType, $mrnHeaderId)
    {
        // dd($itemCode, $uom, $itemAttributes, $errors, $mrnType, $mrnHeaderId);
        // If item attributes are empty, check for duplicate items in TransactionUploadItem table based on item_code and uom
        if (empty($itemAttributes)) {
            $existingTransactionItem = TransactionUploadItem::where('item_code', $itemCode)
                ->where('uom_id', $uom?->id)
                ->first();

            if ($existingTransactionItem) {
                $errors[] = "Duplicate item found in TransactionUploadItem table with item_code: {$itemCode} and uom: {$uom?->name}.";
            }
        }else{
            // Check for duplicate items in TransactionUploadItem table
            $existingTransactionItem = TransactionUploadItem::where('item_code', $itemCode)
            ->where('uom_id', $uom?->id)
            ->where(function ($query) use ($itemAttributes) {
                foreach ($itemAttributes as $attribute) {
                    $query->whereJsonContains('attributes', [
                        'attribute_name_id' => $attribute['attribute_name_id'],
                        'attribute_value_id' => $attribute['attribute_value_id']
                    ]);
                }
            })
            ->first();
            // If duplicate item found, add error message
            if ($existingTransactionItem) {
                $errors[] = "Duplicate item found in TransactionUploadItem table with item_code: {$itemCode}, uom: {$uom?->name}, and attributes.";
            }
        }

        if($mrnType == 'edit'){
            if (empty($itemAttributes)) {
                $existingMrnItem = MrnDetail::where('item_code', $itemCode)
                    ->where('uom_id', $uom?->id)
                    ->where('mrn_header_id', $mrnHeaderId)
                    ->first();
    
                if ($existingMrnItem) {
                    $errors[] = "Duplicate item found in MrnDetail table with item_code: {$itemCode} and uom: {$uom?->name}.";
                }
            }else{
                // Check for duplicate items in MrnDetail table
                $existingMrnItem = MrnDetail::where('item_code', $itemCode)
                    ->where('uom_id', $uom?->id)
                    ->where('mrn_header_id', $mrnHeaderId)
                    ->whereHas('attributes', function ($query) use ($itemAttributes) {
                        $query->where(function ($subQuery) use ($itemAttributes) {
                            foreach ($itemAttributes as $attribute) {
                                $subQuery->where(function ($nestedQuery) use ($attribute) {
                                    $nestedQuery->where('attr_name', $attribute['attribute_name_id'])
                                                ->where('attr_value', $attribute['attribute_value_id']);
                                });
                            }
                        });
                    })
                    ->first();
                   
                // If duplicate item found, add error message
                if ($existingMrnItem) {
                    $errors[] = "Duplicate item found in MrnDetail table with item_code: {$itemCode}, uom: {$uom?->name}, and attributes.";
                }
            }    
        }
    }
}
