<?php

namespace App\Models\Mrn;
use Illuminate\Database\Eloquent\Model;

class PoMrnType extends Model
{
 
    protected $fillable = [
        'organization_id',
        'name',
        'status',
        'created_by',
        'updated_by'
    ];
}
