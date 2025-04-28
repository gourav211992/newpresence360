<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionUploadItem extends Model
{
    use HasFactory;

    protected $table = 'erp_transation_upload_items';

    protected $fillable = [
        'type', 
        'item_id', 
        'item_name', 
        'item_code', 
        'hsn_id', 
        'hsn_code', 
        'uom_id', 
        'uom_code', 
        'order_qty', 
        'rate',
        'store_id', 
        'store_code', 
        'status', 
        'form_status',        
        'attributes', 
        'reason',
        'is_error',
        'is_sync',
        'created_by', 
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function hsn()
    {
        return $this->belongsTo(Hsn::class);
    }
    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }
    public function erpSubStore()
    {
        return $this->belongsTo(ErpSubStore::class, 'store_id');
    }
    public function itemAttributes()
    {
        return $this->hasMany(ItemAttribute::class);
    }
    public function getAttributesAttribute($value)
    {
        return json_decode($value, true);
    }
    public function getReasonAttribute($value)
    {
        return json_decode($value, true);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
