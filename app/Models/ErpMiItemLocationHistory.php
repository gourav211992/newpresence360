<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpMiItemLocationHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_mi_item_locations_history';
    protected $hidden = ['deleted_at'];

    
    public $referencingRelationships = [
        'erpStore' => 'store_id',
        'erpRack' => 'rack_id',
        'erpShelf' => 'shelf_id',
        'erpBin' => 'bin_id'
    ];

    public function header()
    {
        return $this -> belongsTo(ErpMaterialIssueHeaderHistory::class, 'material_issue_id');
    }

    public function detail()
    {
        return $this -> belongsTo(ErpMiItemHistory::class, 'mi_item_id');
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
