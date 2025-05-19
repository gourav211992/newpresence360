<?php

namespace App\Http\Controllers\LoanManagement;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Employee;
use App\Models\HomeLoan;
use Illuminate\Http\Request;
use App\Exports\LoanReportExport;
use App\Models\LoanReportScheduler;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;
use App\Helpers\Helper;
use App\Models\LoanDisbursement;
use App\Helpers\ConstantHelper;
use Illuminate\Support\Facades\DB;
class LoanDisbursementReportController extends Controller
{
    public function index()
    {
        $loans = LoanDisbursement::where('organization_id',Helper::getAuthenticatedUser()->organization_id)->get();
        $employees = Employee::get();
        $users = User::get();
        $loanReportSchedulers = LoanReportScheduler::get();
        return view('loan.disbursementreport', compact('loans', 'users', 'employees', 'loanReportSchedulers'));
    }

    public function getFilter(Request $request)
    {
        $period = $request->query('period');
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');
        $loanType = $request->query('loanType');
        $customerId = $request->query('customer');
        $status = $request->query('status');

        // Determine user context
        if (Auth::guard('web')->check()) {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 1;
            $utype = 'user';
        } elseif (Auth::guard('web2')->check()) {
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

        $query = LoanDisbursement::with('homeLoan')
        ->where('organization_id',$organization_id)
            ->select('erp_loan_disbursements.*')
            ->whereIn('approvalStatus', [
                'Requested', 'Approved', 'approved', ConstantHelper::APPROVAL_NOT_REQUIRED,
                'Rejected', 'Assessed', 'Disbursed'
            ])
            ->whereNotNull('loan_amount')
            ->orderBy('id', 'desc');

        // Date Filtering
        if (($startDate && $endDate) || $period) {
            if (!$startDate || !$endDate) {
                switch ($period) {
                    case 'this-month':
                        $startDate = Carbon::now()->startOfMonth();
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
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);
        }

        // Loan Type Filter
        if ($loanType) {
            $query->when($loanType, function ($query) use ($loanType) {
                $query->whereHas('homeLoan', function ($q) use ($loanType) {
                    $q->where('type', $loanType);
                });
            });
        }

        // Customer Filter
        if ($customerId) {
                    $query->where('home_loan_id', $customerId);
         }

        // Status Filter
        if ($status) {
            $query->where('approvalStatus', $status);
        }

        // Fetch Results
        $loan_reports = $query->get();

        foreach ($loan_reports as $report) {
            $totalDisAmount = LoanDisbursement::where('home_loan_id', $report->home_loan_id)
                ->where('id', '<=', $report->id)
                ->whereIn('approvalStatus', [
                    'Requested', 'Approved', 'approved', 'Rejected', 'Assessed', 'Disbursed'
                ])
                ->sum(DB::raw('COALESCE(actual_dis, dis_amount)'));
            $report->totalDisAmount = $totalDisAmount ? Helper::formatIndianNumber($totalDisAmount) : '-';
            $report->pendingDisAmount = $report->homeLoan->loan_amount - $totalDisAmount;
        }

        return response()->json([
            'loan_reports' => $loan_reports,
        ]);
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
            LoanReportScheduler::updateOrCreate(
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
        $user = Helper::getAuthenticatedUser();

        $startDate = null;
        $endDate = null;
        // Create the export object with the date range
        $excelData = Excel::raw(new LoanReportExport($startDate, $endDate), \Maatwebsite\Excel\Excel::XLSX);

        // Save the file locally for debugging
        $filePath = storage_path('app/public/loan-report/' . $fileName);
        $directoryPath = storage_path('app/public/loan-report');

        if (!file_exists($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }
        file_put_contents($filePath, $excelData);

        // Check if the file exists
        if (!file_exists($filePath)) {
            throw new \Exception('File does not exist at path: ' . $filePath);
        }

        Mail::send('emails.loan_report', [], function ($message) use ($user, $filePath) {
            $message->to($user->email)
                ->subject('Purchase Order Report')
                ->attach($filePath);
        });

        return Response::json(['success' => 'Send Mail Successfully!']);
    }

    private function getFileName()
    {
        $now = carbon::now()->format('Y-m-d_H-i-s');
        return "loan_report_{$now}.xlsx";
    }
}
