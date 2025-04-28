<?php
namespace App\Models;

use App\Traits\Deletable;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgingMaster extends Model
{
    use HasFactory, SoftDeletes,Deletable,DefaultGroupCompanyOrg;

    protected $table = 'erp_aging_masters'; 

    protected $fillable = [
        'group_id', 'company_id', 'organization_id', 'user_id', 'name', 'value', 'status', 'created_by', 'updated_by', 'deleted_by'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
