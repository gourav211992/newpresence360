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
// use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;


class TransactionItemImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    private $storeId;
    private $mrnType;
    private $mrnHeaderId;


    public function chunkSize(): int
    {
        return 900; 
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

        $batchInsert = [];
        $existingItemsCache = [];
        $existingMrnItemsCache = [];

        $itemCache = [];
        $uomCache = [];
        $storeCache = [];

        // Split filtered rows into smaller chunks of 900 for batch processing
        // $chunkedRows = $filteredRows->chunk(900);
        foreach ($filteredRows as $index => $row) {
            if ($index === 0) continue; // Skip header row

            $errors = [];
            $itemAttributes = [];

            $itemCode = $row['item_code'] ?? null;
            $uomCode = $row['uom_code'] ?? null;
            $storeCode = $row['store_code'] ?? null;

            // Validate Item
            if (!$itemCode) {
                $errors[] = "Item Code is mandatory";
            } elseif (!isset($itemCache[$itemCode])) {
                $item = Item::where('item_code', $itemCode)->with('hsn', 'uom', 'itemAttributes')->first();
                $itemCache[$itemCode] = $item ?: null;
            } else {
                $item = $itemCache[$itemCode];
            }

            if (!$item) {
                $errors[] = "Item not found: $itemCode";
            }

            // Validate UOM
            if (!$uomCode) {
                $errors[] = "UOM Code is mandatory";
            } elseif (!isset($uomCache[$uomCode])) {
                $uom = Unit::where('name', $uomCode)->first();
                $uomCache[$uomCode] = $uom ?: null;
            } else {
                $uom = $uomCache[$uomCode];
            }

            if (!$uom) {
                $errors[] = "UOM not found: $uomCode";
            }

            // Validate Store
            $storeId = null;
            $storeLocation = ErpStore::find($this->storeId);
            $storeKey = "{$this->storeId}_{$storeCode}";

            if ($storeCode && !isset($storeCache[$storeKey])) {
                $store = ErpSubStoreParent::where('store_id', $this->storeId)
                    ->with(['sub_store'])
                    ->whereHas('sub_store', fn($q) => $q->where('code', $storeCode))
                    ->first();
                $storeCache[$storeKey] = $store ?: null;
            } else {
                $store = $storeCache[$storeKey];
            }

            if ($storeCode && !$store) {
                $errors[] = "Store : {$storeCode} not found in location : {$storeLocation->name}";
            }

            // Validate Quantities
            $orderQty = $row['order_qty'] ?? null;
            $itemRate = $row['item_rate'] ?? null;

            if (!is_numeric($orderQty) || $orderQty < 0) {
                $errors[] = "Order Qty must be a non-negative number";
            }

            if (!is_numeric($itemRate) || $itemRate < 0) {
                $errors[] = "Item Rate must be a non-negative number";
            }

            // Validate Attributes
            if ($item && $item->itemAttributes->count()) {
                for ($i = 1; $i <= 5; $i++) {
                    $result = $this->validateAttribute($item, $row, $i);
                    if (!empty($result['error'])) {
                        $errors[] = $result['error'];
                    }
                    if (!empty($result['attribute'])) {
                        $itemAttributes[] = $result['attribute'];
                    }
                }

                if ($item->itemAttributes->count() != count($itemAttributes)) {
                    $errors[] = "Item Attribute length not match";
                }
            }

            // Duplicate Check (in-memory)
            $itemKey = $itemCode . '_' . $uom?->id . '_' . md5(json_encode($itemAttributes));

            if (isset($existingItemsCache[$itemKey])) {
                $errors[] = "Duplicate item found in uploaded file (TransactionUploadItem)";
            } else {
                $existingItemsCache[$itemKey] = true;
            }

            if ($this->mrnType === 'edit') {
                if (!isset($existingMrnItemsCache[$itemKey])) {
                    $exists = MrnDetail::where('item_code', $itemCode)
                        ->where('uom_id', $uom?->id)
                        ->where('mrn_header_id', $this->mrnHeaderId)
                        ->whereHas('attributes', function ($query) use ($itemAttributes) {
                            foreach ($itemAttributes as $attribute) {
                                $query->where('attr_name', $attribute['attribute_name_id'])
                                    ->where('attr_value', $attribute['attribute_value_id']);
                            }
                        })
                        ->exists();

                    if ($exists) {
                        $errors[] = "Duplicate item found in MrnDetail with attributes";
                    }

                    $existingMrnItemsCache[$itemKey] = true;
                }
            }

            // Finalize
            $is_error = count($errors) > 0 ? 1 : 0;
            $status = $is_error ? 'Failed' : 'Success';

            $batchInsert[] = [
                'type' => 'mrn',
                'item_id' => $item?->id,
                'item_code' => $item?->item_code,
                'item_name' => $item?->item_name,
                'hsn_id' => $item?->hsn?->id,
                'hsn_code' => $item?->hsn?->code,
                'uom_id' => $item?->uom?->id,
                'uom_code' => $item?->uom?->name,
                'order_qty' => $orderQty,
                'rate' => $itemRate,
                'store_id' => $store?->sub_store_id,
                'store_code' => $store?->sub_store?->code,
                'reason' => json_encode($errors),
                'created_by' => $user->id,
                'status' => $status,
                'attributes' => json_encode($itemAttributes),
                'is_error' => $is_error,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        // Bulk insert
        if (!empty($batchInsert)) {
            $chunks = array_chunk($batchInsert, 900); // Insert in chunks of 1000
            foreach ($chunks as $chunk) {
                TransactionUploadItem::insert($batchInsert);
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

}
