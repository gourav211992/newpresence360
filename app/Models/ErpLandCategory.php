<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpLandCategory extends Model
{
    use HasFactory;

    // Define the table name if different from the convention
    protected $table = 'erp_land_categories';

    // Define the fillable fields
    protected $fillable = [
        'category_name',
        'status',
    ];

    // Add any relationships, accessors, or additional methods if needed
}
