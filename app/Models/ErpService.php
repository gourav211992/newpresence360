<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpService extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql_master';

    public function __construct(array $attributes = [])
    {
        $this->table = config('database.connections.mysql_master.database') .'.'.$this->table;
        parent::__construct($attributes);
    }


    protected $fillable = [
        'name',
        'alias',
        'icon',
        'status'
    ];
}
