<?php

namespace App\Models;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Traits\DateFormatTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpSaleReturnItem extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait;

    protected $fillable = [
        'sale_return_id',
        'si_item_id',
        'item_id',
        'item_code',
        'item_name',
        'hsn_id',
        'hsn_code',
        'uom_id',
        'uom_code',
        'store_id',
        'order_qty',
        'rate',
        'return_amount',
        'item_discount_amount',
        'header_discount_amount',
        'tax_amount',
        'item_expense_amount',
        'header_expense_amount',
        'total_item_amount',
        'remarks'
    ];

    protected $appends = [
        'balance_qty',
        'cgst_value',
        'sgst_value',
        'igst_value',
        'cess_value'
    ];

    public $referencingRelationships = [
        'item' => 'item_id',
        'attributes' => 'si_item_id',
        'uom' => 'uom_id',
        'hsn' => 'hsn_id',
        'inventoryUom' => 'inventory_uom_id'
    ];

    protected $hidden = ['deleted_at'];

    public function item()
    {
        return $this -> belongsTo(Item::class, 'item_id', 'id');
    }

    public function teds()
    {
        return $this->hasMany(ErpSaleReturnTed::class,'sale_return_item_id');
    }
    public function item_attributes()
    {
        return $this -> hasMany(ErpSaleReturnItemAttribute::class, 'sale_return_item_id');
    }

    public function attributes()
    {
        return $this -> hasMany(ErpSaleReturnItemAttribute::class, 'sale_return_item_id', 'id');
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
            $existingAttribute = ErpSaleReturnItemAttribute::where('sale_return_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> first();
            if (!isset($existingAttribute)) {
                continue;
            }
            $attributesArray = array();
            $attribute_ids = [];
            if ($attribute -> all_checked) {
                $attribute_ids = ErpAttribute::where('attribute_group_id', $attribute -> attribute_group_id) -> get() -> pluck('id') -> toArray();
            } else {
                $attribute_ids = $attribute -> attribute_id ? json_decode($attribute -> attribute_id) : [];
            }
            $attribute -> group_name = $attribute -> group ?-> name;
            $attribute -> short_name = $attribute -> group ?-> short_name;

            foreach (isset($attribute_ids) ? $attribute_ids : [] as $attributeValue) {
                $attributeValueData = ErpAttribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                if (isset($attributeValueData))
                {
                    $isSelected = ErpSaleReturnItemAttribute::where('sale_return_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> where('attribute_value', $attributeValueData -> value) -> first();
                    $attributeValueData -> selected = $isSelected ? true : false;
                    array_push($attributesArray, $attributeValueData);
                }
            }
            $attribute -> values_data = $attributesArray;
            $attribute = $attribute -> only(['id','group_name', 'short_name', 'values_data', 'attribute_group_id']);
            array_push($processedData, ['id' => $attribute['id'], 'group_name' => $attribute['group_name'], 'short_name' => $attribute['short_name'], 'values_data' => $attributesArray, 'attribute_group_id' => $attribute['attribute_group_id']]);
         }
         $processedData = collect($processedData);
;         return $processedData;
    }

    public function discount_ted()
    {
        return $this -> hasMany(ErpSaleReturnTed::class, 'sale_return_item_id', 'id') -> where('ted_level', 'D') -> where('ted_type', 'Discount');
    }
    public function tax_ted()
    {
        return $this -> hasMany(ErpSaleReturnTed::class, 'sale_return_item_id', 'id') -> where('ted_level', 'D') -> where('ted_type', 'Tax');
    }
    public function item_locations()
    {
        return $this -> hasMany(ErpSaleReturnItemLocation::class, 'sale_return_item_id', 'id')->select('*',"returned_qty as store_qty");
    }
    public function sale_invoice()
    {
        return $this -> belongsTo(ErpSaleInvoice::class, 'sale_invoice_id');
    }
    public function invoice_item()
    {
        return $this -> belongsTo(ErpInvoiceItem::class, 'si_item_id');
    }
    public function dn_cum_invoice()
    {
        return $this -> belongsTo(ErpSaleInvoice::class, 'dn_id');
    }
    public function header()
    {
        return $this -> belongsTo(ErpSaleReturn::class, 'sale_return_id');
    }

    public function getCgstValueAttribute()
    {
        $tedRecords = ErpSaleReturnTed::where('sale_return_item_id', $this->id)
            ->where('sale_return_id', $this->sale_return_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'CGST')
            ->sum('ted_amount');

        $tedRecord = ErpSaleReturnTed::with(['taxDetail'])
            ->where('sale_return_item_id', $this->id)
            ->where('sale_return_id', $this->sale_return_id)
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
        $tedRecords = ErpSaleReturnTed::where('sale_return_item_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'SGST')
            ->sum('ted_amount');
        
            $tedRecord = ErpSaleReturnTed::with(['taxDetail'])
            ->where('sale_return_item_id', $this->id)
            ->where('sale_return_id', $this->sale_return_id)
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
        $tedRecords = ErpSaleReturnTed::where('sale_return_item_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'IGST')
            ->sum('ted_amount');
        
            $tedRecord = ErpSaleReturnTed::with(['taxDetail'])
            ->where('sale_return_item_id', $this->id)
            ->where('sale_return_id', $this->sale_return_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'IGST')
            ->first();
            
        
        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'value' => $tedRecords ?? 0.00
        ];
    }
    public function getCessValueAttribute()
    {
        $tedRecords = ErpSaleReturnTed::where('sale_return_item_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'CESS')
            ->sum('ted_amount');
        
            $tedRecord = ErpSaleReturnTed::with(['taxDetail'])
            ->where('sale_return_item_id', $this->id)
            ->where('sale_return_id', $this->sale_return_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'CESS')
            ->first();
            
        
        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function getBalanceQtyAttribute()
    {
        $totalQty = $this -> getAttribute('invoice_qty');
        $usedQty = $this -> getAttribute('order_qty');
        $balanceQty = min([$totalQty, ($totalQty - $usedQty)]);
        return $balanceQty;
    }

    public function getStockBalanceQty()
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
        $stocks = InventoryHelper::totalInventoryAndStock($itemId, $selectedAttributeIds,null,null,null,null);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        // return $stockBalanceQty;
        return $this -> getAttribute('order_qty');

    }

    public function hsn()
    {
        return $this -> belongsTo(Hsn::class);
    }

    public function specifications()
    {
        return $this->hasMany(ItemSpecification::class,'item_id','item_id');
    }
    
    public function taxes()
    {
        return $this->hasMany(ErpSaleReturnTed::class,'sale_return_item_id')->where('ted_level', 'D')->where('ted_type', 'Tax');
    }
    public function erpSrItemLot()
    {
        return $this->hasMany(ErpSrItemLotDetail::class, 'sr_item_id', 'id');
    }
    public function erpStore()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }
    public function subStore()
    {
        return $this->belongsTo(ErpSubStore::class, 'sub_store_id');
    }
    
}
