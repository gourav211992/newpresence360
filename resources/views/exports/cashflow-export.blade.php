@php
    //  $payment_made = [
    //         (object)[
    //             'date' => now()->subDays(10)->format('Y-m-d'),
    //             'ledger_name' => 'Creditor A',
    //             'payment_mode' => 'Bank',
    //             'bank_name' => 'HDFC',
    //             'amount' => 50000,
    //         ],
    //         (object)[
    //             'date' => now()->subDays(9)->format('Y-m-d'),
    //             'ledger_name' => 'Creditor B',
    //             'payment_mode' => 'Bank',
    //             'bank_name' => 'COSMOS BANK ACCOUNT',
    //             'amount' => 300000,
    //         ],
    //         (object)[
    //             'date' => now()->subDays(8)->format('Y-m-d'),
    //             'ledger_name' => 'PQR Pvt Ltd',
    //             'payment_mode' => 'Bank',
    //             'bank_name' => 'COSMOS BANK ACCOUNT',
    //             'amount' => 10000,
    //         ]
    //     ];
    //     $payment_received = [
    //         (object)[
    //             'date' => now()->subDays(10)->format('Y-m-d'),
    //             'ledger_name' => 'Creditor A',
    //             'payment_mode' => 'Bank',
    //             'bank_name' => 'HDFC',
    //             'amount' => 50000,
    //         ],
    //         (object)[
    //             'date' => now()->subDays(9)->format('Y-m-d'),
    //             'ledger_name' => 'Creditor B',
    //             'payment_mode' => 'Bank',
    //             'bank_name' => 'COSMOS BANK ACCOUNT',
    //             'amount' => 300000,
    //         ],
    //         (object)[
    //             'date' => now()->subDays(8)->format('Y-m-d'),
    //             'ledger_name' => 'PQR Pvt Ltd',
    //             'payment_mode' => 'Bank',
    //             'bank_name' => 'COSMOS BANK ACCOUNT',
    //             'amount' => 10000,
    //         ]
    //     ];
    $payment_made = is_string($payment_made) ? json_decode($payment_made) : $payment_made;
    $payment_received = is_string($payment_received) ? json_decode($payment_received) : $payment_received;
@endphp
<table>
    <tr>
        <td colspan="7"><strong>{{ $organization->name??"-" }} </strong></td>
    </tr>
    <tr>
        <td colspan="7">Cashflow Report</td>
    </tr>

    {{-- Date Range --}}
    <tr>
        <td colspan="7"><strong> {{ $fy }} </strong></td>
    </tr>
    
</table>
<table>
    <thead>
        <tr>
            <th><strong>S.No.</strong></th>
            <th><strong>Particulars</strong></th>
            <th><strong>Date</strong></th>
            <th><strong>Ledger Name</strong></th>
            <th><strong>Payment Mode</strong></th>
            <th><strong>Bank Name</strong></th>
            <th align="right"><strong>Total Amount</strong></th>
        </tr>
    </thead>
    <tbody>
        

        {{-- {{ dd($payment_made) }} --}}
        <tr>
            <td><strong>1.</strong></td>
            <td><strong>Opening Balance</strong></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td align="right"><strong>{{ number_format($opening_balance, 2) }}</strong></td>
        </tr>

        <!-- Payment Made Section -->
        <tr>
            <td><strong>2.</strong></td>
            <td colspan="5"><strong>Payment Made</strong></td>
            {{-- <td></td> --}}
            {{-- <td></td> --}}
            {{-- <td></td> --}}
            {{-- <td></td> --}}
            <td align="right"><strong>{{ number_format($payment_made_t, 2) }}</strong></td>
        </tr>
        @foreach ($payment_made as $index => $item)
        {{-- {{ dd($item) }} --}}
            <tr>
                <td></td>
                <td></td>
                <td>{{ \Carbon\Carbon::parse($item->document_date)->format('d-m-Y') }}</td>
                <td>{{ $item->ledger_name }}</td>
                <td>{{ $item->payment_mode }}</td>
                <td>{{ $item->bank_name }}</td>
                <td align="right">{{ number_format($item->amount, 2) }}</td>
            </tr>
        @endforeach

        <!-- Payment Received Section -->
        <tr>
            <td><strong>3.</strong></td>
            <td colspan="5"><strong>Payment Received</strong></td>
            <td align="right"><strong>{{ number_format($payment_received_t, 2) }}</strong></td>

        </tr>
        @foreach ($payment_received as $index => $item)
            <tr>
                <td></td>
                <td></td>
                <td>{{ \Carbon\Carbon::parse($item->document_date)->format('d-m-Y') }}</td>
                <td>{{ $item->ledger_name }}</td>
                <td>{{ $item->payment_mode }}</td>
                <td>{{ $item->bank_name }}</td>
                <td align="right">{{ number_format($item->amount, 2) }}</td>
            </tr>
        @endforeach

        <!-- Footer -->
        <tr>
            <td></td>
            <td colspan="3"><strong>Amount In Words:</strong> {{ $in_words }}</td>
            <td colspan="3" align="right"><strong>Closing Balance: &nbsp;&nbsp; </strong> &nbsp;&nbsp;&nbsp; {{ number_format($closing_balance, 2) }}
            </td>
        </tr>

        <tr>
            <td></td>
            <td colspan="3"><strong>Currency:</strong> {{ $currency }}</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="3"><strong>Created By:</strong> {{ $createdBy }}</td>
            {{-- <td colspan="3" align="right"><em>E. & O.E</em></td> --}}
        </tr>
        {{-- <table>
    <tr>
        <td><strong>Amount In Words</strong></td>
        <td>{{ $in_words ?? 'N/A' }}</td>

        <td colspan="3" align="right"><strong>Closing Balance:</strong></td>
        <td>{{ $closing_balance }}</td>
    </tr>
    <tr>
        <td><strong>Currency:</strong></td>
        <td>{{ $currency }}</td>
    </tr>
</table> --}}

        {{-- <br><br> --}}

        {{-- <table>
    <tr>
        <td><strong>Remark :</strong></td>
    </tr>
    <tr>
        <td colspan="6" style="height: 60px;"></td> <!-- Space for remarks -->
        <td align="right"><em>E. & O.E</em></td>
    </tr>
</table> --}}

        {{-- <br><br>

<table> --}}

        {{-- </table> --}}

    </tbody>
</table>
