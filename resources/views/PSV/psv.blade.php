<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $psv->book_code."-".$psv->document_number }}</title>
    <style>
        .status {
            font-weight: 900;
            text-align: center;
            white-space: nowrap;
        }

        .text-info {
            color: #17a2b8;
            /* Light blue for "Draft" status */
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
    <div style="width:700px; font-size: 11px; font-family:Arial;">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
            <tr>
                <!-- Organization Logo (Left) -->
                <td style="text-align: left; width: 30%;">
                    @if (isset($orgLogo) && $orgLogo)
                        <img src="{!! $orgLogo !!}" alt="" height="50px" />
                    @else
                        <img src="{{ $imagePath }}" height="50px" alt="">
                    @endif
                </td>

                <!--  Document Title (Center) -->
                <td style="width: 40%; text-align: center; font-size: 24px; font-weight: 100; padding: 0;">
                    Physical Stock Verification
                </td>

                <!-- Organization Name (Right) -->
                <td style="width: 30%; text-align: right; font-size: 20px; font-weight: 100; padding: 0;">
                    <!-- {{ @$organization->name }} -->
                </td>
            </tr>
        </table>
        <table style="width: 100%; margin-bottom: 0px; border-collapse: collapse;" cellspacing="0" cellpadding="5">
            <tr>
                <td rowspan="2" style="border: 1px solid #000; padding: 8px; width: 40%; vertical-align: top;">
                    <table style="width: 30%; margin-bottom: 0px; border-collapse: collapse;" cellspacing="0"
                        cellpadding="5">
                        <tr>
                            <td style="padding-top: 3px;"><b>Doc Date:</b></td>
                            @if($psv->document_date)
                                <td style="font-weight: 900; padding-top: 3px; text-align:left;">
                                    {{ date('d-M-y', strtotime($psv->document_date)) }}
                                </td>
                            @else
                                <td colspan="2">N/A</td>
                            @endif
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;"><b>Series:</b></td>
                            <td style="font-weight: 900; padding-top: 3px;">
                                {{ @$psv?->book?->book_code }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px; "><b>Doc No:</b></td>
                            <td style="padding-top: 3px; font-weight: 900;">
                                {{ @$psv->document_number }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;"><b>Status:</b></td>
                            <td style="padding-top: 3px;">
                                <span class="{{$docStatusClass}}">
                                    {{ $psv->display_status }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;"><b>Location:</b></td>
                            <td style="padding-top: 3px;">{{$psv?->store ? $psv?->store?->store_name : '' }}</td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;"><b>Store:</b></td>
                            <td style="padding-top: 3px;">{{$psv?->sub_store ? $psv?->sub_store?->name : " " }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td
                    style="padding: 2px; border: 1px solid #000; background: #80808070; text-align: center; font-weight: bold;width: 15px;">
                    #
                </td>
                <td
                    style="font-weight: bold; width: 100px; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;width:100px">
                    Item
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;width:120px">
                    Attributes
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;width:20px">
                    UOM
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;width:40px">
                    Physical Stock
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;width:40px">
                    Book Stock
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;width:40px">
                    Variance
                </td>
            </tr>

            @php
                $item_total = 0;
            @endphp
            @foreach($psv->items as $key => $psvItem)
                <tr>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: center;">
                        {{ $key + 1 }}
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: left;word-break: break-word;">
                        <b>{{@$psvItem?->item?->item_name}}</b>
                        <br />
                        <b>Code :</b> {{@$psvItem->item_code}}
                        <br />
                        @if($psvItem?->item?->specifications->count())
                            {{ $psvItem?->item->specifications->pluck('value')->implode(', ') }}
                        @endif
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;word-break: break-word;">
                        @if($psvItem?->item_attributes->count())
                            @foreach($psvItem?->item_attributes as $index => $attribute)
                                @if($index > 0 && $index < count($psvItem?->item_attributes))
                                    ,
                                @endif
                                <b>{{ $attribute['group_name'] }}:</b>
                                {{ $attribute['values_data'][0]['value']  }}
                            @endforeach
                        @endif
                        
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;word-break: break-word;">
                        {{@$psvItem?->item?->uom?->name}}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;word-break: break-word;">
                        {{@$psvItem->adjusted_qty}}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                        {{@$psvItem?->confirmed_qty}}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                        {{$psvItem?->confirmed_qty - $psvItem?->adjusted_qty}}
                    </td>
                </tr>
            @endforeach
        </table>
        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
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
                                    {{$psv->remarks}}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
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
                                    @if($psv->getDocuments() && $psv->getDocuments()->count())
                                    @foreach($psv->getDocuments() as $attachment)
                                    @php
                                    $imageExtensions = ['png', 'jpg', 'jpeg', 'gif', 'bmp'];
                                    @endphp
                                    @if(in_array(pathinfo($attachment->file_name, PATHINFO_EXTENSION),
                                    $imageExtensions))
                                    @php
                                    @endphp
                                    <a href="{{ url($psv->getDocumentUrl($attachment)) }}" target="_blank">
                                        <img src="{{$psv->getDocumentUrl($attachment)}}"
                                            alt="Image : {{$attachment->name}}"
                                            style="max-width: 100%; max-height: 150px; margin-top: 10px;">
                                    </a>
                                    @else
                                    <p>
                                        <a href="{{ url($psv->getDocumentUrl($attachment)) }}" target="_blank">
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
                    style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; border-right: none; vertical-align: top;">
                    <table style="width: 50%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="padding-top: 5px;">Created By :</td>
                            <td style="padding-top: 5px;">
                                {{@$psv->createdBy->name}}
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
                <td
                    style="padding: 3px; border: 1px solid #000; border-top: none; border-left: none; vertical-align: bottom;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="text-align: center; padding-bottom: 20px;">FOR
                                {{Str::ucfirst(@$organization->name)}}
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
                    Regd. Office:{{$organizationAddress?->display_address ?? ''}}
                    {{-- <br>
                    Principal office: Plot No 14, Sector 135 Noida Expressway, Noida -201305 --}}
                </td>
            </tr>
        </table>
    </div>
</body>

</html>