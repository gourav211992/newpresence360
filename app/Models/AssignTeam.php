<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignTeam extends Model
{
    protected $table = 'erp_assign_teams';

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'team',
        'remarks',
        'legalid'
    ];

    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'team', 'id'); // Assuming 'team' is the foreign key to 'users' table
    }
    public function legal(){
        return $this->belongsTo(Legal::class, 'legalid', 'id'); // Assuming 'team' is the foreign key to 'users' table
    }


}
