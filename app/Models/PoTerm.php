<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoTerm extends Model
{
    use HasFactory;

    protected $table = 'erp_po_terms';

    protected $fillable = [
        'purchase_order_id',
        'term_id',
        'term_code',
        'remarks'
    ];

    public function termAndCondition()
    {
        return $this->belongsTo(TermsAndCondition::class, 'term_id');
    }
    
}
