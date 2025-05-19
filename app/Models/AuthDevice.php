<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthDevice extends Model
{
    protected $connection = 'mysql_master';

    protected $fillable = [
        'organization_id', 
        'organization_name', 
        'organization_alias', 
        'serial_number',
        'db_name',
        'status',
    ];

    // public function organization() {
    //     return $this->belongsTo(Organization::class);
    // }
}
