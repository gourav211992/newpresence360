<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Http\Controllers\CrDrReportController;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Log;

class CrDrReportExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    protected $type;
    protected $ledger_id;
    protected $ledger_group_id;

    public function __construct($type, $ledger_id, $ledger_group_id)
    {
        $this->type = $type;
        $this->ledger_id = $ledger_id;
        $this->ledger_group_id = $ledger_group_id;
    }

    public function collection()
    {
        return collect(CrDrReportController::getLedgerDetailsReport($this->type, $this->ledger_id, $this->ledger_group_id));  
    }

    public function headings(): array
    {
        return ['Date', 'Bill No', 'Voucher No', 'O/S Days', 'Amount'];
    }

    public function map($purchaseOrder): array
    {
        $outstanding = optional($purchaseOrder)->total_outstanding ?? 0;
        
        // Check if the total_outstanding is less than 0
        if ($outstanding < 0) {
            // If type is debit, write 'Cr' with the positive amount
            if ($this->type == 'debit') {
                $outstanding = 'Cr ' . abs($outstanding); // Absolute value for positive representation
            }
            // If type is credit, write 'Dr' with the positive amount
            elseif ($this->type == 'credit') {
                $outstanding = 'Dr ' . abs($outstanding); // Absolute value for positive representation
            }
        }
    
        return [
            optional($purchaseOrder)->document_date ?? '',
            optional($purchaseOrder)->bill_no ?? '',
            optional($purchaseOrder)->voucher_no ?? '',
            optional($purchaseOrder)->overdue_days ?? '',
            $outstanding, // Return the formatted outstanding amount
        ];
    }
    
}

