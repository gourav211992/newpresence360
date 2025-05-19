<?php

namespace App\Http\Controllers\CRM;

use App\Helpers\ConstantHelper;
use Auth;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\ErpCustomer;
use App\Models\CRM\ErpDiary;
use App\Helpers\FileUploadHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Lib\Validation\ErpDiary as Validator;
use App\Models\Address;
use App\Models\City;
use App\Models\Country;
use App\Models\CRM\ErpDiaryTagPeople;
use App\Models\CRM\ErpMeetingObjective;
use App\Models\CRM\ErpMeetingStatus;
use App\Models\ErpAddress;
use App\Models\ErpDiaryAttachment;
use App\Models\State;
use App\Services\CommonService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use App\Exceptions\ApiGenericException;
use App\Helpers\GeneralHelper;
use App\Models\CRM\ErpIndustry;
use App\Models\CRM\ErpFeedback;
use App\Models\CRM\ErpLeadContacts;
use App\Models\CRM\ErpLeadSource;
use App\Models\CRM\ErpProductCategory;
use App\Models\ErpCurrency;

class NotesController extends Controller
{
    protected $commonService;
    protected $fileUploadHelper;

    public function __construct(CommonService $commonService, FileUploadHelper $fileUploadHelper)
    {
        $this->commonService = $commonService;
        $this->fileUploadHelper = $fileUploadHelper;
    }

    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        
        $customers = ErpCustomer::where(function($query){
                            GeneralHelper::applyUserFilter($query,'ErpCustomer');
                        })
                        ->get();

        $erpDiaries = ErpDiary::with(['customer','attachments','createdByEmployee' => function($q){
            $q->select('id','name');
        },'createdByUser' => function($q){
            $q->select('id','name');
        }])
            ->where(function($query) use($request){
                GeneralHelper::applyDiaryFilter($query);
                if($request->customer_id){
                    $query->where('customer_id',$request->customer_id);
                }
            })
            ->whereDate('created_at', date('Y-m-d'))
            ->orderBy('id','desc')
            ->paginate(ConstantHelper::PAGE_LENGTH_10);

        $meetingObjectives = ErpMeetingObjective::where('status',ConstantHelper::ACTIVE)->get();
        
