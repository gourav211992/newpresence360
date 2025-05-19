<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupPermission extends Model
{
    use SoftDeletes;
    protected $table = 'group_permissions';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function service() {
        return $this->belongsTo(Service::class);
    }

}
