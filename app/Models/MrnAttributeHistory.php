<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MrnAttributeHistory extends Model
{

    use HasFactory, SoftDeletes;
    protected $table = "erp_mrn_attribute_histories";
    protected $fillable = [
        'mrn_header_history_id', 
        'mrn_detail_history_id', 
        'mrn_header_id', 
        'mrn_detail_id', 
        'item_id', 
        'item_attribute_id', 
        'mrn_attribute_id', 
        'attr_name', 
        'attr_value'
    ];

    protected $appends = [
    ];

    protected $hidden = ['deleted_at'];

    public function mrnHeader()
    {
        return $this->belongsTo(MrnHeader::class);
    }

    public function mrnDetail()
    {
        return $this->belongsTo(MrnDetail::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function itemAttribute()
    {
        return $this->belongsTo(ItemAttribute::class);
    }

    public function mrnAttribute()
    {
        return $this->belongsTo(MrnAttribute::class);
    }

    public function mrnHeaderHistory()
    {
        return $this->belongsTo(MrnHeaderHistory::class);
    }

    public function mrnDetailHistory()
    {
        return $this->belongsTo(MrnDetailHistory::class);
    }

}