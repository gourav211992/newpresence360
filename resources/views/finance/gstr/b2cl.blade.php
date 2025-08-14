<table class="datatables-basic table myrequesttablecbox">
    <thead>
        <tr>
            <th>#</th>
            <th>Invoice Number</th>
            <th>Invoice date</th>
            <th>Invoice Value</th>
            <th>Place Of Supply</th>
            <th>Applicable % of Tax Rate</th>
            <th>Rate</th>
            <th>Taxable Value</th>
            <th>Cess Amount</th>
            <th>E-Commerce GSTIN</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($gstrData as $key => $item)
            <tr class="trail-bal-tabl-none">
                <td>{{ $gstrData->firstItem() + $key }}</td>
                <td>{{ $item->invoice_no ? $item->invoice_no : '-' }}</td>
                <td>{{ $item->invoice_date ? App\Helpers\GeneralHelper::dateFormat3($item->invoice_date) : '-' }}</td>
                <td>{{ $item->invoice_amt ? number_format($item->invoice_amt, 2) : '-' }}</td>
                <td>{{ $item->place_of_supply ? $item->pos . '-' . $item->place_of_supply : '-' }}</td>
                <td>{{ $item->applicable_tax_rate ? $item->applicable_tax_rate : 0 }}</td>
                <td>{{ $item->rate ? $item->rate . '%' : 0 }}</td>
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
