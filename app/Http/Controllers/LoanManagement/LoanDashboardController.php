<?php

namespace App\Http\Controllers\LoanManagement;

use App\Helpers\Helper;
use Carbon\Carbon;
use App\Models\HomeLoan;
use App\Models\Recovery;
use App\Models\RecoveryLoan;
use Illuminate\Http\Request;
use App\Models\DisbursalLoan;
use App\Models\LoanSettlement;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\LoanDisbursement;

class LoanDashboardController extends Controller
{
    public function dashboard()
    {
        return view('loan.dashboard');
    }

    public function getDashboardLoanAnalytics(Request $request)
    {
        $time = $request->query('time', null);

        $startDate = $request->query('startDate', null);
        $endDate = $request->query('endDate', null);

        // Define the date range based on the filter value
        if (!$startDate || !$endDate) {
            // Define the date range based on the filter value
            switch ($time) {
                case 'this-month':
                    // Start of this month to the end of today
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfDay();
                    break;

                case 'last-month':
                    // Start and end of the previous month
                    $startDate = Carbon::now()->subMonth()->startOfMonth();
                    $endDate = Carbon::now()->subMonth()->endOfMonth();
                    break;

                case '3-months':
                    // Last 3 months from today to the end of today
                    $startDate = Carbon::now()->subMonths(3)->startOfDay();
                    $endDate = Carbon::now()->endOfDay();
                    break;

                case 'this-year':
                    // Start of the current year to the end of the current year
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    break;

                default:
                    // Default to 'this-month' range
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfDay();
                    break;
            }
        }

        $organization_id = Helper::getAuthenticatedUser()->organization_id;

        $loan_sums = HomeLoan::with('loanAppraisal') // Load the related loanAppraisal
            ->where('organization_id', $organization_id)
            ->whereNotIn('approvalStatus', ['draft', 'rejected'])
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->whereIn('type', [1, 2, 3]) // Ensure we only include relevant loan types
            ->get(); // Get the loans

        // Sum the term_loan for each loan type
        $home_loans = $loan_sums->where('type', 1)->sum(function ($loan) {
            return Helper::removeCommas($loan->loanAppraisal->term_loan ??0); // Add term_loan or 0 if not available
        });

        $vehicle_loans = $loan_sums->where('type', 2)->sum(function ($loan) {
            return Helper::removeCommas( $loan->loanAppraisal->term_loan??0);
        });

        $term_loans = $loan_sums->where('type', 3)->sum(function ($loan) {
            return Helper::removeCommas($loan->loanAppraisal->term_loan??0);
        });


        return response()->json([
            'home_loans' => intval($home_loans),
            'vehicle_loans' => intval($vehicle_loans),
            'term_loans' => intval($term_loans),
        ]);
    }

