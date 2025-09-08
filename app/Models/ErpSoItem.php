<?php

namespace App\Models;

use App\Helpers\InventoryHelper;
use App\Helpers\ItemHelper;
use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ErpSoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_order_id',
        'bom_id',
        'item_id',
        'item_code',
        'item_name',
        'hsn_id',
        'hsn_code',
        'uom_id',
        'uom_code',
        'order_qty',
        'pwo_qty',
        'invoice_qty',
        'dnote_qty',
        'picked_qty',
        'planned_qty',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'rate',
        'delivery_date',
        'item_discount_amount',
        'header_discount_amount',
        'header_discounts',
        'item_expense_amount',
        'header_expense_amount',
        'tax_amount',
        'total_item_amount',
        'company_currency_id',
        'company_currency_exchange_rate',
        'group_currency_id',
        'group_currency_exchange_rate',
        'remarks',
    ];
    protected $appends = [
        'balance_qty',
        'balance_bundle_qty',
        'quotation_balance_qty',
        'work_order_balance_qty',
        'picked_balance_qty',
        'planned_balance_qty',
        'cgst_value',
        'sgst_value',
        'igst_value'
    ];

    public $referencingRelationships = [
        'item' => 'item_id',
        'bom' => 'bom_id',
        'attributes' => 'so_item_id',
        'uom' => 'uom_id',
        'hsn' => 'hsn_id',
        'inventoryUom' => 'inventory_uom_id'
    ];

    protected $casts = [
        'header_discounts' => 'array'
    ];

    public function bom()
    {
        return $this -> belongsTo(Bom::class, 'bom_id');
    }

    public function item()
    {
        return $this -> belongsTo(Item::class, 'item_id', 'id');
    }

    public function teds()
    {
        return $this->hasMany(ErpSaleOrderTed::class,'so_item_id');
    }


    public function item_attributes()
    {
        return $this -> hasMany(ErpSoItemAttribute::class, 'so_item_id');
    }

    public function itemAttributes()
    {
        return $this -> belongsTo(ErpSoItemAttribute::class, 'so_item_id');
    }

    public function attributes()
    {
        return $this -> hasMany(ErpSoItemAttribute::class, 'so_item_id');
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function inventoryUom()
    {
        return $this->belongsTo(Unit::class, 'inventory_uom_id');
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
            $existingAttribute = ErpSoItemAttribute::where('so_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> first();
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
            $attribute -> short_name = $attribute -> group ?-> short_name;
            foreach ($attribute_ids as $attributeValue) {
                    $attributeValueData = Attribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                    if (isset($attributeValueData))
                    {
                        $isSelected = ErpSoItemAttribute::where('so_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> where('attribute_value', $attributeValueData -> value) -> first();
                        $attributeValueData -> selected = $isSelected ? true : false;
                        array_push($attributesArray, $attributeValueData);
                    }
                
            }
           $attribute -> values_data = $attributesArray;
           $attribute = $attribute -> only(['id','group_name', 'short_name' ,'values_data', 'attribute_group_id']);
           array_push($processedData, ['id' => $attribute['id'], 'group_name' => $attribute['group_name'], 'values_data' => $attributesArray, 'attribute_group_id' => $attribute['attribute_group_id'],'short_name' => $attribute['short_name']]);
        }
        $processedData = collect($processedData);
        return $processedData;
    }

    # only get selected attribute for view purpose
    public function selected_item_attributes_array()
    {
        return $this->item_attributes_array()
            ->map(function ($group) {
                $selected = collect($group['values_data'])
                    ->first(function ($attr) {
                        return $attr->selected === true;
                    });
                if ($selected) {
                    return [
                        'attribute_group_id' => $group['attribute_group_id'],
                        'attribute_group_name' => $group['group_name'],
                        'attribute_id' => $selected->id,
                        'attribute_value' => $selected->value,
                    ];
                }
                return null;
            })
            ->filter()
            ->values();
    }
    
    public function discount_ted()
    {
        return $this -> hasMany(ErpSaleOrderTed::class, 'so_item_id', 'id') -> where('ted_level', 'D') -> where('ted_type', 'Discount');
    }
    public function tax_ted()
    {
        return $this -> hasMany(ErpSaleOrderTed::class, 'so_item_id', 'id') -> where('ted_level', 'D') -> where('ted_type', 'Tax');
    }
    public function item_deliveries()
    {
        return $this -> hasMany(ErpSoItemDelivery::class, 'so_item_id', 'id');
    }
    public function header()
    {
        return $this -> belongsTo(ErpSaleOrder::class, 'sale_order_id');
    }
    public function sale_order()
    {
        return $this -> belongsTo(ErpSaleOrder::class, 'sale_order_id');
    }
    public function quotation()
    {
        return $this -> belongsTo(ErpSaleOrder::class, 'order_quotation_id');
    }
    public function getBalanceQtyAttribute()
    {
        // $totalQty = $this -> getAttribute('pslip_qty');
        // $usedQty = $this -> getAttribute('dnote_qty');
        // $balanceQty = min([$totalQty, ($totalQty - $usedQty)]);
        // return $balanceQty;
        $totalQty = $this -> getAttribute('order_qty');
        $usedQty = $this -> getAttribute('dnote_qty');
        $shortQty = $this -> getAttribute('short_close_qty');
        $pickedQty = $this -> getAttribute('picked_qty');
        $plannedQty = $this -> getAttribute('planned_qty');
        $plistQty = $this -> getAttribute('plist_qty');
        $balanceQty = min([$totalQty, ($totalQty - ( $usedQty + $shortQty + $pickedQty + $plistQty))]);
        return $balanceQty;
    }
    public function getBalanceBundleQtyAttribute()
    {
        $pslipItemIds = ErpPslipItem::where('so_item_id', $this -> id) -> get() -> pluck('id') -> toArray();
        return ((ErpPslipItemDetail::whereIn('pslip_item_id', $pslipItemIds) -> whereNull('dn_item_id') -> sum('qty')));
    }
    public function getQuotationBalanceQtyAttribute()
    {
        $totalQty = $this -> getAttribute('order_qty');
        $usedQty = $this -> getAttribute('quotation_order_qty');
        $balanceQty = min([$totalQty, ($totalQty - $usedQty)]);
        return $balanceQty;
    }
    public function getWorkOrderBalanceQtyAttribute()
    {
        $totalQty = $this -> getAttribute('inventory_uom_qty');
        $usedQty = $this -> getAttribute('pwo_qty');
        $balanceQty = min([$totalQty, ($totalQty - $usedQty)]);
        return $balanceQty;
    }
    public function getPickedBalanceQtyAttribute()
    {
        $balanceQty = $this->order_qty - ($this->dnote_qty+$this->short_close_qty+$this->picked_qty);
        return $balanceQty;
    }
    public function getPlannedBalanceQtyAttribute()
    {
        $balanceQty = $this->order_qty - ($this->dnote_qty+$this->short_close_qty+$this->planned_qty);
        return $balanceQty;
    }

    public function getCgstValueAttribute()
    {
        $tedRecords = ErpSaleOrderTed::where('so_item_id', $this->id)
            ->where('sale_order_id', $this->sale_order_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'CGST')
            ->sum('ted_amount');

        $tedRecord = ErpSaleOrderTed::with(['taxDetail'])
            ->where('so_item_id', $this->id)
            ->where('sale_order_id', $this->sale_order_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'CGST')
            ->first();
            
        
        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function getSgstValueAttribute()
    {
        $tedRecords = ErpSaleOrderTed::where('so_item_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'SGST')
            ->sum('ted_amount');
        
            $tedRecord = ErpSaleOrderTed::with(['taxDetail'])
            ->where('so_item_id', $this->id)
            ->where('sale_order_id', $this->sale_order_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'SGST')
            ->first();
            
        
        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function getIgstValueAttribute()
    {
        $tedRecords = ErpSaleOrderTed::where('so_item_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'IGST')
            ->sum('ted_amount');
        
            $tedRecord = ErpSaleOrderTed::with(['taxDetail'])
            ->where('so_item_id', $this->id)
            ->where('sale_order_id', $this->sale_order_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'IGST')
            ->first();
            
        
        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function getStockBalanceQty($storeId = null, $subStoreId = null)
    {
        $itemId = $this -> getAttribute('item_id');
        $selectedAttributeIds = [];
        $itemAttributes = $this -> item_attributes_array();
        foreach ($itemAttributes as $itemAttr) {
            foreach ($itemAttr['values_data'] as $valueData) {
                if ($valueData['selected']) {
                    array_push($selectedAttributeIds, $valueData['id']);
                }
            }
        }
        $stocks = InventoryHelper::totalInventoryAndStock($itemId, $selectedAttributeIds,null,$storeId, $subStoreId);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        $stockBalanceQty = ItemHelper::convertToAltUom($this -> getAttribute(('item_id')), $this -> getAttribute('uom_id'), (float)$stockBalanceQty);
        return $stockBalanceQty;
    }

    public function hsn()
    {
        return $this -> belongsTo(Hsn::class);
    }

    /*Bom header item*/
    public function bomHeader()
    {
        return $this->hasOne(Bom::class, 'item_id', 'item_id');
    }

    public function bomHeaders()
    {
        return $this->hasMany(Bom::class, 'item_id', 'item_id');
    }

    public function bomAttributes()
    {
        return $this->hasMany(BomAttribute::class, 'item_id', 'item_id')->where('type','H');
    }

    public function soItemMapping()
    {
        return $this->hasMany(PiSoMapping::class,'so_item_id');
    }

    public function attributes_array()
    {
        return DB::table("erp_so_item_attributes") -> select(DB::raw("GROUP_CONCAT(CONCAT(erp_so_item_attributes.item_attribute_id, ':', erp_so_item_attributes.attribute_value) ORDER BY erp_so_item_attributes.item_attribute_id SEPARATOR ', ') as attributes")) -> where('so_item_id', $this -> getAttribute('id')) -> get();
    }
    public function custom_bom_details()
    {
        return $this -> hasMany(ErpSoItemBom::class,'so_item_id');
    }
    public function getShortBalQtyAttribute()
    {
        $maxQty = $this->dnote_qty;
        $balance = max(($this->order_qty - $maxQty - $this->short_close_qty),0);
        return $balance; 
    }
    public function getPendingQtyAttribute()
    {
        $orderQty = $this -> order_qty;
        $shortCloseQty = $this -> short_close_qty;
        $invoiceQty = $this -> invoice_qty;
        $returnQty = $this -> srn_qty;
        return ((($orderQty - $shortCloseQty) - $invoiceQty) + $returnQty);
    }

    public function getAvailableStocks($storeId, $subStoreId)
    {
        $itemId = $this -> getAttribute('item_id');
        $selectedAttributeIds = [];
        $itemAttributes = $this -> item_attributes_array();
        foreach ($itemAttributes as $itemAttr) {
            foreach ($itemAttr['values_data'] as $valueData) {
                if ($valueData['selected']) {
                    array_push($selectedAttributeIds, $valueData['id']);
                }
            }
        }
        $stocks = InventoryHelper::totalInventoryAndStock($itemId, $selectedAttributeIds, $this -> getAttribute('uom_id'), $storeId, $subStoreId);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        return $stockBalanceQty;
    }

    public function getDnotePullBalanceQtyAttribute()
    {
        $orderQty = $this -> order_qty;
        $shortCloseQty = $this -> short_close_qty;
        $pickedQty = $this -> picked_qty;
        $plannedQty = $this -> planned_qty;
        $plistQty = $this -> plist_qty;
        $dnoteQty = $this -> dnote_qty;
        $srnQty = $this -> srn_qty;

        $maxQty = max([$pickedQty, $plistQty, $dnoteQty]);
        return ($orderQty - $shortCloseQty - $maxQty + $srnQty);
    }
}
