<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitMaster extends Model
{
    use SoftDeletes;  
    
    protected $connection = 'mysql_master';
    protected $table = 'erp_unit_masters';  

    protected $fillable = [
        'unit_code', 
        'unit_name', 
        'status',
        'created_by', 
        'updated_by',
        'deleted_by'
    ];

}
