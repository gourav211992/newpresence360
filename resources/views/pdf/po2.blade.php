<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order</title>
    <style>
        .status {
            font-weight: 900;
            text-align: center;
            white-space: nowrap;
        }

        .text-info {
            color: #17a2b8;
        }

        .text-primary {
            color: #007bff;
            /* Blue for "Submitted" status */
        }

        .text-success {
            color: #28a745;
            /* Green for "Approval Not Required" and "Approved" statuses */
        }

        .text-warning {
            color: #ffc107;
            /* Yellow for "Partially Approved" status */
        }

        .text-danger {
            color: #dc3545;
            /* Red for "Rejected" status */
        }
    </style>
</head>

<body>
    @include('components.pdf-watermark', [
        'status' => isset($po->document_status) ? $po->document_status : '',
    ])
    <div style="width:700px; font-size: 11px; font-family:Arial;">
        @include('pdf.partials.header', [
            'orgLogo' => $orgLogo,
            'imagePath' => $imagePath,
            'moduleTitle' => 'Purchase Order',
        ])
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
                                    <b>{{ Str::ucfirst(@$organization->name) }}</b>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-top: 3px;">
                                <span style="font-weight: 700; font-size: 13px;">
                                    <b>{{ Str::ucfirst(@$po?->store_location?->store_name ?? '') }}</b>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 10px;">
                                {{ $sellerBillingAddress?->address }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">
                                {{ @$sellerBillingAddress?->city?->name }}, {{ @$sellerBillingAddress?->state?->name }},
                                {{ @$sellerBillingAddress?->country?->name }}, Pin Code:
                                {{ @$sellerBillingAddress->pincode }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">
                                @if (@$sellerBillingAddress->phone)
                                    Phone: {{ @$sellerBillingAddress->phone }},
                                    @endif @if (@$organization?->email)
                                        Email: {{ @$organization?->email }}
                                    @endif
                            </td>
                        </tr>
                        @if ($organization?->gst_number || $organization?->pan_number)
                            <tr>
                                <td style="padding-top: 3px;">
                                    GSTIN NO: {{ $organization?->gst_number }}
                                </td>
                            </tr>
                        @endif
                        @if ($organization?->pan_number)
                            <tr>
                                <td style="padding-top: 3px;">
                                    PAN NO: {{ $organization?->pan_number }}
                                </td>
                            </tr>
                        @endif
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
                                <b>{{ Str::ucfirst(@$po?->vendor?->company_name) }}</b>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 15px;">Address: </td>
                            <td style="padding-top: 15px;">
                                {{ Str::ucfirst(@$sellerShippingAddress?->address) }},
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">City :</td>
                            <td style="padding-top: 3px;">
                                {{ @$sellerShippingAddress?->city?->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">State:</td>
                            <td style="padding-top: 3px;">
                                {{ @$sellerShippingAddress?->state?->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Country:</td>
                            <td style="padding-top: 3px;">
                                {{ @$sellerShippingAddress?->country?->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Pin Code : </td>
                            <td style="padding-top: 3px;">{{ @$sellerShippingAddress->pincode }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">GSTIN No:</td>
                            <td style="padding-top: 3px;">{{ @$po?->vendor?->compliances?->gstin_no }}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 3px;">Phone:</td>
                            <td style="padding-top: 3px;">
                                {{ @$sellerShippingAddress->phone }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Email:</td>
                            <td style="padding-top: 3px;">
                                {{ @$po?->vendor?->email }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="border: 1px solid #000; padding: 6px 8px; vertical-align: top; width: 20%;">
                    <table style="width: 100%; border-collapse: collapse;" cellspacing="0" cellpadding="3">
                        <tr>
                            <td style="font-weight: bold; white-space: nowrap;">PO No:</td>
                            <td style="font-weight: 900;">{{ $po->document_number ?? '-' }}</td>
                        </tr>

                        @if ($po->document_date)
                            <tr>
                                <td style="font-weight: bold; white-space: nowrap;">PO Date:</td>
                                <td style="font-weight: 900;">{{ date('d-M-Y', strtotime($po->document_date)) }}</td>
                            </tr>
                        @endif

                        @if ($po->reference_number)
                            <tr>
                                <td style="font-weight: bold; white-space: nowrap;">Reference No.:</td>
                                <td style="font-weight: 900;">{{ $po->reference_number }}</td>
                            </tr>
                        @endif

                        @if (!empty($referenceText))
                            <tr>
                                <td style="font-weight: bold; white-space: nowrap;">Indent:</td>
                                <td style="font-weight: 900;">{{ $referenceText }}</td>
                            </tr>
                        @endif
                    </table>
                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 3px; width: 40%; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td colspan="3" style="font-weight: 900; font-size: 13px; padding-bottom: 3px;">
                                Delivery Address:
                            </td>
                        </tr>
                        @if ($po?->consignee_name)
                            <tr>
                                <td style="padding-top: 3px;">
                                    {{ @$po->consignee_name }}
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td style="padding-top: 3px;">
                                {{ @$buyerAddress->address }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">
                                {{ @$buyerAddress?->city?->name }}, {{ @$buyerAddress?->state?->name }},
                                {{ @$buyerAddress?->country?->name }}, Pin Code: {{ @$buyerAddress->pincode }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">
                                @if (@$buyerAddress->phone)
                                    Phone: {{ @$buyerAddress->phone }},
                                    @endif @if (@$organization?->email)
                                        Email: {{ @$organization?->email }}
                                    @endif
                            </td>
                        </tr>
                    </table>
                </td>
                <td
                    style="border: 1px solid #000; padding: 3px; border-left: none; vertical-align: top; width: 35%; border-top: none;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>
                                <b style="font-weight: 900;">Status :-</b>
                                @if ($po->document_status == 'submitted')
                                    <span class="status" style="color: #17a2b8 ">
                                        {{ $po->display_status }}
                                    </span>
                                @elseif($po->document_status == 'draft')
                                    <span style="color: #6c757d">
                                        {{ $po->display_status }}
                                    </span>
                                @elseif($po->document_status == 'approved' || $po->document_status == 'approval_not_required')
                                    <span style="color: #28a745">
                                        Approved
                                    </span>
                                @elseif($po->document_status == 'rejected')
                                    <span style="color: #dc3545">
                                        {{ $po->display_status }}
                                    </span>
                                @else
                                    <span style="color: #007bff">
                                        {{ $po->display_status }}
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
                    style="padding: 2px; border: 1px solid #000; border-top: none; background: #80808070; text-align: center; font-weight: bold; width:10px">
                    #
                </td>
                <td
                    style="font-weight: bold; width: 150px; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;width:190px">
                    <div style="">Item</div>
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;width:54px">
                    HSN
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;width:50px">
                    Qty
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;width:30px">
                    UOM
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;width:50px">
                    Rate
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;width:60px">
                    Value
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;width:50px">
                    Discount
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center; width: 60px;">
                    Taxable <br> Value
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center; width: 50px;">
                    Tax <br>Amnt
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center; width: 40px;">
                    Tax <br> Group
                </td>
            </tr>
            @php
                $hsnGroups = [];
                $taxBracket = [];
                $totalCGSTValue = 0.0;
                $totalSGSTValue = 0.0;
                $totalIGSTValue = 0.0;
            @endphp
            @foreach ($po->po_items as $key => $val)
                <tr>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: center;">
                        {{ $key + 1 }}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; text-align:left; border: 1px solid #000; border-top: none; border-left: none; word-break: break-word;">
                        <b> {{ @$val?->item?->item_name }}</b><br />

                        @if ($val?->attributes->count())
                            @php
                                $html = '';
                                foreach ($val?->attributes as $attribute) {
                                    $attr = \App\Models\AttributeGroup::where(
                                        'id',
                                        @$attribute->attribute_name,
                                    )->first();
                                    $attrValue = \App\Models\Attribute::where(
                                        'id',
                                        @$attribute->attribute_value,
                                    )->first();

                                    if ($attr && $attrValue) {
                                        if ($html) {
                                            $html .= ', ';
                                        }
                                        $html .= "$attr->name : $attrValue->value";
                                    } else {
                                        $html .= ':';
                                    }
                                }
                            @endphp
                            {{ $html }}
                        @endif
                        <br />
                        @if (@$val?->item?->specifications->count())
                            {{ $val->item->specifications->pluck('value')->implode(', ') }}
                            <br />
                        @endif
                        Code : {{ @$val->item_code }}
                        <br />
                        @if (@$val?->itemDelivery?->count() > 1)
                            Delivery Schedule: <br />
                            @foreach (@$val?->itemDelivery as $poItemDelivery)
                                {{ $poItemDelivery->getFormattedDate('delivery_date') }}:{{ $poItemDelivery->qty }}
                                @if (!$loop->last)
                                    ,
                                @endif
                            @endforeach
                        @else
                            Delivery Date: {{ $val->getFormattedDate('delivery_date') }}
                        @endif

                        @if ($soTracking)
                            <br /> So No.: {{ @$val->so->full_document_number }}
                        @endif
                        @if (@$val->remarks)
                            <br /> Remarks : {{ @$val->remarks }}
                        @endif
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;word-break: break-word;">
                        {{ @$val?->hsn?->code }}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right; word-break: break-word;">
                        {{ @$val->order_qty }}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center; word-break: break-word;">
                        {{ ucfirst(@$val?->item?->uom?->name) }}
                    </td>

                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right; word-break: break-word;">
                        {{ @$val->rate }}
                    </td>
                    @php
                        $total = number_format($val->order_qty * $val->rate, 2, '.', '');
                    @endphp
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right; word-break: break-word;">
                        {{ $total }}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none;  text-align: center; text-align: right; word-break: break-word;">
                        {{ number_format($val->item_discount_amount + $val->header_discount_amount, 2) }}
                    </td>
                    @php
                        $total = $val->order_qty * $val->rate;
                        $netValue = $total - $val->item_discount_amount - $val->header_discount_amount;
                        $netValue = number_format($netValue, 2, '.', '');
                    @endphp
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none;  text-align: center; text-align: right; word-break: break-word;">
                        {{ number_format($netValue, 2) }}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none;  text-align: center; text-align: right; word-break: break-word;">
                        {{ number_format($val->cgst_value['value'] + $val->sgst_value['value'] + $val->igst_value['value'], 2) }}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none;  text-align: center; text-align: right; word-break: break-word;">
                        {{ $val?->ted_tax?->taxDetail?->erpTax?->tax_group ?? 'NA' }}
                    </td>
                </tr>
            @endforeach
        </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td style="padding: 3px; border: 1px solid #000; width: 60%; border-top: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td> <b>Amount In Words</b> <br>
                                {{ @$amountInWords }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 15px;"><b>Currency:</b> {{ @$po->currency->name }} </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 10px;"><b>Payment Terms :</b>
                                {{ @$po->paymentTerm->name }}
                            </td>
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
                    <table style="width: 100%; margin-bottom: 0px; margin-top: 10px;" cellspacing="0"
                        cellpadding="0">
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
                                {{ number_format($totalItemDiscount + $totalHeaderDiscount, 2) }}
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
                        @foreach($taxes as $tax)
                            <tr>
                                <td style="text-align: right; padding-top: 3px;">
                                    <b>{{ $tax->ted_name }} @ {{  number_format($tax->ted_perc, 2)}}%:</b>
                                </td>
                                <td style="text-align: right; padding-top: 3px;">
                                    {{ number_format($tax->total_amount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                        @if ($po?->headerExpenses?->count())
                            <tr>
                                <td style="text-align: right; padding-top: 3px;">
                                    <b>Total After Tax:</b>
                                </td>
                                <td style="text-align: right; padding-top: 3px;">
                                    {{ number_format($totalAfterTax, 2) }}
                                </td>
                            </tr>
                        @endif
                        @foreach ($po->headerExpenses as $expense)
                            @php
                                $totalExp = @$expense->ted_amount + @$expense->tax_amount;
                            @endphp
                            <tr>
                                <td style="text-align: right; padding-top: 3px;">
                                    <b>{{ ucFirst($expense->ted_name ?? 'NA') ?? 'NA' }}:</b>
                                </td>
                                <td style="text-align: right; padding-top: 3px;">
                                    {{ number_format($totalExp, 2) }}
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Total PO Value:</b>
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ number_format($totalAmount, 2) }}
                            </td>
                        </tr>
                        {{-- @if ($isDifferentCurrency)
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Value in {{$po?->org_currency_code}}:</b>
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ number_format(round($totalAmount * $po?->org_currency_exg_rate,2) ,2) }}
                            </td>
                        </tr>
                        @endif --}}
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
                                    {{ $po->remarks }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td colspan="2" style="border: 1px solid #000; width: 50%; border-top: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="font-weight: bold; padding: 4px; background: #80808070; text-align: center;">
                                <b>HSN / SAC</b>
                            </td>
                            <td
                                style="font-weight: bold; padding: 4px; border-left: 1px solid #000; background: #80808070; text-align: center;">
                                <b>Tax Rate</b>
                            </td>
                            <td
                                style="font-weight: bold; padding: 4px; border-left: 1px solid #000; background: #80808070; text-align: center;">
                                <b>Taxable Amount</b>
                            </td>
                            <td
                                style="font-weight: bold; padding: 4px; border-left: 1px solid #000; background: #80808070; text-align: center;">
                                <b>CGST Amt</b>
                            </td>
                            <td
                                style="font-weight: bold; padding: 4px; border-left: 1px solid #000; background: #80808070; text-align: center;">
                                <b>SGST Amt</b>
                            </td>
                            <td
                                style="font-weight: bold; padding: 4px; border-left: 1px solid #000; background: #80808070; text-align: center;">
                                <b>IGST Amt</b>
                            </td>
                            <td
                                style="font-weight: bold; padding: 4px; border-left: 1px solid #000; background: #80808070; text-align: center;">
                                <b>Total Tax</b>
                            </td>
                        </tr>
                        @foreach ($hsnSummary as $row)
                        <tr>
                            <td style="padding: 4px; border-left: 1px solid #000; text-align: center;">{{ $row['hsn'] }}</td>
                            <td style="padding: 4px; border-left: 1px solid #000; text-align: center;">{{ number_format(($row['total_tax'] / $row['taxable_value']) * 100, 2) }}%</td>
                            <td style="padding: 4px; border-left: 1px solid #000; text-align: right;">{{ number_format($row['taxable_value'], 2) }}</td>
                            <td style="padding: 4px; border-left: 1px solid #000; text-align: right;">{{ number_format($row['cgst'], 2) }}</td>
                            <td style="padding: 4px; border-left: 1px solid #000; text-align: right;">{{ number_format($row['sgst'], 2) }}</td>
                            <td style="padding: 4px; border-left: 1px solid #000; text-align: right;">{{ number_format($row['igst'], 2) }}</td>
                            <td style="padding: 4px; border-left: 1px solid #000; text-align: right;">{{ number_format($row['total_tax'], 2) }}</td>
                        </tr>
                        @endforeach

                    </table>
                </td>
            </tr>
            @if ($po?->tnc)
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
            <tr>
                <td
                    style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; border-right: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="padding-top: 5px;">Created By : {{ @$po?->createdBy?->name ?? '' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 5px;">Printed By : {{ @$user?->name ?? '' }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td
                    style="padding: 3px; border: 1px solid #000; border-top: none; border-left: none; vertical-align: bottom;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="text-align: center; padding-bottom: 20px;">FOR
                                {{ Str::ucfirst(@$organization->name) }}
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
                    Regd. Office: {{ @$organizationAddress->getFullAddressAttribute() }} @if (@$organization?->gst_number)
                        , GSTIN NO - {{ @$organization?->gst_number }}
                        @endif @if (@$organization?->pan_number)
                            , PAN NO - {{ @$organization?->pan_number }}
                        @endif
                        <br>
                </td>
            </tr>
        </table>

    </div>
</body>

</html>
