<?php

namespace App\Models;

use App\Helpers\InventoryHelper;
use App\Models\JobOrder\JoItem;
use App\Models\JobOrder\JoProduct;
use App\Models\WHM\ErpItemUniqueCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Log;

class ErpMiItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'material_issue_id',
        'jo_item_id',
        'jo_product_id',
        'mi_item_id',
        'mo_item_id',
        'pwo_item_id',
        'pi_item_id',
        'pslip_item_id',
        'pslip_issue_type',
        'department_id',
        'department_code',
        'user_name',
        'user_id',
        'item_id',
        'wip_station_id',
        'item_code',
        'item_name',
        'stock_type',
        'hsn_id',
        'hsn_code',
        'uom_id',
        'uom_code',
        'from_store_id',
        'from_sub_store_id',
        'from_station_id',
        'from_store_code',
        'to_store_id',
        'to_sub_store_id',
        'to_station_id',
        'to_store_code',
        'issue_qty',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'rate',
        'item_discount_amount',
        'header_discount_amount',
        'item_expense_amount',
        'header_expense_amount',
        'tax_amount',
        'total_item_amount',
        'remarks',
    ];

    public $appends =[
        'mi_balance_qty',
    ];
    public $referencingRelationships = [
        'item' => 'item_id',
        'attributes' => 'mi_item_id',
        'uom' => 'uom_id',
        'hsn' => 'hsn_id',
        'inventoryUom' => 'inventory_uom_id'
    ];

    protected $hidden = ['deleted_at'];

    public function item()
    {
        return $this -> belongsTo(Item::class, 'item_id', 'id');
    }

    public function item_attributes()
    {
        return $this -> hasMany(ErpMiItemAttribute::class, 'mi_item_id');
    }

    public function attributes()
    {
        return $this -> hasMany(ErpMiItemAttribute::class, 'mi_item_id');
    }
    
    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function inventoryUom()
    {
        return $this->belongsTo(Unit::class, 'inventory_uom_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }

    public function item_attributes_array()
    {
        $itemId = $this -> getAttribute('item_id');
        if (isset($itemId)) {
            $itemAttributes = ErpItemAttribute::where('item_id', $this -> item_id) -> get();
        } else {
            $itemAttributes = [];
        }
        $processedData = [];
        foreach ($itemAttributes as $attribute) {
            $existingAttribute = ErpMiItemAttribute::where('mi_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> first();
            if (!isset($existingAttribute)) {
                continue;
            }
            $attributesArray = array();
            $attribute_ids = [];
            if ($attribute -> all_checked) {
                $attribute_ids = Attribute::where('attribute_group_id', $attribute -> attribute_group_id) -> get() -> pluck('id') -> toArray();
            } else {
                $attribute_ids = $attribute -> attribute_id ? json_decode($attribute -> attribute_id) : [];
            }
            $attribute -> group_name = $attribute -> group ?-> name;
            $attribute -> short_name = $attribute->group?->short_name;

            foreach ($attribute_ids as $attributeValue) {
                $attributeValueData = Attribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                    if (isset($attributeValueData))
                    {
                        $isSelected = ErpMiItemAttribute::where('mi_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> where('attribute_value', $attributeValueData -> value) -> first();
                        $attributeValueData -> selected = $isSelected ? true : false;
                        array_push($attributesArray, $attributeValueData);
                    }
                
            }
           $attribute -> values_data = $attributesArray;
           $attribute = $attribute -> only(['id','group_name', 'short_name', 'values_data', 'attribute_group_id']);
           array_push($processedData, ['id' => $attribute['id'], 'group_name' => $attribute['group_name'], 'short_name' => $attribute['short_name'], 'values_data' => $attributesArray, 'attribute_group_id' => $attribute['attribute_group_id']]);
        }
        $processedData = collect($processedData);
        return $processedData;
    }
    public function getMiBalanceQtyAttribute()
    {
        return $this -> getAttribute('issue_qty') - $this -> getAttribute('mr_qty');
    }
    public function hsn()
    {
        return $this -> belongsTo(Hsn::class);
    }
    public function from_item_locations()
    {
        return $this -> hasMany(ErpMiItemLocation::class, 'mi_item_id', 'id') -> where('type', 'from');
    }
    public function to_item_locations()
    {
        return $this -> hasMany(ErpMiItemLocation::class, 'mi_item_id', 'id') -> where('type', 'to');
    }
    public function header()
    {
        return $this -> belongsTo(ErpMaterialIssueHeader::class, 'material_issue_id');
    }
    public function getAvlStock($storeId)
    {
        $selectedAttributeIds = [];
        $itemAttributes = $this -> item_attributes_array();
        foreach ($itemAttributes as $itemAttr) {
            foreach ($itemAttr['values_data'] as $valueData) {
                if ($valueData['selected']) {
                    array_push($selectedAttributeIds, $valueData['id']);
                }
            }
        }
        $stocks = InventoryHelper::totalInventoryAndStock($this -> item_id, $selectedAttributeIds,$this -> uom_id,$storeId,null,null);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        error_log( $stockBalanceQty);
        return $stockBalanceQty;
    }

    public function fromErpStore()
    {
        return $this -> belongsTo(ErpStore::class, 'from_store_id');
    }
    public function toErpStore()
    {
        return $this -> belongsTo(ErpStore::class, 'to_store_id');
    }
    public function fromErpSubStore()
    {
        return $this -> belongsTo(ErpSubStore::class, 'from_sub_store_id');
    }
    public function toErpSubStore()
    {
        return $this -> belongsTo(ErpSubStore::class, 'to_sub_store_id');
    }
    public function jo_item()
    {
        return $this -> belongsTo(JoItem::class, 'jo_item_id');
    }
    public function jo_product()
    {
        return $this -> belongsTo(JoProduct::class, 'jo_product_id');
    }
    public function uniqueCodes()
    {
        return $this->morphMany(ErpItemUniqueCode::class, 'morphable');
    }
    public function stockReservation()
    {
        return $this->hasMany(StockLedgerReservation::class, 'issue_detail_id','id');
    }
}
