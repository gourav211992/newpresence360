<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GateEntryTedHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_gate_entry_ted_history';

    protected $fillable = [
        'source_id',
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
        return $this->belongsTo(GateEntryHeader::class);
    }

    public function gateEntryDetail()
    {
        return $this->belongsTo(GateEntryDetail::class);
    }

    public function gateEntryHeaderHistory()
    {
        return $this->belongsTo(GateEntryHeaderHistory::class);
    }

    public function gateEntryDetailHistory()
    {
        return $this->belongsTo(GateEntryDetailHistory::class);
    }

    public function gateEntryExtraAmount()
    {
        return $this->belongsTo(GateEntryTed::class);
    }
}
