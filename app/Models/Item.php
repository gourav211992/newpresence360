<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;

class Item extends Model
{
    use HasFactory, Deletable,DefaultGroupCompanyOrg;

    protected $table = 'erp_items';

    protected $fillable = [
        'type',
        'unit_id',
        'hsn_id',
        'currency_id',
        'category_id',
        'subcategory_id',
        'item_code',
        'item_name',
        'item_initial',
        'item_remark',
        'uom_id',
        'storage_uom_id',
        'storage_uom_conversion',
        'storage_uom_count',
        'storage_weight',
        'storage_volume',
        'is_inspection',
        'cost_price',
        'sell_price',
        'book_id',
        'book_code',
        'item_code_type',
        'min_stocking_level',
        'max_stocking_level',
        'reorder_level',
        'minimum_order_qty',
        'lead_days',
        'safety_days',
        'shelf_life_days',
        'po_positive_tolerance',  
        'po_negative_tolerance', 
        'so_positive_tolerance',  
        'so_negative_tolerance',  
        'group_id',     
        'company_id',       
        'organization_id',
        'service_type',
        'storage_type',
        'status',
        'created_by'
    ];

    protected $dates = ['created_at', 'updated_at'];

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }
    
    public function alternateUOMs()
    {
        return $this->hasMany(AlternateUOM::class);
    }


    public function hsn()
    {
        return $this->belongsTo(Hsn::class, 'hsn_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    // public function subTypes()
    // {
    //     return $this->belongsToMany(SubType::class, 'erp_item_subtypes');
    // }

    
    public function subTypes()
    {
        return $this->hasMany(ItemSubType::class);
        // ->using(ItemSubType::class); 
    }

    public function inventoryDetails()
    {
        return $this->hasOne(InventoryDetail::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, );
    }

    public function approvedCustomers()
    {
        return $this->hasMany(CustomerItem::class);
    }

    public function approvedVendors()
    {
        return $this->hasMany(VendorItem::class);
    }

    public function approvedVendor()
    {
        return $this->hasOne(VendorItem::class)->latest();
    }

    public function attributes()
    {
        return $this->hasMany(ErpAttribute::class);
    }

    public function itemAttributes()
    {
        return $this->hasMany(ItemAttribute::class);
    }
    public function alternateItems()
    {
        return $this->hasMany(AlternateItem::class);
    }
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function specifications()
    {
        return $this->hasMany(ItemSpecification::class);
    }
    public function notes()
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    public function createdByEmployee()
    {
        return $this->belongsTo(Employee::class,'created_by','id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }

    public function auth_user()
    {
        return $this->belongsTo(AuthUser::class, 'created_by', 'id');
    }

    public function item_attributes_array(array $arr = [])
    {
        $mappingAttributes = $arr ?? [];
        $itemId = $this->getAttribute('id');
        if (!$itemId) {
            return collect([]);
        }
        $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
        $processedData = [];
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

    public function scopeSearchByKeywords($query, $term)
    { 
        $keywords = preg_split('/\s+/', trim($term));
        return $query->where(function ($q) use ($keywords) {
            foreach ($keywords as $word) {
                $q->where(function ($subQ) use ($word) {
                    $subQ->where('item_name', 'LIKE', "%{$word}%")
                        ->orWhere('item_code', 'LIKE', "%{$word}%");
                });
            }
        });
    }
}
