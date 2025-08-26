<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FileUploadTrait;

class ErpRgrMedia extends Model
{
    use HasFactory,FileUploadTrait;

    protected $table = 'erp_rgr_media';

    public $timestamps = true;

    protected $fillable = [
        'model_id',
        'model_type',
        'uuid',
        'model_name',
        'collection_name',
        'name',
        'file_name',
        'mime_type',
        'disk',
        'conversions_disk',
        'size',
        'manipulations',
        'custom_properties',
        'generated_conversions',
        'responsive_images',
        'order_column',
    ];

    protected $casts = [
        'manipulations'         => 'array',
        'custom_properties'     => 'array',
        'generated_conversions' => 'array',
        'responsive_images'     => 'array',
    ];

     public function model()
    {
        return $this->morphTo();
    }
}
