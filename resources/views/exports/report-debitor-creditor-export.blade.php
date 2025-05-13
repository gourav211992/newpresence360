<table>
    <tr>
        <td><strong>{{ $group ?? '' }}</strong></td>
    </tr>
    <tr>
        <td>{{ isset($type) && $type == 'credit' ? 'Account Payable ' : 'Account Receivable ' }}</td>
    </tr>
    <tr>
        <td>{{ $date ?? $date2 }}</td>
    </tr>
    <tr></tr> {{-- empty row --}}
    <tr>
        <th><strong>SNO.</strong></th>
        <th><strong>Party Name</strong></th>
        <th><strong>Group Name</strong></th>
        <th><strong>Credit Days</strong></th>
        <th><strong>Invoice Date</strong></th>
        <th><strong>Invoice No</strong></th>
        <th><strong>Voucher No</strong></th>
        <th><strong>O/S Days</strong></th>
        <th><strong>Invoice Amount</strong></th>
        <th><strong>Balance Amount</strong></th>
    </tr>
    @if (isset($entities))
        @php $serial = 1; @endphp
        {{-- {{ dd($entities) }} --}}
        @foreach ($entities as $item)
            @foreach ($item['records'] as $record)
                <tr>
                    <td>{{ $serial++ }}</td>
                    <td>{{ $item['vendor_name'] }}</td>
                    <td>{{ $item['group_name'] ?? '' }}</td>
                    <td>{{ $record->credit_days ?? 0 }}</td>
                    <td>{{ $record->document_date ?? null }}</td>
                    <td>{{ $record->bill_no ?? null}}</td>
                    <td>{{ $record->voucher_no ?? null }}</td>
                    <td>@if($record->overdue_days!="-")
                                                        <span class="badge rounded-pill @if($item['credit_days']<$record->overdue_days) badge-light-danger @else badge-light-secondary @endif  badgeborder-radius">{{$record->overdue_days}}</span>
                                                        @endif</td>
                    <td  align="right">{{ $record->invoice_amount > 0 ? number_format($record->invoice_amount) : '' }}</td>
                    @if ($type == 'debit')
                        <td  align="right">{{ number_format(abs($record->total_outstanding), 2) }}
                            {{ $record->total_outstanding < 0 ? 'Cr' : 'Dr' }}</td>
                    @else
                        <td  align="right">{{ number_format(abs($record->total_outstanding), 2) }}
                            {{ $record->total_outstanding < 0 ? 'Dr' : 'Cr' }}</td>
                    @endif
                    
                </tr>
            @endforeach
        @endforeach
    @else
        <tr></tr>
    @endif

</table>
