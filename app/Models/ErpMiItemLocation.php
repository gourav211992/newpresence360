<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpMiItemLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'material_issue_id',
        'mi_item_id',
        'item_id',
        'item_code',
        'store_id',
        'store_code',
        'rack_id',
        'rack_code',
        'shelf_id',
        'shelf_code',
        'bin_id',
        'bin_code',
        'type',
        'quantity',
        'inventory_uom_qty'
    ];

    protected $hidden = ['deleted_at'];

    
    public $referencingRelationships = [
        'erpStore' => 'store_id',
        'erpRack' => 'rack_id',
        'erpShelf' => 'shelf_id',
        'erpBin' => 'bin_id'
    ];

    public function header()
    {
        return $this -> belongsTo(ErpMaterialIssueHeader::class, 'material_issue_id');
    }

    public function detail()
    {
        return $this -> belongsTo(ErpMiItem::class, 'mi_item_id');
    }

    public function erpStore()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }

    public function erpRack()
    {
        return $this->belongsTo(ErpRack::class, 'rack_id');
    }

    public function erpShelf()
    {
        return $this->belongsTo(ErpShelf::class, 'shelf_id');
    }

    public function erpBin()
    {
        return $this->belongsTo(ErpBin::class, 'bin_id');
    }

    public function fromErpStore()
    {
        return $this -> belongsTo(ErpStore::class, 'store_id');
    }
    public function toErpStore()
    {
        return $this -> belongsTo(ErpStore::class, 'store_id');
    }
}
