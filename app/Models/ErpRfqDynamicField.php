<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRfqDynamicField extends Model
{
    use HasFactory;
    protected $fillable = [
        'header_id',
        'dynamic_field_id',
        'dynamic_field_detail_id',
        'name',
        'value'
    ];

    public function rfq()
    {
        return $this -> belongsTo(ErpRfqHeader::class, 'header_id');
    }
}
