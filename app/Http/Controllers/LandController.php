<?php

namespace App\Http\Controllers;

use App\Models\LandLease;
use App\Models\LandLeasePlot;
use App\Models\LandParcel;
use Carbon\Carbon;
use App\Models\Book;
use App\Models\BookType;
use App\Models\Land;
use App\Models\LandPlot;
use App\Models\Lease;
use App\Models\Recovery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\NumberPattern;
use App\Models\Customer;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Response;
use App\Helpers\ConstantHelper;
use App\Models\LandLeaseScheduler;

class LandController extends Controller
{
    // Show the land index page
    public function index()
    {
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 1;
            $utype = 'user';
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
            $user_id = Auth::guard('web2')->user()->id;
            $type = 2;
            $utype = 'employee';
        } else {
            $organization_id = 1;
            $user_id = 1;
            $type = 1;
            $utype = 'user';
        }
        // Fetch data related to lands and return the view
        $lands = Land::where('organization_id', $organization_id)
            // Legals created by the user
            ->where('user_id', $user_id)
            ->where('type', $type)->orderby('id', 'desc')->get(); // Example: fetching all lands from the database
        $selectedDateRange = '';
        $pincode = '';
        $land_no = '';
        $selectedStatus = '';
        $khasra = '';
        $plot = '';

