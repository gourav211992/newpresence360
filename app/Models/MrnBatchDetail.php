<?php   
namespace App\Models;

use App\Models\WHM\ErpItemUniqueCode;
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
        'inventory_uom_qty',
        'inspection_qty', 
        'inspection_inv_uom_qty',
        'accepted_qty', 
        'accepted_inv_uom_qty',
        'rejected_qty', 
        'rejected_inv_uom_qty'
    ];

    protected $hidden = ['deleted_at'];

    // In App\Models\MrnBatchDetail (or whatever model)
    protected $casts = [
        'expiry_date' => 'date', // returns a Carbon instance
    ];

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

    public function uniqueCodes()
    {
        return $this->hasMany(ErpItemUniqueCode::class, 'batch_id');
    }

}