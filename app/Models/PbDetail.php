<?php
namespace App\Models;

use App\Models\PO\PoHeader;
use App\Models\PO\PoDetail;
use App\Helpers\ConstantHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PbDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_pb_details';
    protected $fillable = [
        'header_id',
        'mrn_detail_id',
        'so_id',
        'item_id',
        'item_code',
        'item_name',
        'hsn_id',
        'hsn_code',
        'cost_center_id',
        'uom_id',
        'uom_code',
        'order_qty',
        'accepted_qty',
        'rejected_qty',
        'inventory_uom',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'po_rate',
        'rate',
        'variance',
        'basic_value',
        'discount_percentage',
        'discount_amount',
        'header_discount_amount',
        'net_value',
        'sgst_percentage',
        'cgst_percentage',
        'igst_percentage',
        'tax_value',
        'taxable_amount',
        'sub_total',
        'item_exp_amount',
        'header_exp_amount',
        'company_currency',
        'exchange_rate_to_company_currency',
        'group_currency',
        'exchange_rate_to_group_currency',
        'selected_item',
        'remark'
    ];

    public $referencingRelationships = [
        'item' => 'item_id',
        'uom' => 'uom_id',
        'hsn' => 'hsn_id',
        'inventoryUom' => 'inventory_uom_id'
    ];

    protected $appends = [
        'cgst_value',
        'sgst_value',
        'igst_value'
    ];

    public function header()
    {
        return $this->belongsTo(PbHeader::class, 'header_id');
    }

    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'so_id');
    }

    public function attributes()
    {
        return $this->hasMany(PbItemAttribute::class, 'detail_id');
    }

    public function pbTed()
    {
        return $this->hasMany(PbTed::class, 'detail_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function hsn()
    {
        return $this->belongsTo(Hsn::class, 'hsn_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class,'cost_center_id');
    }

    public function getAssessmentAmountTotalAttribute()
    {
        return ($this->accepted_qty * $this->rate) - ($this->discount_amount - $this->header_discount_amount);
    }

    public function getAssessmentAmountItemAttribute()
    {
        return ($this->accepted_qty * $this->rate) - ($this->discount_amount);
    }

    // After item discount
    public function getAssessmentAmountHeaderAttribute()
    {
        return ($this->accepted_qty * $this->rate) - ($this->discount_amount);
    }

    public function getTotalItemValueAttribute()
    {
        return ($this->accepted_qty * $this->rate);
    }

    public function getTotalDiscValueAttribute()
    {
        return ($this->discount_amount + $this->header_discount_amount);
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function inventoryUom()
    {
        return $this->belongsTo(Unit::class, 'inventory_uom_id');
    }

    public function itemDiscount()
    {
        return $this->hasMany(PbTed::class, 'detail_id')->where('ted_level', 'D')->where('ted_type','Discount');
    }

    /*Header Level Discount*/
    public function headerDiscount()
    {
        return $this->hasMany(PbTed::class, 'detail_id')->where('ted_level', 'H')->where('ted_type','Discount');
    }

    public function taxes()
    {
        return $this->hasMany(PbTed::class, 'detail_id')->where('ted_type','Tax');
    }

    public function getCgstValueAttribute()
    {
        $tedRecords = PbTed::where('detail_id', $this->id)
            ->where('header_id', $this->header_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'CGST')
            ->sum('ted_amount');

        $tedRecord = PbTed::with(['taxDetail'])
            ->where('detail_id', $this->id)
            ->where('header_id', $this->header_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'CGST')
            ->first();


        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function getSgstValueAttribute()
    {
        $tedRecords = PbTed::where('detail_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'SGST')
            ->sum('ted_amount');

            $tedRecord = PbTed::with(['taxDetail'])
            ->where('detail_id', $this->id)
            ->where('header_id', $this->header_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'SGST')
            ->first();


        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function getIgstValueAttribute()
    {
        $tedRecords = PbTed::where('detail_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'IGST')
            ->sum('ted_amount');

            $tedRecord = PbTed::with(['taxDetail'])
            ->where('detail_id', $this->id)
            ->where('header_id', $this->header_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'IGST')
            ->first();


        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function item_attributes_array()
    {
        $itemId = $this->getAttribute('item_id');
        if (!$itemId) {
            return collect([]);
        }
        $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
        $processedData = [];
        $mappingAttributes = PbItemAttribute::where('detail_id', $this->getAttribute('id'))
        ->select(['item_attribute_id as attribute_id', 'attr_value as attribute_value_id'])
        ->get()
        ->toArray();
        foreach ($itemAttributes as $attribute) {
            $attributeIds = is_array($attribute->attribute_id) ? $attribute->attribute_id : [$attribute->attribute_id];
            $attribute->group_name = $attribute->group?->name;
            $valuesData = [];
            foreach ($attributeIds as $attributeValueId) {
                $attributeValueData = ErpAttribute::where('id', $attributeValueId)
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
}
