<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubStoreType extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_store_id',
        'type'
    ];

    protected $table = 'erp_sub_store_types';

    public function sub_store()
    {
        return $this -> belongsTo(ErpSubStore::class, 'sub_store_id');
    }
}
