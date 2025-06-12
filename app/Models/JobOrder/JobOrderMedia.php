<?php

namespace App\Models\JobOrder;

use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOrderMedia extends Model
{
    use HasFactory,FileUploadTrait;

    protected $table = 'erp_jo_media';

    protected $fillable = [
        'uuid',
        'model_name',
        'model_type',
        'collection_name',
        'name',
        'file_name',
        'mime_type',
        'disk',
        'size',
        'model_id',
        'manipulations',
        'custom_properties',
        'generated_conversions',
        'responsive_images',
        'order_column'
    ];
}
