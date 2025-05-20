<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeRole extends Model
{
    use HasFactory;

    protected $table = "employee_role";

    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'role_id'
    ];
}
