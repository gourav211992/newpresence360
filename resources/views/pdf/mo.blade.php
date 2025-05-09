<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bom</title>
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
    <div style="width:700px; font-size: 11px; font-family:Arial;">
        <table style="width: 100%; margin-bottom: 10px;" cellspacing="0" cellpadding="0">
            <tr>
                <td style="text-align: left;">
                    @if (isset($orgLogo) && $orgLogo)
                        <img src="{!! $orgLogo !!}" alt="" height="50px" />
                    @else
                        <img src="{{$imagePath}}" height="50px" alt="">
                    @endif
                </td>
                <td style="text-align: center; font-weight: bold; font-size: 18px;">
                    Manufacturing Order
                </td>
                <td style="text-align: right; font-weight: bold; font-size: 18px;">
                    {{ Str::ucfirst(@$organization->name) }}
                </td>
            </tr>
        </table>
        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td style="border: 1px solid #000; padding: 3px; width: 40%; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td colspan="3" style="font-weight: 900; font-size: 13px; padding-bottom: 3px;">
                                Product Details:
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Name : </td>
                            <td style="padding-top: 3px; font-weight: 700;">{{ @$bom?->item?->item_name }}</td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Code : </td>
                            <td style="padding-top: 3px; font-weight: 700;">{{ @$bom?->item?->item_code }}</td>
                        </tr>
                        @if($bom?->item?->itemAttributes->count())
                            @foreach($bom?->item?->itemAttributes as $index => $attribute)
                            @php
                            $headerAttribute = $bom->moAttributes()->where('attribute_name',$attribute->attribute_group_id)->first();
                            @endphp
                            @if(isset($headerAttribute))
                            <tr>
                                <td style="padding-top: 3px;">{{$headerAttribute?->headerAttribute?->name ?? "NA"}}:</td>
                                <td style="padding-top: 3px;">
                                    {{ $headerAttribute?->headerAttributeValue?->value }}
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        @endif
                        <tr>
                            <td style="padding-top: 3px;">UOM:</td>
                            <td style="padding-top: 3px;">
                                {{ @$bom?->item?->uom?->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Quantity:</td>
                            <td style="padding-top: 3px;">
                                {{ @$bom?->qty_produced }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="border: 1px solid #000; padding: 3px; border-left: none; vertical-align: top; width: 40%;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td colspan="3" style="font-weight: 900; font-size: 13px; padding-bottom: 3px;">
                                Specifications:
                            </td>
                        </tr>
                        @if(isset($specifications))
                            @foreach($specifications as $specification)
                            <tr>
                                <td style="padding-top: 3px;">{{$specification?->specification_name}}: </td>
                                <td style="padding-top: 3px;">
                                    {{ $specification->value }}
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </table>
                </td>
                <td style="border: 1px solid #000; padding: 3px;float: right; border-left: none; vertical-align: top; width: 20%;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="padding-top: 3px;"><b>Doc Date:</b></td>
                            @if($bom->document_date)
                                <td style="font-weight: 900;padding-top: 3px;">
                                    {{ date('d-M-y', strtotime($bom->document_date)) }}
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;"><b>Series:</b></td>
                            <td style="font-weight: 900;padding-top: 3px;">
                                {{ @$bom?->book?->book_code }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;"><b>Doc No:</b></td>
                            <td style="padding-top: 3px;font-weight: 900;">
                                {{ @$bom->document_number }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px"><b style="font-weight: 900;">Status:</b></td>
                            <td style="padding-top: 3px">
                                <span class="{{$docStatusClass}}">
                                        {{ $bom->display_status }}
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
            style="padding: 2px; border: 1px solid #000; border-top: none; background: #80808070; text-align: center; font-weight: bold;width: 10px;">
            #
        </td>
        <td rowspan="2"
            style="font-weight: bold; width: 100px; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">Section
        </td>
        <td rowspan="2"
            style="font-weight: bold; width: 100px; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">Item
        </td>
        <td rowspan="2"
            style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
            UOM
        </td>
        <td rowspan="2"
            style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
            Station
        </td>
        <td rowspan="2"
            style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
            Consumption
        </td>
        <td rowspan="2"
            style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
            Cost
        </td>
        <td rowspan="2"
            style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
            Item Value
        </td>
        <td colspan="2"
            style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; text-align: center; background: #80808070; text-align: center;">
            Waste
        </td>
        <td rowspan="2"
            style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
            Overhead
        </td>
        <td rowspan="2" style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; background: #80808070; text-align: center;">
            Total Cost
        </td>
    </tr>
    <tr>
        <td
            style="padding: 2px; border: 1px solid #000; border-left: none; border-top: none; background: #80808070; text-align: center;">
            %
        </td>
        <td
            style="padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
            Value
        </td>
    </tr>
    @php
    $item_total = 0;
    $waste_total = 0;
    $over_total = 0;
    @endphp
    @foreach($bom->moItems as $key => $bomItem)
        <tr>
            <td style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: center;">
                {{ $key + 1 }}</td>
            <td style="vertical-align: top; padding:10px 3px; text-align:left; border: 1px solid #000; border-top: none; border-left: none;">
                {{ @$bomItem?->section_name }}<br/>
                {{@$bomItem?->sub_section_name}}
            </td>
            <td style="vertical-align: top; padding:10px 3px; text-align:left; border: 1px solid #000; border-top: none; border-left: none;">
                <b>{{ @$bomItem?->item?->item_name }}</b><br/>
                <b>{{ @$bomItem->item_code }}</b><br/>
                @if($bomItem?->item?->itemAttributes->count())
                    @foreach($bomItem?->item?->itemAttributes as $index => $attribute)
                        @php
                        $headerAttribute = $bomItem->attributes()->where('attribute_name',$attribute->attribute_group_id)->first();
                        @endphp
                        @if(isset($headerAttribute))
                            {{$headerAttribute?->headerAttribute?->name ?? "NA"}}:
                            {{ $headerAttribute?->headerAttributeValue?->value }}
                            @if(!$loop->last)
                                <br/>
                            @endif
                        @endif
                    @endforeach
                @endif
            </td>
            <td style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                {{@$bomItem?->item?->uom?->name}}
            </td>
            <td style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                {{ @$bomItem->station_name }}
                @if($bomItem->remark) <br /> Remark: {{ $bomItem->remark }} @endif
            </td>
            <td style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                {{@$bomItem->qty}}
            </td>
            <td style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                {{@$bomItem->item_cost}}
            </td>
            @php
                $total = $bomItem->qty * $bomItem->item_cost;
            @endphp
            <td style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                {{ number_format($total,2) }}
            </td>
            <td style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: right;">
                {{ $bomItem->waste_perc }}    
            </td>
            <td style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: right;">
                {{ number_format($bomItem->waste_amount, 2) }}  
            </td>
            <td style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: right;">
                {{ number_format($bomItem->overhead_amount, 2) }}   
            </td>
            <td style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: right;">
                {{ number_format($total + $bomItem->waste_amount + $bomItem->overhead_amount , 2) }}   
            </td>
        </tr>
        @php
        $item_total += $total;
        $waste_total += $bomItem->waste_amount;
        $over_total += $bomItem->overhead_amount;
        @endphp 
    @endforeach
    <tr>
        <td colspan="7" style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: 1px solid #000; text-align: center;"></td>
        <td style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
            {{number_format($item_total,2)}}
        </td>
        <td colspan="2" style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
            {{number_format($waste_total,2)}}
        </td>
        <td style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
            {{number_format($over_total,2)}}
        </td>
        <td style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
            {{ number_format($item_total + $waste_total + $over_total, 2) }}
        </td>
    </tr>
</table>


        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td> <b>Total Value (In Words)</b> <br>
                                {{ @$amountInWords }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td
                    style="padding: 3px; border: 1px solid #000; border-top: none; border-left: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px; margin-top: 10px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="text-align: right;">
                                <b>Total Item Cost:</b>
                            </td>
                            <td style="text-align: right;">
                                {{ number_format($item_total + $waste_total + $over_total,2) }}
                            </td>
                        </tr>
                        {{-- <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Total Overheads:</b>
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ number_format($bom->item_overhead_amount + $bom->header_waste_amount,2) }}
                            </td>
                        </tr> --}}
                        @if($bom->moOverheadItems->count())
                        @foreach($bom->moOverheadItems as $_key => $bomOverheadItem)
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>{{$bomOverheadItem->overhead_description ?? 'Overhead'}} {{++$_key}}:</b>
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ number_format($bomOverheadItem->overhead_amount,2) }}
                            </td>
                        </tr>
                        @endforeach
                        @endif
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Wastage: @if($bom->header_waste_perc) ({{$bom->header_waste_perc}} %) @endif</b>
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ number_format($bom->header_waste_amount,2) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Grand Total:</b>
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
                                    {{$bom->remarks}}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td
                    style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; border-right: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="padding-top: 5px;">Created By :</td>
                            <td style="padding-top: 5px;">
                                {{@$bom->createdBy->name}}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 5px;">Printed By :</td>
                            <td style="padding-top: 5px;">
                                {{ auth()->guard('web2')->user()->name ?? ''}}
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="padding: 3px; border: 1px solid #000; border-top: none; border-left: none; vertical-align: bottom;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="text-align: center; padding-bottom: 20px;">FOR {{Str::ucfirst(@$organization->name)}} </td>
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
                    Regd. Office:{{$organizationAddress?->display_address ?? ''}}
                </td>
            </tr>
        </table>
        @if($bom->getDocuments() && $bom->getDocuments()->count())
        <div style="page-break-before: always;"></div>
        <table style="width: 100%; margin-bottom: 0px; border: 1px solid #000; border-collapse: collapse;" cellspacing="0" cellpadding="5">
            <tr>
                <td colspan="2" style="padding: 8px; border: 1px solid #000; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px; border-collapse: collapse;" cellspacing="0" cellpadding="5">
                        <tr>
                            <td style="font-weight: bold; font-size: 13px;">
                                <b>Attachments:</b>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="min-height: 80px;">
                                    @foreach($bom->getDocuments() as $attachment)
                                        @if(Str::contains($attachment->mime_type, 'image'))
                                        <img src="{{$bom->getPdfDocumentUrl($attachment)}}" alt="Image : {{$attachment->name}}" style="max-width: 100%; max-height: 150px; margin-top: 10px;">
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        @endif
    </div>
</body>
</html>
