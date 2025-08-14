<?php

namespace App\Models;

use App\Traits\Deletable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpItemAttribute extends Model
{
    use HasFactory, Deletable, SoftDeletes;

    public function group()
    {
        return $this -> hasOne(ErpAttributeGroup::class, 'id', 'attribute_group_id');
    }
}
