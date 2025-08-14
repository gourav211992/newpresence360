<?php

namespace App\Models\Kaizen;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpKaizenTeam extends Model
{
    use HasFactory;

    protected $fillable = ['kaizen_id','team_id'];
}
