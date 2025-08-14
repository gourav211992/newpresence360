<table class="datatables-basic table myrequesttablecbox">
    <thead>
        <tr>
            <th>#</th>
            <th>Supplier GSTIN/UIN</th>
            <th>Supplier Name</th>
            <th>Recipient GSTIN/UIN</th>
            <th>Recipient Name</th>
            <th>Document Number</th>
            <th>Document Date</th>
            <th>Value of supplies made</th>
            <th>Place Of Supply</th>
            <th>Document type</th>
            <th>Rate</th>
            <th>Taxable Value</th>
            <th>Cess Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($gstrData as $key => $item)
            <tr class="trail-bal-tabl-none">
                <td>{{ $gstrData->firstItem() + $key }}</td>
                <td>{{ $item->supplier_gstin ? $item->supplier_gstin : '-' }}</td>
                <td>{{ $item->supplier_name ? $item->supplier_name : '-' }}</td>
                <td>{{ $item->party_gstin ? $item->party_gstin : '-' }}</td>
                <td>{{ $item->party_name ? $item->party_name : '-' }}</td>
                <td>{{ $item->doc_no ? $item->doc_no : '-' }}</td>
                <td>{{ $item->doc_date ? App\Helpers\GeneralHelper::dateFormat3($item->doc_date) : '-' }}</td>
                <td>{{ $item->value_of_supplies_made ? number_format($item->value_of_supplies_made, 2) : 0 }}</td>
                <td>{{ $item->place_of_supply ? $item->pos . '-' . $item->place_of_supply : '-' }}</td>
                <td>{{ $item->doc_type }}</td>
                <td>{{ $item->rate }}</td>
                <td>{{ $item->taxable_amt ? number_format($item->taxable_amt, 2) : 0 }}</td>
                <td>{{ $item->cess ? number_format($item->cess, 2) : 0 }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="13" class="text-center text-danger">No record(s) found</td>
            </tr>
        @endforelse
    </tbody>
</table>
