<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExpenseTed extends Model
{
    use HasFactory;

    protected $table = 'erp_expense_ted';

    protected $fillable = [
        'expense_header_id', 
        'expense_detail_id', 
        'ted_id',
        'ted_type', 
        'ted_name',
        'ted_level', 
        'book_code', 
        'document_number', 
        'ted_code', 
        'assesment_amount', 
        'ted_percentage', 
        'ted_amount', 
        'applicability_type'
    ];

    public function expenseHeader()
    {
        return $this->belongsTo(ExpenseHeader::class);
    }

    public function expenseDetail()
    {
        return $this->belongsTo(ExpenseDetail::class);
    }

    public function taxDetail()
    {
        return $this->belongsTo(TaxDetail::class, 'ted_id');
    }
}
