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
                        <img src="{{ $imagePath }}" height="50px" alt="">
                    @endif
                </td>

                <!--  {{$type}} Text (Center) -->
                <td style="width: 34%; text-align: center; font-size: 24px; font-weight: 100; padding: 0;">
                    {{$type}}
                </td>

                <!-- Organization Name (Right) -->
                <td style="width: 33%; text-align: right; font-size: 20px; font-weight: 100; padding: 0;">
                    <!-- {{ @$organization->name }} -->
                </td>
            </tr>
        </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td style="border: 1px solid #000; padding: 3px; width: 40%; vertical-align: top;">
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
                            <td style="padding-top: 10px;">
                                {{@$billingAddress->address}}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">
                                {{ @$billingAddress?->city?->name }}, {{ @$billingAddress?->state?->name }}, {{ @$billingAddress?->country?->name }}
                            </td>
                            @if(@$billingAddress->pincode)
                            <td style="padding-top: 3px;">
                                Pin Code: {{ @$billingAddress->pincode }}
                            </td>
                            @endif
                        </tr>

                        <tr>
                            <td style="padding-top: 3px;">
                                @if(@$billingAddress->phone)Phone: {{ @$billingAddress->phone }}, @endif @if(@$order?->customer?->email) Email: {{ @$order?->customer?->email }} @endif
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
                                Seller's Name & Address:
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
                                {{ Str::ucfirst(@$order->location_address_details->address) }},
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">City :</td>
                            <td style="padding-top: 3px;">
                                {{ @$order->location_address_details->city->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">State:</td>
                            <td style="padding-top: 3px;">
                                {{ @$order->location_address_details->state->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Country:</td>
                            <td style="padding-top: 3px;">
                                {{ @$order->location_address_details->country->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Pin Code : </td>
                            <td style="padding-top: 3px;">{{ @$order->location_address_details->pincode }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">GSTIN NO:</td>
                            <td style="padding-top: 3px;">{{@$organization->compliances->gstin_no}}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 3px;">PHONE:</td>
                            <td style="padding-top: 3px;">
                                {{ @$order->location_address_details->contact_phone_no }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">EMAIL ID:</td>
                            <td style="padding-top: 3px;">
                                {{ @$order->location_address_details->contact_email }}
                            </td>
                        </tr>
                        <!-- <tr>
                            <td style="padding-top: 3px;">PAN NO. :</td>
                            <td style="padding-top: 3px;"></td>
                        </tr> -->

                    </table>
                </td>
                <td style="border: 1px solid #000; padding: 3px; border-left: none; vertical-align: top; width: 35%;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td><b>Series:</b></td>
                            <td >{{ @$order->book_code }}
                            </td>
                        </tr>
                        <tr>
                            <td><b>Document No:</b></td>
                            <td >{{ @$order->document_number }}
                            </td>
                        </tr>
                        <tr>
                            <td><b>Document Date:</b></td>
                            @if($order->document_date)
                                <td >{{ date('d-M-y', strtotime($order->document_date)) }}
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <td><b>Reference:</b></td>
                            @if($order->reference_number)
                                <td>{{ $order->reference_number }}
                                </td>
                            @endif
                        </tr>
                        
                    </table>
                </td>
            </tr>
        <tr>
            <td style="border: 1px solid #000; padding: 3px; width: 40%; vertical-align: top;">
                <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                    <tr>
                        <td colspan="3" style="font-weight: 900; font-size: 13px; padding-bottom: 3px;">
                            Shipping Address:
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 3px;">
                            {{@$shippingAddress->address}}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 3px;">
                            {{ @$shippingAddress?->city?->name }}, {{ @$shippingAddress?->state?->name }}, {{ @$shippingAddress?->country?->name }}
                        </td>
                        @if (@$shippingAddress?->pincode)
                        <td style="padding-top: 3px;">
                            Pin Code: {{ @$shippingAddress->pincode }}
                        </td>
                        @endif 
                    </tr>
                    <tr>
                        <td style="padding-top: 3px;">
                            @if(@$shippingAddress->phone)Phone: {{ @$shippingAddress->phone }}, @endif @if(@$organization?->email) Email: {{ @$organization?->email }} @endif
                        </td>
                    </tr>
                </table>
            </td>
                <td
                style="border: 1px solid #000; padding: 3px; border-left: none; vertical-align: top; width: 35%; border-top: none;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>
                                <b style="font-weight: 900;">Status :</b>
                            </td>
                            <td>
                                @if($order->document_status == 'submitted')
                                    <span class="status" style="color: #17a2b8 ">
                                        {{ $order->display_status }}
                                    </span>
                                @elseif($order->document_status == 'draft')
                                    <span style="color: #6c757d">
                                        {{ $order->display_status }}
                                    </span>
                                @elseif($order->document_status == 'approved' || $order->document_status == "approval_not_required")
                                    <span style="color: #28a745">
                                        Approved
                                    </span>
                                @elseif($order->document_status == 'rejected')
                                    <span style="color: #dc3545">
                                        {{ $order->display_status }}
                                    </span>
                                @else
                                    <span style="color: #007bff">
                                        {{ $order->display_status }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><b>{{@$order -> display_status}} by:</b></td>
                                <td>{{$approvedBy}}
                                </td>
                        </tr>
                        <tr>
                            <td><b>Location:</b></td>
                            @if($order->store?->store_name)
                                <td>{{$order->store?->store_name }}
                                </td>
                            @endif
                        </tr>
                    </table>
                </td> 
            </tr>
        </table>

        @if (count($dynamicFields))
            <table style = "border-left: 1px solid #000; border-right:1px solid #000; width:100%;">
                <tr>
                    @foreach ($dynamicFields as $dynamicField)
                        @if ($dynamicField -> value)
                            <td style="padding: 5px"><b>{{$dynamicField -> name}}</b>: {{$dynamicField -> value}} </td>
                        @endif
                    @endforeach
                </tr>
            </table>
        @endif

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td 
                    style="padding: 6px; border: 1px solid #000; border-bottom:none; border-top: none; background: #80808070; text-align: center; font-weight: bold;">
                    #
                </td>
                <td 
                    style="font-weight: bold; width: 25%; padding: 6px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Item
                </td>
                <td 
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    HSN Code
                </td>
                <td 
                    style="font-weight: bold; width:10%; padding: 4px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Attribute
                </td>
                <td 
                    style="font-weight: bold; padding: 4px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Quantity
                </td>
                <td 
                    style="font-weight: bold; padding: 4px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    UOM
                </td>
                <td 
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Rate
                </td>
                <td 
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Value
                </td>
                <td 
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Disc
                </td>
                <td 
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Taxable<br> Value
                </td>
                <td 
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-top: none; border-left: none; text-align: center; background: #80808070;">
                    Tax <br> Amt
                </td>
                <td 
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-top: none; border-left: none; text-align: center; background: #80808070;">
                    Tax <br>Group
                </td>
            </tr> 
            <tr>
        </tr>
            @php 
                $totalCGSTValue = 0.00;
                $totalSGSTValue = 0.00;
                $totalIGSTValue = 0.00;
                $totalTaxValue = 0.00;
            @endphp
            @foreach($items as $key => $val)
            <tr>
                            <td rowspan = "{{count($val -> attribute_wise_qty) + 1}}"
                                style=" vertical-align: middle; padding:10px 3px; border: 1px solid #000;  text-align: center;">
                                {{ $key + 1 }}
                            </td>
                            <td rowspan = "{{count($val -> attribute_wise_qty) + 1}}"
                                style="vertical-align: middle; padding:10px 3px; text-align:left; border: 1px solid #000; border-left: none;">
                                <b> {{ @$val->item_name }}</b><br>
                          @if($val?->attributes->count())
                                    @php 
                                        $html = '';
                                        foreach ($val?->attributes as $data) {
                                            $attr = $data->attribute_name;
                                            $attrValue = $data->attribute_value;
                                            if ($attr && $attrValue) {
                                                if ($html) {
                                                    $html .= ' , ';
                                                }
                                                $html .= "$attr : $attrValue";
                                            } else {
                                                $html .= ":";
                                            }
                                        }
                                    @endphp
                                    {{$html}}
                                    <br>
                                @endif
                                @if(isset($val->specifications))
                                    @foreach($val->specifications as $data)
                                        @if(isset($data->value))
                                            {{$data->specification_name}}:{{$data->value}}<br>
                                        @endif
                                    @endforeach
                                @endif
                                {{ @$val->item_code }}<br />
                                {{@$val->remarks}}
                            </td>
                            <td rowspan = "{{count($val -> attribute_wise_qty) + 1}}"
                                style=" vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-left: none; text-align: center;">
                                {{ @$val->hsn_code }}
                            </td>
                            <td  style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-left: none; text-align: right;">Total</td>
                            <td  style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-left: none; text-align: right;">{{@$val -> order_qty}}</td>
                            <td rowspan = "{{count($val -> attribute_wise_qty) + 1}}"
                            style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-left: none; text-align: center;">
                            {{@$val->uom->name}}
                            </td>
                            <td rowspan = "{{count($val -> attribute_wise_qty) + 1}}"
                                style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-left: none; text-align: right;">
                                {{@$val->rate}}
                            </td>
                            @php
                                $total = $val->order_qty * $val->rate;
                            @endphp
                            <td rowspan = "{{count($val -> attribute_wise_qty) + 1}}"
                                style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-left: none; text-align: right;">
                                {{number_format($total, 2) }}
                            </td>
                            <td rowspan = "{{count($val -> attribute_wise_qty) + 1}}"
                                style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-left: none;  text-align: right;">
                                {{number_format($val->item_discount_amount + $val->header_discount_amount, 2)}}
                            </td>
                            @php
                                $total = $val->order_qty * $val->rate;
                                $netValue = $total - ($val->item_discount_amount + $val->header_discount_amount);
                            @endphp
                            <td rowspan = "{{count($val -> attribute_wise_qty) + 1}}"
                                style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-left: none;  text-align: right;">
                                {{number_format($netValue, 2)}}
                            </td>
                            <td rowspan = "{{count($val -> attribute_wise_qty) + 1}}"
                                style=" vertical-align: middle; padding:10px 3px; border: 1px solid #000;  text-align: right;">
                                @php
                                    $totalTaxAmount=0;
                                    if ($val->tax_ted && $val->tax_ted->count() > 0) {
                                        foreach ($val->tax_ted as $tax) {
                                            $taxName = $tax->ted_name . " " . number_format($tax->ted_percentage, 2) . " %";

                                            // Add tax values to the taxBracket
                                            if (isset($taxBracket[$taxName])) {
                                                $taxBracket[$taxName][0] += $tax->ted_amount;
                                                $taxBracket[$taxName][1] += $tax->assessment_amount;
                                            } else {
                                                $taxBracket[$taxName][0] = $tax->ted_amount;
                                                $taxBracket[$taxName][1] = $tax->assessment_amount;
                                            }

                                            // Add the current tax amount to the total tax
                                            $totalTaxAmount += $tax->ted_amount;
                                        }
                                    }

                                    $totalCGSTValue += $val->cgst_value['value'];
                                    $totalSGSTValue += $val->sgst_value['value'];
                                    $totalIGSTValue += $val->igst_value['value'];
                                    $totalTaxValue = $totalCGSTValue + $totalIGSTValue + $totalSGSTValue;
                                @endphp
                                {{$totalTaxAmount}}
                            </td>
                            <td rowspan = "{{count($val -> attribute_wise_qty) + 1}}"
                                style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; text-align: center;">
                                {{isset($val->tax_ted->toArray()[0]['ted_group_code']) ? $val->tax_ted->toArray()[0]['ted_group_code'] : "NA"}}
                            </td>
                        </tr>
                        @foreach ($val -> attribute_wise_qty as $attrVal)
                            <tr>
                                <td style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-left: none; text-align: right;">{{$attributeName}} : {{$attrVal['attribute_value']}}</td>
                                <td style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-left: none; text-align: right;">{{$attrVal['qty']}}</td>
                            </tr>
                        @endforeach
                            
                        
            @endforeach
        </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td style="padding: 3px; border: 1px solid #000; width: 60%; border-top:none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td> <b> Amount In Words</b> <br>
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
                                <b>Item Total :</b>
                            </td>
                            <td style="text-align: right;">
                                {{ number_format($totalItemValue, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Discount:</b>
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
                        @if (isset($taxBracket) && count($taxBracket) > 0)
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
                        @endif
                        
                        @if(count($order?->expense_ted))
                            <tr>
                                <td style="text-align: right; padding-top: 3px;">
                                    <b>Total After Tax:</b>
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
                                <b>Total Value:</b>
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
                                    {{$order->remarks}}
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
                            <td style = "font-size:10px;">This is a computer generated document, signature not required </td>
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
<!-- 
        @if(isset($pattern) && $pattern == "Delivery Note")
                <div style="page-break-before:always"></div>
                @include('pdf.delivery-note')
        @endif -->

</body>

</html>