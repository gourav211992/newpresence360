<?php

namespace App\Models;

use App\Helpers\Helper;
use App\Traits\DateFormatTrait;
use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpSaleReturnItemLocationHistory extends Model
{
    use HasFactory, SoftDeletes, FileUploadTrait, DateFormatTrait;

    protected $fillable = [
        'sale_return_id',
        'sr_item_id',
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
        'returned_qty',
        'inventory_uom_qty',
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
        return $this -> belongsTo(ErpSaleReturnHistory::class, 'sale_return_id');
    }

    public function detail()
    {
        return $this -> belongsTo(ErpSaleReturnItemHistory::class, 'sale_return_item_id');
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

}
