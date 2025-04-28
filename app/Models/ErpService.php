<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpService extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql_master';


    protected $fillable = [
        'name',
        'alias',
        'icon',
        'status'
    ];
}
