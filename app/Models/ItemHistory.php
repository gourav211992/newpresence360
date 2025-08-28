<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Deletable;
use App\Helpers\ConstantHelper;
use Illuminate\Support\Facades\Storage;

class ItemHistory extends Model
{
    use HasFactory, SoftDeletes, Deletable, DefaultGroupCompanyOrg;

    protected $table = 'erp_items_history';

    protected $fillable = [
        'source_id',
        'item_code_type',
        'item_code',
        'item_initial',
        'item_name',
        'item_remark',
        'type',
        'service_type',
        'category_id',
        'subcategory_id',
        'unit_id',
        'uom_id',
        'storage_uom_id',
        'storage_uom_conversion',
        'storage_uom_count',
        'storage_type',
        'storage_weight',
        'storage_volume',
        'cost_price',
        'cost_price_currency_id',
        'sell_price',
        'sell_price_currency_id',
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
        'is_serial_no',
        'is_batch_no',
        'is_expiry',
        'is_inspection',
        'inspection_checklist_id',
        'is_traded_item',
        'is_asset',
        'is_scrap',
        'asset_category_id',
        'expected_life',
        'maintenance_schedule',
        'brand_name',
        'model_no',
        'bom_type',
        'hsn_id',
        'book_id',
        'book_code',
        'group_id',
        'company_id',
        'organization_id',
        'status',
        'production_route_id',
        'document_status',
        'approval_level',
        'revision_number',
        'revision_date',
        'created_by',
    ];

    protected $dates = ['created_at', 'updated_at'];

    // ==================== Relationships ==================== //

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function alternateUOMs()
    {
        return $this->hasMany(AlternateUOMHistory::class, 'item_id');
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

    public function subTypes()
    {
        return $this->hasMany(ItemSubTypeHistory::class, 'item_id');
    }

    public function costCurrency()
    {
        return $this->belongsTo(Currency::class, 'cost_price_currency_id');
    }

    public function sellCurrency()
    {
        return $this->belongsTo(Currency::class, 'sell_price_currency_id');
    }

    public function approvedCustomers()
    {
        return $this->hasMany(CustomerItemHistory::class, 'item_id');
    }

    public function approvedVendors()
    {
        return $this->hasMany(VendorItemHistory::class, 'item_id');
    }

    public function approvedVendor()
    {
        return $this->hasOne(VendorItemHistory::class, 'item_id')->latest();
    }

    public function itemAttributes()
    {
        return $this->hasMany(ItemAttributeHistory::class, 'item_id');
    }

    public function alternateItems()
    {
        return $this->hasMany(AlternateItemHistory::class, 'item_id');
    }

    public function specifications()
    {
        return $this->hasMany(ItemSpecificationHistory::class, 'item_id');
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    public function group()
    {
        return $this->belongsTo(OrganizationGroup::class, 'group_id');
    }

    public function company()
    {
        return $this->belongsTo(OrganizationCompany::class, 'company_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function createdByEmployee()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function auth_user()
    {
        return $this->belongsTo(AuthUser::class, 'created_by');
    }

    public function inspectionChecklist()
    {
        return $this->belongsTo(InspectionChecklist::class, 'inspection_checklist_id');
    }

    public function item_attributes_array(array $arr = [])
    {
        $mappingAttributes = $arr ?? [];
        $itemId = $this->getAttribute('id');
        if (!$itemId) {
            return collect([]);
        }

        $itemAttributes = ItemAttributeHistory::where('item_id', $itemId)->get();
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

    public function scopeSearchByKeywords($query, $term): mixed
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

    public function getDocumentStatusAttribute()
    {
        return $this->attributes['document_status'] == ConstantHelper::APPROVAL_NOT_REQUIRED
            ? ConstantHelper::APPROVED
            : $this->attributes['document_status'];
    }

    public function getDisplayStatusAttribute()
    {
        $status = str_replace('_', ' ', $this->document_status);
        return ucwords($status);
    }
}
