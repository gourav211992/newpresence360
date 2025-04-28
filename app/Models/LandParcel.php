<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use App\Traits\Deletable;
use App\Helpers\Helper;
class LandParcel extends Model
{
    protected $table = 'erp_land_parcels';

    use HasFactory,DefaultGroupCompanyOrg,Deletable;



    protected $fillable = [
        'series_id',
        'document_no',
        'name',
        'description',
        'latitude',
        'longitude',
        'surveyno',
        'status',
        'khasara_no',
        'plot_area',
        'area_unit',
        'dimension',
        'land_valuation',
        'address',
        'district',
        'state',
        'country',
        'pincode',
        'remarks',
        'handoverdate',
        'attachments',
        'organization_id',
        'type',
        'service_item',
        'approval_level',
        'document_status',
        'revision_number',
        'revision_date',
        'created_by',
        'group_id',
        'book_id',
        'company_id',
        'document_date',
        'doc_number_type',
        'doc_reset_pattern',
        'doc_prefix',
        'doc_suffix',
        'doc_no',
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

    public function plots()
    {
        return $this->hasMany(LandPlot::class, 'land_id');
    }

    public function lease()
    {
        return $this->hasMany(LandParcel::class, 'land_id');
    }

    public function countryRelation()
    {
        return $this->belongsTo(Country::class, 'country');
    }

    public function documentType()
    {
        return $this->belongsTo(Book::class, 'book_id');
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
        return $this->hasMany(ApprovalWorkflow::class, 'book_id', 'book_id');
    }
    public $referencingRelationships = [
        'country' => 'country',
        'city' => 'city',
        'state'=>'state',

    ];
}
