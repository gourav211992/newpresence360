<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GateEntryAttributeHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_gate_entry_attributes_history';

    protected $fillable = [
        'source_id',
        'header_id',
        'detail_id',
        'item_id',
        'item_code',
        'item_attribute_id',
        'attr_name',
        'attr_value',
    ];

    protected $appends = [
    ];

    protected $hidden = ['deleted_at'];

    public function source()
    {
        return $this->belongsTo(GateEntryAttribute::class);
    }

    public function GateEntryHeaderHistory()
    {
        return $this->belongsTo(GateEntryHeaderHistory::class, 'source_id');
    }

    public function GateEntryDetailHistory()
    {
        return $this->belongsTo(GateEntryDetailHistory::class, 'source_id');
    }
}
