<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Helpers\Helper;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Deletable;

class LandLeaseAction extends Model
{
    use HasFactory,Deletable;
    protected $table = 'erp_land_leases_actions';

    public $referencingRelationships = [
        'lease' => 'source_id',
    ];

    protected $fillable = [
        'source_id', 'comment', 'action_date', 'attachments','user_id','user_type', 'status'
    ];

    // Relationship to the lease (foreign key source_id)
    public function lease()
    {
        return $this->belongsTo(LandLease::class, 'source_id');
    }

    /**
     * Perform action based on the lease_end_date.
     *
     * @param string $action - The action to perform (renew, close, terminate, reminder)
     * @return bool|string - true if the action is successful, or an error message.
     */
    public static function performAction($data)
    {
        $lease = LandLease::find($data->source_id);
        if (!$lease) {
            return ['type' => 'error', 'message' => 'Lease not found.'];
        }

        $leaseEndDate = Carbon::parse($lease->lease_end_date);
        $today = Carbon::today();
        $action = $data->action;
        $userData = Helper::userCheck();
        $user_id = $userData['user_id'];
        $user_type = $userData['type'];

        // Check if an action for this source_id already exists
        if (self::where('source_id', $lease->id)->exists()) {
            return ['type' => 'error', 'message' => 'An action for this lease already exists.'];
        }

        // Handle attachments and prepare JSON array
        $attachments = [];
        if (!empty($data->attachments)) {
            foreach ($data->attachments as $key => $document) {
                $extension = $document->getClientOriginalExtension();
                $documentName = "lease_{$lease->id}_{$data->action}_doc{$key}_" . time() . ".{$extension}";
                $document->move(public_path('documents'), $documentName);
                $attachments[] = $documentName;
            }
        }

        // Set up common data for creating an action
        $actionData = [
            'source_id' => $lease->id,
            'comment' => $data->comment ?? null,
            'attachments' => json_encode($attachments),
            'action_date' => $data->action_date ?? Carbon::now(),
            'user_id' => $user_id,
            'user_type' => $user_type,
            'status' => $action,
        ];

        switch ($action) {
            case 'reminder':
                self::create($actionData);
                return ['type' => 'success', 'message' => 'Reminder Added.'];

            case 'terminate':
                $terminationStatus = self::terminateLease($lease, $leaseEndDate);
                if ($terminationStatus === 'success') {
                    self::create($actionData);
                    return ['type' => 'success', 'message' => 'Lease Terminated.'];
                }
                return ['type' => 'error', 'message' => $terminationStatus];

            case 'close':
                $closureStatus = self::closeLease($lease, $leaseEndDate, $today);
                if ($closureStatus === 'success') {
                    self::create($actionData);
                    return ['type' => 'success', 'message' => 'Lease Closed.'];
                }
                return ['type' => 'error', 'message' => $closureStatus];

            case 'renew':
                $renewalStatus = self::renewLease($lease, $leaseEndDate, $today);
                if ($renewalStatus === 'success') {
                    self::create($actionData);
                    return ['type' => 'success', 'message' => 'Lease Renewed Successfully.'];
                }
                return ['type' => 'error', 'message' => $renewalStatus];

            default:
                return ['type' => 'error', 'message' => 'Invalid action.'];
        }
    }

    /**
     * Terminate the lease if the lease_end_date is greater than or equal to today.
     */
    public static function terminateLease($lease, $leaseEndDate)
    {
        if ($leaseEndDate->greaterThanOrEqualTo(Carbon::today())) {
            $lease->update(['approvalStatus' => 'terminate']);
            return 'success';
        }
        return 'Termination not possible as the lease end date has passed.';
    }

    /**
     * Close the lease if the lease_end_date is less than today.
     */
    public static function closeLease($lease, $leaseEndDate, $today)
    {
        if ($leaseEndDate->lessThan($today)) {
            $lease->update(['approvalStatus' => 'close']);
            return 'success';
        }
        return 'Closing not possible as the lease has not yet expired.';
    }

    /**
     * Renew the lease by duplicating it and its related data if lease_end_date has passed.
     */
    public static function renewLease($lease, $leaseEndDate, $today)
    {
        if ($leaseEndDate->lessThan($today)) {
            try {
                $newLease = $lease->replicate();
                $newLease->approvalStatus = 'draft';
                $newLease->document_date = $today->format('Y-m-d');
                $newLease->lease_start_date = $today->format('Y-m-d');
                $newLease->lease_end_date = $today->addYears($lease->lease_time)->format('Y-m-d');
                $newLease->save();

                // Clone related models with foreign key updates
                foreach ($lease->plots as $plot) {
                    $newPlot = $plot->replicate();
                    $newPlot->lease_id = $newLease->id;
                    $newPlot->save();
                }
                foreach ($lease->otherCharges as $charge) {
                    $newCharge = $charge->replicate();
                    $newCharge->lease_id = $newLease->id;
                    $newCharge->save();
                }
                if ($lease->address) {
                    $newAddress = $lease->address->replicate();
                    $newAddress->lease_id = $newLease->id;
                    $newAddress->save();
                }
                foreach ($lease->document as $document) {
                    $newDocument = $document->replicate();
                    $newDocument->lease_id = $newLease->id;
                    $newDocument->save();
                }

                $lease->update(['approvalStatus' => 'renew']);
                return 'success';

            } catch (Exception $e) {
                return $e->getMessage();
            }
        }
        return 'Renewal not possible as the lease is still valid.';
    }

}
