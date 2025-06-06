<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpPsvMedia extends Model
{
    use HasFactory;
    protected $table = 'erp_psv_media';


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
        'manipulations', // Add any other fields that should be mass assignable
        'custom_properties',
        'generated_conversions',
        'responsive_images',
        'order_column',
    ];

    protected $appends = ['file_url'];

    public function getFileUrlAttribute()
    {
        if (isset($this->file_name)) {
            return Storage::url($this->file_name);
        }
        return '';
    }
}
