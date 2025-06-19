<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Deletable;


class DynamicFieldDetail extends Model
{
    use SoftDeletes,Deletable;
    protected $table = 'erp_dynamic_field_details';
    protected $fillable = [
        'header_id',
        'name',
        'description',
        'data_type',
        'mandatory'
    ];

    public function dynamicField()
    {
        return $this->belongsTo(DynamicField::class, 'header_id');
    }
    public function header()
    {
        return $this -> belongsTo(DynamicField::class, 'header_id');
    }
    public function getNameAttribute($value)
    {
        return preg_replace('/[^\w\s]/', ' ', $value);
    }

    public function values()
    {
        return $this->hasMany(DynamicFieldDetailValue::class, 'dynamic_field_detail_id');
    }
}