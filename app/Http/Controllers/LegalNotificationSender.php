<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Helpers\Helper;
use App\Models\User;
use App\Models\Legal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LegalNotification;
use Illuminate\Support\Facades\Log;



class LegalNotificationSender extends Controller
{
    // Send Request Assignment Notification
    public static function sendNotification($user, $data)
    {
       
            // Notify the user with the GeneralNotification
            $user->notify(new LegalNotification($data));

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
       
    }

    public static function notifyLegalApproved($user, $legal, $approver)
    {
        $data = [
            'source_id' => $legal->id,
            'title' => 'Legal Approved',
            'description' => "Dear {$user->name}, the Legal Application [ID: {$legal->requestno}] has been approved by {$approver->name}.",
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'type'=>get_class($legal),
            'created_at' => now(),

        ];

        self::sendNotification($user, $data);
    }
    public static function notifyLegalReject($user, $legal, $approver)
    {
        $data = [
            'source_id' => $legal->id,
            'title' => 'Legal Rejected',
            'description' => "Dear {$user->name}, the Legal Application [ID: {$legal->requestno}] has been rejected by {$approver->name}.",
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'type'=>get_class($legal),
            'created_at' => now(),

        ];

        self::sendNotification($user, $data);
    }
    public static function sendLegalSubmission($user, $legal)
    {
        $message = "Dear {$user->name}, a new Legal Application [ID: {$legal->requestno}] has been submitted and is pending your approval.";
        $data = [
            'source_id' => $legal->id,
            'title' => "Legal Application Submission :{$legal->requestno}",
            'description' => $message,
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'created_at'=>now()
        ];

        try {
            $user->notify(new LegalNotification($data));
           // broadcast(new LegalNotification($data));

            DB::enableQueryLog();
            $notification = $user->notifications()->latest('id')->first();

            if ($notification) {
                $user_ch = Helper::getAuthenticatedUser();


                $notification->update([
                    'organization_id' => $user_ch->organization_id ?? null,
                    'auth_type' => get_class($user_ch) ?? null,
                    'auth_id' => $user_ch->id ?? null,
                    'type_id' => $legal->id,
                    'title' => $data['title'],
                    'description' => $data['description'],
                ]);
                $queries = DB::getQueryLog();
                Log::info('Update Query Log:', end($queries));
            } else {
                throw new \Exception('Notification was not inserted, no data to update.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notification or update:', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }


    }
    public static function sendRequestAssignmentNotification( $user, Legal $legal)
    {
        $message = "Dear {$user->name}, a new Request (Request ID: {$legal->requestno}) has been assigned to you. Please review the details and take the necessary action. Thank you for your prompt attention.";

        $data = [
            'source_id' => $legal->id,
            'title' => "Request Assigned for Ticket ID:{$legal->requestno}",
            'description' => $message,
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'created_at'=>now()
        ];

        try {
            $user->notify(new LegalNotification($data));
           // broadcast(new LegalNotification($data));

            DB::enableQueryLog();
            $notification = $user->notifications()->latest('id')->first();

            if ($notification) {
                $user_ch = Helper::getAuthenticatedUser();


                $notification->update([
                    'organization_id' => $user_ch->organization_id ?? null,
                    'auth_type' => get_class($user_ch) ?? null,
                    'auth_id' => $user_ch->id ?? null,
                    'type_id' => $legal->id,
                    'title' => $data['title'],
                    'description' => $data['description'],
                ]);
                $queries = DB::getQueryLog();
                Log::info('Update Query Log:', end($queries));
            } else {
                throw new \Exception('Notification was not inserted, no data to update.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notification or update:', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }


    public static function sendNewCommentAddedNotification( $user, Legal $legal)
    {
        $commentDate = now()->toDateString();
        $commenterName = Helper::getAuthenticatedUser()->name;

        $message = "Dear {$user->name}, a new comment has been added to your legal ticket (Ticket ID: {$legal->requestno}) by {$commenterName} on {$commentDate}. Please log in to your account to review the comment. Thank you.";

        $data = [
            'source_id' => $legal->id,
            'title' => "New Comment for Ticket ID:{$legal->requestno}",
            'description' => $message,
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'created_at'=>now()
        ];

       

        try {
            $user->notify(new LegalNotification($data));
            //broadcast(new LegalNotification($data));
           
            $notification = $user->notifications()->latest('id')->first();

            //dd($notification);
            if ($notification) {
                $user_ch = Helper::getAuthenticatedUser();

                $notification->update([
                    'organization_id' => $user_ch->organization_id ?? null,
                    'auth_type' => get_class($user_ch) ?? null,
                    'auth_id' => $user_ch->id ?? null,
                    'type_id' => $legal->id,
                    'title' => $data['title'],
                    'description' => $data['description'],
                ]);
            } else {
                throw new \Exception('Notification was not inserted, no data to update.');
            }
        } catch (\Exception $e) {
          
            Log::error('Failed to send notification or update:', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }


    // Send Request Resolved Notification
    public static function sendRequestResolvedNotification($user, Legal $legal)
    {

        $resolvedDate = now()->toDateString();

        $message = "Dear {$user->name}, your legal ticket (Ticket ID: {$legal->requestno}) has been closed on {$resolvedDate}. If you have any further questions or require additional assistance, please contact our support team. Thank you for using our ticketing system.";

        $data = [
            'source_id' => $legal->id,
            'title' => "Request Resolved for Ticket ID:{$legal->requestno}",
            'description' => $message,
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'created_at'=>now()
        ];

        try {
            $user->notify(new LegalNotification($data));
            //broadcast(new LegalNotification($data));
            $notification = $user->notifications()->latest('id')->first();

            if ($notification) {
                $user_ch = Helper::getAuthenticatedUser();


                $notification->update([
                    'organization_id' => $user_ch->organization_id ?? null,
                    'auth_type' => get_class($user_ch) ?? null,
                    'auth_id' => $user_ch->id ?? null,
                    'type_id' => $legal->id,
                    'title' => $data['title'],
                    'description' => $data['description'],
                ]);
            } else {
                throw new \Exception('Notification was not inserted, no data to update.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notification or update:', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }

    // Send Document Upload Notification
    public static function sendDocumentUploadNotification( $user, Legal $legal)
    {
        $uploadDate = now()->toDateString();

        $message = "Dear {$user->name}, a new document has been uploaded for your legal ticket (Ticket ID: {$legal->requestno}) on {$uploadDate}. Please log in to your account to view the document. Thank you for your prompt attention.";

        $data = [
            'source_id' => $legal->id,
            'title' => "New Document Upload for Ticket ID:{$legal->requestno}",
            'description' => $message,
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'created_at'=>now()
        ];

        try {
            $user->notify(new LegalNotification($data));
            //broadcast(new LegalNotification($data));
            $notification = $user->notifications()->latest('id')->first();

            if ($notification) {
                $user_ch = Helper::getAuthenticatedUser();


                $notification->update([
                    'organization_id' => $user_ch->organization_id ?? null,
                    'auth_type' => get_class($user_ch) ?? null,
                    'auth_id' => $user_ch->id ?? null,
                    'type_id' => $legal->id,
                    'title' => $data['title'],
                    'description' => $data['description'],
                ]);
            } else {
                throw new \Exception('Notification was not inserted, no data to update.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notification or update:', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }
}
