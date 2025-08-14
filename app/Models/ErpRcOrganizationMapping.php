<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRcOrganizationMapping extends Model
{
    use HasFactory;
    protected $table = 'erp_rc_organization_mappings';
    protected $fillable = [
        'organization_id',
        'rate_contract_id',
    ];
}
