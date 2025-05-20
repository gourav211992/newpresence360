<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Deletable;

class ErpDocument  extends Model
{
    use HasFactory,Deletable;

    protected $table = 'erp_documents';

    protected $fillable = [
        'service',
        'name',
        'group_id',
        'company_id',
        'organization_id',
        'status',
    ];

    protected $appends = ['alias'];

    public function alternateUOMs()
    {
        return $this->hasMany(AlternateUOM::class, 'uom_id');
    }
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function getAliasAttribute()
    {
        // if ($this ->attributes['alias'] === null) {
        //     return $this -> attributes['name'];
        // } else {
        //     return $this -> attributes['alias'];
        // }
        if (isset($this ->attributes['service'])) {
            return $this -> attributes['service'];
        } else {
            return "";
        }
    }
}
