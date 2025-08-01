<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Deletable;

class StationLine extends Model
{
    use HasFactory, Deletable;
    protected $table = 'erp_station_lines';
    protected $fillable = [
        'station_id',
        'name',
        'supervisor_name'
    ];
}
