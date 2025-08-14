<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandScheduler extends Model
{
    use HasFactory;

    public $table = 'erp_land_schedulers';

    protected $guarded = [];


    public function to()
    {
        return $this->morphTo();
    }
}
