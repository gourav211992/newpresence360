<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\ConstantHelper;
use App\Traits\Deletable;

class ErpEinvoiceLog extends Model
{
    use HasFactory,Deletable;
    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'request_uid',
        'api_name',
        'method',
        'is_error',
        'request_payload',
        'response_payload',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
        'source'
    ];

}
