<?php
namespace App\Models;

use App\Models\PO\PoHeader;
use App\Models\PO\PoDetail;
use App\Helpers\ConstantHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PRDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_purchase_return_details';
    protected $fillable = [
        'header_id',
        'mrn_detail_id',
        'so_id',
        'item_id',
        'item_code',
        'item_name',
        'hsn_id',
        'hsn_code',
        'uom_id',
        'uom_code',
        'accepted_qty',
        'inventory_uom',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'rate',
        'basic_value',
        'discount_percentage',
        'discount_amount',
        'header_discount_amount',
        'net_value',
        'tax_value',
        'taxable_amount',
        'item_exp_amount',
        'header_exp_amount',
        'total_item_amount',
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
        return $this->belongsTo(PRHeader::class, 'header_id');
    }

    public function mrnDetail()
    {
        return $this->belongsTo(MrnDetail::class, 'mrn_detail_id');
    }

    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'so_id');
    }

    public function attributes()
    {
        return $this->hasMany(PRItemAttribute::class, 'detail_id');
    }

    public function pbTed()
    {
        return $this->hasMany(PRTed::class, 'detail_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function hsn()
    {
        return $this->belongsTo(Hsn::class, 'hsn_id');
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
        return $this->hasMany(PRTed::class, 'detail_id')->where('ted_level', 'D')->where('ted_type','Discount');
    }

    /*Header Level Discount*/
    public function headerDiscount()
    {
        return $this->hasMany(PRTed::class, 'detail_id')->where('ted_level', 'H')->where('ted_type','Discount');
    }

    public function taxes()
    {
        return $this->hasMany(PRTed::class, 'detail_id')->where('ted_type','Tax');
    }

    public function getCgstValueAttribute()
    {
        $tedRecords = PRTed::where('detail_id', $this->id)
            ->where('header_id', $this->header_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'CGST')
            ->sum('ted_amount');

        $tedRecord = PRTed::with(['taxDetail'])
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
        $tedRecords = PRTed::where('detail_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'SGST')
            ->sum('ted_amount');

            $tedRecord = PRTed::with(['taxDetail'])
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
        $tedRecords = PRTed::where('detail_id', $this->id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'IGST')
            ->sum('ted_amount');

            $tedRecord = PRTed::with(['taxDetail'])
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

    public function ted_tax()
    {
        return $this->hasOne(PRTed::class,'detail_id')->where('ted_type','Tax')->latest();
    }

    // public function item_attributes_array()
    // {
    //     $itemId = $this -> getAttribute('item_id');
    //     if (isset($itemId)) {
    //         $itemAttributes = ItemAttribute::where('item_id', $this -> item_id) -> get();
    //     } else {
    //         $itemAttributes = [];
    //     }
    //     $processedData = [];
    //     foreach ($itemAttributes as $attribute) {
    //         $existingAttribute = PRItemAttribute::where('detail_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> first();
    //         if (!isset($existingAttribute)) {
    //             continue;
    //         }
    //         $attributesArray = array();
    //         // $attribute_ids = [];
    //         $attribute_ids = $attribute -> attribute_id;
    //         // if ($attribute -> all_checked) {
    //         //     $attribute_ids = ErpAttribute::where('attribute_group_id', $attribute -> attribute_group_id) -> get() -> pluck('id') -> toArray();
    //         // } else {
    //             // $attribute_ids = $attribute -> attribute_id ? json_decode($attribute -> attribute_id) : [];
    //         // }
    //         $attribute -> group_name = $attribute -> group ?-> name;
    //         $attribute -> short_name = $attribute -> group ?-> short_name;
    //         foreach ($attribute_ids as $attributeValue) {
    //                 $attributeValueData = ErpAttribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
    //                 if (isset($attributeValueData))
    //                 {
    //                     $isSelected = PRItemAttribute::where('detail_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> where('attr_value', $attributeValueData -> id) -> first();
    //                     $attributeValueData -> selected = $isSelected ? true : false;
    //                     array_push($attributesArray, $attributeValueData);
    //                 }
    //         }
    //        $attribute -> values_data = $attributesArray;
    //        $attribute = $attribute -> only(['id','group_name', 'short_name' ,'values_data', 'attribute_group_id']);
    //        array_push($processedData, ['id' => $attribute['id'], 'group_name' => $attribute['group_name'], 'values_data' => $attributesArray, 'attribute_group_id' => $attribute['attribute_group_id'],'short_name' => $attribute['short_name']]);
    //     }
    //     $processedData = collect($processedData);
    //     return $processedData;
    // }

    public function item_attributes_array()
    {
        $itemId = $this->getAttribute('item_id');
        if (!$itemId) {
            return collect([]);
        }
        $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
        $processedData = [];
        $mappingAttributes = PRItemAttribute::where('detail_id', $this->getAttribute('id'))
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
