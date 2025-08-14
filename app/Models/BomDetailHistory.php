<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomDetailHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_bom_details_history';

    protected $fillable = [
        'source_id',
        'bom_id',
        'uom_id',
        'item_id',
        'item_code',
        'qty',
        'item_cost',
        'item_value',
        'superceeded_cost',
        'waste_perc',
        'waste_amount',
        'overhead_amount',
        'total_amount',
        'sub_section_id',
        'section_name',
        'sub_section_name',
        'station_id',
        'station_name',
        'remark',
        'vendor_id',
        'is_inherit_batch_item',
    ];

    public $referencingRelationships = [
        'item' => 'item_id',
        'uom' => 'uom_id',
        'section' => 'section_id', 
        'subSection' => 'sub_section_id', 
        'station' => 'station_id'
    ];

    public function getQtnAttribute()
    {
        $formattedQty = sprintf("%.6f", (float) $this->attributes['qty']);
        return $formattedQty;
    }
    
    public function getSuperceededCostAttribute()
    {
        $formattedQty = sprintf("%.6f", (float) $this->attributes['superceeded_cost']);
        return $formattedQty;
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function attributes()
    {
        return $this->hasMany(BomAttributeHistory::class,'bom_detail_id');
    }

    public function overheads()
    {
        return $this->hasMany(BomOverheadHistory::class,'bom_detail_id');
    }

    public function bom()
    {
        return $this->belongsTo(BomHistory::class,'bom_id');
    }

    public function subSection()
    {
        return $this->belongsTo(ProductSectionDetail::class, 'sub_section_id');
    } 

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    public function section()
    {
        return $this->belongsTo(ProductSection::class, 'section_id');
    } 
}
