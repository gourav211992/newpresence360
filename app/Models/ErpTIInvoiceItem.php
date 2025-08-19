<?php

namespace App\Models;

use App\Helpers\InventoryHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\WHM\ErpItemUniqueCode;

class ErpTIInvoiceItem extends Model
{
    use HasFactory, SoftDeletes;
protected $table = 'erp_ti_invoice_items';
 protected $guarded = ['id'];
    protected $appends = [
        'return_balance_qty',
        'balance_qty',
        'cgst_value',
        'sgst_value',
        'igst_value',
        'cess_value'
    ];

    public $referencingRelationships = [
        'item' => 'item_id',
        'attributes' => 'so_item_id',
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
        return $this -> hasMany(ErpInvoiceItemAttribute::class, 'invoice_item_id');
    }

    public function attributes()
    {
        return $this -> hasMany(ErpInvoiceItemAttribute::class, 'invoice_item_id');
    }
     public function bundles(){
        return $this->hasMany(ErpPslipItemDetail::class,'dn_item_id');
    }

    public function packets(){
        return $this->hasMany(ErpInvoiceItemPacket::class,'invoice_item_id');
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
            $existingAttribute = ErpInvoiceItemAttribute::where('invoice_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> first();
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

            foreach (isset($attribute_ids) ? $attribute_ids : [] as $attributeValue) {
                    $attributeValueData = Attribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                    if (isset($attributeValueData))
                    {
                        $isSelected = ErpInvoiceItemAttribute::where('invoice_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> where('attribute_value', $attributeValueData -> value) -> first();
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

    public function discount_ted()
    {
        return $this -> hasMany(ErpSaleInvoiceTed::class, 'invoice_item_id', 'id') -> where('ted_level', 'D') -> where('ted_type', 'Discount');
    }
    public function tax_ted()
    {
        return $this -> hasMany(ErpSaleInvoiceTed::class, 'invoice_item_id', 'id') -> where('ted_level', 'D') -> where('ted_type', 'Tax');
    }
    public function item_locations()
    {
        return $this -> hasMany(ErpInvoiceItemLocation::class, 'invoice_item_id', 'id');
    }
    public function ti_order()
    {
        return $this -> belongsTo(ErpSaleOrder::class, 'ti_order_id');
    }
    public function invoice()
    {
        return $this -> belongsTo(ErpSaleInvoice::class, 'invoice_id');
    }
    public function dn_cum_invoice()
    {
        return $this -> belongsTo(ErpSaleInvoice::class, 'dn_id');
    }
    public function header()
    {
        return $this -> belongsTo(ErpSaleInvoice::class, 'ti_invoice_id');
    }

    public function getCgstValueAttribute()
    {
        $tedRecords = ErpTransportInvoiceTed::where('invoice_item_id', $this->id)
            ->where('transport_invoice_id', $this->ti_invoice_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'CGST')
            ->sum('ted_amount');

        $tedRecord = ErpTransportInvoiceTed::with(['taxDetail'])
            ->where('invoice_item_id', $this->id)
            ->where('transport_invoice_id', $this->ti_invoice_id)
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
        $tedRecords = ErpTransportInvoiceTed::where('invoice_item_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'SGST')
            ->sum('ted_amount');
        
            $tedRecord = ErpTransportInvoiceTed::with(['taxDetail'])
            ->where('invoice_item_id', $this->id)
            ->where('transport_invoice_id', $this->ti_invoice_id)
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
        $tedRecords = ErpTransportInvoiceTed::where('invoice_item_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'IGST')
            ->sum('ted_amount');
        
            $tedRecord = ErpTransportInvoiceTed::with(['taxDetail'])
            ->where('invoice_item_id', $this->id)
            ->where('transport_invoice_id', $this->ti_invoice_id)
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
        $tedRecords = ErpTransportInvoiceTed::where('invoice_item_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_name', '=', 'CESS')
            ->sum('ted_amount');
        
            $tedRecord = ErpTransportInvoiceTed::with(['taxDetail'])
            ->where('invoice_item_id', $this->id)
            ->where('transport_invoice_id', $this->ti_invoice_id)
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
        $totalQty = (float)$this -> getAttribute('order_qty');
        $usedQty = (float) $this -> getAttribute('invoice_qty');
        $balanceQty = min([$totalQty, ($totalQty - $usedQty)]);
        return $balanceQty;
    }
    public function getReturnBalanceQtyAttribute()
    {
        $type=$this->header()->first()?->document_type;
        if($type=="dnote"){
            $totalQty = (float)$this -> getAttribute('dnote_qty');
            $usedQty = (float) $this -> getAttribute('srn_qty');
            $balanceQty = min([$totalQty, ($totalQty - $usedQty)]);
        }
        else{
            $totalQty = (float)$this -> getAttribute('invoice_qty');
            $usedQty = (float) $this -> getAttribute('srn_qty');
            $balanceQty = min([$totalQty, ($totalQty - $usedQty)]);
        }
        return $balanceQty;
    }

    public function getStockBalanceQty()
    {
        // $itemId = $this -> getAttribute('item_id');
        // $selectedAttributeIds = [];
        // $itemAttributes = $this -> item_attributes_array();
        // foreach ($itemAttributes as $itemAttr) {
        //     foreach ($itemAttr['values_data'] as $valueData) {
        //         if ($valueData['selected']) {
        //             array_push($selectedAttributeIds, $valueData['id']);
        //         }
        //     }
        // }
        // $stocks = InventoryHelper::totalInventoryAndStock($itemId, $selectedAttributeIds,null,null,null,null);
        // $stockBalanceQty = 0;
        // if (isset($stocks) && isset($stocks['confirmedStocks'])) {
        //     $stockBalanceQty = $stocks['confirmedStocks'];
        // }
        // return $stockBalanceQty;
        return $this -> getAttribute('order_qty');
    }
    
    public function hsn()
    {
        return $this -> belongsTo(Hsn::class);
    }

     public function lorry()
    {
        return $this -> belongsTo(ErpLorryReceipt::class, 'lr_id');
    }

    public function lease()
    {
        return $this -> belongsTo(LandLease::class, 'land_lease_id');
    }
    public function lease_schedule()
    {
        return $this -> belongsTo(LandLeaseScheduler::class, 'lease_schedule_id');
    }

    public function ti_order_item()
    {
        $saleOrderItem = ErpSoItem::find($this -> getAttribute('so_item_id'));
        return $saleOrderItem;
    }
    
    // public function mapped_so_item_ids()
    // {
    //     return ErpSoDnMapping::where('delivery_note_id', $this -> getAttribute('ti_invoice_id')) -> where('dn_item_id', $this -> getAttribute('id')) -> get() -> pluck('so_item_id') -> toArray();
    // }
    // public function mapped_so_items()
    // {
    //     return $this -> hasMany(ErpSoDnMapping::class, 'delivery_note_id');
    // }

    public function teds()
    {
        return $this->hasMany(ErpSaleInvoiceTed::class,'invoice_item_id');
    }
    public function taxes()
    {
        return $this->hasMany(ErpSaleInvoiceTed::class,'invoice_item_id')->where('ted_level', 'D')->where('ted_type', 'Tax');
    }
    
    public function plItem()
    {
        return $this->belongsTo(ErpPlItem::class, 'pl_item_id');
    }

    public function uniqueCodes()
    {
        return $this->morphMany(ErpItemUniqueCode::class, 'morphable');
    }
}
