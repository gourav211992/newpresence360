<?php

namespace App\Helpers;

use App\Models\Book;
use App\Models\DocumentApproval;
use App\Models\Recruitment\ErpRecruitmentJobReferral;
use App\Models\Recruitment\ErpRecruitmentJobRequestLog;
use App\Helpers\RGR\Constants as RgrConstant;

class CommonHelper
{
    const PAGE_LENGTH_10 = 10;
    const PAGE_LENGTH_20 = 20;
    const PAGE_LENGTH_50 = 50;
    const PAGE_LENGTH_100 = 100;
    const PAGE_LENGTH_2000 = 2000;
    const PAGE_LENGTH_1000 = 1000;
    const PAGE_LENGTH_10000 = 10000;
    const ACTIVE = 'active';
    const INACTIVE = 'inactive';
    const HIGH = 'high';
    const LOW = 'low';
    const MEDIUM = 'medium';
    const SCHEDULED = 'scheduled';
    const ASSIGNED = 'assigned';
    const APPROVED_FORWARD = 'approved-forward';
    const FINAL_APPROVED = 'final-approved';
    const APPROVED = 'approved';
    const JOB_CREATED = 'job-created';
    const ONHOLD = 'onhold';
    const REVOKED = 'revoked';
    const QUALIFIED = 'qualified';
    const NOT_QUALIFIED = 'not-qualified';
    const OPEN = 'open';
    const JOB = 'job';
	const INTERVIEW = 'interview';
	const CANDIDATE = 'candidate';
	const REJECTED = 'rejected';
	const PENDING = 'pending';
	const CLOSED = 'closed';
	const SELECTED = 'selected';
	const INTERNAL = 'internal';
	const SELF = 'self';
	const REFER = 'refer';
	const SEND_BACK = 'send-back';
    const PAYMENTS = 'payments';
    const RECEIPTS = 'receipts';
    const IAM_VENDOR = 'IAM-VENDOR';
    const SCANNED = 'scanned';
    const IN_PROGRESS = 'in_progress';
    const DEVIATION = 'deviation';
    const RECEIPT = 'receipt';
    const ISSUE = 'issue';
    const PUTAWAY = 'putaway';
    const UNLOADING = 'unloading';

    const PICKING = 'picking';
    const DISPATCH = 'dispatch';
    const UNLOADING_REQUIRED = 'unloading_required';
    const ENFORCE_UIC_SCANNING = 'enforce_uic_scanning';
    const TRANSFERRED = 'transferred';
    const PRODUCTIVITY = 'productivity';
    const INNOVATION = 'innovation';
    const QUALITY = 'quality';
    const COST = 'cost';
    const MORAL = 'moral';
    const DELIVERY = 'delivery';
    const SAFETY = 'safety';
    const AFTER_KAIZEN = 'after kaizen';
    const BEFORE_KAIZEN = 'before kaizen';
    const PO_PROCUREMENT_TYPE_VALUES = ['Buy', 'Lease'];

    const IMPROVEMENT_TYPES = [
        self::PRODUCTIVITY,
        self::INNOVATION,
        self::QUALITY,
        self::COST,
        self::MORAL,
        self::DELIVERY,
        self::SAFETY,
    ];

    const PAGE_LENGTHS = [
        self::PAGE_LENGTH_10,
        self::PAGE_LENGTH_20,
        self::PAGE_LENGTH_50,
        self::PAGE_LENGTH_100,
    ];

    const PRIORITY = [
       self::HIGH,
       self::MEDIUM,
       self::LOW
    ];

    const JOB_REQUEST_STATUS = [
        self::APPROVED_FORWARD,
        self::FINAL_APPROVED,
        self::REJECTED,
        self::REVOKED,
        self::PENDING
    ];

    const JOB_STATUS = [
        self::OPEN,
        self::CLOSED,
    ];

    const INTERVIEW_STATUS = [
        self::SCHEDULED,
        self::SELECTED,
        self::REJECTED,
        self::ONHOLD,
    ];

    const CANDIDATE_STATUS = [
        self::ASSIGNED,
        self::QUALIFIED,
        self::NOT_QUALIFIED,
        self::ONHOLD,
    ];

    const EMPLOYEMENT_TYPE = [
        'Full Time',
        'Part Time',
        'Trainee'
    ];

    const WORK_MODE = [
        'In Office',
        'REMOTE'
    ];

