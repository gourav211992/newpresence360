<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpInvoiceItemLocationHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_invoice_item_locations_history';

    public $referencingRelationships = [
        'erpStore' => 'store_id',
        'erpRack' => 'rack_id',
        'erpShelf' => 'shelf_id',
        'erpBin' => 'bin_id'
    ];

    public function header()
    {
        return $this -> belongsTo(ErpSaleInvoiceHistory::class, 'sale_invoice_id');
    }

    public function detail()
    {
        return $this -> belongsTo(ErpInvoiceItemHistory::class, 'invoice_item_id');
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
