<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlotLocation extends Model
{
    use HasFactory;

    // Specify the table associated with the model
    protected $table = 'erp_plot_locations';

    protected $fillable = [
        'land_plot_id', // Foreign key
        'name',
        'latitude',
        'longitude',
    ];

    /**
     * Get the land parcel associated with the location.
     */
    public function landParcel()
    {
        return $this->belongsTo(LandPlot::class, 'land_plot_id');
    }
}


