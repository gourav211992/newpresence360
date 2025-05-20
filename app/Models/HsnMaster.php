<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HsnMaster extends Model
{
    use HasFactory,SoftDeletes;
    protected $connection = 'mysql_master';
    protected $table = 'erp_hsn_masters';

    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
