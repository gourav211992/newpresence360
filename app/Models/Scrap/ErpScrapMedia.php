<?php

namespace App\Models\Scrap;

use Illuminate\Database\Eloquent\Model;

class ErpScrapMedia extends Model
{
    protected $table = 'erp_scrap_media';
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
        'order_column',
    ];

    protected $appends = ['file_url'];

    public function getFileUrlAttribute()
    {
        if (isset($this->file_name)) {
            return \Storage::url($this->file_name);
        }
        return '';
    }

}
