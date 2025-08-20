<?php

namespace App\Http\Controllers\Kaizen;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Employee;

use Illuminate\Http\Request;


class IndexController extends Controller
{
    public function index(){
        return view('kaizen.dasboard');
    }

    public function fetchEmployees(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $search = $request->get('search'); // The search term from the select2
        $page = $request->get('page', 1);  // The current page from select2

        if ($request->has('id')) {
            $employee = Employee::select('id','name','email','mobile')->find($request->id);
            return response()->json([
                'success' => true,
                'data' => $employee,
            ]);
        }

        $employees = Employee::select('id','name','email','mobile')
                        ->where('name', 'like', '%' . $search . '%')
                        ->where('organization_id',$user->organization_id)
                        ->paginate(10);

        return [
            'success' => true,
            'data' => [
                'employees' => $employees->items(),
                'pagination' => $employees->hasMorePages() // Indicate if there are more pages
            ]
        ];
    }
}
