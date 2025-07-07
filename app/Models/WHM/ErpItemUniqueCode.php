<?php

namespace App\Models\WHM;

use App\Models\Attribute;
use App\Models\ErpVendor;
use App\Models\Item;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpItemUniqueCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'job_id',
        'morphable_id',
        'morphable_type',
        'group_id',
        'company_id',
        'organization_id',
        'store_id',
        'sub_store_id',
        'book_id',
        'book_code',
        'doc_type',
        'doc_no',
        'doc_date',
        'item_id',
        'item_attributes',
        'item_name',
        'item_code',
        'item_uid',
        'type',
        'uitlized_id',
        'vendor_id',
        'qty',
        'status',
        'action_by',
        'action_at',
    ];
    
    protected  $casts = [
        'item_attributes' => 'array'
    ];

    public function morphable()
    {
        return $this->morphTo();
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function vendor()
    {
        return $this->belongsTo(ErpVendor::class, 'vendor_id');
    }
}