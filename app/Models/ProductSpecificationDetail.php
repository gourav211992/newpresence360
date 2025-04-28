<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Deletable;

class ProductSpecificationDetail extends Model
{
    use HasFactory,SoftDeletes,Deletable;

    protected $table = 'erp_product_specification_details';

    protected $fillable = [
        'product_specification_id',
        'name',
        'description',
    ];

    public function productSpecification()
    {
        return $this->belongsTo(ProductSpecification::class, 'product_specification_id');
    }

    public function itemSpecifications()
    {
        return $this->hasMany(ItemSpecification::class);
    }
}
