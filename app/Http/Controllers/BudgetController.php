<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use App\Models\Book;
use App\Models\BookType;
use App\Models\NumberPattern;
use App\Models\OrganizationCompany;
use App\Models\Organization;
use App\Models\UserOrganizationMapping;
use App\Helpers\Helper;
use App\Models\Budget;
use App\Models\Ledger;


class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
                    
                    $data = Budget::all();
        
                    return view('budget.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if(!empty(Auth::guard('web')->user()))
                    {
                        $organization_id = Auth::guard('web')->user()->organization_id;
                        $user_id = Auth::guard('web')->user()->id;
                        $type = 1;
                    }
                    elseif (!empty(Auth::guard('web2')->user())) 
                    {
                        $organization_id = Auth::guard('web2')->user()->organization_id;
                        $user_id = Auth::guard('web2')->user()->id;
                        $type = 2;
                    }
                    else
                    {
                        $organization_id = 1;
                        $user_id = 1;
                        $type = 2;
                    }

                    $books = BookType::where('status','Active')->whereHas('service', function($query) {
                            $query->where('alias', 'budgets');
                        })
                        ->where('organization_id',$organization_id)
                        ->pluck('id');

                    $series = Book::whereIn('booktype_id',$books)->get();
                     $companies = OrganizationCompany::whereIn('id',Organization::whereIn('id',UserOrganizationMapping::where('user_id', Helper::getAuthenticatedUser()->id)->pluck('organization_id')->toArray())->pluck('company_id')->toArray())->with('organizations')->select('id', 'name')->get();
                      $ledgers = Ledger::where('organization_id',Helper::getAuthenticatedUser()->organization_id)->select('*')->orderBy('id', 'desc')->get();
                      $unit = Budget::pluck('unit')->toArray();

                      $unit = array_values(array_unique($unit));
        return view('budget.create',compact('series','companies','ledgers','unit'));
    }

    /**
     * Store a newly created resource in storage.
     */
         public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'series' => 'required|string',
            'documentno' => 'required|string',
            'type' => 'required|string',
            'unit' => 'required|string',
            'companies' => 'required|array',
        ]);

        // Retrieve the request data
        $requestData = $request->all();

        // Combine Heads, In, and Value
        $combinedData = [];


        foreach ($requestData['Heads'] as $index => $head) 
        {
            foreach ($head as $key => $value) 
            {
                if (isset($requestData['In'][$index][$key]) && isset($requestData['Value'][$index][$key])) {
                    if($key == 1)
                    {
                        $le = $index;
                    }
                    else
                    {
                        $le = $key;
                    }
                    $combinedData[] = [
                        'level' => $le,
                        'head' => $value,
                        'in' => $requestData['In'][$index][$key],
                        'value' => $requestData['Value'][$index][$key],
                    ];
                }
            }
        }

        // Prepare the final JSON structure
        $comp = implode(',',$requestData['companies']);
        $br = implode(',',$requestData['branch']);
        $le = implode(',',$requestData['ledger']);



        $finalData = [
            'series' => $requestData['series'],
            'documentno' => $requestData['documentno'],
            'type' => $requestData['type'],
            'unit' => $requestData['unit'],
            'companies' => $comp,
            'branch' => $br,
            'ledger' => $le,
            'budget' => $requestData['budget'],
            'period' => $requestData['period'],
            'total_percent' => $requestData['total_percent'],
            'total_value' => $requestData['total_value'],
            'details' => json_encode($combinedData),
        ];

        // Optionally save the data to the database
        Budget::create($finalData);

        // Return the JSON response
        return redirect('/budgets');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = Budget::find($id);
        
        if(!empty(Auth::guard('web')->user()))
                    {
                        $organization_id = Auth::guard('web')->user()->organization_id;
                        $user_id = Auth::guard('web')->user()->id;
                        $type = 1;
                    }
                    elseif (!empty(Auth::guard('web2')->user())) 
                    {
                        $organization_id = Auth::guard('web2')->user()->organization_id;
                        $user_id = Auth::guard('web2')->user()->id;
                        $type = 2;
                    }
                    else
                    {
                        $organization_id = 1;
                        $user_id = 1;
                        $type = 2;
                    }

                    $books = BookType::where('status','Active')->whereHas('service', function($query) {
                            $query->where('alias', 'budgets');
                        })
                        ->where('organization_id',$organization_id)
                        ->pluck('id');

                    $series = Book::whereIn('booktype_id',$books)->get();
                    $companies = OrganizationCompany::whereIn('id',Organization::whereIn('id',UserOrganizationMapping::where('user_id', Helper::getAuthenticatedUser()->id)->pluck('organization_id')->toArray())->pluck('company_id')->toArray())->with('organizations')->select('id', 'name')->get();
                      $ledgers = Ledger::where('organization_id',Helper::getAuthenticatedUser()->organization_id)->select('*')->orderBy('id', 'desc')->get();

                      $organ = Organization::whereIn('company_id',explode(',',$data->companies))->select('id', 'name')->get();

                      $unit = Budget::pluck('unit')->toArray();

                      $unit = array_values(array_unique($unit));

        return view('budget.edit',compact('series','companies','data','ledgers','unit','organ'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

        public function getRequests($book_id)
        {
            if(!empty(Auth::guard('web')->user()))
            {
                $organization_id = Auth::guard('web')->user()->organization_id;
            }
            elseif (!empty(Auth::guard('web2')->user())) 
            {
                $organization_id = Auth::guard('web2')->user()->organization_id;
            }
            else
            {
                $organization_id = 1;
            }

            $request = NumberPattern::where('book_id', $book_id)->where('organization_id',$organization_id)->select('prefix', 'suffix','starting_no','current_no')->first();

            $requestno = $request->prefix.$request->current_no.$request->suffix;

            return response()->json(['requestno' => $requestno]);
        }
}
