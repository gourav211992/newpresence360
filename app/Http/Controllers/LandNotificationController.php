<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Log;
use App\Helpers\Helper;

class LandNotificationController extends Controller
{
    public static function sendNotification($user, $data)
    {
        try {
            // Notify the user with the GeneralNotification
            $user->notify(new GeneralNotification($data));

            // Fetch the latest notification
            $notification = $user->notifications()->latest('id')->first();

            if ($notification) {
                // Retrieve authenticated user details
                $user_ch = Helper::getAuthenticatedUser();

                // Update the notification with additional details
                $notification->update([
                    'organization_id' => $user_ch->organization_id ?? null,
                    'auth_type' => get_class($user_ch) ?? null,
                    'auth_id' => $user_ch->id ?? null,
                    'type'=>$data['type'],
                    'type_id' => $data['source_id'],
                    'title' => $data['title'],
                    'description' => $data['description'],
                ]);
            } else {
                throw new \Exception('Notification was not inserted, no data to update.');
            }
        } catch (\Exception $e) {
            // Log any errors during the notification process
            Log::error('Failed to send notification or update:', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }

    public static function notifyLandParcelSubmission($approver, $landParcel)
    {
        $data = [
            'source_id' => $landParcel->id,
            'title' => 'Land Parcel Submission',
            'description' => "Dear {$approver->name}, a new land parcel [Parcel ID: {$landParcel->document_no}] has been submitted and is pending your approval.",
            'notifiable_id' => $approver->id,
            'notifiable_type' => get_class($approver),
            'type'=>get_class($landParcel),
            'created_at' => now(),
        ];

        self::sendNotification($approver, $data);
    }

    public static function notifyLandParcelApproved($user, $landParcel, $approver)
    {
        $data = [
            'source_id' => $landParcel->id,
            'title' => 'Land Parcel Approved',
            'description' => "Dear {$user->name}, the land parcel [Parcel ID: {$landParcel->document_no}] has been approved by {$approver->name}.",
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'type'=>get_class($landParcel),
            'created_at' => now(),

        ];

        self::sendNotification($user, $data);
    }
    public static function notifyLandParcelReject($user, $landParcel, $approver)
    {
        $data = [
            'source_id' => $landParcel->id,
            'title' => 'Land Parcel Rejected',
            'description' => "Dear {$user->name}, the land parcel [Parcel ID: {$landParcel->document_no}] has been rejected by {$approver->name}.",
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'type'=>get_class($landParcel),
            'created_at' => now(),

        ];

        self::sendNotification($user, $data);
    }
    public static function notifyLandPlotSubmission($approver, $landPlot)
    {
        $data = [
            'source_id' => $landPlot->id,
            'title' => 'Land Plot Submission',
            'description' => "Dear {$approver->name}, a new land plot [Plot ID: {$landPlot->document_no}] has been submitted for approval.",
            'notifiable_id' => $approver->id,
            'notifiable_type' => get_class($approver),
            'type'=>get_class($landPlot),
            'created_at' => now(),
        ];

        self::sendNotification($approver, $data);
    }

    public static function notifyLandPlotApproved($user, $landPlot, $approver)
    {
        $data = [
            'source_id' => $landPlot->id,
            'title' => 'Land Plot Approved',
            'description' => "Dear {$user->name}, the land plot [Plot ID: {$landPlot->document_no}] has been approved by {$approver->name}.",
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'type'=>get_class($landPlot),
            'created_at' => now(),
        ];

        self::sendNotification($user, $data);
    }
    public static function notifyLandPlotReject($user, $landPlot, $approver)
    {
        $data = [
            'source_id' => $landPlot->id,
            'title' => 'Land Plot Rejected',
            'description' => "Dear {$user->name}, the land plot [Plot ID: {$landPlot->document_no}] has been rejected by {$approver->name}.",
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'type'=>get_class($landPlot),
            'created_at' => now(),
        ];

        self::sendNotification($user, $data);
    }

    public static function notifyLeaseCreation($approver, $lease)
    {
       $data = [
            'source_id' => $lease->id,
            'title' => 'Lease Creation',
            'description' => "Dear {$approver->name}, a new lease [Lease ID: {$lease->document_no}] has been created and is pending your approval.",
            'notifiable_id' => $approver->id,
            'notifiable_type' => get_class($approver),
            'type'=>get_class($lease),
            'created_at' => now(),
        ];

        self::sendNotification($approver, $data);
    }

    public static function notifyLeaseApproved($user, $lease, $approver)
    {
        $data = [
            'source_id' => $lease->id,
            'title' => 'Lease Approved',
            'description' => "Dear {$user->name}, the lease [Lease ID: {$lease->document_no}] has been approved by {$approver->name}.",
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'type'=>get_class($lease),
            'created_at' => now(),
        ];

        self::sendNotification($user, $data);
    }

    public static function notifyLeaseReject($user, $lease, $approver)
    {
        $data = [
            'source_id' => $lease->id,
            'title' => 'Lease Rejected',
            'description' => "Dear {$user->name}, the lease [Lease ID: {$lease->document_no}] has been rejected by {$approver->name}.",
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'type'=>get_class($lease),
            'created_at' => now(),
        ];

        self::sendNotification($user, $data);
    }
    public static function notifyRecoveryUpdate($user, $lease)
    {
        $data = [
            'source_id' => $lease->id,
            'title' => 'Recovery Update',
            'description' => "Dear {$user->name}, recovery for lease [Lease ID: {$lease->document_no}] has been processed successfully.",
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'type'=>get_class($lease),
            'created_at' => now(),
        ];

        self::sendNotification($user, $data);
    }
}
