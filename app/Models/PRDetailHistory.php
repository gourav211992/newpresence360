<?php
namespace App\Models;

use App\Helpers\ConstantHelper;
use Illuminate\Database\Eloquent\Model;

class PRDetailHistory extends Model
{
    protected $table = 'erp_purchase_return_details_history';
    protected $fillable = [
        'header_id',
        'header_history_id',
        'detail_id',
        'mrn_detail_id',
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

    protected $appends = [
        'cgst_value',
        'sgst_value',
        'igst_value'
    ];

    public function header()
    {
        return $this->belongsTo(PRHeader::class, 'header_id');
    }

    public function headerHistory()
    {
        return $this->belongsTo(PRHeaderHistory::class, 'header_history_id');
    }
    public function detail()
    {
        return $this->belongsTo(PRDetail::class, 'detail_id');
    }

    public function attributes()
    {
        return $this->hasMany(PRItemAttributeHistory::class, 'detail_history_id');
    }

    public function pbTed()
    {
        return $this->hasMany(PRTedHistory::class, 'detail_history_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
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

    public function itemDiscount()
    {
        return $this->hasMany(PRTedHistory::class, 'detail_history_id')->where('ted_level', 'D')->where('ted_type','Discount');
    }

    /*Header Level Discount*/
    public function headerDiscount()
    {
        return $this->hasMany(PRTedHistory::class, 'detail_history_id')->where('ted_level', 'H')->where('ted_type','Discount');
    }

    public function taxes()
    {
        return $this->hasMany(PRTedHistory::class, 'detail_history_id')->where('ted_type','Tax');
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
            $existingAttribute = PRItemAttributeHistory::where('detail_history_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> first();
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
            foreach ($attribute_ids as $attributeValue) {
                    $attributeValueData = ErpAttribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                    if (isset($attributeValueData))
                    {
                        $isSelected = PRItemAttributeHistory::where('detail_history_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> where('attr_value', $attributeValueData -> id) -> first();
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

}
