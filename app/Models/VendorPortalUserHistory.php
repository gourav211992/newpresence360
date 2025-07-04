<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPortalUserHistory extends Model
{
    protected $connection = 'mysql';
    use HasFactory;

    protected $table = 'erp_vendor_portal_users_history';

    protected $fillable = [
        'source_id',
        'vendor_id',
        'user_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
