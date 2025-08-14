<table class="datatables-basic table myrequesttablecbox">
    <thead>
        <tr>
            <th>#</th>
            <th>Financial Year</th>
            <th>Original Month</th>
            <th>Place Of Supply</th>
            <th>Type</th>
            <th>Rate</th>
            <th>Applicable % of Tax Rate</th>
            <th>Taxable Value</th>
            <th>Cess Amount</th>
            <th>E-Commerce GSTIN</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($gstrData as $key => $item)
            <tr class="trail-bal-tabl-none">
                <td>{{ $gstrData->firstItem() + $key }}</td>
                <td>{{ $item->year ? $item->year : '-' }}</td>
                <td>{{ $item->month ? \DateTime::createFromFormat('!m', $item->month)->format('F') : '-' }}</td>
                <td>{{ $item->place_of_supply ? $item->pos . '-' . $item->place_of_supply : '-' }}</td>
                <td>{{ $item->invoice_type ? $item->invoice_type : '-' }}</td>
                <td>{{ $item->rate ? $item->rate . '%' : 0 }}</td>
                <td>{{ $item->applicable_tax_rate ? $item->applicable_tax_rate : 0 }}</td>
                <td>{{ $item->taxable_amt ? number_format($item->taxable_amt, 2) : 0 }}</td>
                <td>{{ $item->cess ? number_format($item->cess, 2) : 0 }}</td>
                <td>{{ $item->e_commerce_gstin ? $item->e_commerce_gstin : '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center text-danger">No record(s) found</td>
            </tr>
        @endforelse
    </tbody>

</table>
