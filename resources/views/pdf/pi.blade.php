<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Indent</title>
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
    {{-- @include('components.pdf-watermark',['status' => isset($pi->document_status) ? $pi->document_status : '']) --}}
    <div style="width:700px; font-size: 11px; font-family:Arial;">
        @include('pdf.partials.header', [
            'orgLogo' => $orgLogo,
            'imagePath' => $imagePath,
            'moduleTitle' => 'Purchase Indent'
        ])
        <table style="width: 100%; margin-bottom: 0px; border-collapse: collapse;" cellspacing="0" cellpadding="5">
            <tr>
                <td rowspan="2" style="border: 1px solid #000; padding: 8px; width: 40%; vertical-align: top;">
                    <table style="width: 30%; margin-bottom: 0px; border-collapse: collapse;" cellspacing="0"
                        cellpadding="5">
                        <tr>
                            <td style="padding-top: 3px;"><b>Doc Date:</b></td>
                            @if($pi->document_date)
                                <td style="font-weight: 900; padding-top: 3px; text-align:left;">
                                    {{ date('d-M-y', strtotime($pi->document_date)) }}
                                </td>
                            @else
                                <td colspan="2">N/A</td>
                            @endif
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;"><b>Series:</b></td>
                            <td style="font-weight: 900; padding-top: 3px;">
                                {{ @$pi?->book?->book_code }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px; "><b>Doc No:</b></td>
                            <td style="padding-top: 3px; font-weight: 900;">
                                {{ @$pi->document_number }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;"><b>Status:</b></td>
                            <td style="padding-top: 3px;">
                                <span class="{{$docStatusClass}}">
                                    {{ $pi->display_status }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;"><b>Location:</b></td>
                            <td style="padding-top: 3px;">{{$pi?->store ? $pi?->store?->store_name : '' }}</td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;"><b>Requester:</b></td>
                            <td style="padding-top: 3px;">{{$pi?->sub_store ? $pi?->sub_store?->name : $pi?->requester?->name }}</td>
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
                    style="font-weight: bold; width: 100px; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;width:150px">
                    Item
                </td>
                <td
                    style="font-weight: bold; width: 100px; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;width:100px">
                    Attributes
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;width:40px">
                    UOM
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;width:60px">
                    Quantity
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;width:100px">
                    Vendor Name
                </td>
                @if(strtolower($pi->so_tracking_required) == 'yes')
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;width:80px">
                    SO No.
                </td>
                @endif
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;width:110px">
                    Remarks
                </td>
            </tr>

            @php
                $item_total = 0;
                $waste_total = 0;
                $over_total = 0;
            @endphp
            @foreach($pi->pi_items as $key => $piItem)
                <tr>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: center;">
                        {{ $key + 1 }}
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: left;word-break: break-word;">
                        <b>{{@$piItem?->item?->item_name}}</b>
                        <br />
                        Code : {{@$piItem->item_code}}
                        <br />
                        @if($piItem?->item?->specifications->count())
                            {{ $piItem?->item->specifications->pluck('value')->implode(', ') }}
                        @endif
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: left;word-break: break-word;">
                        @if($piItem?->item?->itemAttributes->count())
                            @foreach($piItem?->item?->itemAttributes as $index => $attribute)
                                @php
                                    $headerAttribute = $piItem->attributes()->where('attribute_name', $attribute->attribute_group_id)->first();
                                @endphp
                                @if(isset($headerAttribute))
                                    {{$headerAttribute?->headerAttribute?->name ?? "NA"}}:
                                    {{ $headerAttribute?->headerAttributeValue?->value }}
                                    @if(!$loop->last)
                                        ,
                                    @endif
                                @endif
                            @endforeach
                        @endif
                    </td>
                    {{-- <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: left;">
                        @if($piItem?->item?->specifications->count())
                            {{ $piItem?->item->specifications->pluck('value')->implode(', ') }}
                        @endif
                    </td> --}}
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: left;word-break: break-word;">
                        {{@$piItem?->item?->uom?->name}}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;word-break: break-word;">
                        {{@$piItem->indent_qty}}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: left;word-break: break-word;">
                        {{@$piItem?->vendor?->company_name}}
                    </td>
                    @if(strtolower($pi->so_tracking_required) == 'yes')
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: left;word-break: break-word;">
                        {{@$piItem?->so?->full_document_number ?? ''}}
                    </td>
                    @endif
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: left;word-break: break-word;">
                        {{ $piItem->remarks }}
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
                                    {{$pi->remarks}}
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
                                    @if($pi->getDocuments() && $pi->getDocuments()->count())
                                    @foreach($pi->getDocuments() as $attachment)
                                    @php
                                    $imageExtensions = ['png', 'jpg', 'jpeg', 'gif', 'bmp'];
                                    @endphp
                                    @if(in_array(pathinfo($attachment->file_name, PATHINFO_EXTENSION),
                                    $imageExtensions))
                                    @php
                                    @endphp
                                    <a href="{{ url($pi->getDocumentUrl($attachment)) }}" target="_blank">
                                        <img src="{{$pi->getDocumentUrl($attachment)}}"
                                            alt="Image : {{$attachment->name}}"
                                            style="max-width: 100%; max-height: 150px; margin-top: 10px;">
                                    </a>
                                    @else
                                    <p>
                                        <a href="{{ url($pi->getDocumentUrl($attachment)) }}" target="_blank">
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
                                {{@$pi->createdBy->name}}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 5px;">Printed By :</td>
                            <td style="padding-top: 5px;">
                                {{ $user->name ?? ''}}
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