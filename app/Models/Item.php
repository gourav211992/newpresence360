<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Deletable;
use App\Helpers\Helper;
use App\Helpers\ConstantHelper;
use Illuminate\Support\Facades\Schema;
use App\Interfaces\Exportable;



class Item extends Model implements Exportable
{
    use HasFactory,SoftDeletes,Deletable,DefaultGroupCompanyOrg;

    protected $table = 'erp_items';

    protected $fillable = [
        'type',
        'unit_id',
        'hsn_id',
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
        'cost_price',
        'cost_price_currency_id',
        'sell_price',
        'sell_price_currency_id',
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
        'document_status',
        'approver_level',
        'revision_number',
        'revision_date',
        'created_at',
        'created_by'
    ];

    protected $dates = ['created_at', 'updated_at'];

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

     public function storageUom()
    {
        return $this->belongsTo(Unit::class, 'storage_uom_id');
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

    // public function attributes()
    // {
    //     return $this->hasMany(ErpAttribute::class);
    // }

    public function itemAttributes()
    {
        return $this->hasMany(ItemAttribute::class);
    }
    public function alternateItems()
    {
        return $this->hasMany(AlternateItem::class);
    }
    // Item.php
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

    public function inspectionChecklist()
    {
        return $this->belongsTo(InspectionChecklist::class, 'inspection_checklist_id');
    }

     public function assetCategory()
    {
        return $this->belongsTo(ErpAssetCategory::class,'asset_category_id');
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
        if ($this->attributes['document_status'] == ConstantHelper::APPROVAL_NOT_REQUIRED) {
            return ConstantHelper::APPROVED;
        }
        return $this->attributes['document_status'];
    }
    public function getDisplayStatusAttribute()
    {
        $status = str_replace('_', ' ', $this->document_status);
        return ucwords($status);
    }

    public function getStatusAttribute()
    {
        $status = str_replace('_', ' ', $this->attributes['status'] ?? '');
        return ucwords($status);
    }
    public function getSelectedAttributeData(array $selectedAttrIds)
    {
        $attrData = collect();
        $itemAttributes = ItemAttribute::where('item_id', $this->id)->get();
        foreach ($itemAttributes as $itemAttribute) {
            $attributeIds = is_array($itemAttribute->attribute_id)
                ? $itemAttribute->attribute_id
                : [$itemAttribute->attribute_id];
            foreach ($attributeIds as $attrValueId) {
                if (in_array($attrValueId, $selectedAttrIds)) {
                    $attrData->push((object)[
                        'item_attribute_id' => $itemAttribute->id,
                        'attribute_value'   => $attrValueId,
                    ]);
                }
            }
        }
        return $attrData;
    }

    // Load Inspection Checklists
    public function loadInspectionChecklists()
    {
        $checkLists = [];

        if ($this->inspection_checklist_id) {
            if ($this->inspectionChecklist()->exists()) {
                $checkLists = $this->inspectionChecklist()->with('details.values')->get()->toArray();
            } elseif ($this->category_id && $this->category && $this->category->inspectionChecklist()->exists()) {
                $checkLists = $this->category?->inspectionChecklist()->with('details.values')->get()->toArray();
            }
        } else{
            $checkLists = $this->category?->inspectionChecklist()->with('details.values')->get()->toArray();
        }

        return isset($checkLists) ? $checkLists : [];
    }

    // Corrected function to get all table columns.  Use the model's table name.
    public static function getAllTableColumns()
    {
        return Schema::getColumnListing((new self())->getTable());
    }


   public function getExportColumns()
    {
        $columns = [];
        $columns['Sub Types'] = 'sub_types_list';
        $columns['UOM'] = 'uom.name';
        $columns['Storage UOM'] = 'storageUom.name';
        $columns['HSN'] = 'hsn.code';
        $columns['Category'] = 'subCategory.name';
        $columns['Asset Category'] = 'assetCategory.name';
        $columns['Cost Currency'] = 'costCurrency.short_name';
        $columns['Sell Currency'] = 'sellCurrency.short_name';
        $columns['Group'] = 'group.name';
        $columns['Company'] = 'company.name';
        $columns['Organization'] = 'organization.name';


        // 2. Table columns
       $skipColumns = [
            'unit_id','hsn_id','category_id','subcategory_id','uom_id','storage_uom_id',
            'inspection_checklist_id','asset_category_id','cost_price_currency_id','sell_price_currency_id',
            'book_id','book_code','group_id','company_id','organization_id','created_by','min_stocking_level',
            'max_stocking_level','reorder_level','minimum_order_qty','lead_days','safety_days','shelf_life_days',
            'po_positive_tolerance','po_negative_tolerance','so_positive_tolerance','so_negative_tolerance',
            'approver_level','revision_number','revision_date','is_serial_no','is_batch_no','is_expiry','
            is_inspection','is_traded_item','is_asset','storage_uom_conversion','is_inspection',
            'storage_uom_count','storage_weight','storage_volume','item_initial','item_code_type','item_remark',
            'service_type','document_status','storage_type',
       ];


        foreach ($this->getFillable() as $column) {
            if (in_array($column, $skipColumns)) continue;
            $columns[ucwords(str_replace('_', ' ', $column))] = $column;
        }

        $columns['Created By'] = 'auth_user.name';
       // 3.Attributes
        for ($i = 1; $i <= 5; $i++) {
            $columns["Attribute {$i} Group Name"] = "attribute_{$i}_group_name";
            $columns["Attribute {$i} Attribute Name"] = "attribute_{$i}_attribute_name";
            $columns["Attribute {$i} All Checked"] = "attribute_{$i}_all_checked";
        }

        // 4. Specifications
        $columns["Product Specification Group"] = "product_specification_group";

        for ($i = 1; $i <= 10; $i++) {
            $columns["Specification {$i} Name"] = "specification_{$i}_name";
            $columns["Specification {$i} Value"] = "specification_{$i}_value";
        }

        // 5. Alternate UOMs
        for ($i = 1; $i <= 5; $i++) {
            $columns["Alternate UOM {$i} UOM"] = "alternate_uom_{$i}_uom";
            $columns["Alternate UOM {$i} Conversion To Inventory"] = "alternate_uom_{$i}_conversion";
            $columns["Alternate UOM {$i} Cost Price"] = "alternate_uom_{$i}_cost_price";
            $columns["Alternate UOM {$i} Sell Price"] = "alternate_uom_{$i}_sell_price";
            $columns["Alternate UOM {$i} Usage"] = "alternate_uom_{$i}_usage";
        }

        return $columns;
    }

    public function getExportFileName(): string
    {
        return 'items';
    }

    // Get Salvage Percentage
    public function getSalvagePercentage()
    {
        $salvagePercentage = FixedAssetSetup::where('act_type', 'company')
            ->where('asset_category_id', $this->asset_category_id)
            ->value('salvage_percentage');

        if (!$salvagePercentage) {
            return 0;
        }

        return $salvagePercentage;
    }

    // Get Asset Code
    public function getAssetCode()
    {
        $assetCode = Helper::generateAssetCode($this->asset_category_id);
        return $assetCode;

    }


}
