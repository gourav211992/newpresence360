<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpOrganizationService extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'group_id',
        'company_id',
        'organization_id',
        'service_id',
        'name',
        'alias',
        'icon',
        'status'
    ];

    public function service()
    {
        return $this -> belongsTo(ErpService::class, 'service_id');
    }
}
