<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceGroup extends Model
{
    use HasFactory;

    protected $connection = 'mysql_master';
    protected $table = 'service_groups';
    protected $fillable = ['id', 'name', 'alias', 'status', 'created_by', 'updated_by', 'deleted_by'];
}
