<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;
    protected $connection = 'mysql_master';

    protected $table = 'erp_services';

    public function __construct(array $attributes = [])
    {
        $this->table = config('database.connections.mysql_master.database') .'.'.$this->table;
        parent::__construct($attributes);
    }

    public function parameters()
    {
        return $this -> hasMany(ServiceParameter::class);
    }
}
