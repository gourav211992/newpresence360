<?php

namespace App\Http\Controllers\LoanManagement;


use App\Http\Controllers\Controller;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Log;
use App\Helpers\Helper;

class LoanNotificationController extends Controller
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

    public static function notifyLoanSubmission($approver, $loan)
    {
        $data = [
            'source_id' => $loan->id,
            'title' => 'Loan Submission',
            'description' => "Dear {$approver->name}, a new loan [Loan ID: {$loan->appli_no}] has been submitted and is pending your approval.",
            'notifiable_id' => $approver->id,
            'notifiable_type' => get_class($approver),
            'type'=>get_class($loan),
            'created_at' => now(),
        ];

        self::sendNotification($approver, $data);
    }

    public static function notifyLoanApproved($user, $loan, $approver)
    {
        $data = [
            'source_id' => $loan->id,
            'title' => 'Loan Approved',
            'description' => "Congratulations, {$user->name} ! Your loan application (Application ID: {$loan->appli_no}) has been approved for an amount of {$loan->loanAppraisal->term_loan} at an interest rate of {$loan->loanAppraisal->interest_rate}% per annum.",
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'type'=>get_class($loan),
            'created_at' => now(),

        ];

        self::sendNotification($user, $data);
    }
    public static function notifyLoanReject($user, $loan, $approver)
    {
        $data = [
            'source_id' => $loan->id,
            'title' => 'Loan Rejected',
            'description' => "Dear {$user->name}, unfortunately, we are unable to approve your loan application (Application ID: {$loan->appli_no}) at this moment. We appreciate your interest in MIDC.",
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'type'=>get_class($loan),
            'created_at' => now(),

        ];

        self::sendNotification($user, $data);
    }
    public static function notifyLoanDisbursSubmission($approver, $loan)
    {
        $data = [
            'source_id' => $loan->id,
            'title' => 'Loan Disbursement Submission',
            'description' => "Dear {$approver->name}, a new loan disbursement [ ID: {$loan->disbursal_no}] has been submitted and is pending your approval.",
            'notifiable_id' => $approver->id,
            'notifiable_type' => get_class($approver),
            'type'=>get_class($loan),
            'created_at' => now(),
        ];

        self::sendNotification($approver, $data);
    }

    public static function notifyLoanDisbursApproved($user, $loan)
    {
        $DisDate = now()->toDateString();
        $data = [
            'source_id' => $loan->id,
            'title' => 'Loan Disbursement Approved',
            'description' => "Dear {$user->name}, the disbursement of {$loan->actual_dis} for your loan (Disbursement ID: {$loan->disbursal_no}) has been successfully completed on [$DisDate]. Please check your account for details. Kindly ensure the funds are utilized for the stated purpose. Thank you.",
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'type'=>get_class($loan),
            'created_at' => now(),

        ];

        self::sendNotification($user, $data);
    }
    public static function notifyLoanDisbursReject($user, $loan)
    {
        $data = [
            'source_id' => $loan->id,
            'title' => 'Loan Disburesement Rejected',
            "description" => "Dear {$user->name}, unfortunately, we are unable to proceed with the disbursement of your loan application (Disbursement ID: {$loan->disbursal_no}) at this moment due to certain constraints. For further details, please contact our support team. We appreciate your understanding and thank you for choosing MIDC.",
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'type'=>get_class($loan),
            'created_at' => now(),

        ];

        self::sendNotification($user, $data);
    }
    public static function notifyLoanRecoverSubmission($approver, $loan)
    {
        $data = [
            'source_id' => $loan->id,
            'title' => 'Loan Recovery Submission',
            'description' => "Dear {$approver->name}, a new loan Recovery [ ID: {$loan->document_no}] has been submitted and is pending your approval.",
            'notifiable_id' => $approver->id,
            'notifiable_type' => get_class($approver),
            'type'=>get_class($loan),
            'created_at' => now(),
        ];

        self::sendNotification($approver, $data);
    }

    public static function notifyLoanRecoverApproved($user, $loan)
    {
        $DisDate = now()->toDateString();
        $data = [
            'source_id' => $loan->id,
            'title' => 'Loan Recovery Approved',
            'description' => "Dear {$user->name}, the Recovery of {$loan->recovery_amnnt} for your loan ( Recovery ID: {$loan->document_no}) has been successfully completed on [$DisDate]. Please check your account for details. Kindly ensure the funds are utilized for the stated purpose. Thank you.",
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'type'=>get_class($loan),
            'created_at' => now(),

        ];

        self::sendNotification($user, $data);
    }
    public static function notifyLoanRecoverReject($user, $loan)
    {
        $data = [
            'source_id' => $loan->id,
            'title' => 'Loan Recovery Rejected',
            "description" => "Dear {$user->name}, unfortunately, we are unable to proceed with the Recovery of your Loan  (Recovery ID: {$loan->document_no}) at this moment due to certain constraints. For further details, please contact our support team. We appreciate your understanding and thank you for choosing MIDC.",
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'type'=>get_class($loan),
            'created_at' => now(),

        ];

        self::sendNotification($user, $data);
    }
    public static function notifyLoanSettleSubmission($approver, $loan)
    {
        $data = [
            'source_id' => $loan->id,
            'title' => 'Loan Settlement Submission',
            'description' => "Dear {$approver->name}, a new loan Settlement [ ID: {$loan->settle_document_no}] has been submitted and is pending your approval.",
            'notifiable_id' => $approver->id,
            'notifiable_type' => get_class($approver),
            'type'=>get_class($loan),
            'created_at' => now(),
        ];

        self::sendNotification($approver, $data);
    }

    public static function notifyLoanSettleApproved($user, $loan)
    {
        $DisDate = now()->toDateString();
        $data = [
            'source_id' => $loan->id,
            'title' => 'Loan Settlement Approved',
            'description' => "Dear {$user->name}, the Settlement of {$loan->settle_amnnt} for your loan ( Settlement ID: {$loan->settle_document_no}) has been successfully completed on [$DisDate]. Please check your account for details. Kindly ensure the funds are utilized for the stated purpose. Thank you.",
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'type'=>get_class($loan),
            'created_at' => now(),

        ];

        self::sendNotification($user, $data);
    }
    public static function notifyLoanSettleReject($user, $loan)
    {
        $data = [
            'source_id' => $loan->id,
            'title' => 'Loan Settlement Rejected',
            "description" => "Dear {$user->name}, unfortunately, we are unable to proceed with the Settlement of your Loan  (Settlement ID: {$loan->settle_document_no}) at this moment due to certain constraints. For further details, please contact our support team. We appreciate your understanding and thank you for choosing MIDC.",
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'type'=>get_class($loan),
            'created_at' => now(),

        ];

        self::sendNotification($user, $data);
    }
}
