<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Models\NumberPattern;
use App\Models\RolePermission;
use App\Models\RoleUser;
use App\Models\User;
use Auth;
use DB;
use Illuminate\Http\Request;

class TestingController extends Controller
{
    public function testing(){


        $a = Helper::createRevisionHistory('erp_purchase_orders','id',1);
        dd($a);

        $a = InventoryHelper::settlementOfInventoryAndStock(2,2,'mrn','approved');
        dd($a);
        $role_id=RoleUser::where('user_id',Auth::guard('web')->user()->id)->value('id');
        $permissionsId=RolePermission::where('role_id',$role_id)->pluck('permission_id')->toArray();
        // $permittedMenus=
        return response()->json($permissionsId);

        // // Reset Patterns Daily
        // NumberPattern::where('series_numbering','Auto')->where('reset_pattern','Daily')->each(function (NumberPattern $pattern) {
        //     $pattern->current_no = $pattern->starting_no ?? 1;
        //     $pattern->save();
        // });

        // // Reset Patterns Monthly
        // NumberPattern::where('series_numbering','Auto')->where('reset_pattern','Monthly')->each(function (NumberPattern $pattern) {
        //     $pattern->current_no = $pattern->starting_no ?? 1;
        //     $pattern->save();
        // });

        // // Reset Patterns Quarterly
        // NumberPattern::where('series_numbering','Auto')->where('reset_pattern','Quarterly')->each(function (NumberPattern $pattern) {
        //     $pattern->current_no = $pattern->starting_no ?? 1;
        //     $pattern->save();
        // });

        // // Reset Patterns Yearly
        // NumberPattern::where('series_numbering','Auto')->where('reset_pattern','Yearly')->each(function (NumberPattern $pattern) {
        //     $pattern->current_no = $pattern->starting_no ?? 1;
        //     $pattern->save();
        // });
    }
    public static function actionButtonDisplayForLegal($bookId, $docStatus, $docId, $docApprLevel, int $createdBy = 0, $creatorType, $revisionNumber = 0)
    {
        $draft = false;
        $submit = false;
        $approve = false;
        $reject = false;
        $assign=false;
        $close=false;
        $view=false;
        $edit=false;


        $user = self::getAuthenticatedUser();
        $book = Book::where('id', $bookId)->first();
        $bookTypeServiceAlias = $book?->service?->alias;
        $currUser = Helper::userCheck();

        if ($docStatus == ConstantHelper::DRAFT || $docStatus == ConstantHelper::REJECTED) {
            $draft = true;
            $submit = true;
            $edit=true;
        }
        if ($docStatus == ConstantHelper::SUBMITTED) {
            $approvalWorkflow = BookLevel::where('book_id', $book->id)
                ->where('organization_id', $user->organization_id)
                ->where('level', 1)
                ->whereHas('approvers', function ($approver) use ($currUser) {
                    $approver->where('user_id', $currUser['user_id'])
                        ->where('user_type', $currUser['type']);
                })
                ->orderByDesc('min_value')
                ->first();

            if ($approvalWorkflow) {
                $approve = true;
                $reject=true;
            }
        }
        if ($docStatus == ConstantHelper::APPROVED || $docStatus == ConstantHelper::APPROVAL_NOT_REQUIRED) {
            $assign=true;
            $reject=true;
            $view=true;
        }
        if ($docStatus == ConstantHelper::PARTIALLY_APPROVED) {
            $assign=true;
            $reject=true;
            $view=true;
      }
        if ($docStatus == ConstantHelper::ASSIGNED) {
            $close=true;
            $view=true;
        }
        //Creator of document cannot approve
        if ($user->id === $createdBy && self::userCheck()['type'] == $creatorType) {
            $approve = false;
        }
        return [
            'draft' => $draft,
            'submit' => $submit,
            'approve' => $approve,
            'reject'=>$reject,
            'assign'=> $assign,
            'close'=>$close,
            'view'=>$view,
            'edit'=>$edit,
        ];
    }

}
