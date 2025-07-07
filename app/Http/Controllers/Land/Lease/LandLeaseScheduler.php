<?php

namespace App\Models;

use App\Traits\Deletable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LandLeaseScheduler extends Model
{
    use HasFactory, Deletable;

    protected $table = "erp_land_lease_schedulers";

    protected $fillable = [
        "lease_id",
        "installment_cost",
        "due_date",
        "status",
        'tax_amount'
    ];

    public $referencingRelationships = [
        'lease' => 'lease_id'
    ];
    public function lease()
    {
        return $this->belongsTo(LandLease::class, 'lease_id');
    }
    public static function createUpdateScheduler($request, $leaseId)
    {
        $scheduleData = $request->input('sc'); // Retrieve `sc` array from the request

        if (is_array($scheduleData) && count($scheduleData) > 0) {
            if (!empty($leaseId)) {
                // Delete all existing schedules for the given lease ID
                LandLeaseScheduler::where('lease_id', $leaseId)->delete();
            }
            // Insert the new schedules
            foreach ($scheduleData as $schedule) {
                $dueDate = Carbon::createFromFormat('m/d/Y', $schedule['due_date'])->format('Y-m-d');
                LandLeaseScheduler::create([
                    'lease_id' => $leaseId,
                    'tax_amount'=>$schedule['tax_amount'],
                    'installment_cost' => $schedule['installment_cost'],
                    'due_date' => $dueDate,
                    'status' => $schedule['status'],
                ]);
            }
        }

        return true;
    }
}
