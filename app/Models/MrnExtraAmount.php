<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MrnExtraAmount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_mrn_extra_amounts';

    protected $fillable = [
        'mrn_header_id', 
        'mrn_detail_id', 
        'po_id',
        'jo_id',
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

    public function taxDetail()
    {
        return $this->belongsTo(TaxDetail::class, 'ted_id');
    }
}
