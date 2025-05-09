<table class="datatables-basic table myrequesttablecbox">
    <thead>
        <tr>
            <th>#</th>
            <th>UR Type</th>
            <th>Note Number</th>
            <th>Note date</th>
            <th>Note Type</th>
            <th>Place Of Supply</th>
            <th>Note Value</th>
            <th>Applicable % of Tax Rate</th>
            <th>Rate</th>
            <th>Taxable Value</th>
            <th>Cess Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($gstrData as $key => $item)
            <tr class="trail-bal-tabl-none">
                <td>{{ $gstrData->firstItem() + $key }}</td>
                <td>{{ $item->ur_type }}</td>
                <td>{{ $item->note_number ? $item->note_number : '-' }}</td>
                <td>{{ $item->note_date ? App\Helpers\GeneralHelper::dateFormat3($item->note_date) : '-' }}</td>
                <td>{{ $item->note_type ? $item->note_type : '-' }}</td>
                <td>{{ $item->place_of_supply ? $item->pos . '-' . $item->place_of_supply : '-' }}</td>
                <td>{{ $item->note_value ? number_format($item->note_value, 2) : '-' }}</td>
                <td>{{ $item->applicable_tax_rate ? $item->applicable_tax_rate : 0 }}</td>
                <td>{{ $item->rate ? $item->rate . '%' : 0 }}</td>
                <td>{{ $item->taxable_amt ? number_format($item->taxable_amt, 2) : 0 }}</td>
                <td>{{ $item->cess ? number_format($item->cess, 2) : 0 }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="16" class="text-center text-danger">No record(s) found</td>
            </tr>
        @endforelse
    </tbody>

</table>
