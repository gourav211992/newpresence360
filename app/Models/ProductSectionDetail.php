<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Deletable;


class ProductSectionDetail extends Model
{
    use HasFactory,SoftDeletes,Deletable;

    protected $table = 'erp_product_section_details';

    protected $fillable = [
        'name',
        'description',
        'station_id',
    ];
    
    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    public function section()
    {
        return $this->belongsTo(ProductSection::class, 'section_id');
    }
}
