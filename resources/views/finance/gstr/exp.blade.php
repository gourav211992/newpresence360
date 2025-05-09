<table class="datatables-basic table myrequesttablecbox">
    <thead>
        <tr>
            <th>#</th>
            <th>Export Type</th>
            <th>Invoice Number</th>
            <th>Invoice date</th>
            <th>Invoice Value</th>
            <th>Port Code</th>
            <th>Shipping Bill Number</th>
            <th>Shipping Bill Date</th>
            <th>Rate</th>
            <th>Taxable Value</th>
            <th>Cess Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($gstrData as $key => $item)
            <tr class="trail-bal-tabl-none">
                <td>{{ $gstrData->firstItem() + $key }}</td>
                <td>{{ $item->exp_type }}</td>
                <td>{{ $item->invoice_no ? $item->invoice_no : '-' }}</td>
                <td>{{ $item->invoice_date ? App\Helpers\GeneralHelper::dateFormat3($item->invoice_date) : '-' }}</td>
                <td>{{ $item->invoice_amt ? number_format($item->invoice_amt, 2) : 0 }}</td>
                <td>{{ $item->port_code ? $item->port_code : '-' }}</td>
                <td>{{ $item->shipping_bill_no ? $item->shipping_bill_no : 0 }}</td>
                <td>{{ $item->shipping_bill_date ? App\Helpers\GeneralHelper::dateFormat3($item->shipping_bill_date) : 0 }}
                </td>
                <td>{{ $item->rate ? $item->rate . '%' : 0 }}</td>
                <td>{{ $item->taxable_amt ? number_format($item->taxable_amt, 2) : 0 }}</td>
                <td>{{ $item->cess ? number_format($item->cess, 2) : 0 }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="text-center text-danger">No record(s) found</td>
            </tr>
        @endforelse
    </tbody>
</table>
