<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PLGroups extends Model
{
    protected $table = 'erp_p_l_groups';

    use HasFactory;
    protected $fillable = [
        'name',
        'group_ids'
    ];

    protected $casts = [  
        'group_ids' => 'array', 
    ];

    public function groups()  
    {  
        return Group::whereIn('id', $this->group_ids);  
    } 

    public function setGroupIdsAttribute($value)  
    {  
        // Convert the incoming array of IDs to integers  
        $this->attributes['group_ids'] = json_encode(array_map('intval', $value));  
    }
}
