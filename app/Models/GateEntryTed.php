<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GateEntryTed extends Model
{
    use HasFactory;

    protected $table = 'erp_gate_entry_ted';

    protected $fillable = [
        'header_id',
        'detail_id',
        'po_id',
        'jo_id',
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
        'applicability_type',
    ];

    public function gateEntryHeader()
    {
        return $this->belongsTo(GateEntryHeader::class, 'header_id');
    }

    public function gateEntryDetail()
    {
        return $this->belongsTo(GateEntryDetail::class, 'detail_id');
    }

    public function taxDetail()
    {
        return $this->belongsTo(TaxDetail::class, 'ted_id');
    }
}
