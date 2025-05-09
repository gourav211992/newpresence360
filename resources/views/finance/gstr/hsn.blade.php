<table class="datatables-basic table myrequesttablecbox">
    <thead>
        <tr>
            <th>#</th>
            <th>HSN</th>
            <th>Description</th>
            <th>UQC</th>
            <th>Total Quantity</th>
            <th>Taxable Value</th>
            <th>Integrated Tax Amount</th>
            <th>Central Tax Amount</th>
            <th>State/UT Tax Amount</th>
            <th>Cess Amount</th>
            <th>Rate</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($gstrData as $key => $item)
            <tr class="trail-bal-tabl-none">
                <td>{{ $gstrData->firstItem() + $key }}</td>
                <td>{{ $item->hsn_code }}</td>
                <td>{{ $item->description ? $item->description : '-' }}</td>
                <td>{{ $item->uqc ? $item->uqc : '-' }}</td>
                <td>{{ $item->qty ? $item->qty : '-' }}</td>
                <td>{{ $item->taxable_amt ? number_format($item->taxable_amt, 2) : '-' }}</td>
                <td>{{ $item->igst ? number_format($item->igst, 2) : 0 }}</td>
                <td>{{ $item->cgst ? number_format($item->cgst, 2) : 0 }}</td>
                <td>{{ $item->sgst ? number_format($item->sgst, 2) : 0 }}</td>
                <td>{{ $item->cess ? number_format($item->cess, 2) : 0 }}</td>
                <td>{{ $item->rate ? $item->rate . '%' : 0 }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="text-center text-danger">No record(s) found</td>
            </tr>
        @endforelse
    </tbody>
</table>
