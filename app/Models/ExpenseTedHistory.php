<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExpenseTedHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_expense_ted_histories';

    protected $fillable = [
        'header_id', 
        'header_history_id', 
        'detail_id', 
        'detail_history_id', 
        'expense_ted_id',
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
        return $this->belongsTo(ExpenseHeader::class, 'header_id');
    }

    public function headerHistory()
    {
        return $this->belongsTo(ExpenseHeaderHistory::class, 'header_history_id');
    }

    public function detail()
    {
        return $this->belongsTo(ExpenseDetail::class, 'detail_id');
    }

    public function detailHistory()
    {
        return $this->belongsTo(ExpenseDetailHistory::class, 'detail_history_id');
    }

    public function expenseTed()
    {
        return $this->belongsTo(ExpenseTed::class, 'expense_ted_id');
    }

    public function taxDetail()
    {
        return $this->belongsTo(TaxDetail::class, 'ted_id');
    }
}
