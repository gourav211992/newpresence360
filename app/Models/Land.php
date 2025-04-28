<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Land extends Model
{
    protected $table = 'erp_lands';
    
    use HasFactory;

    protected $fillable = [
        'series',
        'documentno',
        'land_no',
        'plot_no',
        'khasara_no',
        'area',
        'dimension',
        'address',
        'pincode',
        'latitude',
        'longitude',
        'cost',
        'status',
        'remarks',
        'organization_id',
        'user_id',
        'type'
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class, 'id','land_no');
    }

    public function recovery()
    {
        return $this->hasMany(Recovery::class, 'id','land_no');
    }

    public function serie()
    {
        return $this->belongsTo(Book::class, 'series');
    }
}

