<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MrnBatchDetailHistory extends Model
{

    use HasFactory;

    protected $table = "erp_mrn_batch_details_history";
    protected $fillable = [
        'source_id', 
        'header_id', 
        'detail_id', 
        'item_id', 
        'batch_number', 
        'manufacturing_year', 
        'expiry_date', 
        'quantity', 
        'inventory_uom_quantity',
        'inspection_qty', 
        'inspection_inv_uom_qty',
        'accepted_qty', 
        'accepted_inv_uom_qty',
        'rejected_qty', 
        'rejected_inv_uom_qty'
    ];

    protected $hidden = ['deleted_at'];

    public function mrnHeader()
    {
        return $this->belongsTo(MrnHeaderHistory::class, 'header_id');
    }

    public function source()
    {
        return $this->belongsTo(MrnBatchDetail::class, 'source_id');
    }

    public function mrnDetail()
    {
        return $this->belongsTo(MrnDetailHistory::class, 'detail_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

}