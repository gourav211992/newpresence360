<?php

namespace App\Http\Controllers\Kaizen;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Kaizen\ErpKaizen;
use Carbon\Carbon;
use Illuminate\Http\Request;


class IndexController extends Controller
{
    public function index(){
        return view('kaizen.dasboard');
    }
    public function getDashboard(Request $request){
        $user = Helper::getAuthenticatedUser();
      

        $columns = [
            'productivity_imp_id',
            'quality_imp_id',
            'moral_imp_id',
            'delivery_imp_id',
            'cost_imp_id',
            'safety_imp_id',
        ];

        $kaizens = ErpKaizen::with('department')
                    ->where('organization_id', $user->organization_id)
                    ->whereBetween('created_at', [Carbon::now()->subDays(30), Carbon::now()])
                    ->get(array_merge($columns, ['problem', 'counter_measure', 'department_id', 'cost_imp_id', 'innovation_imp_id']));

                $counts = collect($columns)->mapWithKeys(function ($col) use ($kaizens) {
                    return [
                        $col => $kaizens->whereNotNull($col)->where($col, '!=', '')->count()
                    ];
                });

        $data = $kaizens->map(function ($row) {
                    return [
                        'problem'       => $row->problem,
                        'countermeasure'=> $row->counter_measure,
                        'department'    => $row->department?->name, 
                        'cost'          => $row->cost,
                        'innovation'    => $row->innovation,
                    ];
                });

                return response()->json([
                    'counts' => $counts,
                    'data'   => $data,
                ]);
    }

    public function fetchEmployees(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $search = $request->get('search'); 
        $page = $request->get('page', 1); 

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