        return view('land.my-land', compact('lands', 'selectedDateRange', 'pincode', 'land_no', 'selectedStatus', 'khasra', 'plot')); // Return the 'land.index' view
    }

    public function dashboard(Request $request)
    {
        $user = Helper::getAuthenticatedUser();;
        $organization_id = $user->organization_id;
        $user_id = $user->id;
        $type = $user->authenticable_type;
        $today = Carbon::now();
        $next30Days = $today->copy()->addDays(30);
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : null;
        $today = date('Y-m-d');
      
        // Check if $request is present
        if ($request->start_date) {
            
            // Parse the date from the request
            $startDate = Carbon::createFromFormat('m/d/Y', $request['start_date'])->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $request['end_date'])->endOfDay();
            

            // Active leases within the range
            $active_leases = LandLease::where('organization_id',$organization_id)
            ->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED)->whereBetween('lease_end_date', [$startDate, $endDate])
                ->whereRaw('DATEDIFF(lease_end_date, ?) > 30', [$today])
                ->count();

            // Expiring leases within the range
            $expiring_leases = LandLease::where('organization_id',$organization_id)
            ->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED)->whereBetween('lease_end_date', [$startDate, $endDate])
                ->whereRaw('DATEDIFF(lease_end_date, ?) < 30', [$today])
                ->count();

            // Expired leases within the range
            $expired_leases = LandLease::where('organization_id',$organization_id)
            ->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED)->whereBetween('lease_end_date', [$startDate, $endDate])
                ->whereRaw('DATEDIFF(?, lease_end_date) > 0', [$today])
                ->count();

            // Leases with customer data within the range
            $leases = LandLease::where('organization_id',$organization_id)
            ->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED)->with('customer')
                ->whereBetween('lease_end_date', [$startDate, $endDate])
                ->whereRaw('DATEDIFF(lease_end_date, ?) > 0', [$today])
                ->orderBy('lease_end_date', 'asc')
                ->get();

            // Lease revenue summary within the range
            $lease_revenue_summary = LandLease::where('organization_id',$organization_id)
            ->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED)->whereBetween('lease_end_date', [$startDate, $endDate])
                ->sum('total_amount');
        } else {
            // Original queries when $request is not present
            $active_leases = LandLease::where('organization_id',$organization_id)
            ->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED)->whereRaw('DATEDIFF(lease_end_date, ?) > 30', [$today])
                ->count();

            $expiring_leases = LandLease::where('organization_id',$organization_id)
            ->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED)->whereRaw('DATEDIFF(lease_end_date, ?) < 30', [$today])
                ->count();

            $expired_leases = LandLease::where('organization_id',$organization_id)
            ->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED)->whereRaw('DATEDIFF(?, lease_end_date) > 0', [$today])
                ->count();

            $leases = LandLease::where('organization_id',$organization_id)
            ->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED)->with('customer')
                ->whereRaw('DATEDIFF(lease_end_date, ?) > 0', [$today])
                ->orderBy('lease_end_date', 'asc')
                ->get();

            $lease_revenue_summary = LandLease::where('organization_id',$organization_id)
            ->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED)->sum('total_amount');
        }
    

        
        $recoveries = LandLeaseScheduler::with('lease')->whereHas('lease', function ($query) use ($organization_id) {
            $query->where('organization_id', $organization_id);
            $query->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED);
        }) ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            return $query->whereBetween('created_at', [$startDate, $endDate]);
        })
        //->where('status','paid')
        ->orderBy('due_date', 'asc')->get()->map(function ($recovery) {
            $recovery->due_amount = $recovery->installment_cost+$recovery->tax_amount;
            return $recovery;
        });
       
        
        $locations = LandParcel::with(['plots.locations', 'locations'])
        ->where('organization_id',$organization_id)
        ->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED)->get();

        // Fetch all plot lease data with lease details (including start_date and end_date)
        $filledPlotsWithLease = LandLeasePlot::with('lease')
        ->whereHas('lease', function ($query) use ($organization_id) {
            $query->where('organization_id', $organization_id);
            $query->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED);
        })
        ->get()->mapWithKeys(function ($item) {
                return [
                    $item->land_plot_id => [
                        'filled' => true,
                        'start_date' => $item->lease->lease_start_date,
                        'end_date' => $item->lease->lease_end_date,
                    ]
                ];
            })
            ->toArray();

        // Process each location and mark the plots as filled based on `filledPlotsWithLease`
        $locations->map(function ($location) use ($filledPlotsWithLease) {
            $location->plots->map(function ($plot) use ($filledPlotsWithLease) {
                if (isset($filledPlotsWithLease[$plot->id])) {
                    // If the plot ID exists in the filled plots, mark it as filled
                    $plot->filled = true;
                    $plot->start_date = $filledPlotsWithLease[$plot->id]['start_date'];
                    $plot->end_date = $filledPlotsWithLease[$plot->id]['end_date'];
                } else {
                    // If not, set filled as false and no dates
                    $plot->filled = false;
                    $plot->start_date = null;
                    $plot->end_date = null;
                }
                return $plot;
            });
            return $location;
        });
      return view('land.dashboard', compact('leases', 'recoveries', 'active_leases', 'expiring_leases', 'expired_leases', 'lease_revenue_summary', 'locations'));
    }

    public function getDashboardRevenueReport()
    {
        $totals = [
            'total_revenue' => 0,
            'total_overdue' => 0,
            'total_pending' => 0,
        ];
        $user = Helper::getAuthenticatedUser();;
        $organization_id = $user->organization_id;
        

        $totals = [
            'total_revenue' => 0,
            'total_overdue' => 0,
            'total_pending' => 0,
        ];
        
        $recoveries = LandLeaseScheduler::with('lease')
            ->whereHas('lease', function ($query) use ($organization_id) {
                $query->where('organization_id', $organization_id)
                      ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED);
            })
            ->get()            
            ->map(function ($recovery) use (&$totals) {
                $due_amount = $recovery->installment_cost + $recovery->tax_amount;
                $due_date = Carbon::parse($recovery->due_date);
                $is_overdue = now()->gt($due_date);
                $overdue_amount = $is_overdue && $due_amount > 0 ? $due_amount : 0;
        
                // Aggregate totals
                $totals['total_revenue'] += $recovery->status === "paid" ? $due_amount : 0;
                $totals['total_overdue'] += $overdue_amount;
                $totals['total_pending'] += $due_amount;

        
                // Modify recovery object
                $recovery->setAttribute('due_amount', round($due_amount,2));
                $recovery->setAttribute('due_date', $due_date->format('d-M-Y'));
                $recovery->setAttribute('overdue_amount', round($overdue_amount,2));
                $recovery->setAttribute('revenue_amount', $recovery->status === "paid" ? $recovery->installment_cost + $recovery->tax_amount : 0);
        
                return $recovery;
            });
        
        return response()->json([
            'revenue' => round($totals['total_revenue'],2),
            'overdue' => round($totals['total_overdue'],2),
            'pending' => round($totals['total_pending'],2),
        ]);
    }
    // Show the form to add land
    public function create()
    {
        // $series = Book::where('status','Active')->pluck('book_name', 'id');

        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 1;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
            $user_id = Auth::guard('web2')->user()->id;
            $type = 2;
        } else {
            $organization_id = 1;
            $user_id = 1;
            $type = 2;
        }

        $books = BookType::where('status', 'Active')->whereHas('service', function ($query) {
            $query->where('alias', 'land');
        })
            ->where('organization_id', $organization_id)
            ->pluck('id');

        $series = Book::whereIn('booktype_id', $books)->get();



        return view('land.my-land-add', compact('series')); // Return the 'land.add' view
    }

    public function saveLand(Request $request)
    {
        // Validation
        $validatedData = $request->validate([
            'series' => 'required',
            'land_no' => 'required|unique:erp_lands,land_no', // Add unique validation
            'plot_no' => 'required',
            'documentno' => 'required',
            'khasara_no' => 'required',
            'area' => 'required|numeric',
            'dimension' => 'required',
            'address' => 'required',
            'pincode' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'status' => 'required|in:active,inactive',
            'remarks' => 'nullable|string',
            'cost' => 'nullable|numeric', // Add cost field as nullable and numeric
        ]);

        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 1;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
            $user_id = Auth::guard('web2')->user()->id;
            $type = 2;
        } else {
            $organization_id = 1;
            $user_id = 1;
            $type = 2;
        }

        do {
            $document_no = Helper::reGenerateDocumentNumber($request->series);
            $existingLoan = Land::where('documentno', $document_no)->first();
        } while ($existingLoan !== null);
        //dd('here', $document_no);
        try {
            $validatedData = array_merge($validatedData, [
                'organization_id' => $organization_id,
                'user_id' => $user_id,
                'type' => $type,
                'documentno' => $document_no,
            ]);
            // Save the data to the database
            Land::create($validatedData);


            $numberPattern = NumberPattern::where('book_id', $request->series)->first();

            if (!empty($numberPattern)) {

                $number = $numberPattern->current_no + 1;
                $numberPattern->current_no = $number;
                $numberPattern->save();
            }

            // Redirect to /land with a success message
            return redirect('/land')->with('success', 'Land information saved successfully.');
        } catch (\Exception $e) {

            // Redirect back with input data and an error message if something goes wrong
            return redirect()->back()->withInput()->withErrors(['error' => 'An error occurred while saving the data.']);
        }
    }


    public function edit($id)
    {
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 1;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
            $user_id = Auth::guard('web2')->user()->id;
            $type = 2;
        } else {
            $organization_id = 1;
            $user_id = 1;
            $type = 2;
        }

        $books = BookType::where('status', 'Active')->whereHas('service', function ($query) {
            $query->where('alias', 'land');
        })
            ->where('organization_id', $organization_id)
            ->pluck('id');

        $series = Book::whereIn('booktype_id', $books)->get();

        $data = Land::where('organization_id', $organization_id)
            // Legals created by the user
            ->where('user_id', $user_id)
            ->where('type', $type)->with('lease')->find($id);

        return view('land.my-land-edit', compact('series', 'data')); // Return the 'land.add' view
    }

    public function updateLand(Request $request)
    {
        // Validation
        $validatedData = $request->validate([
            'series' => 'required',
            'land_no' => 'required|unique:erp_lands,land_no,' . $request->id, // Ignore the current record
            'plot_no' => 'required',
            //'documentno' => 'required',
            'khasara_no' => 'required',
            'area' => 'required|numeric',
            'dimension' => 'required',
            'address' => 'required',
            'pincode' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'status' => 'required|in:active,inactive',
            'remarks' => 'nullable|string',
            'cost' => 'nullable|numeric', // Add cost field as nullable and numeric
        ]);


        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 1;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
            $user_id = Auth::guard('web2')->user()->id;
            $type = 2;
        } else {
            $organization_id = 1;
            $user_id = 1;
            $type = 2;
        }

        try {
            $validatedData = array_merge($validatedData, [
                'organization_id' => $organization_id,
                'user_id' => $user_id,
                'type' => $type,
            ]);
            // Save the data to the database
            Land::find($request->id)->update($validatedData);

            // Redirect to /land with a success message
            return redirect('/land')->with('success', 'Land information saved successfully.');
        } catch (\Exception $e) {

            // Redirect back with input data and an error message if something goes wrong
            return redirect()->back()->withInput()->withErrors(['error' => 'An error occurred while saving the data.']);
        }
    }

    // Handle the submission of the on lease land form
    public function onleaseadd(Request $request)
    {
        // Process the request and add new lease data
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 1;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
            $user_id = Auth::guard('web2')->user()->id;
            $type = 2;
        } else {
            $organization_id = 1;
            $user_id = 1;
            $type = 2;
        }

        $books = BookType::where('status', 'Active')->whereHas('service', function ($query) {
            $query->where('alias', 'land-lease');
        })
            ->where('organization_id', $organization_id)
            ->pluck('id');

        $series = Book::whereIn('booktype_id', $books)->get();
        $leasedLandNumbers = Lease::pluck('land_no')->toArray(); // Get all leased land numbers

        $lands = Land::where('organization_id', $organization_id)
            // Legals created by the user
            ->where('user_id', $user_id)
            ->where('type', $type)->whereNotIn('id', $leasedLandNumbers)->get();


        $user = Helper::getAuthenticatedUser();
        $organizationId = $user->organization_id;
        $customers = Customer::with(['erpOrganizationType', 'category', 'subcategory'])
            ->where('organization_id', $organizationId)
            ->get();
        return view('land.on-lease-add', compact('series', 'lands', 'customers')); // Redirect back to the on lease page
    }

    public function savelease(Request $request)
    {
        // Validate the request data
        $request->validate([
            'series' => 'required',
            'agreement_no' => 'required|string|max:255',
            'date_of_agreement' => 'required|date',
            'lease_no' => 'required|string|max:255',
            'land_no' => 'required',
            'khasara_no' => 'required',
            'area_sqft' => 'required|numeric',
            'plot_details' => 'required',
            'pincode' => 'required',
            'cost' => 'nullable|numeric', // Add cost field as nullable and numeric
            'customer' => 'required',
            'lease_time' => 'required|numeric',
            'lease_cost' => 'required|numeric',
            'period_type' => 'required',
            'repayment_period' => 'required|numeric',
            'installment_cost' => 'required|numeric',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048', // max 2MB
            'remarks' => 'nullable|string',
        ]);
        do {
            $lease_no = Helper::reGenerateDocumentNumber($request->series);
            $existingdata = Lease::where('lease_no', $lease_no)->first();
        } while ($existingdata !== null);
        //dd('here', $lease_no);
        // Handle file upload
        $documentPath = null;
        if ($request->hasFile('document')) {
            $document = $request->file('document');
            $documentName = time() . '-' . $document->getClientOriginalName();
            $document->move(public_path('documents'), $documentName); // Save to public/documents
            $documentPath = $documentName;
        }

        // Prepare data for storage
        $data = $request->only([
            'series',
            'lease_no',
            'land_no',
            'khasara_no',
            'area_sqft',
            'plot_details',
            'pincode',
            'cost',
            'customer',
            'lease_time',
            'lease_cost',
            'period_type',
            'repayment_period',
            'installment_cost',
            'remarks',
            'agreement_no',
            'date_of_agreement'
        ]);

        $data['document'] = $documentPath;

        // Determine organization_id, user_id, and type
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 1;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
            $user_id = Auth::guard('web2')->user()->id;
            $type = 2;
        } else {
            $organization_id = 1;
            $user_id = 1;
            $type = 2;
        }

        // Add organization_id, user_id, and type to the data
        $data = array_merge($data, [
            'lease_no' => $lease_no,
            'organization_id' => $organization_id,
            'user_id' => $user_id,
            'type' => $type,
        ]);

        // Attempt to save the data
        try {
            Lease::create($data);
            $numberPattern = NumberPattern::where('book_id', $request->series)->first();

            if (!empty($numberPattern)) {

                $number = $numberPattern->current_no + 1;
                $numberPattern->current_no = $number;
                $numberPattern->save();
            }

            // Redirect to /land with a success message
            return redirect('/land/on-lease')->with('success', 'Land information saved successfully.');
        } catch (\Exception $e) {

            // Redirect back with input data and an error message if something goes wrong
            return redirect()->back()->withInput()->withErrors(['error' => 'An error occurred while saving the data.']);
        }
    }

    // Handle the submission of the on lease land form
    public function onleaseedit($id)
    {
        // Process the request and add new lease data
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 1;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
            $user_id = Auth::guard('web2')->user()->id;
            $type = 2;
        } else {
            $organization_id = 1;
            $user_id = 1;
            $type = 2;
        }

        $books = BookType::where('status', 'Active')->whereHas('service', function ($query) {
            $query->where('alias', 'land-lease');
        })
            ->where('organization_id', $organization_id)
            ->pluck('id');

        $series = Book::whereIn('booktype_id', $books)->get();
        $leasedLandNumbers = Lease::pluck('land_no')->toArray(); // Get all leased land numbers

        $lands = Land::where('organization_id', $organization_id)
            // Legals created by the user
            ->where('user_id', $user_id)
            ->where('type', $type)->whereNotIn('id', $leasedLandNumbers)->get(); // Exclude already leased lands

        $user = Helper::getAuthenticatedUser();
        $organizationId = $user->organization_id;
        $customers = Customer::with(['erpOrganizationType', 'category', 'subcategory'])
            ->where('organization_id', $organizationId)
            ->get();

        $data = Lease::find($id);
        return view('land.on-lease-edit', compact('series', 'data', 'lands', 'customers')); // Redirect back to the on lease page
    }

    public function updatelease(Request $request)
    {

        // Validate the request data
        $request->validate([
            'series' => 'required',
            'agreement_no' => 'required|string|max:255',
            'date_of_agreement' => 'required|date',
            'lease_no' => 'required|string|max:255|unique:erp_leases,lease_no,' . $request->id,
            'land_no' => 'required',
            'khasara_no' => 'required',
            'area_sqft' => 'required|numeric',
            'plot_details' => 'required',
            'pincode' => 'required',
            'cost' => 'nullable|numeric', // Add cost field as nullable and numeric
            'customer' => 'required',
            'lease_time' => 'required|numeric',
            'lease_cost' => 'required|numeric',
            'period_type' => 'required',
            'repayment_period' => 'required|numeric',
            'installment_cost' => 'required|numeric',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048', // max 2MB
            'remarks' => 'nullable|string',
        ]);

        // Handle file upload
        $documentPath = null;
        if ($request->hasFile('document')) {
            $document = $request->file('document');
            $documentName = time() . '-' . $document->getClientOriginalName();
            $document->move(public_path('documents'), $documentName); // Save to public/documents
            $documentPath = $documentName;
        }

        // Prepare data for storage
        $data = $request->only([
            'series',
            'lease_no',
            'land_no',
            'khasara_no',
            'area_sqft',
            'plot_details',
            'pincode',
            'cost',
            'customer',
            'lease_time',
            'lease_cost',
            'period_type',
            'repayment_period',
            'installment_cost',
            'remarks',
            'agreement_no',
            'date_of_agreement'
        ]);

        $data['document'] = $documentPath;

        // Determine organization_id, user_id, and type
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 1;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
            $user_id = Auth::guard('web2')->user()->id;
            $type = 2;
        } else {
            $organization_id = 1;
            $user_id = 1;
            $type = 2;
        }

        // Add organization_id, user_id, and type to the data
        $data = array_merge($data, [
            'organization_id' => $organization_id,
            'user_id' => $user_id,
            'type' => $type,
        ]);


        // Attempt to save the data
        try {
            Lease::find($request->id)->update($data);

            // Redirect to /land with a success message
            return redirect('/land/on-lease')->with('success', 'Land information saved successfully.');
        } catch (\Exception $e) {

            // Redirect back with input data and an error message if something goes wrong
            return redirect()->back()->withInput()->withErrors(['error' => 'An error occurred while saving the data.']);
        }
    }

    public function onLeaseAddFilterLand(Request $request)
    {
        $land_no = $request->query('landNo');
        $customer_name = $request->query('customerName');
        $plot_no = $request->query('plotNo');
        $khasara_no = $request->query('khasaraNo');

        // Process the request and add new lease data
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 1;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
            $user_id = Auth::guard('web2')->user()->id;
            $type = 2;
        } else {
            $organization_id = 1;
            $user_id = 1;
            $type = 2;
        }

        $query = Land::query()
            ->where('organization_id', $organization_id)
            // Legals created by the user
            ->where('user_id', $user_id)
            ->where('type', $type);

        if ($land_no) {
            $query->where('land_no', $land_no);
        }
        if ($customer_name) {
            //$query->where('land_no', $land_no);
        }
        if ($plot_no) {
            $query->where('plot_no', $plot_no);
        }
        if ($khasara_no) {
            $query->where('khasara_no', $khasara_no);
        }
        $land_filter_list = $query->get();

        $user = Helper::getAuthenticatedUser();
        $organizationId = $user->organization_id;
        $customers = Customer::with(['erpOrganizationType', 'category', 'subcategory'])
            ->where('organization_id', $organizationId)
            ->get();
        return Response::json(compact('land_filter_list'));
    }

    // Show the recovery form
    public function recovery()
    {
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 1;
            $utype = 'user';
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
            $user_id = Auth::guard('web2')->user()->id;
            $type = 2;
            $utype = 'employee';
        } else {
            $organization_id = 1;
            $user_id = 1;
            $type = 1;
            $utype = 'user';
        }
        $recovery = Recovery::with('cust')->where('organization_id', $organization_id)
            // Legals created by the user
            ->where('user_id', $user_id)
            ->where('type', $type)->orderby('id', 'desc')->get();

        $selectedDateRange = '';
        $land_no = '';
        $selectedStatus = '';
        $customer = '';
        return view('land.recovery', compact('recovery', 'selectedDateRange', 'land_no', 'selectedStatus', 'customer')); // Return the 'land.recovery' view
    }

    // Handle the submission of the recovery form
    public function recoveryadd(Request $request)
    {
        // Process the recovery request
        // Example: $recovery = Recovery::create($request->all());
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 1;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
            $user_id = Auth::guard('web2')->user()->id;
            $type = 2;
        } else {
            $organization_id = 1;
            $user_id = 1;
            $type = 2;
        }

        $books = BookType::where('status', 'Active')->whereHas('service', function ($query) {
            $query->where('alias', 'land-recovery');
        })
            ->where('organization_id', $organization_id)
            ->pluck('id');
        $series = Book::whereIn('booktype_id', $books)->get();
        $leasedLandNumbers = Lease::where('organization_id', $organization_id)
            // Legals created by the user
            ->where('user_id', $user_id)
            ->where('type', $type)->pluck('land_no')->toArray(); // Get all leased land numbers

        $lands = Land::where('organization_id', $organization_id)
            // Legals created by the user
            ->where('user_id', $user_id)
            ->where('type', $type)->whereIn('id', $leasedLandNumbers)->get(); // Exclude already leased lands

        return view('land.recovery-add', compact('series', 'lands')); // Redirect back to the recovery page
    }

    public function recoveryedit($id)
    {
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 1;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
            $user_id = Auth::guard('web2')->user()->id;
            $type = 2;
        } else {
            $organization_id = 1;
            $user_id = 1;
            $type = 2;
        }

        $books = BookType::where('status', 'Active')->whereHas('service', function ($query) {
            $query->where('alias', 'land-recovery');
        })
            ->where('organization_id', $organization_id)
            ->pluck('id');
        $series = Book::whereIn('booktype_id', $books)->get();
        $leasedLandNumbers = Lease::where('organization_id', $organization_id)
            // Legals created by the user
            ->where('user_id', $user_id)
            ->where('type', $type)->pluck('land_no')->toArray(); // Get all leased land numbers

        $lands = Land::where('organization_id', $organization_id)
            // Legals created by the user
            ->where('user_id', $user_id)
            ->where('type', $type)->whereIn('id', $leasedLandNumbers)->get(); // Exclude already leased lands

        $data = Recovery::find($id);
        return view('land.recovery-edit', compact('series', 'lands', 'data')); // Redirect back to the on lease page
    }

    public function getLandBySeries($seriesId)
    {
        $lands = Land::where('series', $seriesId)
            ->pluck('land_no', 'id');
        return response()->json($lands);
    }

    public function getLandDetails($landId)
    {
        $land = Land::find($landId);

        $landDetails = [
            'khasara_no' => $land->khasara_no,
            'area_sqft' => $land->area,
            'plot_details' => $land->address,
            'pincode' => $land->pincode,
            'cost' => $land->cost,
        ];

        return response()->json($landDetails);
    }

    public function getLeaseDetails($landId)
    {
        $land = Lease::where('land_no', $landId)->first();


        $bl = Recovery::where('land_no', $landId)->pluck('received_amount')->toArray();

        $bal = array_sum($bl);

        $landDetails = [
            'khasara_no' => $land->khasara_no,
            'area_sqft' => $land->area_sqft,
            'plot_details' => $land->plot_details,
            'pincode' => $land->pincode,
            'cost' => $land->cost,
            'customer' => $land->cust->company_name,
            'customerid' => $land->customer,
            'lease_time' => $land->lease_time,
            'lease_cost' => $land->lease_cost,
            'bal_lease_cost' => $land->lease_cost - $bal,
            'lease_date' => $land->created_at->format('Y-m-d'),
        ];

        return response()->json($landDetails);
    }

    public function saveRecovery(Request $request)
    {
        $validatedData = $request->validate([
            'series' => 'required|string|max:255',
            'document_no' => 'required|string|max:255',
            'land_no' => 'required|string|max:255',
            'khasara_no' => 'required|string|max:255',
            'area_sqft' => 'required|string|max:255',
            'plot_details' => 'required|string|max:255',
            'pincode' => 'required|string|max:10',
            'cost' => 'nullable|numeric', // Add cost field as nullable and numeric
            'customer' => 'required|string|max:255',
            'lease_time' => 'required|string|max:255',
            'lease_cost' => 'required|numeric',
            'bal_lease_cost' => 'required|numeric',
            'received_amount' => 'required|numeric',
            'date_of_payment' => 'required|date',
            'payment_mode' => 'required|string|max:255',
            'reference_no' => 'required|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
            'remarks' => 'nullable|string|max:250',
        ]);

        do {
            $document_no = Helper::reGenerateDocumentNumber($request->series);
            $existingLoan = Recovery::where('document_no', $document_no)->first();
        } while ($existingLoan !== null);
        //dd('here', $document_no);

        // Handle file upload
        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('documents', 'public');
        }

        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 1;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
            $user_id = Auth::guard('web2')->user()->id;
            $type = 2;
        } else {
            $organization_id = 1;
            $user_id = 1;
            $type = 2;
        }

        // Save the data to the database
        $recovery = new Recovery();
        $recovery->organization_id = $validatedData['series'];
        $recovery->series = $validatedData['series'];
        $recovery->series = $validatedData['series'];
        $recovery->series = $validatedData['series'];
        $recovery->document_no = $document_no;
        $recovery->land_no = $validatedData['land_no'];
        $recovery->khasara_no = $validatedData['khasara_no'];
        $recovery->area_sqft = $validatedData['area_sqft'];
        $recovery->plot_details = $validatedData['plot_details'];
        $recovery->pincode = $validatedData['pincode'];
        $recovery->cost = $validatedData['cost'];
        $recovery->customer = $validatedData['customer'];
        $recovery->lease_time = $validatedData['lease_time'];
        $recovery->lease_cost = $validatedData['lease_cost'];
        $recovery->bal_lease_cost = $validatedData['bal_lease_cost'];
        $recovery->received_amount = $validatedData['received_amount'];
        $recovery->date_of_payment = $validatedData['date_of_payment'];
        $recovery->payment_mode = $validatedData['payment_mode'];
        $recovery->reference_no = $validatedData['reference_no'];
        $recovery->bank_name = $validatedData['bank_name'];
        $recovery->document = $documentPath;
        $recovery->remarks = $validatedData['remarks'];
        $recovery->organization_id = $organization_id;
        $recovery->user_id = $user_id;
        $recovery->type = $type;

        // Attempt to save the data
        try {
            $recovery->save();
            $numberPattern = NumberPattern::where('book_id', $request->series)->first();

            if (!empty($numberPattern)) {

                $number = $numberPattern->current_no + 1;
                $numberPattern->current_no = $number;
                $numberPattern->save();
            }
            return redirect('/land/recovery')->with('success', 'Recovery saved successfully.');
        } catch (\Exception $e) {
            // Log the exception

            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function approveRecovery(Request $request)
    {
        $ids = explode(',', $request->ids);
        $remarks = $request->remarks;

        Recovery::whereIn('id', $ids)->update([
            'status' => 'Approved',
            'remarks' => $remarks
        ]);

        return response()->json(['success' => true]);
    }
    public function rejectRecovery(Request $request)
    {
        $ids = explode(',', $request->ids);
        $remarks = $request->remarks;

        Recovery::whereIn('id', $ids)->update([
            'status' => 'Rejected',
            'remarks' => $remarks
        ]);

        return response()->json(['success' => true]);
    }

    public function recoveryfilter(Request $request)
    {
        // Initialize the query for the `erp_recoveries` table
        $query = Recovery::query();

        // Filter by date range
        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $start_date = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
                $end_date = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay();
                $query->whereBetween('created_at', [$start_date, $end_date]);
            }
        }

        // Filter by land number (from the `land` table)
        if ($request->filled('land_no')) {
            $query->whereHas('land', function ($q) use ($request) {
                $q->where('land_no', 'like', '%' . $request->land_no . '%');
            });
        }

        // Filter by customer name (from the `land` table)
        if ($request->filled('customer')) {
            $query->where('customer', 'like', '%' . $request->customer . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Execute the query and get the filtered results
        $recovery = $query->with('land')->get();


        // Pass filtered results back to the view (modify view name if needed)
        return view('land.recovery', [
            'recovery' => $recovery,
            'selectedDateRange' => $request->date_range,
            'land_no' => $request->land_no,
            'customer' => $request->customer,
            'selectedStatus' => $request->status,
            // Pass any other filter data if needed
        ]);
    }
    public function leasefilter(Request $request)
    {
        // Initialize the query for the `erp_recoveries` table
        $query = Lease::query();

        // Filter by date range
        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $start_date = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
                $end_date = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay();
                $query->whereBetween('created_at', [$start_date, $end_date]);
            }
        }

        // Filter by land number (from the `land` table)
        if ($request->filled('land_no')) {
            $query->whereHas('land', function ($q) use ($request) {
                $q->where('land_no', 'like', '%' . $request->land_no . '%');
            });
        }

        // Filter by customer name (from the `land` table)
        if ($request->filled('pincode')) {
            $query->where('pincode', 'like', '%' . $request->pincode . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            // $query->where('status', $request->status);
        }

        // Execute the query and get the filtered results
        $leases = $query->with('land')->get();


        // Pass filtered results back to the view (modify view name if needed)
        return view('land.on-lease', [
            'leases' => $leases,
            'selectedDateRange' => $request->date_range,
            'pincode' => $request->pincode,
            'land_no' => $request->land_no,
            'selectedStatus' => $request->status,
            // Pass any other filter data if needed
        ]);
    }
    public function landfilter(Request $request)
    {
        // Initialize the query for the `erp_lands` table
        $query = Land::query();

        // Filter by date range
        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $start_date = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
                $end_date = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay();
                $query->whereBetween('created_at', [$start_date, $end_date]);
            }
        }

        // Filter by land number (from the `land` table)
        if ($request->filled('land_no')) {
            $query->where('land_no', 'like', '%' . $request->land_no . '%');
        }

        // Filter by plot number (from the `land` table)
        if ($request->filled('plot')) {
            $query->where('plot_no', 'like', '%' . $request->plot . '%');
        }

        // Filter by khasra number (from the `land` table)
        if ($request->filled('khasra')) {
            $query->where('khasara_no', 'like', '%' . $request->khasra . '%');
        }

        // Filter by pincode (from the `land` table)
        if ($request->filled('pincode')) {
            $query->where('pincode', 'like', '%' . $request->pincode . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status == 'active' || $request->status == 'inactive') {
                $query->where('status', $request->status);
                $query->whereDoesntHave('lease');  // Applies for 'active' or 'inactive' status without lease
            } else if ($request->status == 'Allotted') {
                $query->whereHas('lease');  // Applies for cases where lease exists
            }
        }

        // Execute the query and get the filtered results
        $lands = $query->get();

        // dd($query->toSql(), $query->getBindings());

        // Pass filtered results back to the view (modify view name if needed)
        return view('land.my-land', [
            'lands' => $lands,
            'selectedDateRange' => $request->date_range,
            'pincode' => $request->pincode,
            'land_no' => $request->land_no,
            'plot' => $request->plot,
            'khasra' => $request->khasra,
            'selectedStatus' => $request->status,
            // Pass any other filter data if needed
        ]);

    }

    public function getRequests($book_id)
    {
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }

        $request = NumberPattern::where('book_id', $book_id)->where('organization_id', $organization_id)->select('prefix', 'suffix', 'starting_no', 'current_no')->first();
        $requestno = "{$request->prefix}{$request->current_no}{$request->suffix}";

        return response()->json(['requestno' => $requestno]);
    }






}

