<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterGroup extends Model
{
    use HasFactory;

    // Specify which attributes are mass assignable
    protected $fillable = ['name'];

    // Define relationships
    public function parentGroups()
    {
        return $this->hasMany(ParentGroup::class, 'master_group_id', 'id');
    }
}