        return view('crm.notes.index', [
            'customers' => $customers,
            'erpDiaries' => $erpDiaries,
            'meetingObjectives' => $meetingObjectives,
        ] );
    }

    public function renderDiaries(Request $request){

        $user = Helper::getAuthenticatedUser();
        $erpDiaries = ErpDiary::with(['customer','attachments','createdByEmployee' => function($q){
            $q->select('id','name');
        },'createdByUser' => function($q){
            $q->select('id','name');
        }])
            ->where(function($query){
                GeneralHelper::applyDiaryFilter($query);
            });

            if($request->objective){
                $erpDiaries->where('meeting_objective_id',$request->objective);
            }

            if($request->daterange && $request->daterange == 'today'){
                $erpDiaries->whereDate('created_at',date('Y-m-d'));
            }

            if($request->daterange && $request->daterange == 'this week'){
                $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
                $endOfWeek = Carbon::now()->endOfWeek()->toDateString();
                $erpDiaries->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
            }

            if($request->daterange && $request->daterange == 'this month'){
                $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
                $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
                $erpDiaries->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
            }

            if($request->daterange && $request->daterange == 'this year'){
                $startOfYear = Carbon::now()->startOfYear()->toDateString();
                $endOfYear = Carbon::now()->endOfYear()->toDateString();
                $erpDiaries->whereBetween('created_at', [$startOfYear, $endOfYear]);
            }

            if($request->date){
                $erpDiaries->whereDate('created_at', date('Y-m-d', strtotime($request->date)));
            }

            if($request->customer_id){
                $erpDiaries->where('customer_id',$request->customer_id);
            }

            $erpDiaries = $erpDiaries->orderBy('id','desc')
                            ->paginate(ConstantHelper::PAGE_LENGTH_10);

            return [
                'data' => view('crm.notes.diary-list', ['erpDiaries' => $erpDiaries])->render(),
                'message' => 'HTML render',
            ];
    }

    public function create()
    {
        $user = Helper::getAuthenticatedUser();
        $type = GeneralHelper::loginUserType();
        $teamsIds = GeneralHelper::getTeam($user);

        $customers = ErpCustomer::where(function($query){
                            GeneralHelper::applyUserFilter($query,'ErpCustomer');
                        })
                        // ->where('lead_status',ConstantHelper::WON)
                        ->where('status',ConstantHelper::ACTIVE)
                        ->get();
        
        $salePersons = Employee::where(function($query) use($type,$user,$teamsIds){
                            if($type == 'employee'){
                                $query->whereIn('id', $teamsIds);
                            }else{
                                $query->where('organization_id', $user->organization_id);
                            }
                        })->get();

        $meetingStatus = ErpMeetingStatus::where('organization_id', $user->organization_id)->where('status', ConstantHelper::ACTIVE)->orderBy('sequence','asc')->get();
        $categories = ErpProductCategory::where('organization_id', $user->organization_id)->where('status', ConstantHelper::ACTIVE)->get();
        $leadSources = ErpLeadSource::where('organization_id', $user->organization_id)->where('status', ConstantHelper::ACTIVE)->get();
        $industries = ErpIndustry::where('organization_id', $user->organization_id)->where('status', ConstantHelper::ACTIVE)->get();
        $meetingObjectives = ErpMeetingObjective::where('organization_id', $user->organization_id)->where('status', ConstantHelper::ACTIVE)->get();
        $countries = Country::select('id','code','name')->get();
        $states = State::get();
        $currencies = ErpCurrency::where('status',ConstantHelper::ACTIVE)->get();
        return view('crm.notes.create',[
            'customers' => $customers,
            'salePersons' => $salePersons,
            'meetingStatus' => $meetingStatus,
            'meetingObjectives' => $meetingObjectives,
            'states' => $states,
            'industries' => $industries,
            'countries' => $countries,
            'categories' => $categories,
            'leadSources' => $leadSources,
            'currencies' => $currencies,
        ] );
    }

    public function store(Request $request)
    {
        $validator = (new Validator($request))->store();

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            \DB::beginTransaction();
            $user = Helper::getAuthenticatedUser();

            $erpCustomer = ErpCustomer::with('address')
                ->select('id','company_name','industry_id','sales_figure','customer_address','customer_pincode','state_id','city_id','country_id','customer_code','email','contact_person','phone','lead_status','sales_person_id')
                ->where('customer_code',$request->customer_code)
                ->first();

            if(!$erpCustomer){
                $erpCustomer = new ErpCustomer();
            }

            $meetingStatus = ErpMeetingStatus::find($request->meeting_status_id); 

            $erpCustomer->company_name = $erpCustomer->company_name ? $erpCustomer->company_name : $request->customer_code;
            $erpCustomer->mobile = $erpCustomer->phone ? $erpCustomer->phone : $request->phone_no;
            $erpCustomer->phone = $erpCustomer->phone ? $erpCustomer->phone : $request->phone_no;
            $erpCustomer->contact_person = $erpCustomer->contact_person ? $erpCustomer->contact_person : $request->contact_person;
            $erpCustomer->sales_person_id = $erpCustomer->sales_person_id ? $erpCustomer->sales_person_id : $request->sales_representative_id;
            $erpCustomer->currency_id = $erpCustomer->currency_id ? $erpCustomer->currency_id : $request->currency_id;
            $erpCustomer->email = $erpCustomer->email ? $erpCustomer->email : $request->email_id;
            $erpCustomer->organization_id = $user->organization_id;
            $erpCustomer->customer_type = ConstantHelper::INDIVIDUAL;
            $erpCustomer->status = $meetingStatus && $meetingStatus->alias == ConstantHelper::WON ? ConstantHelper::ACTIVE : ConstantHelper::PENDING;
            $erpCustomer->industry_id = $erpCustomer->industry_id ? $erpCustomer->industry_id : $request->industry_id;
            $erpCustomer->product_category_id = $erpCustomer->product_category_id ? $erpCustomer->product_category_id : $request->product_category_id;
            $erpCustomer->lead_source_id = $erpCustomer->lead_source_id ? $erpCustomer->lead_source_id : $request->lead_source_id;
            $erpCustomer->lead_status = $meetingStatus && $meetingStatus->alias ? $meetingStatus->alias : $erpCustomer->lead_status;
            $erpCustomer->sales_figure = $erpCustomer->sales_figure ? $erpCustomer->sales_figure : ($request->sales_figure ? $request->sales_figure : 0);
            $erpCustomer->customer_address = $erpCustomer->customer_address ? $erpCustomer->customer_address : $request->address;
            $erpCustomer->state_id = $erpCustomer->state_id ? $erpCustomer->state_id : $request->state_id;
            $erpCustomer->country_id = $erpCustomer->country_id ? $erpCustomer->country_id : $request->country_id;
            $erpCustomer->city_id = $erpCustomer->city_id ? $erpCustomer->city_id : $request->city_id;
            $erpCustomer->customer_pincode = $erpCustomer->customer_pincode ? $erpCustomer->customer_pincode : $request->zip_code;
            $erpCustomer->is_prospect = 1;
            $erpCustomer->save();
            
            if(!$erpCustomer->customer_code){
                $erpCustomer->customer_code = $erpCustomer->id;
                $erpCustomer->save();
            }

            $diary = ErpDiary::where('customer_code', $erpCustomer->customer_code)
            ->where('organization_id', $user->organization_id)
            ->latest()
            ->first();

            $erpDiary = new ErpDiary();
            $erpDiary->fill($request->all());
            $erpDiary->organization_id = $user->organization_id;
            $erpDiary->sales_figure = $erpCustomer->sales_figure;
            $erpDiary->customer_id = $erpCustomer->id;
            $erpDiary->customer_code = $erpCustomer->customer_code;
            $erpDiary->customer_name = $erpCustomer->company_name;
            $erpDiary->contact_person = $erpCustomer->contact_person;
            $erpDiary->email = $erpCustomer->email;
            $erpDiary->industry_id = $erpCustomer->industry_id;
            $erpDiary->location = $erpCustomer->full_address;
            $erpDiary->subject = $request->meeting_objective;
            $erpDiary->meeting_objective_id = $request->meeting_objective_id;
            $erpDiary->meeting_status_id = $request->meeting_status_id ? $request->meeting_status_id : @$diary->meeting_status_id;
            $erpDiary->created_by = $user->id;
            $erpDiary->created_by_type = GeneralHelper::loginUserType();
            $erpDiary->save();

            $documentPath = '';

            if ($request->hasFile('attachment')) {
                $attachments = $request->file('attachment');
                foreach ($attachments as $key => $attachment) {
                    $documentName = time() . '-' . $attachment->getClientOriginalName();
                    $attachment->move(public_path('attachments/note_attchments'), $documentName);
                    $documentPath = 'attachments/note_attchments/'.$documentName;

                    $erpDiaryAttachment = new ErpDiaryAttachment();
                    $erpDiaryAttachment->erp_diary_id = $erpDiary->id;
                    $erpDiaryAttachment->document_path = $documentPath;
                    $erpDiaryAttachment->save();
                }
            }

            if($request->get('leads')){
                $data = $request->get('leads');
                foreach ($data as $key => $lead) {
                    $leadContact = new ErpLeadContacts();
                    $leadContact->organization_id = $erpCustomer->organization_id;
                    $leadContact->customer_id = $erpCustomer->id;
                    $leadContact->customer_code = $erpCustomer->customer_code;
                    $leadContact->contact_name = $lead['contact_name'];
                    $leadContact->contact_number = $lead['contact_number'];
                    $leadContact->contact_email = $lead['contact_email'];
                    $leadContact->save();
                }

            }

            
            \DB::commit();
            return [
                "data" => $erpDiary,
                "message" => "Note added successfully!"
            ];

        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }
    }

    public function getLocations($customerId)
    {
        $data = [];
        if($customerId){
                $data = ErpAddress::where('addressable_type', 'App\Models\ErpCustomer' )
                ->where('addressable_id', $customerId)
                ->get();
        }
        return response()->json($data);
    }

    public function getCustomer($customerId)
    {
        $user = Helper::getAuthenticatedUser();
        $customer = ErpCustomer::with('salesRepresentative','leadSource','productCategory','leadContacts','currency')
            ->where('customer_code', $customerId)
            ->where('erp_customers.organization_id', $user->organization_id)
            ->first();
            // dd($customer);
        if(!$customer)
        {
            return response()->json([
                'message' => 'Customer not found.',
                'status' => false
            ]);
        }
        $diary = ErpDiary::with('industry')
            ->where('customer_code', $customerId)
            ->where('organization_id', $user->organization_id)
            ->latest()
            ->first();

        return response()->json([
            'customer' => $customer,
            'diary' => $diary
        ]);
    }

    public function getCustomers(Request $request)
    {
        $customers = ErpCustomer::where(function($query){
                        GeneralHelper::applyUserFilter($query,'ErpCustomer');
                    })
                    ->select('id','customer_code','company_name');

        if($request->customer_type == 'New'){
            $customers->where('is_prospect','1')
            ->where('status',ConstantHelper::PENDING);
        }else{
            $customers->where('status',ConstantHelper::ACTIVE);
        }

        $customers = $customers->get();

        if(!$customers)
        {
            return response()->json([
                'message' => 'Customers not found.',
                'status' => false
            ]);
        }

        return response()->json([
            'customers' => $customers,
            'status' => 200
        ]);
    }

    public function storeAnswer(Request $request){
        $validator = (new Validator($request))->storeFeedback();

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            \DB::beginTransaction();
            if ($request->feedback) {
                $data = $request->feedback;
                foreach ($data as $id => $feedback) {
                    if(!$feedback){
                        continue;
                    }
                    $erpFeedback = ErpFeedback::updateOrCreate(['question_id' => $id,'customer_id' => $request->customer_id,'organization_id' => $request->organization_id,'customer_code' => $request->customer_code],[
                        'feedback' => $feedback,
                    ]);
                
                }
            }
        \DB::commit();
            return [
                "data" => 1,
                "message" => "Feedback added successfully!"
            ];

        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }
    }

    public function addLeadContacts(Request $request){
        $validator = (new Validator($request))->leadContacts();

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $request->get('data');

        return [
            'data' => $data,
            'message' => 'HTML render',
            "status" => 200
        ];


    }

    public function removeLeadContact($id){
        $erpLeadContact = ErpLeadContacts::find($id);
        $erpLeadContact->delete();
        return [
            "data" => [
                'id' => $id
            ],
            "message" => "Contacts removed.",
            "status" => 200
        ];

    }

}
