<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationGroup extends Model
{
    use HasFactory;

    public $table = 'organization_groups';
    public function currency() {
        return $this->belongsTo(Currency::class,'currency_id');
    }
}
