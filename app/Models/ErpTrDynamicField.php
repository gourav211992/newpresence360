<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpTrDynamicField extends Model
{
    use HasFactory;
    protected $fillable = [
        'header_id',
        'dynamic_field_id',
        'dynamic_field_detail_id',
        'name',
        'value'
    ];

    public function tr()
    {
        return $this -> belongsTo(ErpTransporterRequest::class, 'header_id');
    }
}
