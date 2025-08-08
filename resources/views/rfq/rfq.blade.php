<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $rfq->book_code."-".$rfq->document_number }}</title>
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
                    Request For Quotation
                </td>

                <!-- Organization Name (Right) -->
                <td style="width: 30%; text-align: right; font-size: 20px; font-weight: 100; padding: 0;">
                    <!-- {{ @$organization->name }} -->
                </td>
            </tr>
        </table>
        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td rowspan="2"
                    style="border: 1px solid #000; padding: 3px; border-right: none; vertical-align: top; width: 60%;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td colspan="2"
                                style="font-weight: 900; font-size: 13px; padding-bottom: 3px; vertical-align: top;">
                                Buyer's Name & Address:
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="font-weight: 700; font-size: 13px; padding-top: 3px;">
                                <b>{{ Str::ucfirst(@$organization->name) }}</b>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 15px; width: 20%;"><b>Location:</b></td>
                            @if($rfq->store?->store_name)
                                <td style="padding-top: 15px; width: 80%;">{{$rfq->store?->store_name }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td style="padding-top: 15px; width: 20%;">Address: </td>
                            <td style="padding-top: 15px; width: 80%;">
                                {{ Str::ucfirst(@$rfq->location_address_details->address) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px; width: 20%;">City :</td>
                            <td style="padding-top: 3px; width: 80%;">
                                {{ @$rfq->location_address_details->city->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px; width: 20%;">State:</td>
                            <td style="padding-top: 3px; width: 80%;">
                                {{ @$rfq->location_address_details->state->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px; width: 20%;">Country:</td>
                            <td style="padding-top: 3px; width: 80%;">
                                {{ @$rfq->location_address_details->country->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px; width: 20%;">Pin Code : </td>
                            <td style="padding-top: 3px; width: 80%;">{{ @$rfq->location_address_details->pincode }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px; width: 20%;">GSTIN NO:</td>
                            <td style="padding-top: 3px; width: 80%;">{{@$organization->compliances->gstin_no}}</td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px; width: 20%;">PHONE:</td>
                            <td style="padding-top: 3px; width: 80%;">
                                {{ @$rfq->location_address_details->contact_phone_no }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px; width: 20%;">EMAIL ID:</td>
                            <td style="padding-top: 3px; width: 80%;">
                                {{ @$rfq->location_address_details->contact_email }}
                            </td>
                        </tr>
                    </table>
                </td>
                
                <td style="border: 1px solid #000; padding: 3px; vertical-align: top; width: 35%;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td><b>Series:</b></td>
                            <td >{{ @$rfq->book_code }}
                            </td>
                        </tr>
                        <tr>
                            <td><b>Document No:</b></td>
                            <td >{{ @$rfq->document_number }}
                            </td>
                        </tr>
                        <tr>
                            <td><b>Document Date:</b></td>
                            @if($rfq->document_date)
                                <td >{{ date('d-M-y', strtotime($rfq->document_date)) }}
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <td><b>Contact Name:</b></td>
                            @if($rfq->contact_name)
                                <td>{{ $rfq->contact_name }}
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <td><b>Email:</b></td>
                            @if($rfq->contact_email)
                                <td>{{ $rfq->contact_email }}
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <td><b>Phone:</b></td>
                            @if($rfq->contact_phone)
                                <td>{{ $rfq->contact_phone }}
                                </td>
                            @endif
                        </tr>
                        
                    </table>
                </td>
            </tr>
        <tr>
            <td
                style="border: 1px solid #000; padding: 3px;  vertical-align: top; width: 35%; border-top: none;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>
                                <b style="font-weight: 900;">Status :</b>
                            </td>
                            <td>
                                @if($rfq->document_status == 'submitted')
                                    <span class="status" style="color: #17a2b8 ">
                                        {{ $rfq->display_status }}
                                    </span>
                                @elseif($rfq->document_status == 'draft')
                                    <span style="color: #6c757d">
                                        {{ $rfq->display_status }}
                                    </span>
                                @elseif($rfq->document_status == 'approved' || $rfq->document_status == "approval_not_required")
                                    <span style="color: #28a745">
                                        Approved
                                    </span>
                                @elseif($rfq->document_status == 'rejected')
                                    <span style="color: #dc3545">
                                        {{ $rfq->display_status }}
                                    </span>
                                @else
                                    <span style="color: #007bff">
                                        {{ $rfq->display_status }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><b>{{@$rfq -> document_status!=App\Helpers\ConstantHelper::APPROVAL_NOT_REQUIRED ? @$rfq->display_status : "Approved" }} by:</b></td>
                                <td>{{$approvedBy}}
                                </td>
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
                    style="padding: 2px; border: 1px solid #000; background: #80808070; text-align: center; font-weight: bold;width: 15px;">
                    #
                </td>
                <td
                    style="font-weight: bold; width: 100px; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;width:100px">
                    Item Code
                </td>
                <td
                    style="font-weight: bold; width: 100px; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;width:100px">
                    Item Description
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;width:20px">
                    UOM
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;width:40px">
                    Quantity
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;width:40px">
                    Remarks
                </td>
            </tr>

            @php
                $item_total = 0;
            @endphp
            @foreach($rfq->items as $key => $rfqItem)
                <tr>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: center;">
                        {{ $key + 1 }}
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: left;word-break: break-word;">
                        <b>Code :</b> {{@$rfqItem->item_code}}
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: left;word-break: break-word;">
                        <b>Name : {{@$rfqItem?->item?->item_name}}</b>
                        <br />
                        @if($rfqItem?->item_attributes->count())
                            <b>Attributes :</b> 
                            @foreach($rfqItem?->item_attributes as $index => $attribute)
                                @if($index > 0 && $index < count($rfqItem?->item_attributes))
                                    ,
                                @endif
                                <b>{{ $attribute['group_name'] }}:</b>
                                {{ $attribute['values_data'][0]['value']  }}
                            @endforeach
                        @endif
                        <br />
                        @if($rfqItem?->item?->specifications->count())
                            <b>Specifications :</b>
                            {{ $rfqItem?->item->specifications->pluck('value')->implode(', ') }}
                        @endif
                        
                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;word-break: break-word;">
                        {{@$rfqItem?->item?->uom?->name}}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;word-break: break-word;">
                        {{@$rfqItem->request_qty}}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;word-break: break-word;">
                        {{@$rfqItem->remarks}}
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
                            <td style="font-weight: bold; font-size: 13px;"> <b>Instructions :</b></td>
                        </tr>
                        <tr>
                            <td>
                                <div style="min-height: 80px;">
                                    {!! $rfq->instructions !!}
                                </div>
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
                                    {{$rfq->remark}}
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
                                    @if($rfq->getDocuments() && $rfq->getDocuments()->count())
                                    @foreach($rfq->getDocuments() as $attachment)
                                    @php
                                    $imageExtensions = ['png', 'jpg', 'jpeg', 'gif', 'bmp'];
                                    @endphp
                                    @if(in_array(pathinfo($attachment->file_name, PATHINFO_EXTENSION),
                                    $imageExtensions))
                                    @php
                                    @endphp
                                    <a href="{{ url($rfq->getDocumentUrl($attachment)) }}" target="_blank">
                                        <img src="{{$rfq->getDocumentUrl($attachment)}}"
                                            alt="Image : {{$attachment->name}}"
                                            style="max-width: 100%; max-height: 150px; margin-top: 10px;">
                                    </a>
                                    @else
                                    <p>
                                        <a href="{{ url($rfq->getDocumentUrl($attachment)) }}" target="_blank">
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
                                {{@$rfq->createdBy->name}}
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