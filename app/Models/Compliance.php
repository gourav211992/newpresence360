<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Compliance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_compliances';

    protected $fillable = [
        'country_id',
        'tds_applicable',
        'wef_date',
        'tds_certificate_no',
        'tds_tax_percentage',
        'tds_category',
        'tds_value_cab',
        'tan_number',
        'gst_applicable',
        'gstin_no',
        'gst_registered_name',
        'gstin_registration_date',
        'msme_registered',
        'msme_no',
        'msme_type',
        'gst_certificate',
        'msme_certificate',
        'morphable_id' ,
        'morphable_type',
        'status',
    ];

    protected $casts = [
        'tds_applicable' => 'boolean',
        'msme_registered' => 'boolean',
        'gst_applicable' => 'boolean',
        'gst_certificate' => 'array',
        'msme_certificate' => 'array',
    ];

    public function morphable()
    {
        return $this->morphTo();
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function getGstCertificatesUrlsAttribute()
    {
        return $this->generateFileUrls($this->gst_certificate);
    }

    public function getMsmeCertificatesUrlsAttribute()
    {
        return $this->generateFileUrls($this->msme_certificate);
    }

    protected function generateFileUrls($filePaths)
    {
        if (is_array($filePaths)) {
            return array_map(function ($filePath) {
                return Storage::url($filePath);
            }, $filePaths);
        }
        return [];
    }
}
