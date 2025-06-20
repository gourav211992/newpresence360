<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;

class Category  extends Model
{
    use HasFactory,SoftDeletes,Deletable,DefaultGroupCompanyOrg;
 
    protected $table = 'erp_categories';

    protected $fillable = [
        'parent_id',
        'hsn_id',
        'type',
        'name',
        'status', 
        'group_id',     
        'company_id',       
        'organization_id',
        'inspection_checklist_id',
        'cat_initials',     
        'sub_cat_initials',  
    ];
    protected $appends = ['full_name'];
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    public function subCategories()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function getFullNameAttribute()
    {
        $names = [];
        $category = $this;
        $maxDepth = 10;
        $count = 0;

        while ($category && $count < $maxDepth) {
            $names[] = $category->name; 
            $category = $category->parent; 
            $count++;
        }

        return implode(' > ', array_reverse($names));
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
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'category_id');
    }
    public function itemSub()
    {
        return $this->hasMany(Item::class, 'subcategory_id');
    }

    public function customersSub()
    {
        return $this->hasMany(Customer::class, 'subcategory_id');
    }
    
    public function customers()
    {
        return $this->hasMany(Customer::class, 'category_id');
    }

    public function hsn()
    {
        return $this->belongsTo(Hsn::class, 'hsn_id');
    }
    public function inspectionChecklist()
    {
        return $this->belongsTo(InspectionChecklist::class, 'inspection_checklist_id');
    }

    // ✅ Recursive child ID fetch method
    public function getAllNestedSubCategoryIds()
    {
        $descendantIds = [];
    
        foreach ($this->subCategories as $subCategory) {
            $descendantIds[] = $subCategory->id;
            $descendantIds = array_merge($descendantIds, $subCategory->getAllNestedSubCategoryIds());
        }
    
        return $descendantIds;
    }

}
