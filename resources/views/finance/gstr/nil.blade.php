<table class="datatables-basic table myrequesttablecbox"> 
    <thead>
        <tr>
            <th>#</th>
            <th>Description</th>
            <th>Nil Rated Supplies</th>
            <th>Exempted(other than nil rated/non GST supply)</th>
            <th>Non-GST Supplies</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($gstrData as $key => $item)
            <tr class="trail-bal-tabl-none">
                <td>{{ $gstrData->firstItem() + $key }}</td> 
                <td>{{ $item->description ? $item->description : '-' }}</td>
                <td>{{ $item->nil_amt ? $item->nil_amt : '-' }}</td>
                <td>{{ $item->expt_amt ? $item->expt_amt : '-' }}</td>
                <td>{{ $item->non_gst_amt ? $item->non_gst_amt : '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="13" class="text-center text-danger">No record(s) found</td>
            </tr>
        @endforelse
    </tbody>
</table>
