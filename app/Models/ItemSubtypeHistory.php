<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemSubTypeHistory extends Model
{
    use HasFactory,SoftDeletes;

    protected $connection = "mysql";

    protected $table = 'erp_item_subtypes_history';

    protected $fillable = [
        'source_id',
        'item_id', 
        'sub_type_id'
    ];

    public function subType()
    {
        return $this -> belongsTo(SubType::class);
    }
}
