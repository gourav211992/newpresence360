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

    public function items()
    {
        return $this->hasMany(Item::class, 'group_id');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'group_id');
    }

}
