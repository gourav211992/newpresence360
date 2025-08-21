<?php

namespace App\Models\Kaizen;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpKaizenImprovement extends Model
{
    use HasFactory;
    protected $table = 'erp_kaizen_improvements';
    protected $fillable = [
        'type',
        'description',      
        'marks',
        'status',
        'organization_id',
        'group_id',
        'company_id',
    ];
}
