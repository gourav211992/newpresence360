<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRfqPiMapping extends Model
{
     use HasFactory;
    protected $fillable = [
        'pi_id',
        'pi_item_id',
        'rfq_id',
        'rfq_item_id',
        'pi_qty',
        'rfq_qty'
    ];
    
    public function pi_item()
    {
        return $this->belongsTo(PiItem::class,'pi_item_id');
    }

    public function pi()
    {
        return $this->belongsTo(PurchaseIndent::class,'pi_id');
    }

    public function rfq()
    {
        return $this->belongsTo(ErpRfqHeader::class,'rfq_id');
    }
    public function rfq_item()
    {
        return $this->belongsTo(ErpRfqItem::class,'rfq_item_id');
    }
}
