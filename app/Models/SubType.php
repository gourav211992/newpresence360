<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_sub_types';
    // protected $connection = 'mysql_master';
    protected $fillable = [
        'name',
        'status'
    ];

   
    public function items()
    {
        return $this->belongsToMany(Item::class, 'erp_item_subtypes');
    }

}
