<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $table = 'erp_approval_processes';


    public function users()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }
}
