<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@if($type=="debit") Debtor Statment @else Creditor Statment @endif</title>
    <style>
        table { page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        td { word-wrap: break-word; }
        @media print {
    body {
        visibility: visible;
    }
}
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
                window.print();
        });
    </script>
    
</head>

<body>
    <div style="width: 730px; font-size: 11px; font-family: Arial;">


        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">

            <tr>
                <td style="vertical-align: top;">
                    <img src="{{@$orgLogo ?? url('')."/public/assets/sheela-logonew.jpeg"}}" height="50px">
                </td>
                
                <td style="text-align: center;  font-weight: bold; font-size: 20px;">
                    Account Statement
                </td>
              <td style="text-align: right;  font-weight: bold; font-size: 16px; width: 260px;">
                {{ Str::ucfirst(@$organization->name) }}
              </td>
            </tr>
        </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td rowspan="2" style="border: 1px solid #000; padding: 3px; width: 35%; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td colspan="2" style="font-weight: 900; font-size: 13px; padding-bottom: 3px;">From Name & Address
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <span style="font-weight: 700; font-size: 13px; padding-top: 5px">{{ Str::ucfirst(@$organization->name) }}</span> <br>
                               
                            </td>
                        </tr>
                        <tr valign="top">
                            <td style="padding-top: 10px; width: 70px">Address: </td>
                            <td style="padding-top: 10px;">
                                {{@$organizationAddress->getFullAddressAttribute()}}
                            </td>
                        </tr> 
                        <tr>
                            <td style="padding-top: 5px;">City:</td>
                            <td style="padding-top: 5px;">{{ @$organizationAddress?->city?->name }}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 5px;">State: </td>
                            <td style="padding-top: 5px;">{{ @$organizationAddress?->state?->name }}</td>
                        </tr>
						
						 <tr>
                            <td style="padding-top: 5px;">State Code: </td>
                            <td style="padding-top: 5px;">{{ @$organizationAddress?->state?->state_code }}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 5px;">Country: </td>
                            <td style="padding-top: 5px;">{{ @$organizationAddress?->country?->name }}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 5px;">Pin Code:</td>
                            <td style="padding-top: 5px;">:{{ @$organizationAddress?->postal_code }}</td>
                        </tr>  

                        <tr>
                            <td style="padding-top: 5px;">Phone:</td>
                            <td style="padding-top: 5px;">{{ @$organization?->phone }}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 5px;">Email ID:</td>
                            <td style="padding-top: 5px;">{{ @$organization?->email }}</td>
                        </tr> 
						
						<tr>
                            <td style="padding-top: 5px;">GSTIN No:</td>
                            <td style="padding-top: 5px;"><strong>{{@$organization?->gst_number}}</strong></td>
                        </tr>
 
                      

                    </table>
                </td>
                <td rowspan="2" style="border: 1px solid #000; padding: 3px; border-left: none; vertical-align: top; width: 30%;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td colspan="2" style="font-weight: 900; font-size: 13px;  vertical-align: top;">To Name & Address:
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="font-weight: 700; font-size: 13px; padding-top: 5px">
                                {{strtoupper($party?->company_name)}} 
                            </td>
                        </tr>
                        <tr valign="top">
                            <td style="padding-top: 10px; width: 70px">Address: </td>
                            <td style="padding-top: 10px;">{{@$party_address?->address}}</td>
                        </tr>
                        <tr>
                            <td style="padding-top: 5px;">City:</td>
                            <td style="padding-top: 5px;">{{@$party_address?->city?->name}}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 5px;">State: </td>
                            <td style="padding-top: 5px;">{{@$party_address?->state?->name}}</td>
                        </tr>
						
						 <tr>
                            <td style="padding-top: 5px;">State Code: </td>
                            <td style="padding-top: 5px;">{{@$party_address?->state?->state_code}}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 5px;">Country: </td>
                            <td style="padding-top: 5px;">{{@$party_address?->country?->name}}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 5px;">Pin Code:</td>
                            <td style="padding-top: 5px;">:{{@$party_address?->pincode}}</td>
                        </tr>  

                        <tr>
                            <td style="padding-top: 5px;">Phone:</td>
                            <td style="padding-top: 5px;">{{$party?->phone ?? $party?->mobile}}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 5px;">Email ID:</td>
                            <td style="padding-top: 5px;">{{@$party?->email}}</td>
                        </tr> 
						
						<tr>
                            <td style="padding-top: 5px;">GSTIN No:</td>
                            <td style="padding-top: 5px;"><strong>{{@$party?->compliances?->gstin_no}}</strong></td>
                        </tr> 
                    </table>
                </td> 
                
                <td rowspan="2" style="border: 1px solid #000; padding: 3px; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                 
                
                           
                            <tr>
                                <td style="padding: 5px;"><strong>Date:</strong> {{date('d-m-Y')}}</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px;"><strong>Credit Days:</strong> {{@$credit_days}} Days</td>
                            </tr>
                             <tr>
                                <td style="padding: 5px;"><strong>Status:</strong> {{ ucfirst($bill_type)}} Bills</td>
                            </tr>
                        </table>
                    </td>
             
          
              
          </tr>
           
        </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td
                    style="padding: 2px; border: 1px solid #000; border-top: none; background: #80808070; text-align: center; font-weight: bold;">#</td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Invoice Date</td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; text-align: center; background: #80808070; text-align: center;">
                    Invoice No</td> 
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    O/S Days</td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: right;">
                    Amount</td>
            </tr>
            @php $index=0; @endphp
            
            @foreach($data as $key => $row)
            @if($bill_type=="outstanding")
            @if($row->total_outstanding > 0)
            @php $index++; @endphp
            
            <tr>
                <td
                    style="padding: 2px; border: 1px solid #000; border-top: none; text-align: center; font-weight: bold;">{{@$index}}</td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                    {{@$row->document_date}}</td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;text-align: center;">
                    {{@$row->bill_no}}</td> 
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none;  text-align: center;">
                    {{@$row->overdue_days}}</td>
                
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                    {{App\Helpers\Helper::formatIndianNumber($row->total_outstanding)}}</td>
            </tr>
            @endif
            @else
            @if($row->overdue > 0)
            @php $index++; @endphp
            
            <tr>
                <td
                    style="padding: 2px; border: 1px solid #000; border-top: none; text-align: center; font-weight: bold;">{{@$index}}</td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                    {{@$row->document_date}}</td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;text-align: center;">
                    {{@$row->bill_no}}</td> 
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none;  text-align: center;">
                    {{@$row->overdue_days}}</td>
                
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                    {{App\Helpers\Helper::formatIndianNumber($row->overdue)}}</td>
            </tr>
            @endif
            
            @endif
            @endforeach

        </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="line-height: 18px"><strong>Amount In Words</strong> <br>
                                {{@$in_words}}</td>
                        </tr>
                        <tr>
                            <td style="padding-top: 15px;"><strong>Currency:</strong> {{@$organization?->currency?->name}}</td>
                        </tr>
                        <tr>
                            <td style="padding-top: 15px;"><strong>Payment Terms :</strong> {{@$party?->paymentTerm?->name}}</td>
                        </tr> 

                    </table>

                </td>
                <td
                    style="padding: 3px; border: 1px solid #000; border-top: none; border-left: none; vertical-align: middle;">
                    <table style="width: 100%; font-size: 13px; margin-bottom: 0px; margin-top: 10px; font-weight: bold" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="text-align: right;">Total Value:</td>
                            <td style="text-align: center;">{{@$total_value}}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2"
                    style="padding: 3px; border: 1px solid #000; border-bottom: none; width: 50%; border-top: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="font-size: 13px; text-align: right; font-style: italic; padding-right: 15px"> E. & O.E</td>
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

             

            <!--  -->

            <tr> 
            <td
                style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; border-right: none; vertical-align: top;">
                <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                     
                    <tr>
                        <td style="padding-top: 5px;;width: 70px">Created By :</td>
                        <td style="padding-top: 5px;">{{@$auth_user?->name}}</td>
                    </tr>
                    <tr>
                        <td style="padding-top: 5px;">Printed By :</td>
                        <td style="padding-top: 5px;">{{@$auth_user?->name}}</td>
                    </tr>
                </table>

            </td>
            <td
                style="padding: 3px; border: 1px solid #000; border-top: none; border-left: none; vertical-align: bottom;">
                <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="text-align: center; padding-bottom: 20px;">FOR {{strtoupper($organization?->name)}} </td>
                    </tr>
                    <tr>
                        <td>This is a computer generated document hence not require any signature. </td>
                    </tr>
                </table>
            </td>
            </tr>

            <tr>
                <td colspan="2"
                    style=" border: 1px solid #000; padding: 7px; text-align: center; font-size: 12px; border-top: none; text-align: center;">
                    Regd. Office: {{@$organizationAddress->getFullAddressAttribute()}}
                </td>
            </tr>

        </table> 

    </div>
    
</body>


</html>