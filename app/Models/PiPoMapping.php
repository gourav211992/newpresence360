<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiPoMapping extends Model
{
    use HasFactory;

    protected $table = 'erp_pi_po_mapping';

    protected $filable = [
        'pi_id',
        'pi_item_id',
        'po_id',
        'po_item_id',
        'so_id',
        'po_qty'
    ];

    public $timestamps = false;
    
    public function pi_item()
    {
        return $this->belongsTo(PiItem::class,'pi_item_id');
    }

    public function pi()
    {
        return $this->belongsTo(PurchaseIndent::class,'pi_id');
    }

    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class,'so_id');
    }
}
