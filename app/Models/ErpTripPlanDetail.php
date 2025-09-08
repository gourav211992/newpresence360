<?php

namespace App\Models;

use App\Models\WHM\ErpItemUniqueCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\InventoryHelper;
use App\Helpers\ItemHelper;
use DB;

class ErpTripPlanDetail extends Model
{
protected $fillable = [
        'trip_header_id',
        'order_id',
        'order_item_id',
        'order_item_delivery_id',
        'item_id',
        'item_code',
        'item_name',
        'attributes',
        'hsn_id',
        'hsn_code',
        'uom_id',
        'uom_code',
        'order_qty',
        'planned_qty',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'delivery_date',
        'rate',
        'total_amount',
        'remarks',
    ];
    public function bom()
    {
        return $this->belongsTo(Bom::class, 'bom_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function inventoryUom()
    {
        return $this->belongsTo(Unit::class, 'inventory_uom_id');
    }
    public function soItem()
    {
        return $this->belongsTo(ErpSoItem::class,'order_item_id');
    }
    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'order_id');
    }
    public function sale_order()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'order_id');
    }
    public function header(){
        return $this->belongsTo(ErpTripPlanHeader::class, 'trip_header_id');
    }
    public function uom(){
        return $this->belongsTo(Unit::class, 'uom_id');
    }
    public function getAvailableStocks($storeId)
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
        $stocks = InventoryHelper::totalInventoryAndStock($itemId, $selectedAttributeIds, $this -> getAttribute('uom_id'), $storeId);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        return $stockBalanceQty;
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
}
