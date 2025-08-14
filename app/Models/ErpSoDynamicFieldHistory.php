<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSoDynamicFieldHistory extends Model
{
    use HasFactory;
    protected $table = 'erp_so_dynamic_fields_history';

    
    public function so()
    {
        return $this -> belongsTo(ErpSaleOrderHistory::class, 'header_id');
    }
}
