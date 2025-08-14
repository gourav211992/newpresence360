<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpGeDynamicField extends Model
{
    use HasFactory;

    protected $fillable = [
        'header_id',
        'dynamic_field_id',
        'dynamic_field_detail_id',
        'name',
        'value'
    ];

    public function ge()
    {
        return $this -> belongsTo(GateEntryHeader::class,'header_id');
    }
}
