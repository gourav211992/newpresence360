<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpDprTemplateMaster extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'template_name',
        'status',
        'group_id',
        'company_id',
        'organization_id',
    ];

    public function dpr()
    {
        return $this->hasMany(ErpDprMaster::class, 'template_id');
    }
}
