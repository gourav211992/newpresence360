<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{str_replace(' ', '_', $ledger_name)}}_{{ucfirst($document_type)}}_Advice</title>
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


        <table style="width: 100%; table-layout: fixed; word-wrap: break-word;">

            <tr>
                <td style="vertical-align: top;">
                    <img src="{{$orgLogo}}" height="50px">
                </td>

                <td style="text-align: center;  font-weight: bold; font-size: 20px;">
                    {{ucfirst($document_type)}} Advice
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
                                {{ @$organizationAddress->line_1 }} @if (!empty($organizationAddress->line_2)), {{ $organizationAddress->line_2 }}@endif @if (!empty($organizationAddress->line_3)), {{ $organizationAddress->line_3 }}@endif
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
                            <td style="padding-top: 5px;">{{ @$organizationAddress?->postal_code }}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 5px;">Phone:</td>
                            <td style="padding-top: 5px;">{{ @$organizationAddress?->mobile ?? @$organizationAddress?->phone }}</td>
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
                            <td style="padding-top: 5px;">{{$party_address?->city?->name}}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 5px;">State: </td>
                            <td style="padding-top: 5px;">{{$party_address?->state?->name}}</td>
                        </tr>

						 <tr>
                            <td style="padding-top: 5px;">State Code: </td>
                            <td style="padding-top: 5px;">{{$party_address?->state?->state_code}}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 5px;">Country: </td>
                            <td style="padding-top: 5px;">{{$party_address?->country?->name}}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 5px;">Pin Code:</td>
                            <td style="padding-top: 5px;">{{@$party_address?->pincode}}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 5px;">Phone:</td>
                            <td style="padding-top: 5px;">{{$party?->phone ?? $party?->mobile}}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 5px;">Email ID:</td>
                            <td style="padding-top: 5px;">{{$party?->email}}</td>
                        </tr>

						<tr>
                            <td style="padding-top: 5px;">GSTIN No:</td>
                            <td style="padding-top: 5px;"><strong>{{@$party?->compliances?->gstin_no}}</strong></td>
                        </tr>
                    </table>
                </td>
                <td rowspan="2" style="border: 1px solid #000; padding: 3px; border-left: none; vertical-align: top; width: 30%;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                       <tr>
                        <td style="padding: 5px;"><strong>{{ucfirst($document_type)}} No:</strong> {{$receipt_no}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px;"><strong>{{ucfirst($document_type)}} Date:</strong> {{ \Carbon\Carbon::parse($receipt_date)->format('d-m-Y') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px;"><strong>Payment Type:</strong> {{$payment_type}} </td>
                    </tr>
                    <tr>
                        <td style="padding: 5px;"><strong>Payment Mode:</strong> {{$payment_mode}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px;"><strong>Ref No:</strong> {{$ref_no}}</td>
                    </tr>

                    {{-- @if($status=="approved" || $status=="approval_not_required") --}}
                    <tr>
                        <td style="padding: 5px;"><strong>Status:</strong> <span style="color: green">Approved</span></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px;"><strong>Approved by:</strong> {{$approver}}</td>
                    </tr>
                    {{-- @endif --}}
                    {{-- @if($status=="posted" )
                    <tr>
                        <td style="padding: 5px;"><strong>Status:</strong> <span style="color: blue">Posted</span></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px;"><strong>Posted by:</strong> {{$approver}}</td>
                    </tr>
                    @endif --}}

              </table>
            </td>

          </tr>


    </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">

             <tr>
                <td
                    style="padding: 2px; border: 1px solid #000;  background: #80808070; text-align: center; font-weight: bold;"> S.No.</td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000;   border-left: none; text-align: center; background: #80808070; text-align: center;"> Invoice No</td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000;  border-left: none; background: #80808070; text-align: center;">
                    Invoice Date</td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000;   border-left: none; background: #80808070; text-align: right; padding-right: 30px">{{$report_type}} Amount</td>
            </tr>
            @php $index=0; @endphp

            @foreach($data as $key => $row)
            @php $index++; @endphp
            <tr>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none;  text-align: center;">
                    {{$index}}</td>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;text-align: center;"">{{$row->bill_no}}</td>
                <td style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none;text-align: center;"">
                    {{ \Carbon\Carbon::parse($row->date)->format('d-m-Y') }}</td>
                <td style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right; padding-right: 30px"> {{ number_format($row->paid,2) }}
                </td>
            </tr>

            @endforeach

        </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="line-height: 18px"><strong>Amount In Words</strong> <br>
                                {{$in_words}}</td>
                        </tr>
                        <tr>
                            <td style="padding-top: 15px;"><strong>Currency:</strong> {{$organization?->currency?->name}}</td>
                        </tr>

                    </table>

                </td>
                <td
                    style="padding: 3px; border: 1px solid #000; border-top: none; border-left: none; vertical-align: middle;">
                    <table style="width: 100%; font-size: 13px; margin-bottom: 0px; margin-top: 10px; font-weight: bold" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="text-align: right;">Total {{$report_type}}:</td>
                            <td style="text-align: center;">{{$total_value}}</td>
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
                                    {{$remarks}}

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
                        <td style="padding-top: 5px;width: 70px">Created By :</td>
                        <td style="padding-top: 5px;">{{$auth_user?->name}}</td>
                    </tr>
                    <tr>
                        <td style="padding-top: 5px;">Printed By :</td>
                        <td style="padding-top: 5px;">{{$auth_user?->name}}</td>
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
