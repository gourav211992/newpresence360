<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandParcelHistory extends Model
{ protected $table = 'erp_land_parcels_history';

    use HasFactory;

    protected $fillable = [
        'source_id','series_id', 'document_no', 'name', 'description', 'latitude', 'longitude',
        'surveyno', 'status', 'khasara_no', 'plot_area', 'area_unit', 'dimension',
        'land_valuation', 'address', 'district', 'state', 'country', 'pincode',
        'remarks', 'handoverdate', 'attachments','organization_id','user_id','type','service_item','approvalLevel','approvalStatus',
        'revision_number','revision_date','created_by'
    ];

    protected $casts = [
        'attachments' => 'array',
    ];



    public function locations()
    {
        return $this->hasMany(Location::class, 'land_parcel_id');
    }

    public function plot()
    {
        return $this->hasMany(LandPlot::class, 'land_id');

    }

    public function countryRelation()
    {
        return $this->belongsTo(Country::class, 'country');
    }

    public function cityRelation()
    {
        return $this->belongsTo(City::class, 'city');
    }

    public function stateRelation()
    {
        return $this->belongsTo(State::class, 'state');
    }

    public function approvelworkflow()
    {
        return $this->hasMany(ApprovalWorkflow::class, 'book_id', 'series_id');
    }
}
