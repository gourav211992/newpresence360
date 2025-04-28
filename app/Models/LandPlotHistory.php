<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandPlotHistory extends Model
{
    protected $table = 'erp_land_plots_history';

    use HasFactory;

    protected $fillable = [
        'source_id', // Reference to the original land plot ID
        'series_id',
        'document_no',
        'land_id',
        'land_size',
        'land_location',
        'status',
        'khasara_no',
        'plot_area',
        'area_unit',
        'dimension',
        'plot_valuation',
        'address',
        'pincode',
        'type_of_usage',
        'remarks',
        'latitude',
        'longitude',
        'geofence_file',
        'organization_id',
        'user_id',
        'type',
        'plot_name',
        'attachments',
        'landable_type',
        'landable_id',
        'approvalLevel',
        'approvalStatus',
        'revision_number',
        'revision_date',
        'appr_rej_recom_remark',
        'appr_rej_doc',
        'appr_rej_behalf_of',
        'created_at', // You might want to include this if you're tracking when history was created
        'updated_at', // This too, if applicable
    ];

    // Specify the casts for the attributes
    protected $casts = [
        'land_size' => 'decimal:2',
        'plot_area' => 'decimal:2',
        'plot_valuation' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'attachments' => 'array', // Assuming it's JSON data
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'revision_date' => 'date',
    ];

    public function landParcel()
    {
        return $this->belongsTo(LandParcel::class, 'land_id');
    }


}

