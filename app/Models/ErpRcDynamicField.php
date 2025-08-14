<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRcDynamicField extends Model
{
    use HasFactory;
    protected $fillable = [
        'header_id',
        'dynamic_field_id',
        'dynamic_field_detail_id',
        'name',
        'value'
    ];

    public function rc()
    {
        return $this -> belongsTo(ErpRateContract::class, 'header_id');
    }
}
