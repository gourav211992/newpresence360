<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPortalUser extends Model
{
    protected $connection = 'mysql';
    use HasFactory;

    public $timestamps = false;
    public $fillable = [
        'vendor_id',
        'user_id',
        'status',
    ];

    protected $table = 'erp_vendor_portal_users';

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
