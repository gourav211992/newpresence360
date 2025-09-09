<?php

namespace App\Models\JobOrder;

use App\Helpers\InventoryHelper;
use App\Models\ErpItem;
use App\Models\ErpSaleOrder;
use App\Models\Hsn;
use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\Attribute;
use App\Models\ErpMiItem;
use App\Models\PwoSoMapping;
use App\Models\Unit;
use App\Models\VendorAsnItem;
use App\Models\VendorAsn;
use App\Models\GateEntryDetail;
use App\Traits\DateFormatTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoProduct extends Model
{
    use HasFactory, DateFormatTrait;
    protected $table = 'erp_jo_products';
    protected $fillable = [
        'jo_id',
        'pwo_so_mapping_id',
        'so_id',
        'item_id',
        'service_item_id',
        'item_code',
        'hsn_id',
        'hsn_code',
        'uom_id',
        'uom_code',
        'order_qty',
        'ge_qty',
        'grn_qty',
        'short_close_qty',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'rate',
        'item_discount_amount',
        'header_discount_amount',
        'tax_amount',
        'expense_amount',
        'remarks',
        'delivery_date'
    ];

    public $referencingRelationships = [
        'item' => 'item_id',
        'uom' => 'uom_id',
        'hsn' => 'hsn_id',
        'sow' => 'service_item_id',
        'inventoryUom' => 'inventory_uom_id'
    ];

    protected $appends = [
        'cgst_value',
        'sgst_value',
        'igst_value',
        'mi_balance_qty',
        'inter_org_so_bal_qty'
    ];

    public function jo()
    {
        return $this->belongsTo(JobOrder::class, 'jo_id');
    }
    public function header()
    {
        return $this->belongsTo(JobOrder::class, 'jo_id');
    }
    public function pwoSoMapping()
    {
        return $this->belongsTo(PwoSoMapping::class, 'pwo_so_mapping_id');
    }

    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'so_id');
    }
    public function sow()
    {
        return $this->belongsTo(ErpItem::class,'service_item_id');
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function inventoryUom()
    {
        return $this->belongsTo(Unit::class, 'inventory_uom_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function serviceItem()
    {
        return $this->belongsTo(Item::class, 'service_item_id');
    }

    public function hsn()
    {
        return $this->belongsTo(Hsn::class, 'hsn_id');
    }

    public function getAssessmentAmountTotalAttribute()
    {
        return ($this->order_qty * $this->rate) - ($this->item_discount_amount - $this->header_discount_amount);
    }

    public function getAssessmentAmountItemAttribute()
    {
        return ($this->order_qty * $this->rate) - ($this->item_discount_amount);
    }

    public function getAssessmentAmountHeaderAttribute()
    {
        return ($this->order_qty * $this->rate) - ($this->item_discount_amount);
    }

    public function getTotalItemValueAttribute()
    {
        return ($this->order_qty * $this->rate);
    }

    public function getTotalDiscValueAttribute()
    {
        return ($this->item_discount_amount + $this->header_discount_amount);
    }

    public function attributes()
    {
        return $this->hasMany(JoProductAttribute::class,'jo_product_id')->with(['headerAttribute', 'headerAttributeValue']);
    }

    public function teds()
    {
        return $this->hasMany(JobOrderTed::class,'jo_product_id');
    }

    public function ted_tax()
    {
        return $this->hasOne(JobOrderTed::class,'jo_product_id')->where('ted_type','Tax')->latest();
    }
    public function tax_ted()
    {
        return $this->hasOne(JobOrderTed::class,'jo_product_id')->where('ted_type','Tax')->latest();
    }

    public function productDelivery()
    {
        return $this->hasMany(JoProductDelivery::class,'jo_product_id');
    }

    /*Detail level*/
    public function itemDiscount()
    {
        return $this->hasMany(JobOrderTed::class,'jo_product_id')->where('ted_level', 'D')->where('ted_type','Discount');
    }
    public function discount_ted()
    {
        return $this->hasMany(JobOrderTed::class,'jo_product_id')->where('ted_level', 'D')->where('ted_type','Discount');
    }
    /*Header Level Discount*/
    public function headerDiscount()
    {
        return $this->hasMany(JobOrderTed::class)->where('ted_level', 'H')->where('ted_type','Discount');
    }

    public function taxes()
    {
        return $this->hasMany(JobOrderTed::class,'jo_product_id')->where('ted_type','Tax');
    }

    public function getCgstValueAttribute()
    {
        $tedRecords = JobOrderTed::where('jo_product_id', $this->id)
            ->where('jo_id', $this->jo_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'CGST')
            ->sum('ted_amount');

        $tedRecord = JobOrderTed::with(['taxDetail'])
            ->where('jo_product_id', $this->id)
            ->where('jo_id', $this->jo_id)
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
        $tedRecords = JobOrderTed::where('jo_product_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'SGST')
            ->sum('ted_amount');

            $tedRecord = JobOrderTed::with(['taxDetail'])
            ->where('jo_product_id', $this->id)
            ->where('jo_id', $this->jo_id)
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
        $tedRecords = JobOrderTed::where('jo_product_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'IGST')
            ->sum('ted_amount');

            $tedRecord = JobOrderTed::with(['taxDetail'])
            ->where('jo_product_id', $this->id)
            ->where('jo_id', $this->jo_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'IGST')
            ->first();


        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function getShortBalQtyAttribute()
    {
        $maxQty = max($this->invoice_quantity,$this->grn_qty);
        // $maxQty = max((float) $this->invoice_quantity, (float) $this->grn_qty) - (float) $this->short_close_qty;
        $balance = max(($this->order_qty - $maxQty - $this->short_close_qty),0);
        return $balance;
    }

    public function item_attributes_array()
    {
        $itemId = $this->getAttribute('item_id');
        if (!$itemId) {
            return collect([]);
        }
        $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
        $processedData = [];
        $mappingAttributes = JoProductAttribute::where('jo_product_id', $this->getAttribute('id'))
        ->select(['item_attribute_id as attribute_id', 'attribute_value as attribute_value_id'])
        ->get()
        ->toArray();
        foreach ($itemAttributes as $attribute) {
            $attributeIds = is_array($attribute->attribute_id) ? $attribute->attribute_id : [$attribute->attribute_id];
            $attribute->group_name = $attribute->group?->name;
            $valuesData = [];
            foreach ($attributeIds as $attributeValueId) {
                $attributeValueData = Attribute::where('id', $attributeValueId)
                    ->where('status', 'active')
                    ->select('id', 'value')
                    ->first();
                if ($attributeValueData) {
                    $isSelected = collect($mappingAttributes)->contains(function ($itemAttr) use ($attribute, $attributeValueData) {
                        return $itemAttr['attribute_id'] == $attribute->id &&
                            $itemAttr['attribute_value_id'] == $attributeValueData->id;
                    });
                    $attributeValueData->selected = $isSelected;
                    $valuesData[] = $attributeValueData;
                }
            }
            $processedData[] = [
                'id' => $attribute->id,
                'group_name' => $attribute->group_name,
                'values_data' => $valuesData,
                'attribute_group_id' => $attribute->attribute_group_id,
            ];
        }
        return collect($processedData);
    }

    public function getAvlStock($storeId, $subStoreId = null, $stationId = null)
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
        $stocks = InventoryHelper::totalInventoryAndStock($this -> item_id, $selectedAttributeIds,$this -> uom_id,$storeId, $subStoreId, null);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        return min($stockBalanceQty, $this -> order_qty - $this -> short_close_qty);
    }

    public function getMiBalanceQtyAttribute()
    {
        $currentQty = $this -> getAttribute('order_qty') - $this -> getAttribute('short_close_qty');
        $miQty = $this -> getAttribute('mi_qty');
        return $currentQty - $miQty;
    }

    public function miItems()
    {
        return $this->hasMany(ErpMiItem::class, 'jo_product_id');
    }
    public function getInterOrgSoBalQtyAttribute()
    {
        return ($this->order_qty - $this->inter_org_so_qty);
    }

    public function asnItems()
    {
        return $this->hasMany(VendorAsnItem::class, 'jo_prod_id', 'id');
    }

    public function geItems()
    {
        return $this->hasMany(GateEntryDetail::class, 'job_order_item_id', 'id');
    }

}
