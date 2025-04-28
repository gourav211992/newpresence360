<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recovery extends Model
{
    // Specify the table if it's not the plural form of the model name
    protected $table = 'erp_recoveries';

    // The attributes that are mass assignable
    protected $fillable = [
        'series',
        'document_no',
        'land_no',
        'khasara_no',
        'area_sqft',
        'plot_details',
        'pincode',
        'cost',
        'customer',
        'lease_time',
        'lease_cost',
        'bal_lease_cost',
        'received_amount',
        'date_of_payment',
        'payment_mode',
        'reference_no',
        'bank_name',
        'document',
        'remarks',
        'organization_id',
        'user_id',
        'type'
    ];

    // Optionally, if you have timestamps in the table
    public $timestamps = true;

    // If your table has a primary key other than 'id'
    protected $primaryKey = 'id';

    public function land()
    {
        return $this->belongsTo(Land::class, 'land_no','id');
    }

    public function lease()
    {
        return $this->belongsTo(Lease::class, 'land_no');
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

