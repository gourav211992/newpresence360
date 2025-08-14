<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INVOICE</title>
    <style>
        .status{
            font-weight: 900;
            text-align: center;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div style="width:700px; font-size: 11px; font-family:Arial;">

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td style="vertical-align: top;">
                    @if (isset($orgLogo))
                        <img src="{!! $orgLogo !!}" alt="" height="50px" />
                    @else
                        <img src="{{$imagePath}}" height="50px" alt="">
                    @endif
                </td>
                <td style="text-align: right; vertical-align: bottom; font-weight: bold; font-size: 18px;">
                    Sales Invoice
                    <br>
                    {{ Str::ucfirst(@$organization->name) }}
                </td>
            </tr>
        </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td rowspan="2" style="border: 1px solid #000; padding: 3px; width: 40%; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td colspan="3" style="font-weight: 900; font-size: 13px; padding-bottom: 3px;">
                                Buyer Name &
                                Address:
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <span style="font-weight: 700; font-size: 13px;">
                                    {{ Str::ucfirst(@$organizationAddress->line_1) }} {{ Str::ucfirst(@$organizationAddress->line_2) }}
                                </span> <br>
                                {{ @$organizationAddress->landmark }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 15px;">Pin Code : </td>
                            <td style="padding-top: 15px; font-weight: 700;">{{ @$organizationAddress->postal_code }}</td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">City:</td>
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
                            <td style="padding-top: 3px;">GSTIN NO</td>
                            <td style="padding-top: 3px;"></td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">State Code:</td>
                            <td style="padding-top: 3px;"></td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Country:</td>
                            <td style="padding-top: 3px;">
                                {{ @$organizationAddress->country->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">GSTIN NO:</td>
                            <td style="padding-top: 3px;"></td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">CIN NO:</td>
                            <td style="padding-top: 3px;"></td>
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
                <td rowspan="2"
                    style="border: 1px solid #000; padding: 3px; border-left: none; vertical-align: top; width: 40%;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td colspan="2" style="font-weight: 900; vertical-align: top;">Supplier's Name & Address:
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-top: 3px;">
                                JPS PLASTICS PVT LTD <br>
                                SARAI ROAD
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 10px;">City: </td>
                            <td style="padding-top: 10px;">
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
                            <td style="padding-top: 3px;"></td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">PHONE:</td>
                            <td style="padding-top: 3px;">
                                {{ @$order->customer->mobile }}
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
                            <td style="padding-top: 3px;"></td>
                        </tr>
                    </table>
                </td>
                <td style="border: 1px solid #000; padding: 3px;float: right; border-left: none; vertical-align: top; width: 20%;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td><b>Order No:</b></td>
                            <td style="font-weight: 900;">
                                {{ @$order->document_number }}
                            </td>
                        </tr>
                        <tr>
                            <td><b>Order Date:</b></td>
                            @if($order->document_date)
                                <td style="font-weight: 900;">
                                    {{ date('d-M-y', strtotime($order->document_date)) }}
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
                <td rowspan="2"
                    style="padding: 2px; border: 1px solid #000; border-top: none; background: #80808070; text-align: center; font-weight: bold;">
                    Sr.No
                </td>
                <td rowspan="2"
                    style="font-weight: bold; width: 150px; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    <div style="">Item</div>
                </td>
                <td rowspan="2"
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    HSN Code
                </td>
                <td rowspan="2"
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Quantity
                </td>
                <td rowspan="2"
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Rate
                </td>
                <td rowspan="2"
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Total
                </td>
                <td rowspan="2"
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Discount
                </td>
                <td rowspan="2"
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Taxable Value
                </td>
                <td colspan="2"
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; text-align: center; background: #80808070; text-align: center;">
                    CGST
                </td>
                <td colspan="2"
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; text-align: center; background: #80808070; text-align: center;">
                    SGST
                </td>
                <td colspan="2"
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; text-align: center; background: #80808070; text-align: center;">
                    IGST
                </td>
            </tr>
            <tr>
                <td
                    style="padding: 2px; border: 1px solid #000; border-left: none; border-top: none; background: #80808070; text-align: center;">
                    Rate
                </td>
                <td
                    style="padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Value
                </td>
                <td
                    style="padding: 2px; border: 1px solid #000; border-left: none; border-top: none; background: #80808070; text-align: center;">
                    Rate
                </td>
                <td
                    style="padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Value
                </td>
                <td
                    style="padding: 2px; border: 1px solid #000; border-left: none; border-top: none; background: #80808070; text-align: center;">
                    Rate
                </td>
                <td
                    style="padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Value
                </td>
            </tr>
            @php 
                $totalCGSTValue = 0.00;
                $totalSGSTValue = 0.00;
                $totalIGSTValue = 0.00;
            @endphp
            @foreach($order->items as $key => $val)
                <tr>

                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: center;">
                        {{ $key + 1 }}</td>
                    <td
                        style="vertical-align: top; padding:10px 3px; text-align:left; border: 1px solid #000; border-top: none; border-left: none;">
                        <b> {{ @$val->item_name }}</b><br/>
                        {{ @$val->item_code }}<br/>
                        {{ucfirst(@$val->item->uom->name)}}<br/>
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
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: left;">
                        {{ number_format($val->cgst_value['rate'], 2) }}    
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: left;">
                        {{ number_format($val->cgst_value['value'], 2) }}  
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: left;">
                        {{ number_format($val->sgst_value['rate'], 2) }}   
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: left;">
                        {{ number_format($val->sgst_value['value'], 2) }}   
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: left;">
                        {{ number_format($val->igst_value['rate'], 2) }}   
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: left;">
                        {{ number_format($val->igst_value['value'], 2) }}   
                        @php 
                            $totalCGSTValue += $val->cgst_value['value'];
                            $totalSGSTValue += $val->sgst_value['value'];
                            $totalIGSTValue += $val->igst_value['value'];
                        @endphp
                    </td>
                </tr>
            @endforeach
        </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td> <b>Total Sales Invoice Value (In Words)</b> <br>
                                {{ @$amountInWords }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 15px;"><b>Currency:</b> {{@$order->currency->name}} </td>
                        </tr>
                        <tr>
                            <td style="color: red; padding-top: 20px;">Please attach Quality Assurance Certificate with
                                each Invoice duly filled, signed
                                and stamped on your company letter head.</td>
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
                                {{ number_format($totalItemValue,2) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Total Discount:</b>
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ number_format($totalDiscount,2) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Taxable Value:</b>
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ number_format($totalTaxableValue,2) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                CGST Value:
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ number_format(@$totalCGSTValue, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                SGST Value:
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ number_format(@$totalSGSTValue, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                IGST Value:
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ number_format(@$totalIGSTValue, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Total After Tax:</b>
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ number_format($totalAfterTax,2)}}
                            </td>
                        </tr>
                        @foreach($order->expense_ted as $key => $expense)
                            <tr>
                                <td style="text-align: right; padding-top: 3px;">
                                    <b>
                                        {{ucFirst($expense->ted_name)}}
                                    </b>
                                </td>
                                <td style="text-align: right; padding-top: 3px;">
                                    {{ number_format(@$expense->ted_amount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Total Sales Invoice Value:</b>
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ number_format($totalAmount,2) }}
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
            <tr>
                <td
                    style="padding: 3px; border: 1px solid #000; width: 55%; border-top: none; border-right: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>Price Basis : </td>
                            <td>FOR GREATER NOIDA</td>
                        </tr>
                        <tr>
                            <td style="padding-top: 15px;">Delivery on or before :</td>
                            <td style="padding-top: 15px;"></td>
                        </tr>
                        <tr>
                            <td style="padding-top: 5px;">Mode of Transport :</td>
                            <td style="padding-top: 5px;"></td>
                        </tr>
                        <tr>
                            <td style="padding-top: 5px;">Payment Terms :</td>
                            <td style="padding-top: 5px;">
                                {{@$order->paymentTerm->name}}
                            </td>
                        </tr>
                    </table>

                </td>
                <td
                    style="padding: 3px; border: 1px solid #000; border-top: none; border-left: none; vertical-align: top; padding-left: 80px;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>Insurance :</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="padding-top: 5px; width: 80px; ">Pack Charges :
                            </td>
                            <td style="padding-top: 5px;"> INCLUDED
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
                            <td colspan="2">Instructions to suppliers :</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-top: 5px;">Please turn over for detailed Purchase Order Terms and
                                Conditions. </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 5px;">Created By :</td>
                            <td style="padding-top: 5px;">
                                {{@$order->createdBy->name}}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 5px;">Printed By :</td>
                            <td style="padding-top: 5px;">
                                {{@$order->createdBy->name}}
                            </td>
                        </tr>
                    </table>

                </td>
                <td
                    style="padding: 3px; border: 1px solid #000; border-top: none; border-left: none; vertical-align: bottom;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="text-align: center; padding-bottom: 20px;">FOR SHEELA FOAM LTD </td>
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
                    Regd. Office:604 Ashadeep,9 Hailey Road,New Delhi 110001 <br>
                    Principal office: Plot No 14, Sector 135 Noida Expressway, Noida -201305
                </td>
            </tr>

        </table>

        <div style="page-break-before:always"></div>


        <!-- Third page Forth page -->

        <table style="width: 100%; margin-bottom: 0px; margin-top: 10px; font-size: 13px;" cellspacing="0"
            cellpadding="0">
            <tr>
                <td colspan="2"
                    style="text-align: center; font-weight: bold; text-decoration: underline; font-size: 14px; padding: 5px; padding-bottom: 10px;">
                    TERMS AND CONDITIONS FOR "PURCHASE ORDER-GOODS"</td>
            </tr>
            <tr>
                <td colspan="2"
                    style="text-align: center; font-weight: bold; text-decoration: underline; font-size: 14px; padding: 5px; padding-bottom: 10px;">
                    {{@$order->TermsCondition->remarks}}
                </td>
            </tr>
        </table>

        <div style="page-break-before:always"></div>
        <!-- Fifth page -->

        <table style="width: 100%; margin-bottom: 0px; margin-top: 15px; font-size: 13px;" cellspacing="0"
            cellpadding="0">
            <tr>
                <td colspan="2" style="padding: 8px 5px;">Date:</td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 5px 5px; padding-top: 40px;">To</td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 0px 5px; line-height: 18px;">SHEELA FOAM LTD UNIT-VI (GNA) <br>
                    PLOT NO 51-A, UDYOG VIHAR , GREATER NOIDA, G.B NAGAR
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 2px 5px; padding-top: 20px;">UTTAR PRADESH</td>
            </tr>
            <tr>
                <td style="width: 45px; padding: 2px 5px;">Phone : </td>
                <td style="padding: 2px 5px;">0120-2569291-93</td>
            </tr>
            <tr>
                <td style="width: 45px; padding: 2px 5px;">E-Mail :</td>
                <td style="padding: 2px 5px;"></td>
            </tr>

            <tr>
                <td colspan="2"
                    style="padding: 8px 5px; padding-top: 50px; font-weight: bold; font-size: 15px; text-decoration: underline;">
                    Sub.: Quality Assurance Certificate. </td>
            </tr>

            <tr>
                <td colspan="2" style="padding: 8px 5px; line-height: 25px;">
                    We hereby certify that the goods manufactured / supplied by us against purchase order No. 13
                    dated : 05-04-2024 do conform to specifications / standard mention in the purchase order.
                    of M/s. Sheela Foam Ltd and our Invoice No. <span
                        style="display: inline-block; min-width: 150px; border-bottom: 1px dotted #000;"> </span>

                    dated : <span style="display: inline-block; min-width: 100px; border-bottom: 1px dotted #000;">
                    </span>, do conform to specifications / standard mention in the purchase order.
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 8px 5px;">We assure for the quality of goods supplied as above.</td>
            </tr>

            <tr>
                <td colspan="2" style="padding: 8px 5px; padding-top: 30px;">For JPS PLASTICS PVT LTD</td>
            </tr>

            <tr>
                <td colspan="2" style="padding: 8px 5px; padding-top: 30px;">AUTHORISED SIGNATORY</td>
            </tr>
        </table>

        <table style="width: 100%; margin-bottom: 0px; margin-top: 15px; font-size: 13px;" cellspacing="0" cellpadding="0">
            <tr>
                <td style="padding: 8px 5px; width: 182px; font-weight: bold; font-size: 13px; vertical-align: top;">IMSales InvoiceRTANT INSTRUCTION-: </td>
                <td style="padding: 8px 5px; font-weight: bold; font-size: 13px;" >Please esnure that latest PDIR format with latest revision number is being filled
                    at your end and sent along with each invoice sent to The Company. For any query
                    regarding same, contact Purchase department.</td>
            </tr>
        </table>

        <div style="page-break-before:always"></div>
        <!-- Six page -->

        <table style="width: 100%; margin-bottom: 0px; margin-top: 15px; font-size: 13px;" cellspacing="0" cellpadding="0">
            <tr>
                <td colspan="2" style="padding: 5px 0px; font-weight: bold; text-decoration: underline; font-size: 16px; text-align: center;">SAFETY INSTRUCTIONS</td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 5px 0px; padding-top: 15px;">Following po_items are strictly prohibited to be brought / used in our factory premises:</td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">1).</td>
                <td style="padding: 5px 0px;">BIDDI, Cigarette. </td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">2).</td>
                <td style="padding: 5px 0px;">Tobacco or any other intoxicant in any form. </td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">3).</td>
                <td style="padding: 5px 0px;">Gutka, Pan Masala.</td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">4).</td>
                <td style="padding: 5px 0px;">Match Sticks or Match Box (Filled or Empty).</td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">5).</td>
                <td style="padding: 5px 0px;">Lighters.</td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">6).</td>
                <td style="padding: 5px 0px;">Alcohal in any form.</td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 5px 0px;">Upon not following above instructions, The Company shall be at liberty to impose the penalties which may please be noted as under:
                </td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">A).</td>
                <td style="padding: 5px 0px;">
                    Penalty of Rs. 250/- (Rupees Two Hundred and Fifty Only) shall be imposed if a pack / bundle or a part of
                    BIDDI or Cigarette is found. This penalty shall multiply with additional packs / bundle.
                </td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">B).</td>
                <td style="padding: 5px 0px;">
                    Penalty of Rs. 250/- (Rupees Two Hundred and Fifty Only) per pouch (Open or Sealed) shall be imposed if a
                    Chewing Tobacco or any other intoxicant in any form is found.
                </td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">C).</td>
                <td style="padding: 5px 0px;">
                    Penalty of Rs. 250/- (Rupees Two Hundred and Fifty Only) per pouch (Open or Sealed) of Gutka, Pan
                    Masala shall be imposed.
                </td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">D).</td>
                <td style="padding: 5px 0px;">
                    Penalty of Rs. 1500/- (Rupees One Thousand Five Hundred Only) shall be imposed if a Match Box (Empty or
                    ttract a penalty of Rs. 1500/- (Rupees One Thousand Five Hundred Only).
                </td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">E).</td>
                <td style="padding: 5px 0px;">
                    Penalty of Rs. 1000/- (Rupees One Thousand Only) shall be imposed on bringing Alcohal inside our factory.

                </td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">F).</td>
                <td style="padding: 5px 0px;">
                    Damage caused to any property inside The Company shall be fully recoverable and the cost of damage shall
                    be acertained by The Company.
                </td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">G).</td>
                <td style="padding: 5px 0px;">Any other instructions which are given by our Security at entry has to be followed to avoid penalty which is
                    not specifically mentioned above.</td>
            </tr>

            <tr>
                <td colspan="2" style="padding: 5px 0px; padding-top: 20px;"> Repeat offenders shall be considered for black-listing.</td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 5px 0px; padding-top: 20px;">
                    It shall be your responsibility to instruct and make sure that the driver / representative of your vehicle deposit
                    above po_items if he / she is carrying at our Security. These po_items shall be returned on vehicle exit from our
                    premises. We shall directly hold you responsible for any lapse on transporters' end on account of above Safety
                    Instructions.
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 5px 0px; padding-top: 20px;">We request your co-operation in the matter.</td>
            </tr>

        </table>

    </div>
</body>

</html>
