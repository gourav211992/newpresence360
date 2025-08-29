<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Advice</title>
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

                <!--  Expense Advice Text (Center) -->
                <td style="width: 34%; text-align: center; font-size: 24px; font-weight: 100; padding: 0;">
                    Expense Advice
                </td>

                <!-- Organization Name (Right) -->
                <td style="width: 33%; text-align: right; font-size: 20px; font-weight: 100; padding: 0;">
                    {{-- {{ @$organization->name }} --}}
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
                                    <b>{{ Str::ucfirst(@$organization->name) }}</b>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 10px;">
                                {{@$organizationAddress->line_1}}, {{@$organizationAddress->line_2}}, {{@$organizationAddress->line_3}}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">
                                {{ @$organizationAddress?->city?->name }}, {{ @$organizationAddress?->state?->name }}, {{ @$organizationAddress?->country?->name }}, Pin Code: {{ @$organizationAddress->pincode }}
                            </td>
                        </tr>
                        {{-- <tr>
                            <td style="padding-top: 3px;">GSTIN NO:</td>
                            <td style="padding-top: 3px;">{{@$organization?->gst_number}}</td>
                        </tr> --}}
                        <tr>
                            <td style="padding-top: 3px;">
                                @if(@$organizationAddress->phone)Phone: {{ @$organizationAddress->phone }}, @endif @if(@$organization?->email) Email: {{ @$organization?->email }} @endif
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
                                {{-- <b>{{ Str::ucfirst(@$organization->name) }}</b> --}}
                                <b>{{ Str::ucfirst(@$exp?->vendor?->company_name) }}</b>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 15px;">Address: </td>
                            <td style="padding-top: 15px;">
                                {{ Str::ucfirst(@$shippingAddress?->address) }},
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">City :</td>
                            <td style="padding-top: 3px;">
                                {{ @$shippingAddress?->city?->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">State:</td>
                            <td style="padding-top: 3px;">
                                {{ @$shippingAddress?->state?->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Country:</td>
                            <td style="padding-top: 3px;">
                                {{ @$shippingAddress?->country?->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Pin Code : </td>
                            <td style="padding-top: 3px;">{{ @$shippingAddress->pincode }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">GSTIN No:</td>
                            <td style="padding-top: 3px;">{{@$exp?->vendor->compliances->gstin_no}}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 3px;">Phone:</td>
                            <td style="padding-top: 3px;">
                                {{ @$shippingAddress->phone }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Email:</td>
                            <td style="padding-top: 3px;">
                                {{ @$exp?->vendor?->email }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="border: 1px solid #000; padding: 3px; border-left: none; vertical-align: top; width: 20%;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td><b>Expense Advice No:</b></td>
                            <td style="font-weight: 900;">{{ @$exp->document_number }}
                            </td>
                        </tr>
                        <tr>
                            @if($exp->document_date)
                            <td><b>Expense Advice Date:</b></td>
                                <td style="font-weight: 900;">{{ date('d-M-y', strtotime($exp->document_date)) }}
                                </td>
                            @endif
                        </tr>
                        @if($exp->reference_number)
                        <tr>
                            <td><b>Reference :</b></td>
                                <td style="font-weight: 900;">{{ $exp->reference_number }}
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td>
                                <b>PO No:</b>
                            </td>
                            <td style="font-weight: 900;">

                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>PO Date:</b>
                            </td>
                            <td style="font-weight: 900;">

                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>SO No:</b>
                            </td>
                            <td style="font-weight: 900;">

                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>SO Date:</b>
                            </td>
                            <td style="font-weight: 900;">

                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b style="font-weight: 900;">Status :-</b>
                                @if($exp->document_status == 'submitted')
                                    <span class="status" style="color: #17a2b8 ">
                                        {{ $exp->display_status }}
                                    </span>
                                @elseif($exp->document_status == 'draft')
                                    <span style="color: #6c757d">
                                        {{ $exp->display_status }}
                                    </span>
                                @elseif($exp->document_status == 'approved' || $exp->document_status == "approval_not_required")
                                    <span style="color: #28a745">
                                        Approved
                                    </span>
                                @elseif($exp->document_status == 'rejected')
                                    <span style="color: #dc3545">
                                        {{ $exp->display_status }}
                                    </span>
                                @else
                                    <span style="color: #007bff">
                                        {{ $exp->display_status }}
                                    </span>
                                @endif
                            </td>
                        </tr>
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
                        <tr>
                            <td style="padding-top: 3px;">
                                <span style="font-weight: 700; font-size: 13px;">
                                    <b>{{ Str::ucfirst(@$mrn?->erpStore?->store_name ?? '') }}</b>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">
                                {{@$buyerAddress->address}}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">
                                {{ @$buyerAddress?->city?->name }}, {{ @$buyerAddress?->state?->name }}, {{ @$buyerAddress?->country?->name }}, Pin Code: {{ @$buyerAddress->pincode }}
                            </td>
                        </tr>
                        {{-- <tr>
                            <td style="padding-top: 3px;">GSTIN NO:</td>
                            <td style="padding-top: 3px;">{{@$organization?->gst_number}}</td>
                        </tr> --}}
                        <tr>
                            <td style="padding-top: 3px;">
                                @if(@$buyerAddress->phone)Phone: {{ @$buyerAddress->phone }}, @endif @if(@$organization?->email) Email: {{ @$organization?->email }} @endif
                            </td>
                        </tr>
                    </table>
                </td>
                <td
                    style="border: 1px solid #000; padding: 3px; border-left: none; vertical-align: top; width: 35%; border-top: none;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td><b>Supplier Invoice No:</b></td>
                            <td style="font-weight: 900;">{{ $exp->supplier_invoice_no}}
                            </td>
                        </tr>
                        <tr>
                            <td><b>Supplier Invoice Date:</b></td>
                            <td style="font-weight: 900;">{{ $exp->supplier_invoice_date ? date('d-M-y', strtotime($exp->supplier_invoice_date)) : ''}}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td
                    style="padding: 2px; border: 1px solid #000; border-top: none; background: #80808070; text-align: center; font-weight: bold;">
                    #
                </td>
                <td
                    style="font-weight: bold; width: 31.25%; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    <div style="">Item</div>
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    HSN Code
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Quantity
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    UOM
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Rate
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Value
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Discount
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Taxable<br> Value
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; text-align: center; background: #80808070;">
                    Tax <br> Amt
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; text-align: center; background: #80808070;">
                    Tax <br>Group
                </td>
            </tr>
            @php
                $taxBracket = [];
                $totalCGSTValue = 0.00;
                $totalSGSTValue = 0.00;
                $totalIGSTValue = 0.00;
                $totalTaxValue = 0.00;
            @endphp
            @foreach($exp->items as $key => $val)
                <tr>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: center;">
                        {{ $key + 1 }}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; text-align:left; border: 1px solid #000; border-top: none; border-left: none;">
                        <div style="max-width:180px;word-wrap:break-word;">
                            <b> {{ @$val->item->item_name }}</b>
                            @if(isset($val->attributes))
                                <br>
                                @php
                                    $arrr = $val->attributes ? $val->attributes()->whereNotNull('attr_value')->pluck('attr_value')->all() : [];

                                    $first = true;
                                @endphp
                                @foreach($val->item->itemAttributes as $itemAttribute)
                                    @if(count($arrr))

                                        @foreach ($itemAttribute->attributes() as $value)
                                            @if (in_array($value->id, $arrr))
                                                @if (!$first)
                                                    {{','}}
                                                @endif
                                                {{$value->attributeGroup->name}}:{{ucfirst($value->value)}}
                                                @php
                                                    $first = false;
                                                @endphp
                                            @endif
                                        @endforeach

                                    @endif
                                @endforeach
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
                            {{@$val->remark}}
                        </div>
                    </td>
                    <td
                        style=" vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                        {{ @$val->hsn_code }}
                    </td>
                    <td
                        style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                        {{number_format(@$val->accepted_qty, 2)}}
                    </td>
                    <td
                        style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                        {{@$val->uom->name}}
                    </td>
                    <td
                        style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                        {{number_format(@$val->rate, 2)}}
                    </td>
                    @php
                        $total = $val->accepted_qty * $val->rate;
                    @endphp
                    <td
                        style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                        {{number_format($total, 2) }}
                    </td>
                    <td
                        style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none;  text-align: right;">
                        {{number_format($val->discount_amount + $val->header_discount_amount, 2)}}
                    </td>
                    @php
                        $total = $val->accepted_qty * $val->rate;
                        $netValue = $total - ($val->discount_amount + $val->header_discount_amount);
                    @endphp
                    <td
                        style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none;  text-align: right;">
                        {{number_format($netValue, 2)}}
                    </td>
                    <td
                        style=" vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: right;">
                        @php
                            if (count($val->taxes)) {
                                foreach ($val->taxes as $taxs) {
                                    $taxName = $taxs->ted_name . " " . number_format($taxs->ted_percentage, 2) . " %";
                                    if (isset($taxBracket[$taxName])) {
                                        $taxBracket[$taxName][0] += $taxs->ted_amount;
                                        $taxBracket[$taxName][1] += $taxs->assesment_amount;
                                    } else {
                                        $taxBracket[$taxName][0] = $taxs->ted_amount;
                                        $taxBracket[$taxName][1] = $taxs->assesment_amount;
                                    }

                                }
                            }
                            $totalCGSTValue += $val->cgst_value['value'];
                            $totalSGSTValue += $val->sgst_value['value'];
                            $totalIGSTValue += $val->igst_value['value'];
                            $totalTaxValue = $totalCGSTValue + $totalIGSTValue + $totalSGSTValue;

                        @endphp
                        {{-- {{isset($val?->taxes?->first()->ted_amount) ? $val->taxes->first()->ted_amount : "NA"}} --}}
                        {{ number_format($val->cgst_value['value'] + $val->sgst_value['value'] + $val->igst_value['value'], 2) }}
                    </td>
                    <td
                        style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none;  text-align: center;">
                        {{ $val?->hsn?->tax?->tax_group ?? 'NA' }}
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
                            <td style="padding-top: 15px;"><b>Currency:</b> {{@$exp->currency->name}} </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 10px;"><b>Payment Terms :</b>
                                {{@$exp->paymentTerm->name}}
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
                        @if(isset($exp?->expenses) && count($exp?->expenses))
                            <tr>
                                <td style="text-align: right; padding-top: 3px;">
                                    <b>Total After Tax:</b>
                                </td>
                                <td style="text-align: right; padding-top: 3px;">
                                    {{ number_format($totalAfterTax, 2)}}
                                </td>
                            </tr>
                            @foreach($exp->expenses as $key => $expense)
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
                                    {{$exp->remarks}}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>


        <!--  -->

        <table style="width: 100%; margin-bottom: 0px" cellspacing="0" cellpadding="0">
            <tr>
                <td
                    style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; border-right: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">

                        <tr>
                            <td style="padding-top: 5px;">Created By : {{@$exp->createdBy->name}}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 5px;">Printed By : {{@$user->name}}
                            </td>
                        </tr>
                    </table>

                </td>
                <td
                    style="padding: 3px; border: 1px solid #000; border-top: none; border-left: none; vertical-align: bottom;">
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
