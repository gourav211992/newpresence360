<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;


class VendorOtherDetail extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia;

    protected $table = 'erp_vendor_other_detail';

    // Fillable fields
    protected $fillable = [
        'vendor_id',
        'currency_id',
        'payment_terms_id',
        'related_party',
        'email',
        'phone',
        'mobile',
        'whatsapp_number',
        'notification',
        'pan_number',
        'tin_number',
        'aadhar_number',
        'opening_balance',
    ];

    protected $casts = [
        'notification' => 'array',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function paymentTerms()
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents');
    }

}
