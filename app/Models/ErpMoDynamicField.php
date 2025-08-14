<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpMoDynamicField extends Model
{
    use HasFactory;

    protected $fillable = [
        'header_id',
        'dynamic_field_id',
        'dynamic_field_detail_id',
        'name',
        'value'
    ];

    public function mo()
    {
        return $this -> belongsTo(MfgOrder::class,'header_id');
    }
}
