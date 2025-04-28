<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashflow Statement</title>
</head>

<body>
    <div style="width:730px; font-size: 11px; font-family:Arial;">

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr> 
                <td style=" font-weight: bold; font-size: 20px;">
                    Cashflow Statement
				  <p style="margin: 5px 0; font-size: 13px;"> {{ Str::ucfirst(@$organization->name) }}</p>
				  <p style="margin: 5px 0px 10px; font-size: 12px; ">{{@$organizationAddress->getFullAddressAttribute()}}</p>
					<p style="margin: 5px 0px 20px; font-size: 12px; ">Date: {{$range}} </p>
              </td>
				<td style="vertical-align: top;">
                    <img src="{{@$orgLogo ?? url('')."/public/assets/sheela-logonew.jpeg"}}" height="50px">
                </td>
          </tr>
        </table>

        
        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td
                    style="padding: 2px; border: 1px solid #000;  background: #80808070; "><strong>S.No.</strong></td>
                <td
                    style="padding: 2px; border: 1px solid #000;   border-left: none; text-align: left; background: #80808070;"><strong>Particulars</strong></td>
                <td
                    style="padding: 2px; border: 1px solid #000;  border-left: none; background: #80808070;"><strong>Date</strong></td>
                <td
                    style="padding: 2px; border: 1px solid #000;  border-left: none; background: #80808070;"><strong>Ledger Name</strong></td>
                <td
                    style="padding: 2px; border: 1px solid #000;   border-left: none; background: #80808070; padding-right: 30px"><strong>Payment Mode</strong></td>
                <td
                    style="padding: 2px; border: 1px solid #000;   border-left: none; background: #80808070; padding-right: 30px"><strong>Bank Name</strong></td>
                <td
                    style="padding: 2px; border: 1px solid #000;   border-left: none; background: #80808070; text-align: right; padding-right: 30px"><strong>Amount</strong></td>
            </tr>
            <tr>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; ">
                    1.</td>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;">Opening Balance</td>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;"> -</td>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;">
                    -</td>
                <td style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none; ">&nbsp;</td>
                <td style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none; ">&nbsp;</td>
                <td style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right; padding-right: 30px;  font-size: 12px"><strong>   @if ($opening < 0)
                    ({{ number_format(abs($opening), 2) }})
                @else
                    {{ number_format($opening, 2) }}
                @endif
            </strong></td>
            </tr>

            <tr>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; ">
                    2.</td>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;"> Payment Made</td>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;"> -</td>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;">
                    -</td>
              <td style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;">&nbsp;</td>
              <td style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;">&nbsp;</td>
               
                <td style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right; padding-right: 30px; font-size: 12px"><strong>({{ number_format($payment_made_t, 2) }})</strong></td>
            </tr>
            @foreach ($payment_made as $index => $paym)
			<tr>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none;"></td>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;">&nbsp;</td>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;"> {{ \Carbon\Carbon::parse($paym->document_date)->format('d-m-Y') }}</td>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;">
                    {{ $paym->ledger_name }}</td>
                <td style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;">{{ $paym->payment_mode }}</td>
                <td style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;">{{ $paym->bank_name}}</td>
                <td style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right; padding-right: 30px">{{ number_format($paym->amount, 2) }}</td>
            </tr>
            @endforeach
            <tr>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; ">
                    3.</td>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;"> Payment Received</td>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;"> -</td>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;">
                    -</td>
              <td style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;">&nbsp;</td>
              <td style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;">&nbsp;</td>
               
                <td style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right; padding-right: 30px; font-size: 12px"><strong>{{ number_format($payment_received_t, 2) }}</strong></td>
            </tr>
            @foreach ($payment_received as $index => $pay)
			<tr>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none;"></td>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;">&nbsp;</td>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;"> {{ \Carbon\Carbon::parse($pay->document_date)->format('d-m-Y') }}</td>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;">
                    {{ $pay->ledger_name }}</td>
                <td style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;">{{ $pay->payment_mode }}</td>
                <td style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;">{{ $pay->bank_name }}</td>
                <td style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right; padding-right: 30px">{{ number_format($pay->amount, 2) }}</td>
            </tr>
            @endforeach
			

        </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="line-height: 18px"><strong>Amount In Words</strong> <br>
                                @if ($closing < 0)
                                ({{$in_words}})
                            @else
                                {{$in_words}}
                            @endif
                        </td>
                        </tr>
						<tr>
                            <td style="padding-top: 15px;"><strong>Currency:</strong> {{$currency}}</td>
                        </tr>
                    </table>

                </td>
                <td
                    style="padding: 3px; border: 1px solid #000; border-top: none; border-left: none; vertical-align: middle;">
                    <table style="width: 100%; font-size: 13px; margin-bottom: 0px; " cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="text-align: right;"><strong>Closing Balance:</strong></td>
                            <td style="text-align: center;"><strong>  @if ($closing < 0)
                                ({{ number_format(abs($closing), 2) }})
                            @else
                                {{ number_format($closing, 2) }}
                            @endif
                        </strong>
                    </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td colspan="2"
                    style="padding: 3px; border: 1px solid #000; border-bottom: none; width: 50%; border-top: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="ffont-size: 13px; text-align: right; font-style: italic; padding-right: 15px"> E. & O.E</td>
                        </tr> 
                    </table>

                </td>
            </tr>
			
			<tr>
                <td colspan="2"
                    style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="font-weight: bold; font-size: 13px;"> Remark :</td>
                        </tr>
                        <tr>
                            <td>
                                <div style="min-height: 80px;">

                                </div>
                            </td>
                        </tr>


                    </table>

                </td>
            </tr>

              

            <tr> 
            <td  colspan="2"
                style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; vertical-align: top;">
                <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                     
                    <tr>
                        <td style="padding-top: 5px; width: 70px">Created By :</td>
                        <td style="padding-top: 5px;">{{$created_by}}</td>
                    </tr> 
                </table>

            </td>
             
            </tr>

             
			 
        </table> 

    </div>
</body>

</html>