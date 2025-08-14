<?php

namespace App\Exports;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Http\Controllers\PaymentVoucherController;

class PaymentReceiptReportExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    protected $payment_id;
    protected $ledger_id;
    protected $ledger_group_id;

    public function __construct($payment_id, $ledger_id, $ledger_group_id)
    {
        $this->payment_id = $payment_id;
        $this->ledger_id = $ledger_id;
        $this->ledger_group_id = $ledger_group_id;
    }

    public function collection()
    {
        return collect(PaymentVoucherController::getPrint($this->payment_id, $this->ledger_id, $this->ledger_group_id,'excel'));  
    }

    public function headings(): array
    {
        return ['Date', 'Bill No', 'Voucher Amount', 'Paid', 'Balance'];
    }

    public function map($payments): array
    {
        return [
            optional($payments)->document_date ?? '',
            optional($payments)->bill_no ?? '',
            optional($payments)->voucher_amount ?? '',
            optional($payments)->paid ?? '',
            optional($payments)->balance ?? '',
        ];
    }
    
}

