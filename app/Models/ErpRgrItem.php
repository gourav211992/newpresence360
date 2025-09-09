<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WHM\ErpItemUniqueCode;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpRgrItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_rgr_items';

    protected $fillable = [
        'rgr_id',
        'item_id',
        'hsn_id',
        'category_id',
        'sub_store_id',
        'item_uid',
        'item_code',
        'item_name',
        'uom_id',
        'customer_id',
        'customer_name',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'uom_name',
        'qty',
        'remarks',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
    ];

    public $referencingRelationships = [
        'item' => 'item_id',
    ];

    /** 🔗 Relationships */
    public function rgr()
    {
        return $this->belongsTo(ErpRgr::class, 'rgr_id');
    }

    public function attributes()
    {
        return $this->hasMany(ErpRgrItemAttribute::class, 'rgr_item_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function hsn()
    {
        return $this->belongsTo(Hsn::class, 'hsn_id');
    }

    public function subStore()
    {
        return $this->belongsTo(ErpSubStore::class, 'sub_store_id');
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function uniqueCodes()
    {
        return $this->morphMany(ErpItemUniqueCode::class, 'morphable');
    }
    public function segregation()
    {
        return $this->hasOne(ErpRgrItemSegregation::class, 'rgr_item_id');
    }
}
