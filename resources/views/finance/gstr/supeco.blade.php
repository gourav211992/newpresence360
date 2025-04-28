<table class="datatables-basic table myrequesttablecbox"> 
    <thead>
        <tr>
            <th>#</th>
            <th>Nature of Supply</th>	
            <th>GSTIN of E-Commerce Operator</th>
            <th>E-Commerce Operator Name</th>	
            <th>Net value of supplies</th>	
            <th>Integrated tax</th>	
            <th>Central tax</th>	
            <th>State/UT tax</th>	
            <th>Cess</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($gstrData as $key => $item)
            <tr class="trail-bal-tabl-none">
                <td>{{ $gstrData->firstItem() + $key }}</td> 
                <td>{{ $item->nature_of_document ? App\Helpers\ConstantHelper::NATURE_OF_DOCUMENT[$item->nature_of_document] : '' }}</td>
                <td>{{ $item->e_commerce_gstin }}</td>
                <td>{{ $item->ecom_operator_name }}</td> 
                <td>{{ $item->net_value_of_supplies }}</td> 
                <td>{{ $item->igst }}</td> 
                <td>{{ $item->cgst }}</td> 
                <td>{{ $item->sgst }}</td> 
                <td>{{ $item->cess }}</td> 
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center text-danger">No record(s) found</td>
            </tr>
        @endforelse
    </tbody>
</table>
