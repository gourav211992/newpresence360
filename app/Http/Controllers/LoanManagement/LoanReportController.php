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
class LoanReportController extends Controller
{
    public function index()
    {
        $loans = HomeLoan::with('settlement', 'loanDisbursements', 'loanAppraisal','recoveryScheduleLoan', 'recoveryLoan')
        ->whereHas('series')->whereHas('loanAppraisal.recovery')
        ->whereHas('loanDisbursements')->orderBy('id', 'desc')->whereIn('approvalStatus', ['Disbursed','completed'])->get();


        $employees = Employee::get();
        $users = User::get();
        //$loanReportSchedulers = LoanReportScheduler::pluck('toable_id', 'toable_type')->toArray();
        $loanReportSchedulers = LoanReportScheduler::get();
        return view('loan.report', compact('loans', 'users', 'employees', 'loanReportSchedulers'));
    }

    public function getLoanFilter(Request $request)
    {
        $period = $request->query('period');
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');
        $loanType = $request->query('loanType');
        $customerId = $request->query('customer');
        $status = $request->query('status');

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
        $currentDate = Carbon::now()->format('Y-m-d');

        $query = HomeLoan::query();
        $query->with('settlement', 'loanDisbursements', 'loanAppraisal','recoveryScheduleLoan', 'recoveryLoan')
        ->whereHas('series')->whereHas('loanAppraisal.recovery')->whereHas('loanDisbursements')->orderBy('id', 'desc');

        $query->where('organization_id', $organization_id);
        $query->whereIn('approvalStatus', ['Disbursed','completed']);


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
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Loan Type Filter
        if ($loanType) {
            $query->where('type', $loanType);
        }

        // Vendor Filter
        if ($customerId) {
            $query->where('id', $customerId);
        }

        // Status Filter
        if ($status) {
            $query->where('approvalStatus', $status);
        }

        // Fetch Results
        $loan_reports = $query->get();

        try {
            $processedHomeLoans = $loan_reports->map(function ($homeLoan) use ($currentDate) {
                $recoveryScheduleLoans = $homeLoan->recoveryScheduleLoan;

                $outstandingAmount = $homeLoan->recoveryLoan->last()->balance_amount ?? ($homeLoan->status >= 4 ? $homeLoan->ass_recom_amnt : null);

                $nextLoan = $recoveryScheduleLoans->where('recovery_date', '>', $currentDate)->where('recovery_status', null)->first();
                $nextLoanAmount = $nextLoan->total ?? null;
                $nextEmiDate = $nextLoan ? Carbon::parse($nextLoan?->recovery_date)->format('d-m-Y') : null;

                return [
                    'home_loan' => $homeLoan,
                    'out_standing_amount' => $outstandingAmount,
                    'next_recovery_loan' => $nextLoanAmount,
                    'next_emi_date' => $nextEmiDate
                ];
            });
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        return response()->json([
            'loan_reports' => $processedHomeLoans,
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
