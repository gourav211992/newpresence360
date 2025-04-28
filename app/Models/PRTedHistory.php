<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PRTedHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_purchase_return_ted_history';

    protected $fillable = [
        'header_id', 
        'header_history_id', 
        'detail_id', 
        'detail_history_id', 
        'pb_ted_id', 
        'ted_id', 
        'ted_type', 
        'ted_level', 
        'book_code', 
        'document_number', 
        'ted_name', 
        'ted_code', 
        'assesment_amount', 
        'ted_percentage', 
        'ted_amount', 
        'applicability_type'
    ];

    public function header()
    {
        return $this->belongsTo(PRHeader::class, 'header_id');
    }

    public function detail()
    {
        return $this->belongsTo(PRDetail::class, 'detail_id');
    }

    public function headerHistory()
    {
        return $this->belongsTo(PRHeaderHistory::class, 'header_history_id');
    }

    public function detailHistory()
    {
        return $this->belongsTo(PRDetailHistory::class, 'detail_history_id');
    }

    public function pbTed()
    {
        return $this->belongsTo(PRTed::class, 'pb_ted_id');
    }

    public function taxDetail()
    {
        return $this->belongsTo(TaxDetail::class, 'ted_id');
    }
}
