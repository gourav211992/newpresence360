<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use HasFactory;
    protected $table = 'role_permission_master';

    protected $fillable = [
        'role_id',
        'permission_id'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the user that owns the RolePermission
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'foreign_key', 'other_key');
    }
}
