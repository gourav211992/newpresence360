<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentGroup extends Model
{
    use HasFactory;

    // Specify which attributes are mass assignable
    protected $fillable = ['name', 'master_group_id'];

    // Define relationships
    public function masterGroup()
    {
        return $this->belongsTo(MasterGroup::class, 'master_group_id', 'id');
    }

    /**
     * Get all of the comments for the ParentGroup
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groups()
    {
        return $this->hasMany(Group::class, 'group_id', 'id');
    }
}