    public function getDashboardLoanKpi(Request $request)
    {
        $time = $request->query('time', null);

        $startDate = $request->query('startDate', null);
        $endDate = $request->query('endDate', null);
        $loanType = $request->query('loanType', null);
        $loan_type = null;

        if (!$startDate || !$endDate) {
            // Define the date range based on the filter value
            switch ($time) {
                case 'this-month':
                    // Start of this month to the end of today
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfDay();
                    break;

                case 'last-month':
                    // Start and end of the previous month
                    $startDate = Carbon::now()->subMonth()->startOfMonth();
                    $endDate = Carbon::now()->subMonth()->endOfMonth();
                    break;

                case '3-month':
                    // Last 3 months from today to the end of today
                    $startDate = Carbon::now()->subMonths(3)->startOfDay();
                    $endDate = Carbon::now()->endOfDay();
                    break;

                case 'this-year':
                    // Start of the current year to the end of the current year
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    break;

                default:
                    // Default to 'this-month' range
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfDay();
                    break;
            }
        }




        if ($loanType) {
            switch ($loanType) {
                case 'home-loan':
                    $loan_type = 1;
                    break;
                case 'vehicle-loan':
                    $loan_type = 2;
                    break;
                case 'term-loan':
                    $loan_type = 3;
                    break;
                default:
                    $loan_type = null;
                    break;
            }
        }

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


        $total_loans = HomeLoan::where('organization_id', $organization_id)
            ->whereNotIn('approvalStatus', ['draft', 'rejected'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($loan_type, function ($query) use ($loan_type) {
                return $query->where('type', $loan_type); // Apply the condition if $loan_type is not null
            })
            ->with('loanAppraisal') // Load the loanAppraisal relationship
            ->get()
            ->sum(function ($homeLoan) {
                return Helper::removeCommas($homeLoan->loanAppraisal->term_loan??0); // Use term_loan or 0 if relationship is missing
            });


        $recovery = RecoveryLoan::where('organization_id', $organization_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("SUM(CAST(REPLACE(recovery_amnnt, ',', '') AS DECIMAL(15, 2))) as total")
            ->value('total');




        $settlement = LoanSettlement::where('organization_id', $organization_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("SUM(CAST(REPLACE(settle_amnnt, ',', '') AS DECIMAL(15, 2))) as total")
            ->value('total');

        // Calculate recovery percentage
        if ($total_loans > 0) {
            $recovery_percentage = ($recovery / $total_loans) * 100;
            $settlement_percentage = ($settlement / $total_loans) * 100;
        } else {
            $recovery_percentage = 0;  // Avoid division by zero
            $settlement_percentage = 0; // Avoid division by zero
        }


        return response()->json([
            'total_loans' => number_format($total_loans),
            'recovery_percentage' => number_format($recovery_percentage),
            'settlement_percentage' => number_format($settlement_percentage, 2)
        ]);
    }

    public function getDashboardLoanSummary(Request $request)
    {
        $time = $request->query('time', null);

        $startDate = $request->query('startDate', null);
        $endDate = $request->query('endDate', null);
        $loanType = $request->query('loanType', null);
        $loan_type = null;

        if (!$startDate || !$endDate) {
            // Define the date range based on the filter value
            switch ($time) {
                case 'this-month':
                    // Start of this month to the end of today
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfDay();
                    break;

                case 'last-month':
                    // Start and end of the previous month
                    $startDate = Carbon::now()->subMonth()->startOfMonth();
                    $endDate = Carbon::now()->subMonth()->endOfMonth();
                    break;

                case '3-months':
                    // Last 3 months from today to the end of today
                    $startDate = Carbon::now()->subMonths(3)->startOfDay();
                    $endDate = Carbon::now()->endOfDay();
                    break;

                case 'this-year':
                    // Start of the current year to the end of the current year
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    break;

                default:
                    // Default to 'this-month' range
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfDay();
                    break;
            }
        }

        if ($loanType) {
            switch ($loanType) {
                case 'home-loan':
                    $loan_type = 1;
                    break;
                case 'vehicle-loan':
                    $loan_type = 2;
                    break;
                case 'term-loan':
                    $loan_type = 3;
                    break;
                default:
                    $loan_type = null;
                    break;
            }
        }

        $organization_id = Helper::getAuthenticatedUser()->organization_id;
        $commonQuery = HomeLoan::with('loanAppraisal') // Eager load the relationship
            ->where('organization_id', $organization_id)
            ->whereNotIn('approvalStatus', ['draft', 'rejected'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Home Loans
        $home_loans = (clone $commonQuery)
            ->where('type', 1)
            ->get()
            ->sum(function ($homeLoan) {
                return Helper::removeCommas($homeLoan->loanAppraisal->term_loan ?? 0);
            });

        // Vehicle Loans
        $vehicle_loans = (clone $commonQuery)
            ->where('type', 2)
            ->get()
            ->sum(function ($homeLoan) {
                return Helper::removeCommas($homeLoan->loanAppraisal->term_loan ?? 0);
            });

        // Term Loans (all loans)
        $term_loans = (clone $commonQuery)
            ->where('type', 3)
            ->get()
            ->sum(function ($homeLoan) {
                return Helper::removeCommas($homeLoan->loanAppraisal->term_loan ?? 0);
            });

        // Total Loans with conditional type filtering
        $total_loans = (clone $commonQuery)
            ->when($loan_type, function ($q) use ($loan_type) {
                return $q->where('type', $loan_type);
            }, function ($q) {
                return $q->whereIn('type', [1, 2, 3]);
            })
            ->get()
            ->sum(function ($homeLoan) {
                return Helper::removeCommas($homeLoan->loanAppraisal->term_loan ?? 0);
            });

        $recovery = RecoveryLoan::where('organization_id', $organization_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($loan_type, function ($query, $loan_type) {
                $query->whereHas('homeLoan', function ($subQuery) use ($loan_type) {
                    $subQuery->where('type', $loan_type); // Apply the filter only if $loan_type is not null
                });
            })->selectRaw("SUM(CAST(REPLACE(recovery_amnnt, ',', '') AS DECIMAL(15, 2))) as total")
            ->value('total');


        $disbursement = LoanDisbursement::where('organization_id', $organization_id)->where('approvalStatus', 'Disbursed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($loan_type, function ($query, $loan_type) {
                $query->whereHas('homeLoan', function ($subQuery) use ($loan_type) {
                    $subQuery->where('type', $loan_type); // Apply the filter only if $loan_type is not null
                });
            })->selectRaw("SUM(CAST(REPLACE(actual_dis, ',', '') AS DECIMAL(15, 2))) as total")
            ->value('total');


        $settlement = LoanSettlement::where('organization_id', $organization_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($loan_type, function ($query, $loan_type) {
                $query->whereHas('homeLoan', function ($subQuery) use ($loan_type) {
                    $subQuery->where('type', $loan_type); // Apply the filter only if $loan_type is not null
                });
            })
            ->selectRaw("SUM(CAST(REPLACE(settle_amnnt, ',', '') AS DECIMAL(15, 2))) as total")
            ->value('total');

        return response()->json([
            'between' =>  $startDate . '-' . $endDate,
            'total_loans' => number_format($total_loans),
            'home_loans' => intval($home_loans),
            'vehicle_loans' => intval($vehicle_loans),
            'term_loans' => intval($term_loans),
            'recovery' => number_format($recovery),
            'settlement' => number_format($settlement),
            'disbursement' => number_format($disbursement)
        ]);
    }
}
