<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpDprMaster extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'template_id',
        'field_name',
        'status',
        'group_id',
        'company_id',
        'organization_id',
    ];

    public function template()
    {
        return $this->belongsTo(ErpDprTemplateMaster::class, 'template_id');
    }
}
