<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MrnBatchDetail extends Model
{

    use HasFactory;

    protected $table = "erp_mrn_batch_details";
    protected $fillable = [
        'header_id', 
        'detail_id', 
        'item_id', 
        'batch_number', 
        'manufacturing_year', 
        'expiry_date', 
        'quantity', 
        'inventory_uom_qty'
    ];

    protected $hidden = ['deleted_at'];

    public function mrnHeader()
    {
        return $this->belongsTo(MrnHeader::class, 'header_id');
    }

    public function source()
    {
        return $this->hasOne(MrnBatchDetailHistory::class, 'source_id');
    }

    public function mrnDetail()
    {
        return $this->belongsTo(MrnDetail::class, 'detail_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

}