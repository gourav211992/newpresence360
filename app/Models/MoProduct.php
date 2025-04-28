<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoProduct extends Model
{
    use HasFactory;
    protected $table = 'erp_mo_products';
    protected $fillable = [
        'production_bom_id',
        'mo_id',
        'item_id',
        'customer_id',
        'item_code',
        'uom_id',
        'qty',
        'pwo_mapping_id',
        'order_id'
    ]; 
    
    public function mo()
    {
        return $this->belongsTo(MfgOrder::class, 'mo_id');
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function attributes()
    {
        return $this->hasMany(MoProductAttribute::class,'mo_product_id');
    }

    public function moBomMapping()
    {
        return $this->hasMany(MoBomMapping::class,'mo_product_id');
    }

    public function customer()
    {
        return $this->belongsTo(ErpCustomer::class, 'customer_id');
    }

    public function pwoMapping()
    {
        return $this->belongsTo(PwoSoMapping::class,'pwo_mapping_id');
    }

    public function bom()
    {
        return $this->belongsTo(Bom::class,'production_bom_id');
    }

    public function productionRoute()
    {
        return $this->belongsTo(ProductionRoute::class,'production_route_id');
    }

    public function pwoStationConsumption()
    {
        return $this->belongsTo(PwoStationConsumption::class,'pwo_mapping_id','pwo_mapping_id')->where('station_id', $this->mo->station_id);
    }

}
