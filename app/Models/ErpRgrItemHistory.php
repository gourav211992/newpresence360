<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpRgrItemHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_rgr_items_history';

    protected $fillable = [
        'source_id',
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

    public function source()
    {
        return $this->belongsTo(ErpRgrItem::class, 'source_id');
    }

    public function rgr()
    {
        return $this->belongsTo(ErpRgr::class, 'rgr_id');
    }

    // Master Item
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    // Category
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    //hsn
     public function hsn()
    {
        return $this->belongsTo(Hsn::class, 'hsn_id');
    }

    // Sub store
    public function subStore()
    {
        return $this->belongsTo(ErpSubStore::class, 'sub_store_id');
    }

    // Unit of Measurement
    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    // Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

     public function attributes()
    {
        return $this->hasMany(ErpRgrItemAttribute::class, 'rgr_item_id');
    }
}
