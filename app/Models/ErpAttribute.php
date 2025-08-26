<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Deletable;

class ErpAttribute extends Model
{
    protected $table = 'erp_attributes';

    use HasFactory, SoftDeletes,Deletable;

    protected $fillable = [
        'id',
        'value',
        'attribute_group_id',
    ];


    protected $auditInclude = [
        'value',
        'attribute_group_id',
    ];

    public function itemAttributes()
    {
        return $this->hasMany(ItemAttribute::class, 'attribute_id');
    }

    public function attributeGroup()
    {
        return $this->belongsTo(AttributeGroup::class, 'attribute_group_id');
    }


}
