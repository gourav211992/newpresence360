<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpMachineDetail extends Model
{
    protected $table = 'erp_machine_details';
    use HasFactory;

    protected $fillable=["machine_id","attribute_group_id","attribute_id","attribute_value","length","width","no_of_pairs","created_by","updated_by"];
}
