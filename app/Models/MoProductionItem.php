<?php

namespace App\Models;

use App\Helpers\InventoryHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoProductionItem extends Model
{
    use HasFactory;

    protected $table = 'erp_mo_production_items';
    protected $fillable = [
        'mo_id',
        'production_bom_id',
        'item_id',
        'item_code',
        'uom_id',
        'required_qty',
        'produced_qty',
        'rate'
    ];
    protected $appends = ['value'];
    public function productionAttributes()
    {
        return $this->hasMany(MoProductionItemAttribute::class,'mo_production_item_id');
    }

    public function mo()
    {
        return $this->belongsTo(MfgOrder::class, 'mo_id');
    }

    public function bom()
    {
        return $this->belongsTo(Bom::class, 'bom_id');
    }

    public function getValueAttribute()
    {
        return $this->produced_qty * $this->rate;
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function getInventoryAndStock()
    {
        $itemId = $this->item_id;
        $uomId = $this->uom_id;
        $storeId = $this?->mo?->store_id;
        $selectedAttr = [];
        if($this->productionAttributes->count()) {
            $selectedAttr = $this->productionAttributes()->pluck('attribute_id')->toArray();
        }
        $data = InventoryHelper::totalInventoryAndStock($itemId, $selectedAttr, $uomId, $storeId);
        return $data; 
    }


}
