<table class="datatables-basic table myrequesttablecbox">
    <thead>
        <tr>
            <th>#</th>
            <th>Place Of Supply</th>
            <th>Taxable Value</th>
            <th>Cess Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($gstrData as $key => $item)
            <tr class="trail-bal-tabl-none">
                <td>{{ $gstrData->firstItem() + $key }}</td>
                <td>{{ $item->place_of_supply ? $item->pos . '-' . $item->place_of_supply : '-' }}</td>
                <td>{{ $item->taxable_amt ? number_format($item->taxable_amt, 2) : 0 }}</td>
                <td>{{ $item->cess ? number_format($item->cess, 2) : 0 }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center text-danger">No record(s) found</td>
            </tr>
        @endforelse
    </tbody>
</table>
