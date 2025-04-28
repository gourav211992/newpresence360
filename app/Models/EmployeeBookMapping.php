<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeBookMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'organization_id',
        'group_id',
        'service_menu_id',
        'erp_service_ids',
        // 'type',
        'book_ids'
    ];

    protected $table = 'employee_book_mapping';
    protected $casts = [
        'erp_service_ids' => 'array',
        'book_ids' => 'array',
        'other_book_ids' => 'array'
    ];
}
