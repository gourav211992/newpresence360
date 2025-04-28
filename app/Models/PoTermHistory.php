<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoTermHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_po_terms_history';

    protected $fillable = [
        'purchase_order_id',
        'source_id',
        'term_id',
        'term_code',
        'remarks'
    ];

    public function termAndCondition()
    {
        return $this->belongsTo(TermsAndCondition::class, 'term_id');
    }
}
