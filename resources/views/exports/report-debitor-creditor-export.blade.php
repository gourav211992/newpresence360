<table>
    <tr>
        <td><strong>{{ $group ?? '' }}</strong></td>
    </tr>
    <tr>
        <td>{{ isset($type) && $type == 'credit' ? 'Account Payable ' : 'Account Receivable ' }}</td>
    </tr>
    <tr>

    <td>{{ isset($date) ? $date  : 'As on' .' '. $date2 }}</td>
    </tr>
    <tr></tr> {{-- empty row --}}
    <tr>
        <th><strong>SNO.</strong></th>
        <th><strong>Party Name</strong></th>
        @if($group == '')<th><strong>Group Name</strong></th>@endif
        <th><strong>Credit Days</strong></th>
        <th><strong>Invoice Date</strong></th>
        <th><strong>Invoice No</strong></th>
        <th><strong>Voucher No</strong></th>
        <th><strong>O/S Days</strong></th>
        <th><strong>Invoice Amount</strong></th>
        <th><strong>Balance Amount</strong></th>
        <th><strong>Running Bal</strong></th>
    </tr>
    @if (isset($entities))
        @php $serial = 1; $runningBalTotal = 0; @endphp


        {{-- {{ dd($entities) }} --}}
        @foreach ($entities as $item)
    @foreach ($item['records'] as $record)
        @php
            // Check for skip conditions
            $invoiceAmount = $record->invoice_amount ?? 0;
            $totalOutstanding = $record->total_outstanding ?? 0;

            // Convert to numeric
            $invoiceAmount = floatval($invoiceAmount);
            $totalOutstanding = floatval($totalOutstanding);

            // Skip if both are zero/empty, or if total_outstanding alone is zero/empty
            if (($invoiceAmount == 0 && $totalOutstanding == 0) || $totalOutstanding == 0) {
                continue;
            }

            // Proceed normally
            $currentOutstanding = $totalOutstanding < 0 ? 0 : $totalOutstanding;
            $runningBalTotal += $currentOutstanding;
        @endphp
        <tr>
            <td>{{ $serial++ }}</td>
            <td>{{ $item['vendor_name'] }}</td>
            @if($group == '')<td>{{ $item['group_name'] ?? '' }}</td>@endif
            <td>{{ $record->credit_days ?? 0 }}</td>
            <td>{{ $record->document_date ?? null }}</td>
            <td>{{ $record->bill_no ?? null }}</td>
            <td>{{ $record->voucher_no ?? null }}</td>
            <td>
                @if($record->overdue_days != "-")
                    <span class="badge rounded-pill @if($item['credit_days'] < $record->overdue_days) badge-light-danger @else badge-light-secondary @endif badgeborder-radius">{{ $record->overdue_days }}</span>
                @endif
            </td>
            <td align="right">@if($record->invoice_amount != ""){{ \App\Helpers\Helper::formatIndianNumber($record->invoice_amount) }}@endif</td>
            <td align="right">{{ \App\Helpers\Helper::formatIndianNumber($record->total_outstanding) }}</td>
            <td align="right">{{ \App\Helpers\Helper::formatIndianNumber($runningBalTotal) }}</td>
        </tr>
    @endforeach
@endforeach

    @else
        <tr></tr>
    @endif

</table>
