<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpMiDynamicField extends Model
{
    use HasFactory;
    protected $fillable = [
        'header_id',
        'dynamic_field_id',
        'dynamic_field_detail_id',
        'name',
        'value'
    ];

    public function mi()
    {
        return $this -> belongsTo(ErpMaterialIssueHeader::class, 'header_id');
    }
}
