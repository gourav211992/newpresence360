<?php

namespace App\Http\Controllers\Kaizen;

use App\Helpers\Helper;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Kaizen\ErpKaizen;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    public function index()
    {
        return view('kaizen.dasboard');
    }
    public function getDashboard(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $fromDate = $request->from_date? Carbon::parse($request->from_date)->startOfDay(): Carbon::now()->subDays(30);
        $toDate = $request->to_date? Carbon::parse($request->to_date)->endOfDay(): Carbon::now();
        $rangeText = Carbon::parse($fromDate)->format('F Y') . ' - ' . Carbon::parse($toDate)->format('F Y');


        $currentYear = Carbon::now()->year;

        // 1) Monthly chart data (organization wise)
        $rawData = ErpKaizen::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->whereYear('created_at', $currentYear)
            ->where('organization_id', $user->organization_id)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $months = collect();
        for ($m = 1; $m <= 12; $m++) {
            $months->put(sprintf("%d-%02d", $currentYear, $m), 0);
        }

        $monthly = $months->map(function ($val, $month) use ($rawData) {
            return $rawData[$month] ?? 0;
        });

        $monthdata = [
            'labels' => $monthly->keys()->map(function ($month) {
                return Carbon::parse($month . '-01')->format('M');
            }),
            'values' => $monthly->values(),
        ];

        $labels = $monthdata['labels'];
        $values = $monthdata['values'];

        // Columns to check
        $columns = [
            'productivity_imp_id',
            'quality_imp_id',
            'moral_imp_id',
            'delivery_imp_id',
            'cost_imp_id',
            'safety_imp_id',
        ];

        // 2) Query for all kaizens (without limit) just for counts
        // $kaizensForCount = ErpKaizen::where('organization_id', $user->organization_id)
        //     ->whereBetween('created_at', [$fromDate, $toDate]) 
        //     ->get($columns);

        // // Count non-null values
        // $counts = collect($columns)->mapWithKeys(function ($col) use ($kaizensForCount) {
        //     return [
        //         $col => $kaizensForCount->whereNotNull($col)->where($col, '!=', '')->count()
        //     ];
        // });
        $counts = ErpKaizen::where('organization_id', $user->organization_id)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->selectRaw("
                COUNT(NULLIF(productivity_imp_id, '')) as productivity_imp_id,
                COUNT(NULLIF(quality_imp_id, '')) as quality_imp_id,
                COUNT(NULLIF(moral_imp_id, '')) as moral_imp_id,
                COUNT(NULLIF(delivery_imp_id, '')) as delivery_imp_id,
                COUNT(NULLIF(cost_imp_id, '')) as cost_imp_id,
                COUNT(NULLIF(safety_imp_id, '')) as safety_imp_id,
                SUM(cost_saving_amt) as total_cost_saving_amt
            ")
            ->first();

        // 3) Query with relations + limit (for list)
        $kaizens = ErpKaizen::with([
            'department:id,name',
            'createdBy:id,designation_id,name',
            'createdBy.designation:id,marks',
            'cost:id,description',
            'delivery:id,description',
            'moral:id,description',
            'innovation:id,description',
            'safety:id,description',
            'quality:id,description',
            'productivity:id,description'
        ])
            ->where('organization_id', $user->organization_id)
            // ->whereBetween('created_at', [Carbon::now()->subDays(30), Carbon::now()])
            ->whereBetween('created_at', [$fromDate, $toDate]) 
            ->limit(5)
            ->get(array_merge($columns, [
                'problem',
                'counter_measure',
                'score',
                'created_by',
                'department_id'
            ]));

        // Transform kaizens into clean response
        $evaluatorNames = $kaizens->pluck('createdBy.name')->filter()->unique()->implode(', ');
        $data = $kaizens->map(function ($row) {
            // $evaluatorNames.=$row->createdBy?->name . ', ';
            return [
                'problem'        => $row->problem,
                'countermeasure' => $row->counter_measure,
                'department'     => $row->department?->name,
                'designation'    => $row->created_by_designation_marks,
                'cost'           => $row->cost?->description,
                'delivery'       => $row->delivery?->description,
                'moral'          => $row->moral?->description,
                'safety'         => $row->safety?->description,
                'quality'        => $row->quality?->description,
                'productivity'   => $row->productivity?->description,
                'innovation'     => $row->innovation?->description,
                'score'          => $row->score,
                'total_score'    => $row->total_score,
                'createdBy'      => $row->createdBy?->name,
            ];
        });
        // top 3 identifier
        $topIdentifiers = $kaizens->groupBy(function ($item) {
            return $item->createdBy->name;
        })->map(function ($group) {
            return [
                'name'  => $group->first()->createdBy->name,
                'count' => $group->count(),
            ];
        })->sortByDesc('count')->take(3)->values(); 
        $counts->total_cost_saving_amt=Helper::currencyFormat($counts->total_cost_saving_amt, 'display');
        return response()->json([
            'counts' => $counts,
            'topIdentifiers' => $topIdentifiers,
            'chart'   => [
                'labels' => $labels,
                'values' => $values,
            ],
            'data'   => $data,
            'rangedate' => $rangeText,
            'evaluatorNames' => $evaluatorNames,
        ]);
    }


    public function fetchEmployees(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $search = $request->get('search');
        $page = $request->get('page', 1);

        if ($request->has('id')) {
            $employee = Employee::select('id', 'name', 'email', 'mobile')->find($request->id);
            return response()->json([
                'success' => true,
                'data' => $employee,
            ]);
        }

        $employees = Employee::select('id', 'name', 'email', 'mobile')
            ->where('name', 'like', '%' . $search . '%')
            ->where('organization_id', $user->organization_id)
            ->paginate(10);

        return [
            'success' => true,
            'data' => [
                'employees' => $employees->items(),
                'pagination' => $employees->hasMorePages()
            ]
        ];
    }
}
