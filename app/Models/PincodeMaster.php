<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PincodeMaster extends Model
{
    use HasFactory,SoftDeletes;

    protected $connection = 'mysql_master';
    protected $table = 'erp_pincode_masters';


    protected $fillable = [
        'state_id', 
        'pincode', 
        'status', 
        'created_by', 
        'updated_by', 
        'deleted_by', 
    ];

    
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

}
