<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ItemSubType extends Model
{
    use HasFactory;
    protected $connection = "mysql";

    protected $fillable = [
        'item_id',
        'sub_type_id'
    ];

    protected $table = "erp_item_subtypes";

    public function subType()
    {
        return $this -> belongsTo(SubType::class);
    }
}
