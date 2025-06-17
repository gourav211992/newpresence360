<tbody>
    @php
        use App\Helpers\Helper;
        $totalDebit=0;
        $totalCredit=0;
    @endphp
    @foreach ($data as $voucher)
        @php
            $currentDebit=0;
            $currentCredit=0;
        @endphp
        <tr>
            <td>{{ date('d-m-Y',strtotime($voucher->date)) }}</td>
            <td>
                <table class="table my-25 ledgersub-detailsnew">
                    @foreach ($voucher->items as $item)
                        @if ($item->ledger_id==$id)
                            @php
                                $totalDebit=$totalDebit+$item->debit_amt;
                                $totalCredit=$totalCredit+$item->credit_amt;
                                $currentDebit=$item->debit_amt;
                                $currentCredit=$item->credit_amt;
                            @endphp
                        @else
                            @php
                                $currentBalance = $item->debit_amt - $item->credit_amt;
                                $currentBalanceType = $currentBalance >= 0 ? 'Dr' : 'Cr';
                                $currentBalance = abs($currentBalance);
                            @endphp
                            <tr>
                                <td  style="font-weight: bold; color:black;">{{ $item->ledger->name ?? '' }}</td>
                                <td class="text-end">{{Helper::formatIndianNumber($currentBalance)}} {{ $currentBalanceType }}</td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            </td>
            <td>
                <a href="{{ route('vouchers.edit', ['voucher' => $voucher->id]) }}">
                    {{ $voucher?->series?->service?->name }}
                </a>
            </td>
            <td>
                <a href="{{ route('vouchers.edit', ['voucher' => $voucher->id]) }}">
                    {{ $voucher?->series?->book_code }}
                </a>
            </td>
            <td>{{ $voucher->voucher_no??"" }}</td>
            <td>{{ Helper::formatIndianNumber($currentDebit) }}</td>
            <td>{{ Helper::formatIndianNumber($currentCredit) }}</td>
        </tr>
    @endforeach
</tbody>
  @php 
    $closing = ($opening->opening)+ $totalDebit-$totalCredit;
    $closing_type =$closing<0?"Cr":"Dr";
    
 @endphp
<tfoot>
    <tr class="ledfootnobg">
        <td colspan="5" class="text-end">Current Total</td>
        <td>{{ Helper::formatIndianNumber($totalDebit) }}</td>
        <td>{{ Helper::formatIndianNumber($totalCredit) }}</td>
    </tr>
    <tr class="ledfootnobg">
        <td colspan="5" class="text-end">Opening Balance</td>
        <td>@if($opening && $opening->opening_type=="Dr") {{ Helper::formatIndianNumber(abs($opening->opening)) }} @endif</td>
        <td>@if($opening && $opening->opening_type=="Cr") {{ Helper::formatIndianNumber(abs($opening->opening)) }} @endif</td>
    </tr>
  
    <td colspan="5" class="text-end">Closing Balance</td>
    <td>@if($closing && $closing_type=="Dr") {{ Helper::formatIndianNumber($closing) }} @endif</td>
    <td>@if($closing && $closing_type=="Cr") {{ Helper::formatIndianNumber(abs($closing)) }} @endif</td>
    </tr>
</tfoot>

