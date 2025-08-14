<?php

namespace App\Http\Controllers\Land;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Helpers\Helper;
use App\Models\Employee;
use App\Exports\LandExport;
use Illuminate\Http\Request;
use App\Models\LandScheduler;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;
use App\Models\LandParcel;
use App\Models\LandLease;
class LandReportController extends Controller
{
    public function index()
    {
        $leases = LandLease::whereHas('land')->get();
        $employees = Employee::get();
        $users = User::get();

        return view('land.report', compact('leases', 'users', 'employees'));
    }

    public function getLandReport(Request $request)
    {
        try {
            $period = $request->query('period');
            $startDate = $request->query('startDate');
            $endDate = $request->query('endDate');
            $series = $request->query('series');
            $customer = $request->query('customer');
            $land = $request->query('landId');
            $area = $request->query('area');
            $landCost = $request->query('landCost');
            $khasaraNumber = $request->query('khasaraNumber');
            $paymentLastReceived = $request->query('paymentLastReceived');
            $totalLeaseAmount = $request->query('totalLeaseAmount');
            $leaseDuration = $request->query('leaseDuration');
            $monthlyInstallment = $request->query('monthlyInstallment');

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

            $query = LandLease::query()
                ->with([
                    'land',
                    'customer',
                    'schedules' => function ($query) {
                        $query->latest();  // Only interested in the latest recovery
                    }
                ])->withSum('schedules', 'installment_cost')
                ->where('organization_id', $organization_id)->whereHas('land')->whereIn('approvalStatus',['Approved','approval_not_required','approved']);

            // Date Filtering
            if (($startDate && $endDate) || $period) {
                if (!$startDate || !$endDate) {
                    switch ($period) {
                        case 'this-month':
                            $startDate = Carbon::wnow()->startOfMonth();
                            $endDate = Carbon::now()->endOfMonth();
                            break;
                        case 'last-month':
                            $startDate = Carbon::now()->subMonth()->startOfMonth();
                            $endDate = Carbon::now()->subMonth()->endOfMonth();
                            break;
                        case 'this-year':
                            $startDate = Carbon::now()->startOfYear();
                            $endDate = Carbon::now()->endOfYear();
                            break;
                    }
                }
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            // Vendor Filter
            if ($series) {
                $query->where('id', $series);
            }

            if ($customer) {
                $query->where('customer_id', $customer);
            }
            if ($land) {
                $query->where('land_id', $land);
            }
            // Status Filter
            if ($request->area) {
                $query->whereHas('land', function ($query) use ($request) {
                    $query->whereRaw("CONCAT(plot_area, '(', area_unit, ')') LIKE ?", ["%{$request->area}%"]);
                });
            }
            if ($landCost) {
                $query->whereHas('land', function ($query) use ($landCost) {
                    $query->where('land_valuation', 'like', "%{$landCost}%");
                });
            }

            if ($khasaraNumber) {
                
                $query->whereHas('land', function ($query) use ($khasaraNumber) {
                    $query->where('khasara_no', $khasaraNumber);
                });
            }

            if ($paymentLastReceived) {
                $query->whereHas('recovery', function ($query) use ($paymentLastReceived) {
                    $query->whereDate('paymentLastReceived', $paymentLastReceived)
                        ->orderBy('created_at', 'desc')
                        ->limit(1);
                });
            }


            if ($totalLeaseAmount) {
                $query->where('total_amount', 'like', "%{$totalLeaseAmount}%");
            }

            // if ($leaseDuration) {
            //     $query->whereRaw("
            //     CASE
            //         WHEN period_type = 'Monthly' THEN lease_time
            //         WHEN period_type = 'Quarterly' THEN lease_time * 3
            //         WHEN period_type = 'Yearly' THEN lease_time * 12
            //     END = ?", [$leaseDuration]);
            // }



            if ($leaseDuration) {
                $query->where("lease_time", $leaseDuration);
            }

            if ($monthlyInstallment) {
                $query->where('installment_amount', 'like', "%{$monthlyInstallment}%");
            }

            // Fetch Results
            $lease_reports = $query->get();

            return response()->json($lease_reports);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function addScheduler(Request $request)
    {
        // Validate request data
        $validatedData = $request->validate([
            'to' => 'required|array',
            'type' => 'required|string',
            'date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);
        $toIds = $validatedData['to'];

        foreach ($toIds as $toId) {
            LandScheduler::updateOrCreate(
                [
                    'toable_id' => $toId['id'],
                    'toable_type' => $toId['type']
                ],
                [
                    'type' => $validatedData['type'],
                    'date' => $validatedData['date'],
                    'remarks' => $validatedData['remarks']
                ]
            );
        }

        return Response::json(['success' => 'Scheduler Added Successfully!']);
    }

    public function sendReportMail()
    {
        $fileName = $this->getFileName();
        $user = Auth::user();

        $startDate = null;
        $endDate = null;
        // Create the export object with the date range
        $excelData = Excel::raw(new LandExport($startDate, $endDate), \Maatwebsite\Excel\Excel::XLSX);

        // Save the file locally for debugging
        $filePath = storage_path('app/public/land-report/' . $fileName);
        $directoryPath = storage_path('app/public/land-report');

        if (!file_exists($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }
        file_put_contents($filePath, $excelData);

        // Check if the file exists
        if (!file_exists($filePath)) {
            throw new \Exception('File does not exist at path: ' . $filePath);
        }

        Mail::send('emails.land_report', [], function ($message) use ($user, $filePath) {
            $message->to($user->email)
                ->subject('Land Report')
                ->attach($filePath);
        });

        return Response::json(['success' => 'Send Mail Successfully!']);
    }

    private function getFileName()
    {
        $now = carbon::now()->format('Y-m-d_H-i-s');
        return "purchase_order_report_{$now}.xlsx";
    }

    public function recoverySchedulerReport(Request $request)
    {
        $land_no = $request->query('landNo');
        $organization_id = Helper::getAuthenticatedUser()->organization->id;

        $recovried = LandLease::where('organization_id', $organization_id)
        ->where('land_id', $land_no)
        ->with(['land', 'schedules' => function ($query) {
            $query->orderBy('due_date', 'asc'); // Sort schedules by due_date in ascending order
        }])
        ->get();

        return Response::json(['recovried' => $recovried]);
    }
}


