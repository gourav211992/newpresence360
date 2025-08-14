<?php

namespace App\Exports;

use App\Models\HomeLoan;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LoanReportExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query()
    {
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 1;
            $utype = 'user';
        }
        elseif (!empty(Auth::guard('web2')->user())) 
        {
            $organization_id = Auth::guard('web2')->user()->organization_id;
            $user_id = Auth::guard('web2')->user()->id;
            $type = 2;
            $utype ='employee';
        }
        else
        {
            $organization_id = 1;
            $user_id = 1;
            $type = 1;
            $utype = 'user';
        }
        return HomeLoan::query()
            ->with('recoveryScheduleLoan')
            ->withSum('recoveryScheduleLoan as recovery_total', 'total')
            ->where('organization_id', $organization_id)
            // Legals created by the user
            ->where('user_id', $user_id)
            ->where('type', $type)
        ;

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }
    }

    public function headings(): array
    {
        $headings = [
            "Loan Id", // Loan Id
            "loan Type", // Loan Type
            "Customer", // Customer
            "Loan Amount", // Loan Amount
            "Recemmended Amount", // Recemmended Amount
            "Interest Rate", // Interest Rate
            "Tenure", // Tenure
            "EMI Amount", // EMI Amount
            "Out Standing Balance", // Out Standing Balance
            "Next EMI Due Date", // Next EMI Due Date
            "Loan Status", // Status
        ];

        return $headings;
    }

    public function map($loanReport): array
    {
        // $vendorName = $loanReport->vendor ? $loanReport->vendor->company_name : 'N/A';
        // $categoryName = $poItem->item->category ? $poItem->item->category->name : 'N/A';
        $type = match($loanReport->type) {
            1 => "Home",
            2 => "Vehicle",
            3 => "Term",
            default => "N/A",
        };
        // Add the dynamic po_items fields for each item
        $mappedData = [
            $loanReport->appli_no,
            $type,
            $loanReport->name,
            $loanReport->loan_amount,
            $loanReport->ass_recom_amnt,
            $loanReport->recovery_sentioned,
            $this->getPeriod($loanReport->recovery_repayment_type, $loanReport->recovery_repayment_period),
            $loanReport->recovery_total,
            "out standing",
            "next due date",
            ($loanReport->status == 1) ? "Active" : "Close",
        ];
        //dd($mappedData);
        // Flatten the array in case po_items are nested within an array
        return $mappedData;

    }


    private function getPeriod($type, $period)
    {
        return match ($type) {
            "Yearly" => $period * 12,      // 1 year = 12 months
            "Half-Yearly" => $period * 6,  // Half-year = 6 months
            "Monthly" => $period,          // 1 month = 1 month
            "Quarterly" => $period * 3,    // 1 quarter = 3 months
            default => 0,                  // Invalid type or unsupported period type
        };
    }
}
