<?php

namespace App\Models\View;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomVsConsumption extends Model
{
    protected $table = 'erp_bom_vs_consumptions_view';

    protected $guarded = [];

    public $timestamps = false;
}
