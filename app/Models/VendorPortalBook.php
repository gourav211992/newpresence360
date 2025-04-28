<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPortalBook extends Model
{
    use HasFactory;

    public $table = 'erp_vendor_portal_books';
    public $timestamps = false;
    protected $fillable = [
        'vendor_id',
        'service_id',
        'book_id'
    ];

}
