<?php

namespace App\Models;

use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionMedia extends Model
{
    use HasFactory,FileUploadTrait;

    protected $table = 'erp_insp_media';

    protected $fillable = [
        'model_type',
        'model_id',
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
}
