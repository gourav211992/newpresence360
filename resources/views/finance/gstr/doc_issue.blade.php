
<table class="datatables-basic table myrequesttablecbox"> 
    <thead>
        <tr>
            <th>#</th>
            <th>Nature of Document</th>	
            <th>Sr. No. From</th>	
            <th>Sr. No. To</th>	
            <th>Total Number</th>	
            <th>Cancelled</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($gstrData as $key => $item)
            <tr class="trail-bal-tabl-none">
                <td>{{ $gstrData->firstItem() + $key }}</td> 
                <td>{{ $item->nature_of_document }}</td>
                <td>{{ $item->sr_no_from ? $item->sr_no_from : '-' }}</td> 
                <td>{{ $item->sr_no_to ? $item->sr_no_to : '-' }}</td> 
                <td>{{ $item->total_number ? $item->total_number : '-' }}</td> 
                <td>{{ $item->cancelled ? $item->cancelled : '-' }}</td> 
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-danger">No record(s) found</td>
            </tr>
        @endforelse
    </tbody>

</table>