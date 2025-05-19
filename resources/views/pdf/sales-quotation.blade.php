<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$type}}</title>
    <style>
        .status {
            font-weight: 900;
            text-align: center;
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <div style="width:700px; font-size: 11px; font-family:Arial;">

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
            <tr>
                <!-- Organization Logo (Left) -->
                <td style="vertical-align: top;">
                    @if (isset($orgLogo) && $orgLogo)
                    <img src="{!! $orgLogo !!}" alt="" height="50px" />
                    @else
                    <img src="{{$imagePath}}" height="50px" alt="">
                    @endif
                </td>
                
                <!-- Sales {{$type}} Text (Center) -->
                <td style="width: 34%; text-align: center; font-size: 24px; font-weight: 100; padding: 0;">
                    Sales {{$type}}
                </td>

                <!-- Organization Name (Right) -->
                <td style="width: 33%; text-align: right; font-size: 20px; font-weight: 100; padding: 0;">
                    {{ @$organization->name }}
                </td>
            </tr>
        </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td rowspan="2" style="border: 1px solid #000; padding: 3px; width: 40%; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td colspan="3" style="font-weight: 900; font-size: 13px; padding-bottom: 3px;">
                                Buyer Name & Address:
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-top: 3px;">
                                <span style="font-weight: 700; font-size: 13px;">
                                    <b>{{ Str::ucfirst(@$order->customer->customer_code) }}</b>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 15px;">Address: </td>
                            <td style="padding-top: 15px;">
                                {{@$shippingAddress->address}}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">City: </td>
                            <td style="padding-top: 3px;">
                                {{ @$shippingAddress->city->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">State:</td>
                            <td style="padding-top: 3px;">
                                {{ @$shippingAddress->state->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Country</td>
                            <td style="padding-top: 3px;">
                                {{ @$shippingAddress->country->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Pin code:</td>
                            <td style="padding-top: 3px;">
                                {{ @$shippingAddress->pincode }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">GSTIN NO:</td>
                            <td style="padding-top: 3px;">{{@$order->cust->compliances->gstin_no}}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 3px;">PHONE:</td>
                            <td style="padding-top: 3px;">
                                {{ @$order->customer->phone }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">EMAIL ID:</td>
                            <td style="padding-top: 3px;">
                                {{ @$order->customer->email }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">PAN NO. :</td>
                            <td style="padding-top: 3px;">
                                {{@$order->customer->pan_number}}
                            </td>
                        </tr>
                    </table>
                </td>
                <td rowspan="2"
                    style="border: 1px solid #000; padding: 3px; border-left: none; vertical-align: top; width: 40%;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td colspan="2"
                                style="font-weight: 900; font-size: 13px; padding-bottom: 3px; vertical-align: top;">
                                Seller's
                                Name & Address:
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" style="font-weight: 700; font-size: 13px; padding-top: 3px;">
                                <b>{{ Str::ucfirst(@$organization->name) }}</b>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 15px;">Address: </td>
                            <td style="padding-top: 15px;">
                                {{ Str::ucfirst(@$organizationAddress->line_1) }},
                                {{@$organizationAddress->line_2}},
                                {{ @$organizationAddress->landmark }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">City :</td>
                            <td style="padding-top: 3px;">
                                {{ @$organizationAddress->city->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">State:</td>
                            <td style="padding-top: 3px;">
                                {{ @$organizationAddress->state->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Country:</td>
                            <td style="padding-top: 3px;">
                                {{ @$organizationAddress->country->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Pin Code : </td>
                            <td style="padding-top: 3px; font-weight: 700;">{{ @$organizationAddress->postal_code }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">GSTIN NO:</td>
                            <td style="padding-top: 3px;">{{@$organization->compliances->gstin_no}}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 3px;">PHONE:</td>
                            <td style="padding-top: 3px;">
                                {{ @$organizationAddress->mobile }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">EMAIL ID:</td>
                            <td style="padding-top: 3px;">
                                {{ @$organizationAddress->email }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">PAN NO. :</td>
                            <td style="padding-top: 3px;"></td>
                        </tr>

                    </table>
                </td>
                <td
                    style="border: 1px solid #000; padding: 3px;float: right; border-left: none; vertical-align: top; width: 20%;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td><b>{{$type}} No:</b></td>
                            <td style="font-weight: 900;">{{ @$order->document_number }}
                            </td>
                        </tr>
                        <tr>
                            <td><b>{{$type}} Date:</b></td>
                            @if($order->document_date)
                                <td style="font-weight: 900;">{{ date('d-M-y', strtotime($order->document_date)) }}
                                </td>
                            @endif
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td
                    style="border: 1px solid #000; padding: 3px; border-left: none; vertical-align: top; width: 35%; border-top: none;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>
                                <b style="font-weight: 900;">Status :-</b>
                                @if($order->document_status == 'submitted')
                                    <span class="status" style="color: #17a2b8 ">
                                        {{ ucfirst($order->document_status) }}
                                    </span>
                                @elseif($order->document_status == 'draft')
                                    <span style="color: #6c757d">
                                        {{ ucfirst($order->document_status) }}
                                    </span>
                                @elseif($order->document_status == 'approved')
                                    <span style="color: #28a745">
                                        {{ ucfirst($order->document_status) }}
                                    </span>
                                @elseif($order->document_status == 'rejected')
                                    <span style="color: #dc3545">
                                        {{ ucfirst($order->document_status) }}
                                    </span>
                                @else
                                    <span style="color: #007bff">
                                        {{ ucfirst($order->document_status) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td
                    style="padding: 6px; border: 1px solid #000; border-top: none; background: #80808070; text-align: center; font-weight: bold;">
                    #
                </td>
                <td
                    style="font-weight: bold; width: 150px; padding: 6px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    <div style="">Item</div>
                </td>
                <td
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    HSN Code
                </td>
                <td
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Quantity
                </td>
                <td
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    UOM
                </td>
                <td
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Rate
                </td>
                <td
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Total
                </td>
                <td
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Discount
                </td>
                <td
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Taxable Value
                </td>
                <td
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-top: none; border-left: none; text-align: center; background: #80808070; text-align: center;">
                    Tax Amount
                </td>
                <td
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-top: none; border-left: none; text-align: center; background: #80808070; text-align: center;">
                    Tax Group
                </td>
            </tr>
            @php 
                                                                                                                                                                                                                                                                                $taxBracket = [];
                $totalCGSTValue = 0.00;
                $totalSGSTValue = 0.00;
                $totalIGSTValue = 0.00;
                $totalTaxValue = 0.00;
            @endphp
            @foreach($order->items as $key => $val)
                        <tr>

                            <td
                                style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: center;">
                                {{ $key + 1 }}
                            </td>
                            <td
                                style="vertical-align: top; padding:10px 3px; text-align:left; border: 1px solid #000; border-top: none; border-left: none;">
                                <b> {{ @$val->item_name }}</b><br />
                                @foreach(@$val->attributes as $data)
                                    {{$data->attribute_name}}:{{ucFirst($data->attribute_value)}}
                                @endforeach
                                <br />
                                {{ @$val->item_code }}<br />
                                {{@$val->remarks}}
                            </td>
                            <td
                                style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                                {{ @$val->hsn_code }}
                            </td>
                            <td
                                style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                                {{@$val->order_qty}}
                            </td>
                            <td
                                style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                                {{@$val->uom->name}}
                            </td>
                            <td
                                style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                                {{@$val->rate}}
                            </td>
                            @php
                                $total = $val->order_qty * $val->rate;
                            @endphp
                            <td
                                style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                                {{$total }}
                            </td>
                            <td
                                style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none;  text-align: center; text-align: center;">
                                {{$val->item_discount_amount}}
                            </td>
                            @php
                                $total = $val->order_qty * $val->rate;
                                $netValue = $total - ($val->item_discount_amount + $val->header_discount_amount) + $val->tax_amount;
                            @endphp
                            <td
                                style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none;  text-align: center; text-align: center;">
                                {{$netValue}}
                            </td>
                            <td
                                style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: center;">
                                @php
                                    if ($val->tax_ted->first()) {
                                        $taxName = $val->tax_ted->first()->ted_name . " " . number_format($val->tax_ted->first()->ted_percentage,2) . " %";
                                        if (isset($taxBracket[$taxName])) {
                                            $taxBracket[$taxName][0] += $val->tax_ted->first()->ted_amount;
                                            $taxBracket[$taxName][1] += $val->tax_ted->first()->assessment_amount;
                                        } else {
                                            $taxBracket[$taxName][0] = $val->tax_ted->first()->ted_amount;
                                            $taxBracket[$taxName][1] = $val->tax_ted->first()->assessment_amount;
                                        }
                                    }

                                    $totalCGSTValue += $val->cgst_value['value'];
                                    $totalSGSTValue += $val->sgst_value['value'];
                                    $totalIGSTValue += $val->igst_value['value'];
                                    $totalTaxValue = $totalCGSTValue + $totalIGSTValue + $totalSGSTValue;

                                @endphp
                                {{$val->tax_ted->first()->ted_amount}}
                            </td>
                            <td
                                style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none;  text-align: center; text-align: center;">
                                {{isset($val->tax_ted->toArray()[0]['ted_group_code']) ? $val->tax_ted->toArray()[0]['ted_group_code'] : "NA"}}
                            </td>
                        </tr>
            @endforeach
        </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td> <b>Total Sales {{$type}} Value (In Words)</b> <br>
                                {{ @$amountInWords }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 15px;"><b>Currency:</b> {{@$order->currency->name}} </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 10px;"><b>Payment Terms :</b>
                                {{@$order->payment_terms->name}}
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                        </tr>
                    </table>

                </td>
                <td
                    style="padding: 3px; border: 1px solid #000; border-top: none; border-left: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px; margin-top: 10px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="text-align: right;">
                                <b>Total Value:</b>
                            </td>
                            <td style="text-align: right;">
                                {{ number_format($totalItemValue, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Total Discount:</b>
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ number_format($totalDiscount, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Taxable Value:</b>
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ number_format($totalTaxableValue, 2) }}
                            </td>
                        </tr>
                        @foreach($taxBracket as $tax => $value)
                            <tr>
                                <td style="text-align: right; padding-top: 3px;">
                                    <b>{{$tax}} @ {{number_format($value[1], 2)}}:</b>
                                </td>
                                <td style="text-align: right; padding-top: 3px;">
                                    {{ number_format($value[0], 2) }}
                                </td>
                            </tr>
                        @endforeach
                        @if($order->expense_ted)
                            <tr>
                                <td style="text-align: right; padding-top: 3px;">
                                    <b>Total Amount After Tax:</b>
                                </td>
                                <td style="text-align: right; padding-top: 3px;">
                                    {{ number_format($totalAfterTax, 2)}}
                                </td>
                            </tr>
                            @foreach($order->expense_ted as $key => $expense)
                                <tr>
                                    <td style="text-align: right; padding-top: 3px;">
                                        <b>{{ucFirst($expense->ted_name)}} :</b>
                                    </td>
                                    <td style="text-align: right; padding-top: 3px;">
                                        {{ number_format(@$expense->ted_amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Total Sales {{$type}} Value:</b>
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ number_format($totalAmount, 2) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td colspan="2"
                    style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="font-weight: bold; font-size: 13px;"> <b>Remark :</b></td>
                        </tr>
                        <tr>
                            <td>
                                <div style="min-height: 80px;">
                                    {{$order->final_remarks}}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            

            <!--  -->

            <tr>
                <td
                    style="padding: 3px; border: 1px solid #000; width: 30%; border-top: none; border-right: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">

                        <tr>
                            <td style="padding-top: 5px;">Created By : {{@$order->createdBy->name}}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 5px;">Printed By : {{@$user->name}}
                            </td>
                        </tr>
                    </table>

                </td>
                <td
                    style="padding: 3px; border: 1px solid #000; width:70% border-top: none; border-left: none; vertical-align: bottom;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="text-align: center; padding-bottom: 20px;">FOR
                                <b>{{ Str::ucfirst(@$organization->name) }}</b>
                            </td>
                        </tr>
                        <tr>
                            <td>This is a computer generated document hence not require any signature. </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td colspan="2"
                    style=" border: 1px solid #000; padding: 5px; text-align: center; font-size: 12px; border-top: none; text-align: center;">
                    Regd. Office: {{@$organizationAddress->getFullAddressAttribute()}} <br>
                </td>
                <!-- Principal Office to be added later -->
            </tr>

        </table>

</body>

</html>