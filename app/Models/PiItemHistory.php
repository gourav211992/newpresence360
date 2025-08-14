<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiItemHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_pi_items_history';

    protected $fillable = [
        'source_id',
        'pi_id',
        'so_item_id',
        'item_id',
        'item_code',
        'hsn_id',
        'hsn_code',
        'uom_id',
        'uom_code',
        'indent_qty',
        'order_qty',
        'mi_qty',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'vendor_id',
        'vendor_code',
        'vendor_name',
        'remarks',
        'adjusted_qty',
        'required_qty'
    ];

    public $referencingRelationships = [
        'item' => 'item_id',
        'uom' => 'uom_id',
        'hsn' => 'hsn_id',
        'inventoryUom' => 'inventory_uom_id',
        'vendor' => 'vendor_id',
    ];
    
    protected $casts = ['so_item_id' => 'array'];

    public function pi()
    {
        return $this->belongsTo(PurchaseIndentHistory::class, 'pi_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
    
    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function inventoryUom()
    {
        return $this->belongsTo(Unit::class, 'inventory_uom_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function hsn()
    {
        return $this->belongsTo(Hsn::class, 'hsn_id');
    }

    public function attributes()
    {
        return $this->hasMany(PiItemAttributeHistory::class,'pi_item_id');
    }

    public function getBalenceQtyAttribute()
    {
        return $this->indent_qty - ($this->order_qty ?? 0);
    }
}
