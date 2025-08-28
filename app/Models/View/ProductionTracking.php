<?php

namespace App\Models\View;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionTracking extends Model
{
    protected $table = 'erp_production_tracking_view';

    protected $guarded = [];

    public $timestamps = false;
}
