<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPortalBookHistory extends Model
{
    use HasFactory;

    protected $table = 'erp_vendor_portal_books_history';

    protected $fillable = [
        'source_id',
        'vendor_id',
        'service_id',
        'book_id',
    ];
}
