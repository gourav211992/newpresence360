<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Deletable;


class DynamicFieldDetailValue extends Model
{
    use HasFactory, SoftDeletes,Deletable;

    protected $table = 'erp_dynamic_field_detail_values';

    protected $fillable = [
        'dynamic_field_detail_id',
        'value',
    ];

    public function dynamicFieldDetail()
    {
        return $this->belongsTo(DynamicFieldDetail::class, 'dynamic_field_detail_id');
    }
}
