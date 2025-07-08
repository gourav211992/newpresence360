<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MrnExtraAmountHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_mrn_extra_amount_histories';

    protected $fillable = [
        'mrn_header_history_id', 
        'mrn_detail_history_id',
        'mrn_header_id', 
        'mrn_detail_id', 
        'po_id',
        'jo_id',
        'mrn_extra_amount_id', 
        'ted_type', 
        'ted_level', 
        'ted_id',
        'book_code', 
        'document_number', 
        'ted_name',
        'ted_code', 
        'assesment_amount', 
        'ted_percentage', 
        'ted_amount', 
        'applicability_type'
    ];

    public function mrnHeader()
    {
        return $this->belongsTo(MrnHeader::class);
    }

    public function mrnDetail()
    {
        return $this->belongsTo(MrnDetail::class);
    }

    public function mrnHeaderHistory()
    {
        return $this->belongsTo(MrnHeaderHistory::class);
    }

    public function mrnDetailHistory()
    {
        return $this->belongsTo(MrnDetailHistory::class);
    }

    public function mrnExtraAmount()
    {
        return $this->belongsTo(MrnExtraAmount::class);
    }
}
