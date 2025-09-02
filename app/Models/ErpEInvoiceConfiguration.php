<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpEInvoiceConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'gst_number',
        'client_id',
        'client_secret',
        'client_username',
        'client_password',
        'client_access_token',
    ];
}
