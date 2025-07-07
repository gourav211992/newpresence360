<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Deletable;


class LandLeasePlot extends Model
{
    use HasFactory,Deletable;

    protected $table = "erp_land_lease_plots";

    protected $fillable = [
        "lease_id",
        "land_parcel_id",
        "land_plot_id",
        "property_type",
        "lease_amount",
        "other_charges",
        "other_charges_json",
        "total_amount",
    ];
    public $referencingRelationships = [
        'lease' => 'lease_id',
        'plot' => 'land_plot_id',
        'land'=>'land_parcel_id'
    ];
    public static function createPlot($request, $lease, $edit_lease_id = null)
{
    $plots = $request->plot_details;
    $id=$lease->id;
    if($edit_lease_id!=null){
        LandLeasePlot::where('lease_id', $edit_lease_id)->delete();
        $id=$edit_lease_id;


    }

    // Delete all existing rows with the lease_id

    foreach ($plots as $data)
    {
        if (!empty($data['land_parcel_id']) || !empty($data['land_plot_id']))
        {
            LandLeasePlot::updateOrCreate([
                'lease_id' => $id,
                'land_parcel_id' => $data['land_parcel_id'],
                'land_plot_id' => $data['land_plot_id'],
                "property_type" => $data['land_property_type'],
                "lease_amount" => $data['land_lease_amount'],
                "other_charges" => $data['land_other_charges'] ?? 0.00,
                "other_charges_json" => $data['land_other_charges_json']=="undefined"?null:$data['land_other_charges_json'],
                "total_amount" => $data['land_total_amount'],
            ]);
        }
    }
}


    public function lease()
    {
        return $this->belongsTo(LandLease::class, 'lease_id');
    }

    public function plot()
    {
        return $this->belongsTo(LandPlot::class, 'land_plot_id');
    }

    public function land()
    {
        return $this->belongsTo(LandParcel::class, 'land_parcel_id');
    }
}
