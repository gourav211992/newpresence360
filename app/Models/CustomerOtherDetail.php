<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CustomerOtherDetail extends Model implements HasMedia
{
    use HasFactory, SoftDeletes,InteractsWithMedia;

    protected $table = 'erp_customer_other_details';

    protected $fillable = [
        'customer_id',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

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
        return $this->belongsTo(PaymentTerms::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents');
    }
}
