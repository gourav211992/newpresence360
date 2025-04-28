<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpItem extends Model
{
    use HasFactory;

    public function hsn()
    {
        return $this -> hasOne(ErpHsn::class, 'id', 'hsn_id');
    }
}
