<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class PermissionMaster extends Model
{
    use SoftDeletes;
    protected $table = 'permission_master';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'service_id',
        'name', 
        'alias',
        'type'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class,'role_permission_master','role_id','permission_id');
    }

}
