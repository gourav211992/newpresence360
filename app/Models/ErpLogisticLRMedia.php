<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Traits\FileUploadTrait;


class ErpLogisticLRMedia extends Model
{
    use HasFactory, FileUploadTrait;

    protected $table = 'erp_logistics_lr_media';

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
        'lorry_column',
    ];

   protected $appends = ['file_url'];

    public function getFileUrlAttribute()
    {
        return asset('storage/lorry_files/' . $this->file_name);
    }




}
