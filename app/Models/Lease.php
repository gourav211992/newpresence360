<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lease extends Model
{
    // Specify the table associated with the model
    protected $table = 'erp_leases';

    // Specify the attributes that are mass assignable
    protected $fillable = [
        'series',
        'lease_no',
        'land_no',
        'khasara_no',
        'area_sqft',
        'plot_details',
        'pincode',
        'cost',
        'customer',
        'lease_time',
        'lease_cost',
        'period_type',
        'repayment_period',
        'installment_cost',
        'document',
        'remarks',
        'agreement_no',
        'date_of_agreement',
        'organization_id',
        'user_id',
        'type'
    ];

    // If you have timestamps (created_at, updated_at) columns
    public $timestamps = true;

    // Optionally, you can define date attributes, accessors, or mutators

    public function land()
    {
        return $this->belongsTo(Land::class, 'land_no','id');
    }

    public function recovery()
    {
        return $this->hasMany(Recovery::class, 'land_no', 'land_no');
    }

    public function serie()
    {
        return $this->belongsTo(Book::class, 'series');
    }

    public function cust()
    {
        return $this->belongsTo(Customer::class, 'customer');
    }
}

