<?php
namespace App\Http\Controllers;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Models\ApprovalProcess;
use App\Models\Voucher;
use Illuminate\Support\Facades\Auth;

class ApprovalProcessController extends Controller
{
    public function approve(Request $request)
    {
        // Get the authenticated user's ID
        $userId = Helper::getAuthenticatedUser()->id;
        // Validate the request to ensure the voucher ID is present
        $validatedData = $request->validate([
            'voucher_id' => 'required|exists:erp_vouchers,id',  // Updated table name
        ]);
    
        // Retrieve the voucher by ID along with series and levels
        $voucher = Voucher::with([
            'series' => function ($s) {
                $s->select('id')->with(['levels']);  // Fetch levels associated with the series
            }
        ])->find($validatedData['voucher_id']);
    
        // Find the current approval level for the authenticated user
        $currentLevel = $voucher->series->levels
            ->where('user_id', $userId)  // user_id is now stored as an integer
            ->first();  // Get the first matching level
    
        // If no current level is found for the user, return an error
        if (!$currentLevel) {
            return redirect()->back()->with('error', 'You do not have the necessary permissions to approve this voucher.');
        }
    
        // Get all users who have approval rights at this level
        $approvalUsers = $voucher->series->levels->where('level', $currentLevel->level)->pluck('user_id');
    
        // Check the approval rights type (all/any) and update the approval status accordingly
        if ($currentLevel->rights == "all") {
            // Ensure that all users in this level have approved the voucher
            $approvedUsersCount = ApprovalProcess::whereIn('user_id', $approvalUsers)
                ->where('voucher_id', $voucher->id)
                ->count();
    
            // If all but the current user have approved, mark as completed; otherwise, partially approved
            if ($approvedUsersCount == $approvalUsers->count() - 1) {
                $voucher->approvalStatus = 'completed';
            } else {
                $voucher->approvalStatus = 'partially';
            }
        } else {
            // For "any" rights, mark as completed upon this approval
            $voucher->approvalStatus = 'completed';
        }
    
        // Update the voucher approval level and save the changes
        $voucher->approvalLevel = $currentLevel->level;
        $voucher->save();
    
        // Record this approval in the ApprovalProcess table
        ApprovalProcess::create([
            'user_id' => $userId,
            'voucher_id' => $voucher->id,
            'book_id' => $voucher->book_id,  // Ensure this field matches the migration
            'approved_at' => now(),  // Add a timestamp for the approval if required
        ]);
    
        return redirect()->back()->with('success', 'Approval process completed successfully.');
    }
    
    
}

