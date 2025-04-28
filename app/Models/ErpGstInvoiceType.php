<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpGstInvoiceType extends Model
{
    use HasFactory;

    protected $connection = 'mysql_master';

    protected $fillable = [
        'name',
        'code'
    ];
}
