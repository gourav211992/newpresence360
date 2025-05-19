<?php

namespace App\Http\Controllers\CRM;

use App\Exceptions\ApiGenericException;
use App\Helpers\ConstantHelper;
use App\Helpers\GeneralHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\CRM\ErpDiary;
use App\Models\CRM\ErpMeetingStatus;
use App\Models\ErpCustomer;
use App\Exports\crm\csv\ProspectsExport;
use App\Models\CRM\ErpFeedback;
use App\Models\CRM\ErpQuestion;
use App\Models\CRM\ErpSupplyPartner;
use App\Models\CRM\ErpSupplySplit;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProspectsController extends Controller
{
    public function index(Request $request){
        $pageLengths = ConstantHelper::PAGE_LENGTHS;
        $length = $request->length ? $request->length : ConstantHelper::PAGE_LENGTH_10;
        $user = Helper::getAuthenticatedUser();

        $customers = ErpCustomer::with(['meetingStatus','industry','supplierSplit' => function($q){
                        $q->select('customer_code','supply_partner_id','supply_percentage');
                    },'latestDiary' => function($q){
                        $q->select('customer_code','created_at');
                    }])
                    ->select('id','lead_status','sales_figure','company_name','industry_id','customer_code')
                    ->where('organization_id', $user->organization_id)
                    ->where('is_prospect', '1')
                    ->where(function($query) use($request){
                        GeneralHelper::applyUserFilter($query,'ErpCustomer');
                    });

                    if ($request->search) {
                        $customers->where(function($q) use ($request) {
                            $q->where('erp_customers.customer_code', 'like', '%' . $request->search . '%')
                                ->orWhere('erp_customers.company_name', 'like', '%' . $request->search . '%')
                                ->orWhereHas('meetingStatus', function ($q) use ($request) {
                                    $q->Where('title', 'like', '%' . $request->search . '%');
                                })
                                ->orWhereHas('industry', function ($q) use ($request) {
                                    $q->Where('name', 'like', '%' . $request->search . '%');
                                });
                        });
                    }

                    if ($request->status) {
                        $customers->where('erp_customers.lead_status', $request->status);
                    }

                    $customers = $customers->orderby('id','DESC')->paginate($length);

        return view('crm.prospects.index',[
            'customers' => $customers,
            'pageLengths' => $pageLengths,
        ]);
    }
    
    public function dashboard(Request $request){
        $user = Helper::getAuthenticatedUser();
        $prospectsData = $this->prospectsData($user,$request);
        return view('crm.prospects.dashboard',[
            'prospectsData' => $prospectsData
        ]);
    }

    private function prospectsData($user, $request){
        $meetingStatus = ErpMeetingStatus::where('erp_meeting_status.organization_id', $user->organization_id)
                        ->where('status',ConstantHelper::ACTIVE)
                        ->get();
        $customers = ErpCustomer::where('organization_id', $user->organization_id)
                        ->where(function($query) use($request){
                            GeneralHelper::applyUserFilter($query,'ErpCustomer');
                            GeneralHelper::applyDateFilter($query,$request,'created_at');
                            if ($request->has('customer_code')) {
                                $query->where('customer_code', $request->customer_code);
                            }
                        })
                        ->where('is_prospect', '1')
                        ->get();
        $totalSalesFigure = $customers->sum('sales_figure');
        $statusData = [];
        foreach ($meetingStatus as $status) {
            $customerCount = $customers->where('lead_status',$status->alias)->count();
            $salesFigureSum = $customers->where('lead_status',$status->alias)->sum('sales_figure');
            $status->prospects_count = $customerCount;
            $status->sales_figure_sum = $salesFigureSum;
            $status->sales_percentage = $totalSalesFigure > 0 ? round((($salesFigureSum / $totalSalesFigure) * 100), 2) : 0;
            $statusData[] = $status;
        }
        $statusData = collect($statusData)->sortBy('prospects_count')->values()->all();

        $limit = 5;
        $topProspects = ErpCustomer::with('industry')
                            ->where(function($query)use($request){
                                GeneralHelper::applyUserFilter($query,'ErpCustomer');
                            })
                            ->where('lead_status','!=',ConstantHelper::LOST)
                            ->where('is_prospect', '1')
                            ->select('id','customer_code','company_name','lead_status','industry_id','sales_figure')
                            ->orderBy('sales_figure', 'desc') 
                            ->limit($limit)
                            ->get();

        $salesFigureSum = $topProspects->sum('sales_figure');

        $lostProspects = ErpCustomer::where(function($query)use($request){
                                GeneralHelper::applyUserFilter($query,'ErpCustomer');
                            })
                            ->select('id','customer_code','company_name','lead_status','industry_id','sales_figure')
                            ->where('lead_status',ConstantHelper::LOST)
                            ->where('is_prospect', '1')
                            ->orderBy('sales_figure','desc')
                            ->limit($limit)
                            ->get();
        return [
            'statusData' => $statusData,
            'topProspects' => $topProspects,
            'totalSalesFigure' => $totalSalesFigure,
            'salesFigureSum' => $salesFigureSum,
            'lostProspects' => $lostProspects,
            'limit' => $limit,
        ];
    }

    public function prospectsCsv(Request $request)
    {
        $type = GeneralHelper::loginUserType();
        $user = Helper::getAuthenticatedUser();
        $customers = ErpCustomer::with(['meetingStatus','industry','latestDiary' => function($q){
                        $q->select('customer_code','created_at');
                    }])
                    ->select('id','lead_status','sales_figure','company_name','industry_id','customer_code')
                    ->where('organization_id', $user->organization_id)
                    ->where(function($query) use($request){
                        GeneralHelper::applyUserFilter($query,'ErpCustomer');
                    });

                    if ($request->search) {
                        $customers->where(function($q) use ($request) {
                            $q->where('erp_customers.customer_code', 'like', '%' . $request->search . '%')
                                ->orWhere('erp_customers.company_name', 'like', '%' . $request->search . '%')
                                ->orWhereHas('meetingStatus', function ($q) use ($request) {
                                    $q->Where('title', 'like', '%' . $request->search . '%');
                                })
                                ->orWhereHas('industry', function ($q) use ($request) {
                                    $q->Where('name', 'like', '%' . $request->search . '%');
                                });
                        });
                    }

                    if ($request->status) {
                        $customers->where('erp_customers.lead_status', $request->status);
                    }

        $customers = $customers->orderBy('id', 'DESC');

        $prospectsCsv = new ProspectsExport();
        $fileName = "temp/crm/csv/prospects.csv";
        $prospectsCsv->export($fileName,$customers);

        return redirect($fileName);
    }

    public function view($customerCode,Request $request){
        $user = Helper::getAuthenticatedUser();
        $request->merge(['customer_code' => $customerCode]);
        $customer = ErpCustomer::with('meetingStatus')->where('customer_code',$customerCode)->first();
        $diariesData = $this->diariesData($user,$request);
        $supplySplitData = $this->supplySplitData($user,$request);
        $meetingStatuses = ErpMeetingStatus::where('status',ConstantHelper::ACTIVE)->where('organization_id', $user->organization_id)->get();
        $partners = ErpSupplyPartner::where('status',ConstantHelper::ACTIVE)->where('organization_id', $user->organization_id)->get();
        $splitData = ErpSupplySplit::where('customer_code',$customerCode)->where('organization_id', $user->organization_id)->get();

        $questions = ErpQuestion::with(['feedback' => function($query) use($customerCode,$user){
                        $query->where('customer_code',$customerCode)
                        ->where('organization_id', $user->organization_id)
                        ->select('question_id','feedback','organization_id','customer_code');
                    }])
                    ->where('status',ConstantHelper::ACTIVE)
                    ->where('organization_id', $user->organization_id)
                    ->select('id','question','organization_id','status','sequence')
                    ->orderBy('sequence','asc')
                    ->get();
                    // dd($questions->toArray());

        $feedbacks = ErpFeedback::with('question')
                    ->where('customer_code',$customerCode)
                    ->where('organization_id', $user->organization_id)
                    ->get();

        return view('crm.prospects.view',[
            'customer' => $customer,
            'diariesData' => $diariesData,
            'meetingStatuses' => $meetingStatuses,
            'partners' => $partners,
            'splitData' => $splitData,
            'feedbacks' => $feedbacks,
            'questions' => $questions,
            'supplySplitData' => $supplySplitData,
        ]);
    }

    private function supplySplitData($user, $request){
        $supplyPartners = ErpSupplyPartner::select('id','name','organization_id')
                        ->where('organization_id', $user->organization_id)
                        ->where('status',ConstantHelper::ACTIVE)
                        ->get();
        
        $supplySplit = ErpSupplySplit::where('organization_id', $user->organization_id)
                        ->where('customer_code',$request->customer_code)
                        ->orderBy('id','DESC')
                        ->get();

        $totalSupplySplitPerc = $supplySplit->sum('supply_percentage');

        foreach ($supplyPartners as $supplyPartner) {
            $supplySplitPerc = $supplySplit->where('supply_partner_id',$supplyPartner->id)->sum('supply_percentage');
            $supplyPartner->percentage = $totalSupplySplitPerc > 0 ?  round((($supplySplitPerc/$totalSupplySplitPerc)*100),2) : 0;
            $supplyPartner->color_code = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
           
        }

        return [
            'supplyPartners' => $supplyPartners,
        ];
    }

    private function diariesData($user, $request){
        $meetingStatus = ErpMeetingStatus::where('erp_meeting_status.organization_id', $user->organization_id)
                        ->where('status',ConstantHelper::ACTIVE)
                        ->get();
        $diaries = ErpDiary::where('organization_id', $user->organization_id)
                        ->where('customer_code',$request->customer_code)
                        ->orderBy('id','DESC')
                        ->get();

        $totalDiaries = $diaries->count();

        foreach ($meetingStatus as $status) {
            $diariesCount = $diaries->where('meeting_status_id',$status->id)->count();
            $status->diaries_count = $diariesCount;
            $status->diaries_percentage = $totalDiaries > 0 ?  (($diariesCount/$totalDiaries)*100) : 0;
           
        }
        $latestDiaries = $diaries->take(5);
        return [
            'meetingStatus' => $meetingStatus,
            'latestDiaries' => $latestDiaries,
        ];

    }

    public function updateLeadStatus($id,Request $request){
        $erpcustomer = ErpCustomer::find($id);
        $erpcustomer->lead_status = $request->status ? $request->status : NULL;
        $erpcustomer->status = $request->status == 'won' ? ConstantHelper::ACTIVE : ConstantHelper::PENDING;
        $erpcustomer->save();

        if($request->status){
            $meetingStatus = $meetingStatus = ErpMeetingStatus::where('alias',$request->status)->first();
            $erpDiary = ErpDiary::where('customer_id',$id)->first();
            $erpDiary->meeting_status_id = $meetingStatus->id;
            $erpDiary->save();
        }

        return response()->json([
            'message' => 'Status updated successfully.',
            'status' => true
        ]);

    }

    public function supplySplit(Request $request){
        $validator = Validator::make(
            $request->all(),
            [
                "supply_partner_id" => [
                    "required"
                ],
                "supply_percentage" => [
                   "required"
                ],
            ],[
                "supply_percentage.required" => "Supply percentage is required.",
                "supply_partner_id.required" => "Supply partner is required.",
            ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            \DB::beginTransaction();
            
                $supplysplit = new ErpSupplySplit();
                $supplysplit->fill($request->all());
                $supplysplit->save();

                $splitData = ErpSupplySplit::where('customer_code',$request->customer_code)
                            ->where('organization_id', $request->organization_id)
                            ->get();
    
            \DB::commit();

            return [
                'data' => view('crm.prospects.supply-split-list', ['splitData' => $splitData])->render(),
                'message' => 'HTML render',
                "status" => 200
            ];
        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }

    }

    public function removeSupplySplit($id){
        $supplysplit = ErpSupplySplit::find($id);
        $supplysplit->delete();
        return [
            "data" => $supplysplit,
            "message" => "Split removed.",
            "status" => 200
        ];
    }
}
