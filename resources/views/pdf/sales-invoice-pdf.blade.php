<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Invoice</title>
    <style>
        .status {
            font-weight: 900;
            text-align: center;
            white-space: nowrap;
        }
        .qr-container {
            text-align: center;
            padding: 0px;
        }
        .qr-container img {
            width: 250px;
            height: 250px;
        }
    </style>
</head>
<body>
    <div style="width:700px; font-size: 11px; font-family:Arial;">
    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr><td colspan="3" style="width:100%; text-align:center; font-size:18px; font-weight: 900;">Tax Invoice</td></tr>
            <tr>
                <td style="border: 1px solid #000;  border-bottom: none; padding: 3px; width: 30%; vertical-align: top;">
                    @if (isset($orgLogo) && $orgLogo)
                        <img src="{!! $orgLogo !!}" alt="" height="50px" />
                    @else
                        <img src="{{ $imagePath }}" height="50px" alt="">
                    @endif
                </td>
                <td style="border: 1px solid #000;  border-bottom: none; padding: 3px; width: 40%; vertical-align: top; font-size: 10px;">
                    @php
                        $addressParts = array_filter([
                            $order->location_address_details->address ?? null,
                            $order->location_address_details->city->name ?? null,
                        ]);

                        $countryPincode = array_filter([
                            $order->location_address_details->state->name ?? null,
                            $order->location_address_details->country->name ?? null,
                            $order->location_address_details->pincode ? 'PinCode :'.$order->location_address_details->pincode : null
                        ]);

                        $gstinPan = [];
                        $email ='';
                        $phone ='';
                        if (!empty($organization->gst_number)) {
                            $gstinPan[] = 'GSTIN: ' . $organization->gst_number;
                        }

                        if (!empty($organization->pan_number)) {
                            $gstinPan[] = 'Pan No: ' . $organization->pan_number;
                        }
                        if (!empty($organization->email)) {
                            $email = 'Email: ' . $organization->email;
                        }
                        if (!empty($organization->phone)) {
                            $phone = 'Phone No: ' . $organization->phone;
                        }
                    @endphp
                    <div style="padding: 5px;">
                        <div style="font-weight: bold; font-size: 18px; text-align: left;">
                            {{ @$organization->name }}
                        </div>
                    </div>

                    {{ implode(', ', $addressParts) }},<br>
                    {{ implode(', ', $countryPincode) }} . <b>STATE CODE: {{ @$organizationAddress?->state?->state_code }}</b>
                    @if($email)
                        <br>
                        {{ $email }}
                    @endif
                    @if($phone)
                        {{ $phone }}
                    @endif
                    @if(count($gstinPan))
                    <br>
                        {{ implode(', ', $gstinPan) }}
                    @endif
                </td>
                <td style="border: 1px solid #000;  border-bottom: none; padding: 3px; width: 30%; vertical-align: top;">
                    <b>Invoice No.:</b>
                    {{ @$order->book_code . '-' . @$order->document_number }}
                    
                    @if($order->document_date)
                    <br>
                    <b>Invoice Date:</b>
                    {{ date('d-M-y', strtotime($order->document_date)) }}
                    <br>
                    @endif
                    <b style="font-weight: 900;">Status :</b>
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
                    <br>
                    <b>Reference</b> : {{ $order->reference_number }}
                
                
                <!-- <td style="text-align: right; font-weight: bold; font-size: 14px; vertical-align: top;">
                    Original for Recipient
                </td> -->
                
            </tr>
        </table>
        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td  style="border: 1px solid #000;  border-bottom: none; padding: 3px; width: 30%; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td colspan="1" style="font-weight: 900; font-size: 13px; padding-bottom: 3px; padding-top: 10px; ">BILL TO:
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 10px;" colspan="3">
                                <span style="font-weight: 900; vertical-align: top; padding-top:10px"> 
                                    {{ @$order -> customer ?-> company_name }} 
                                </span>                                
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;" colspan="2">
                            {{@$billingAddress->address}},
                            {{ @$billingAddress?->city?->name }},<br>
                            {{ @$billingAddress?->state?->name }},
                            {{ @$billingAddress?->country?->name }},
                            PinCode :{{ @$billingAddress->pincode }} <br> <b>STATE CODE: {{ @$billingAddress?->state?->state_code }}</b>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                @if (@$order->vendor?->phone)
                                Phone: {{ @$order->vendor?->phone ?? @$order->vendor?->mobile }} ,
                                @endif
                                @if (@$order->vendor?->email)
                                Email: {{ @$order->vendor?->email }}
                                @endif
                            </td>
                        </tr>

                        @if (@$order->vendor?->compliances?->gstin_no)
                        <tr>
                            <td style="padding-top: 2px; padding-bottom:10px;" colspan="3">
                                GSTIN: {{ @$order->vendor?->compliances?->gstin_no }}
                            </td>
                        </tr>
                        @endif
                        @if(@$order->vendor?->compliances?->pan_number)
                        <tr>
                            <td style="padding-top: 2px; padding-bottom:10px;" colspan="3">
                                PAN No: {{ @$order->vendor?->compliances?->pan_number }}
                            </td>
                        </tr>
                        @endif
                    </table>
                </td>
                
                <td style="border: 1px solid #000; border-bottom: none; padding: 3px;  vertical-align: top;" >
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">

                    <tr>
                        <td colspan="1" style="font-weight: 900; font-size: 13px; padding-bottom: 3px; padding-top: 10px;">SHIP TO:</td>
                    </tr>

                    <tr>
                        <td colspan="3" style="padding-top: 10px;">
                            <span style="font-weight: 900;">
                                {{ Str::ucfirst(@$order?->vendor?->company_name) }}
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="3" style="padding-top: 5px;">
                            {{ @$shippingAddress->address }}
                            @if (@$shippingAddress?->city?->name), {{ @$shippingAddress?->city?->name }}, @endif 
                            <br>
                            @if (@$shippingAddress?->state?->name) {{ @$shippingAddress?->state?->name }}, @endif
                            @if (@$shippingAddress?->country?->name) {{ @$shippingAddress?->country?->name }}, @endif
                            @if (@$shippingAddress?->pincode) PinCode : {{ @$shippingAddress?->pincode }} @endif
                            <br><b>STATE CODE: {{ @$shippingAddress?->state?->state_code }}</b>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="3">
                            @if (@$order->vendor?->phone)
                            Phone: {{ @$order->vendor?->phone ?? @$order->vendor?->mobile }} ,
                            @endif
                            @if (@$order->vendor?->email)
                            Email: {{ @$order->vendor?->email }}
                            @endif
                        </td>
                    </tr>

                    @if (@$order->vendor?->compliances?->gstin_no)
                    <tr>
                        <td style="padding-top: 2px; padding-bottom:10px;" colspan="3">
                            GSTIN: {{ @$order->vendor?->compliances?->gstin_no }}
                        </td>
                    </tr>
                    @endif
                    @if(@$order->vendor?->compliances?->pan_number)
                    <tr>
                        <td style="padding-top: 2px; padding-bottom:10px;" colspan="3">
                            PAN No: {{ @$order->vendor?->compliances?->pan_number }}
                        </td>
                    </tr>
                    @endif
                </table> 
            </td>
            <td rowspan="2" style="border: 1px solid #000; border-bottom: none; border-left: none; vertical-align: top; width: 35%; text-align: center;">
                @if(isset($qrCodeBase64))
                    <img src="{{ $qrCodeBase64 }}" style="margin-top:10px; display: inline-block;" width="80%" alt="QR Code">
                @endif
            </td>
            </tr>
            <tr>
                <td colspan="2" style="border: 1px solid #000; padding: 10px 3px; vertical-align: top; border-bottom: none; text-align: left;">
                    @if(isset($eInvoice->irn_number))
                        <b>IRN : </b>{{ $eInvoice->irn_number ?? '' }}<br>
                    @endif
                    @if(isset($eInvoice->ack_no))
                        <b>Acknowledgment No : </b>{{ $eInvoice->ack_no ?? '' }}<br>
                    @endif
                    @if(isset($eInvoice->ewb_no))
                        <b>EWB Number: </b>
                        {{ $eInvoice->ewb_no ?? '' }}<br>
                    @endif
                    @if(isset($order->transportation_mode))
                        <b>Mode of Transport: </b>
                        {{ $order->transportation_mode ?? '' }},
                    @endif
                    @if(isset($order->transporter_name))
                        <b>Transporter Name: </b>{{ $order->transporter_name ?? '' }},
                    @endif
                    @if(isset($order->vehicle_no))
                        <b>Vehicle No:  </b>
                        {{ $order->vehicle_no ?? '' }}
                    @endif
                </td>
            </tr>
        </table>

       
        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td
                    style="padding: 6px; border: 1px solid #000; background: #80808070; text-align: center; font-weight: bold;">
                    #
                </td>
                <td
                    style="font-weight: bold; width: 31.80%; padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                    <div style="">Item</div>
                </td>
                <td
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                    HSN Code
                </td>
                <td
                    style="font-weight: bold; padding: 4px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                    Quantity
                </td>
                <td
                    style="font-weight: bold; padding: 4px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                    UOM
                </td>
                <td
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                    Rate
                </td>
                <td
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                    Value
                </td>
                <td
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                    Discount
                </td>
                <td
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                    Taxable<br> Value
                </td>
                <td
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-left: none; text-align: center; background: #80808070;">
                    Tax <br> Amt
                </td>
                <td
                    style="font-weight: bold; padding: 6px; border: 1px solid #000; border-left: none; text-align: center; background: #80808070;">
                    Tax <br>Group
                </td>
            </tr>
            @php 
                $totalCGSTValue = 0.00;
                $totalSGSTValue = 0.00;
                $totalIGSTValue = 0.00;
                $totalTaxValue = 0.00;
                $taxableValue = 0.00;
                $hsnGroups = [];
            @endphp
            @foreach($order->items as $key => $val)
            @php
                $totalTaxPercentage = 0.00;
                if ($val->item && $val->item->hsn) {
                    $hsnCode = $val->item->hsn->code;
                    $teds = $val->tax_ted;
                    $taxPercentage = 0.00;
                    foreach ($teds as $ted) {
                        $taxPercentage += $ted->ted_percentage;
                        $taxType = $ted->ted_name;
                        $taxableValue = $ted -> assessment_amount;
                        $taxTypeAmount = ($taxableValue * $ted->ted_percentage) / 100;

                        if (!isset($hsnGroups[$hsnCode])) {
                            $hsnGroups[$hsnCode] = [
                                'hsn_code' => $hsnCode,
                                'taxable_rate' => $taxPercentage,
                                'taxable_value' => 0.00,
                                'tax_amount' => 0.00,
                                'tax_group' => $ted->ted_group_code,
                            ];
                        }

                        // Initialize tax type amount if not set
                        if (!isset($hsnGroups[$hsnCode][$taxType . '_amount'])) {
                            $hsnGroups[$hsnCode][$taxType   . '_amount'] = 0.00;
                        }
                        $totalTaxPercentage += $taxPercentage;
                        $hsnGroups[$hsnCode][$taxType . '_amount'] += $taxTypeAmount;
                    }
                    $hsnGroups[$hsnCode]['taxable_value'] += $taxableValue;
                    $hsnGroups[$hsnCode]['taxable_rate'] = $taxPercentage;

                }

                // Now, calculate total tax_amount for each HSN group
                foreach ($hsnGroups as &$group) {
                    $taxAmount = 0.00;
                    foreach ($group as $key => $value) {
                        if (str_ends_with($key, '_amount') && $key !== 'tax_amount') {
                            $taxAmount += (float)$value;
                        }
                    }
                    $group['tax_amount'] = $taxAmount;
                }
            @endphp
                <tr>
                    <td
                        style=" vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: center;">
                        {{ (INT)$key + 1 }}
                    </td>
                    <td
                        style="vertical-align: middle; padding:10px 3px; text-align:left; border: 1px solid #000; border-top: none; border-left: none;">
                        <b> {{ isset($val->customer_item_name) ? @$val -> customer_item_name : @$val -> item_name }}</b><br>
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
                        @if(isset($val?->item?->specifications))
                            @if (@$shufabOrg)
                                @foreach($val->item->specifications as $data)
                                    @if(isset($data->value) && in_array($data->specification_name, ['COLORWAY', 'COLOURWAY', 'MRP']))
                                        {{$data->specification_name}}:{{$data->value}}<br>
                                    @endif
                                @endforeach
                            @else
                                @foreach($val->item->specifications as $data)
                                    @if(isset($data->value))
                                        {{$data->specification_name}}:{{$data->value}}<br>
                                    @endif
                                @endforeach
                            @endif
                            
                        @endif
                        {{ isset($val->customer_item_code) ? @$val -> customer_item_code : @$val -> item_code }}<br />
                        {{@$val->remarks}}
                    </td>
                    <td
                        style=" vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                        {{ @$val->hsn_code }}
                    </td>
                    <td
                        style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                        {{@$val->order_qty}}
                    </td>
                    <td
                        style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                        {{@$val->uom->name}}
                    </td>
                    <td
                        style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                        {{@$val->rate}}
                    </td>
                    @php
                        $total = $val->order_qty * $val->rate;
                    @endphp
                    <td
                        style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                        {{number_format($total, 2) }}
                    </td>
                    <td
                        style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none;  text-align: right;">
                        {{number_format($val->item_discount_amount + $val->header_discount_amount, 2)}}
                    </td>
                    @php
                        $total = $val->order_qty * $val->rate;
                        $netValue = $total - ($val->item_discount_amount + $val->header_discount_amount);
                    @endphp
                    <td
                        style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none;  text-align: right;">
                        {{number_format($netValue, 2)}}
                    </td>
                    <td
                        style=" vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: right;">
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
                    <td
                        style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none;  text-align: center;">
                        {{isset($val->tax_ted->toArray()[0]['ted_group_code']) ? $val->tax_ted->toArray()[0]['ted_group_code'] : "NA"}}
                    </td>
                </tr>
            @endforeach
        </table>
        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td style="padding: 3px; border: 1px solid #000; width: 60%; border-top: none; vertical-align: top;">
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
                            <td>
                                <div style="min-height: 8px;">
                                    {{$order->remarks}}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td colspan="2"
                    style="border: 1px solid #000; width: 50%; border-top: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="font-weight: bold; padding: 4px; background: #80808070; text-align: center;"> <b>HSN / SAC</b></td>
                            <td style="font-weight: bold; padding: 4px; border-left: 1px solid #000; background: #80808070; text-align: center;"> <b>Tax Rate</b></td>
                            <td style="font-weight: bold; padding: 4px; border-left: 1px solid #000; background: #80808070; text-align: center;"> <b>Taxable Amount</b></td>
                            <td style="font-weight: bold; padding: 4px; border-left: 1px solid #000; background: #80808070; text-align: center;"> <b>CGST Amt</b></td>
                            <td style="font-weight: bold; padding: 4px; border-left: 1px solid #000; background: #80808070; text-align: center;"> <b>SGST Amt</b></td>
                            <td style="font-weight: bold; padding: 4px; border-left: 1px solid #000; background: #80808070; text-align: center;"> <b>IGST Amt</b></td>
                            <td style="font-weight: bold; padding: 4px; border-left: 1px solid #000; background: #80808070; text-align: center;"> <b>Total Tax</b></td>
                        </tr>
                        @foreach($hsnGroups as $hsnCode => $hsnData)
                            <tr>
                                <td style="padding: 4px; text-align: center;">{{ $hsnCode }}</td>
                                <td style="padding: 4px; border-left: 1px solid #000; text-align: center;">{{ number_format($hsnData['taxable_rate'], 2) }} %</td>
                                <td style="padding: 4px; border-left: 1px solid #000; text-align: right;">{{ number_format($hsnData['taxable_value'], 2) }}</td>
                                <td style="padding: 4px; border-left: 1px solid #000; text-align: right;">{{ isset($hsnData['CGST_amount']) ? number_format($hsnData['CGST_amount'], 2) : "" }}</td>
                                <td style="padding: 4px; border-left: 1px solid #000; text-align: right;">{{ isset($hsnData['SGST_amount']) ? number_format($hsnData['SGST_amount'], 2) : "" }}</td>
                                <td style="padding: 4px; border-left: 1px solid #000; text-align: right;">{{ isset($hsnData['IGST_amount']) ? number_format($hsnData['IGST_amount'], 2) : "" }}</td>
                                <td style="padding: 4px; border-left: 1px solid #000; text-align: right;">{{ number_format($hsnData['tax_amount'], 2) }}</td>
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
            @if(isset($order->customer_terms))
            <tr>
                <td colspan="2"
                    style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="font-weight: bold; font-size: 13px;"> <b>Terms And Conditions :</b></td>
                        </tr>
                        <tr>
                            <td>
                                <div style="min-height: 80px;">
                                    {!! @$order->customer_terms !!}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            @endif
            <tr>
                <td
                    style="padding: 3px; border: 1px solid #000; width: 30%; border-top: none; border-right: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="padding-top: 5px;">Created By : {{@$order->createdBy->name}}</td>
                        </tr>
                        <tr>
                            <td style="padding-top: 5px;">Approved By : {{$approvedBy}}</td>
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
            </tr>
        </table>
</body>
</html>