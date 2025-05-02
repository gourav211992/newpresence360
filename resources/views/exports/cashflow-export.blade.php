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
    <thead>
        <tr>
            <th>S.No.</th>
            <th>Particulars</th>
            <th>Date</th>
            <th>Ledger Name</th>
            <th>Payment Mode</th>
            <th>Bank Name</th>
            <th>Total Amount</th>
        </tr>
    </thead>
    <tbody>
        {{-- {{ dd($payment_made) }} --}}
        <tr>
            <td>1.</td>
            <td><strong>Opening Balance</strong></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{ $opening_balance }}</td>
        </tr>

        <!-- Payment Made Section -->
        <tr>
            <td>2.</td>
            <td colspan="6"><strong>Payment Made</strong></td>
        </tr>
        @foreach ($payment_made as $index => $item)
            <tr>
                <td></td>
                <td></td>
                <td>{{ \Carbon\Carbon::parse($item->date)->format('d-m-Y') }}</td>
                <td>{{ $item->ledger_name }}</td>
                <td>{{ $item->payment_mode }}</td>
                <td>{{ $item->bank_name }}</td>
                <td>{{ number_format($item->amount, 2) }}</td>
            </tr>
        @endforeach

        <!-- Payment Received Section -->
        <tr>
            <td>3.</td>
            <td colspan="6"><strong>Payment Received</strong></td>
        </tr>
        @foreach ($payment_received as $index => $item)
            <tr>
                <td></td>
                <td></td>
                <td>{{ \Carbon\Carbon::parse($item->date)->format('d-m-Y') }}</td>
                <td>{{ $item->ledger_name }}</td>
                <td>{{ $item->payment_mode }}</td>
                <td>{{ $item->bank_name }}</td>
                <td>{{ number_format($item->amount, 2) }}</td>
            </tr>
        @endforeach

        <!-- Footer -->
        <tr>
            <td></td>
            <td colspan="3"><strong>Amount In Words:</strong> {{ $in_words }}</td>
            <td colspan="3" align="right"><strong>Closing Balance:</strong> {{ number_format($closing_balance, 2) }}
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
