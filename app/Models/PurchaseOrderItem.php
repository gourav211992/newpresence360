<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PurchaseOrderItem extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $table = 'erp_purchase_order_items';

    protected $fillable = [
        'purchase_order_id',           
        'item_id',                    
        'hsn_code',                               
        'uom_id',      
        'expected_delivery_date',    
        'quantity',                  
        'rate',                      
        'basic_value',             
        'discount_percentage',       
        'discount_amount', 
        'net_value',           
        'sgst_percentage',           
        'cgst_percentage',           
        'igst_percentage',           
        'tax_value',                 
        'taxable_amount',                           
        'sub_total',                 
        'selected_item',              
    ];


    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function hsn()
    {
        return $this->belongsTo(Hsn::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function document(): void
    {
        $this->addMediaCollection('documents');
    }
}
