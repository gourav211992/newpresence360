<table class="datatables-basic table myrequesttablecbox">
    <thead>
        <tr>
            <th>#</th>
            <th>Place Of Supply</th>
            <th>Applicable % of Tax Rate</th>
            <th>Rate</th>
            <th>Gross Advance Received</th>
            <th>Cess Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($gstrData as $key => $item)
            <tr class="trail-bal-tabl-none">
                <td>{{ $gstrData->firstItem() + $key }}</td>
                <td>{{ $item->place_of_supply ? $item->pos . '-' . $item->place_of_supply : '-' }}</td>
                <td>{{ $item->applicable_tax_rate ? $item->applicable_tax_rate : 0 }}</td>
                <td>{{ $item->rate ? $item->rate . '%' : 0 }}</td>
                <td>{{ $item->taxable_amt ? number_format($item->taxable_amt, 2) : 0 }}</td>
                <td>{{ $item->cess ? number_format($item->cess, 2) : 0 }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-danger">No record(s) found</td>
            </tr>
        @endforelse
    </tbody>

</table>
