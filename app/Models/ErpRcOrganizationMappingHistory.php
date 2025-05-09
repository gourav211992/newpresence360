<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRcOrganizationMappingHistory extends Model
{
    use HasFactory;
    protected $table = 'erp_rc_organization_mappings_history';
    protected $fillable = [
        'source_id',
        'organization_id',
        'rate_contract_id',
    ];
}