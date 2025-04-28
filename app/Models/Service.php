<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected $connection = 'mysql_master';

    protected $table = 'erp_services';

    public function parameters()
    {
        return $this -> hasMany(ServiceParameter::class);
    }
}
