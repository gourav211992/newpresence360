<table class="datatables-basic table myrequesttablecbox">
    <thead>
        <tr>
            <th>#</th>
            <th>Nature of Supply</th>
            <th>Financial Year</th>
            <th>Original Month/Quarter</th>
            <th>Original GSTIN of E-Commerce Operator</th>
            <th>Revised GSTIN of E-Commerce Operator</th>
            <th>E-Commerce Operator Name</th>
            <th>Revised Net value of supplies</th>
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
                <td>{{ $item->nature_of_document ? App\Helpers\ConstantHelper::NATURE_OF_DOCUMENT[$item->nature_of_document] : '' }}
                </td>
                <td>{{ $item->year ? $item->year : '-' }}</td>
                <td>{{ $item->month ? \DateTime::createFromFormat('!m', $item->month)->format('F') : '-' }}</td>
                <td>{{ $item->e_commerce_gstin }}</td>
                <td>{{ $item->revised_ecom_gstin }}</td>
                <td>{{ $item->ecom_operator_name }}</td>
                <td>{{ $item->net_value_of_supplies }}</td>
                <td>{{ $item->igst ? number_format($item->igst, 2) : 0 }}</td>
                <td>{{ $item->cgst ? number_format($item->cgst, 2) : 0 }}</td>
                <td>{{ $item->sgst ? number_format($item->sgst, 2) : 0 }}</td>
                <td>{{ $item->cess ? number_format($item->cess, 2) : 0 }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center text-danger">No record(s) found</td>
            </tr>
        @endforelse
    </tbody>
</table>
