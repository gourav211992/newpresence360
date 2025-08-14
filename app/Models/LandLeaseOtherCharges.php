<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Deletable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LandLeaseOtherCharges extends Model
{
    use HasFactory,Deletable;

    protected $table = "erp_land_lease_other_charges";
    protected $fillable = [
        "lease_id",
        "land_parcel_id",
        "land_plot_id",
        "name",
        "percentage",
        "value",
        "total_other_amount",
    ];

    public $referencingRelationships = [
        'lease' => 'lease_id',
        'plot' => 'land_plot_id',
        'land'=>'land_parcel_id'
    ];

    public function lease()
    {
        return $this->belongsTo(LandLease::class, 'lease_id');
    }


    public static function createOtherCharges($request, $lease, $edit_lease_id = null)

    {

        $id=$lease->id;

        if($edit_lease_id!=null){
            LandLeaseOtherCharges::where('lease_id', $edit_lease_id)->delete();
            $id=$edit_lease_id;


        }
        try {
            DB::beginTransaction();
            foreach ($request->other_charges as $other_charge) {
                foreach ($other_charge as $data) {
                    LandLeaseOtherCharges::updateOrCreate([
                        'lease_id' => $id,
                        'land_parcel_id' => $data['land_parcel_id'],
                        'land_plot_id' => $data['land_plot_id'],
                        "name" => $data['name'],
                        "percentage" => $data['percentage'],
                        "value" => $data['value'],
                        "total_other_amount" => $data['total_other_amount'] ?? null,
                    ]);
                }
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {

            DB::rollBack();
            return false;
        }
    }


}
