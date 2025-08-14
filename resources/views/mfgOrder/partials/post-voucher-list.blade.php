@foreach($data['data']['ledgers'] as $key => $d)
<tr>
    <td>{{$key}}</td>  
    <td class="fw-bolder text-dark">{{$d[0]['ledger_group_code']}}</td> 
    <td>{{$d[0]['ledger_code']}}</td>
    <td>{{$d[0]['ledger_name']}}</td>
    <td class="text-end">{{number_format($d[0]['debit_amount'] ?? 0, 2)}}</td>
    <td class="text-end">{{number_format($d[0]['credit_amount'] ?? 0, 2)}}</td>
</tr>
@endforeach
<tr>
    <td colspan="4" class="fw-bolder text-dark text-end">Total</td>   
    <td class="fw-bolder text-dark text-end">{{number_format($data['data']['total_debit'] ?? 0, 2)}}</td> 
    <td class="fw-bolder text-dark text-end">{{number_format($data['data']['total_credit'] ?? 0, 2)}}</td>
</tr>