    const IMPROVEMENT_TYPE = [
        self::PRODUCTIVITY,
        self::INNOVATION,
        self::QUALITY,
        self::COST,
        self::MORAL,
        self::DELIVERY,
        self::SAFETY
    ];

    public static function dateFormat($date)
    {
        $date = $date ? date('d-m-Y', strtotime($date)) : '';
        return $date;
    }

    public static function dateFormat2($date)
    {
        $date = $date ? date('d/m/Y', strtotime($date)) : '';
        return $date;
    }

    public static function timeFormat($date)
    {
        $date = $date ? date('h:i A', strtotime($date)) : '';
        return $date;
    }

    public static function dateTimeFormat($input)
    {
        $date = new \DateTime($input);

        // Get day with suffix (st, nd, rd, th)
        $day = $date->format('j');
        $suffix = date('S', strtotime($input)); // gives st, nd, etc.
        $dayWithSuffix = $day . $suffix;

        // Build final string manually
        $formatted = $date->format('D') . " {$dayWithSuffix} " .
                    $date->format("M 'y - g:ia");

        return $formatted;
    }

    public static function getSummaryData($request, $user){
        $requestCount = ErpRecruitmentJobRequestLog::where('action_by',$user->id)
        ->where('action_by_type',$user->authenticable_type)
        ->count();

        $referralCount = ErpRecruitmentJobReferral::where('created_by',$user->id)
            ->count();
        return [
            'requestCount' =>  $requestCount,
            'referralCount' =>  $referralCount,
        ];
    }

    public static function getJobType($morphableType){
        if($morphableType == 'App\Models\GateEntryHeader'){
            $type = 'unloading';
        }elseif($morphableType == 'App\Models\MrnHeader'){
            $type = 'putaway';
        }elseif($morphableType == 'App\Models\ErpPlHeader'){
            $type = 'picking';
        }elseif($morphableType == 'App\Models\InspectionHeader'){
            $type = 'putaway';
        }elseif($morphableType == 'App\Models\ErpSaleInvoice'){
            $type = 'dispatch';
        }elseif ($morphableType == 'App\Models\ErpRgr') {
            $type = 'rgr';
        } else{
            $type = '';
        }

        return $type;
    }

    public static function getJobTransactionType($morphableType){
        if($morphableType == 'App\Models\GateEntryHeader'){
            $type = ConstantHelper::GATE_ENTRY_SERVICE_ALIAS;
        }elseif($morphableType == 'App\Models\MrnHeader'){
            $type = ConstantHelper::MRN_SERVICE_ALIAS;
        }elseif($morphableType == 'App\Models\ErpPlHeader'){
            $type = ConstantHelper::PL_SERVICE_ALIAS;
        }elseif($morphableType == 'App\Models\InspectionHeader'){
            // $type = ConstantHelper::INSPECTION_SERVICE_ALIAS;
            $type = ConstantHelper::MRN_SERVICE_ALIAS;
        }elseif($morphableType == 'App\Models\ErpSaleInvoice'){
            $type = ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS;
        }elseif($morphableType == 'App\Models\ErpMaterialIssueHeader'){
            $type = ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME;
        }elseif($morphableType == 'App\Models\ErpRgr'){
            $type = RgrConstant::SERVICE_ALIAS;
        }else{
            $type = '';
        }

        return $type;
    }

    public static function approveDocument($bookId, $docId, $revisionNumber, $remarks, $actionType, $modelName = null)
    {
        $user = Helper::getAuthenticatedUser();
        $book = Book::where('id', $bookId)->first();
        $bookTypeServiceAlias = $book?->service->alias;
        $docApproval = new DocumentApproval();
        $docApproval->document_type = $bookTypeServiceAlias;
        $docApproval->document_id = $docId;
        $docApproval->document_name = $modelName;
        $docApproval->approval_type = $actionType ?? null;
        $docApproval->approval_date = now();
        $docApproval->revision_number = $revisionNumber;
        $docApproval->remarks = $remarks;
        $docApproval->user_id = $user->auth_user_id;
        $docApproval->user_type = $user -> authenticable_type;
        $docApproval->save();

        return $docApproval;
    }

    public static function impactKaizenBg($model) {
            if (!$model || !isset($model->description) || $model->description === 'No Impact') {
                return '#ffffff';
            }
            return '#008000';
    }
}