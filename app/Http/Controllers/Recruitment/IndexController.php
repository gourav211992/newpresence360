<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(){
        return view('recruitment.index');
    }

    public function fetchEmployees(Request $request)
    {
        $search = $request->get('search'); // The search term from the select2
        $page = $request->get('page', 1);  // The current page from select2

        if ($request->has('id')) {
            $employee = Employee::select('id','name')->find($request->id);
            return response()->json([
                'success' => true,
                'data' => $employee ? [ $employee ] : [],
            ]);
        }

        $employees = Employee::select('id','name')
                        ->where('name', 'like', '%' . $search . '%')
                        ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $employees->items(),
            'pagination' => [
                'more' => $employees->hasMorePages() // Indicate if there are more pages
            ]
        ]);
    }

    public function fetchEmails(Request $request)
    {
        $search = $request->get('search'); // The search term from the select2
        $page = $request->get('page', 1);  // The current page from select2

        if ($request->has('id')) {
            $employee = Employee::select('id','email')->find($request->id);
            return response()->json([
                'success' => true,
                'data' => $employee ? [ $employee ] : [],
            ]);
        }

        $employees = Employee::select('id','email')
                        ->where('email', 'like', '%' . $search . '%')
                        ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $employees->items(),
            'pagination' => [
                'more' => $employees->hasMorePages() // Indicate if there are more pages
            ]
        ]);
    }
}
