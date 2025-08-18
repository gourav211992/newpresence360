<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order</title>
    <style>
        .status{
            font-weight: 900;
            text-align: center;
            white-space: nowrap;
        }
        .text-info {
            color: #17a2b8; /* Light blue for "Draft" status */
        }

        .text-primary {
            color: #007bff; /* Blue for "Submitted" status */
        }

        .text-success {
            color: #28a745; /* Green for "Approval Not Required" and "Approved" statuses */
        }

        .text-warning {
            color: #ffc107; /* Yellow for "Partially Approved" status */
        }

        .text-danger {
            color: #dc3545; /* Red for "Rejected" status */
        }
    </style>
</head>
<body>
    {{-- @include('components.pdf-watermark',['status' => isset($po->document_status) ? $po->document_status : '']) --}}
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
                    PO
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
                                {{ @$po->vendor->mobile }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">EMAIL ID:</td>
                            <td style="padding-top: 3px;">
                                {{ @$po->vendor->email }}
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
                            <td><b>PO No:</b></td>
                            <td style="font-weight: 900;">
                                {{ @$po->document_number }}
                            </td>
                        </tr>
                        <tr>
                            <td><b>PO Date:</b></td>
                            @if($po->document_date)
                                <td style="font-weight: 900;">
                                    {{ date('d-M-y', strtotime($po->document_date)) }}
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
                                    <span class="{{$docStatusClass}}">
                                        {{ $po->display_status }}
                                    </span>
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
                    #
                </td>
                <td rowspan="2"
                    style="font-weight: bold; width: 150px; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    <div style="">Item</div>
                </td>
                <td rowspan="2"
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    HSN
                </td>
                <td rowspan="2"
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Quantity
                </td>
                <td rowspan="2"
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    UOM
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
                {{-- <td rowspan="2"
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;word-wrap: break-word; word-break: break-word;">
                    Taxable Value
                </td> --}}
                <td rowspan="2"
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center; word-wrap: break-word; word-break: break-word; width: 50px;">
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
            @foreach($po->po_items as $key => $val)
            @php
                $totalTaxPercentage = 0.00;
                if ($val->item && $val->item->hsn) {
                    $hsnCode = $val->item->hsn->code;
                    $taxPercentage = 0.00;
                    $teds = $val->taxes;
                    foreach ($teds as $ted) {
                        $taxPercentage += $ted->ted_perc;
                        $taxType = $ted->ted_name;
                        $taxableValue = $ted -> assessment_amount;
                        $taxTypeAmount = ($taxableValue * $ted->ted_perc) / 100;

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
                        $hsnGroups[$hsnCode]['taxable_value'] += $taxableValue;
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
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: center;">
                        {{ $key + 1 }}</td>
                    <td
                        style="vertical-align: top; padding:10px 3px; text-align:left; border: 1px solid #000; border-top: none; border-left: none;">
                        <b> {{ @$val?->item?->item_name }}</b><br/>

                        @if($val?->attributes->count())
                            @php
                                $html = '';
                                foreach($val?->attributes as $attribute) {
                                $attr = \App\Models\AttributeGroup::where('id', @$attribute->attribute_name)->first();
                                $attrValue = \App\Models\Attribute::where('id', @$attribute->attribute_value)->first();

                                    if ($attr && $attrValue) {
                                            if($html) {
                                                $html.= ', ';
                                            }
                                            $html .= "$attr->name : $attrValue->value";
                                    } else {
                                            $html .= ":";
                                    }
                                }
                            @endphp
                            {{$html}}
                        @endif
                        <br/>
                        @if(@$val?->item?->specifications->count())
                            {{-- @foreach(@$val?->item?->specifications as $specification)
                            @endforeach --}}
                            {{ $val->item->specifications->pluck('value')->implode(', ') }}
                            <br/>
                        @endif
                        Code : {{ @$val->item_code }}<br/>
                        {{-- UOM : {{ucfirst(@$val?->item?->uom?->name)}}<br/> --}}
                        @if(@$val->remarks)Remarks : {{@$val->remarks}}@endif
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                        {{ @$val?->hsn?->code }}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                        {{@$val->order_qty}}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                       {{ucfirst(@$val?->item?->uom?->name)}}
                    </td>

                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                        {{@$val->rate}}
                    </td>
                    @php
                    $total = number_format(($val->order_qty * $val->rate), 2, '.', '');

                        // $total = floatval($val->order_qty * $val->rate);
                    @endphp
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                        {{ $total }}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none;  text-align: center; text-align: right;">
                        {{$val->item_discount_amount + $val->header_discount_amount}}
                    </td>
                    @php
                        $total = $val->order_qty * $val->rate;
                        $netValue = $total- $val->item_discount_amount - $val->header_discount_amount;
                        $netValue = number_format($netValue, 2, '.', '');
                    @endphp
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none;  text-align: center; text-align: right;">
                        {{$netValue}}
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: right;">
                        {{ number_format($val->cgst_value['rate'], 2) }}
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: right;">
                        {{ number_format($val->cgst_value['value'], 2) }}
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: right;">
                        {{ number_format($val->sgst_value['rate'], 2) }}
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: right;">
                        {{ number_format($val->sgst_value['value'], 2) }}
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: right;">
                        {{ number_format($val->igst_value['rate'], 2) }}
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: right;">
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
                            <td> <b>Total PO Value (In Words)</b> <br>
                                {{ @$amountInWords }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 15px;"><b>Currency:</b> {{@$po->currency->name}} </td>
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
                                {{ number_format(($totalItemDiscount + $totalHeaderDiscount),2) }}
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
                        @foreach($po->headerExpenses as $key => $expense)

                            <tr>
                                <td style="text-align: right; padding-top: 3px;">
                                    <b>
                                        {{ucFirst($expense->ted_name ?? 'NA') ?? 'NA'}}
                                    </b>
                                </td>
                                <td style="text-align: right; padding-top: 3px;">
                                    {{ number_format(@$expense->ted_amount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Total PO Value:</b>
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
                            <td>
                                <div style="min-height: 8px;">
                                    {{$po->remarks}}
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
            @if($po?->tnc)
            <tr>
                <td colspan="2"
                    style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="font-weight: bold; font-size: 13px;"> <b>Terms and Conditions :</b></td>
                        </tr>
                        <tr>
                            <td>
                                <div style="min-height: 80px;">
                                    {!! $po?->tnc !!}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            @endif
            
            {{-- <tr>
            <td colspan="2"
                style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; vertical-align: top;">
                <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="font-weight: bold; font-size: 13px;"> <b>Attachment :</b></td>
                    </tr>
                    <tr>
                        <td>
                            <div style="min-height: 80px;">
                                @if($po->getDocuments() && $po->getDocuments()->count())
                                    @foreach($po->getDocuments() as $attachment)
                                    @php
                                        $imageExtensions = ['png', 'jpg', 'jpeg', 'gif', 'bmp'];
                                    @endphp
                                    @if(in_array(pathinfo($attachment->file_name, PATHINFO_EXTENSION), $imageExtensions))
                                    @php
                                    @endphp
                                    <a href="{{ url($po->getDocumentUrl($attachment)) }}" target="_blank">
                                        <img src="{{$po->getDocumentUrl($attachment)}}" alt="Image : {{$attachment->name}}" style="max-width: 100%; max-height: 150px; margin-top: 10px;">
                                    </a>
                                    @else
                                    <p>
                                        <a href="{{ url($po->getDocumentUrl($attachment)) }}" target="_blank">
                                        {{ $attachment->name }}
                                        </a>
                                    </p>
                                    @endif
                                    @endforeach
                                @else
                                    <p>No attachments available.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr> --}}
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
                                {{@$po->paymentTerm->name}}
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
                                {{@$po->createdBy->name}}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 5px;">Printed By :</td>
                            <td style="padding-top: 5px;">
                                {{@$po->createdBy->name}}
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

</body>

</html>
