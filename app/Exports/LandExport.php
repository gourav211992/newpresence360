<?php

namespace App\Exports;

use App\Models\Lease;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LandExport implements FromQuery, WithHeadings, WithMapping
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
        return Lease::query()->with([
            'land',
            'recovery' => function ($query) {
                $query->latest();  // Only interested in the latest recovery
            }
        ])->withSum('recovery', 'received_amount')
            ->where('organization_id', $organization_id)
            // Legals created by the user
            ->where('user_id', $user_id)
            ->where('type', $type);

        // Apply the date filter only if both start and end dates are provided
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }
    }

    public function headings(): array
    {
        $headings = [
            'ID', // ID
            'Series',
            'Customer Name',
            'Land Number',
            'Area (in SQ FT)',
            'Land Cost',
            'Total Lease Amount',
            'Lease Duration',
            'Lease Type',
            'Installment Amount',
            'Total Received',
            'Amount Due',
            'Overdue (Days)',
        ];

        return $headings;
    }

    public function map($leaseReport): array
    {
        $paymentDate = $leaseReport->recovery[0]->date_of_payment ?? null;
        $daysDifference = null;
        if ($paymentDate !== null) {
            $paymentDateObj = Carbon::parse($paymentDate);
            $currentDateObj = Carbon::now();
            $daysDifference = $currentDateObj->diffInDays($paymentDateObj);
        }

        // Add the dynamic po_items fields for each item
        return [
            $leaseReport->id,
            $leaseReport->series,
            $leaseReport->customer,
            $leaseReport->land_no,
            $leaseReport->area_sqft,
            $leaseReport->cost,
            $leaseReport->lease_cost,
            $leaseReport->lease_time,
            $leaseReport->period_type,
            $leaseReport->installment_cost,
            $leaseReport->recovery_sum_received_amount ?? "null",
            $leaseReport->recovery[0]?->bal_lease_cost ?? "null",
            $daysDifference,
        ];
    }
}
