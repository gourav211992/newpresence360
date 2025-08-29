<?php

namespace App\Models\Scrap;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ErpScrapDynamicField extends Model
{
    use HasFactory;

    protected $fillable = [
        'header_id',
        'dynamic_field_id',
        'dynamic_field_detail_id',
        'name',
        'value'
    ];

    public function header()
    {
        return $this->belongsTo(ErpScrapOrder::class, 'header_id');
    }
}
