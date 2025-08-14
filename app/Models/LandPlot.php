<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;

class LandPlot extends Model
{
    protected $table = 'erp_land_plots';

    use HasFactory,DefaultGroupCompanyOrg,Deletable;
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->document_status = $model->approvalStatus;
            $model->approval_level = $model->approvalLevel;
        });
    }

    public $referencingRelationships = [
        'land' => 'land_id'
    ];

    public function landParcel()
    {
        return $this->belongsTo(LandParcel::class, 'land_id');
    }


    // public function lease()
    // {
    //     return $this->hasOneThrough(LandLease::class, LandLeasePlot::class, 'land_plot_id', 'id', 'id', 'lease_id');
    // }

    public function leasePlots()
    {
        return $this->hasMany(LandLeasePlot::class, 'land_plot_id');
    }
    public function approvelworkflow()
    {
        return $this->hasMany(ApprovalWorkflow::class, 'book_id', 'book_id');
    }

    public function locations()
    {
        return $this->hasMany(PlotLocation::class, 'land_plot_id');
    }

}

