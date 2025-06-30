<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$print_type}}</title>
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
        <table style="width: 100%; margin-bottom: 10px;" cellspacing="0" cellpadding="0">
            <tr>
                <td style="text-align: left; width:30%;">
                    @if (isset($orgLogo) && $orgLogo)
                        <img src="{!! $orgLogo !!}" alt="" height="50px" />
                    @else
                        <img src="{{$imagePath}}" height="50px" alt="">
                    @endif
                </td>
                <td style="text-align:center;font-weight: bold; font-size: 22px;  width:40%;">
                    {{$print_type}}
                </td>
                <td style = "width:30%;">
                </td>
            </tr>
        </table>
        <table style="width: 100%; margin-bottom: 0px; border-collapse: collapse;" cellspacing="0" cellpadding="5">
            <tr>
            <td rowspan="1"
                    style="border: 1px solid #000; padding: 3px;  vertical-align: top; width: 40%;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td colspan="3" style="font-weight: 700; font-size: 13px; padding-top: 3px;">
                                <b>{{ Str::ucfirst(@$organization->name) }}</b>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" style="font-weight: 700; font-size: 13px; padding-top: 3px;">
                                <b>{{ Str::ucfirst(@$mx?->from_store?->store_name ?? "No address available") }}</b>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 15px;">Address: </td>
                            <td style="padding-top: 15px;">
                                {{ Str::ucfirst(@$shippingAddress->address) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">City :</td>
                            <td style="padding-top: 3px;">
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
                            <td style="padding-top: 3px;">Country:</td>
                            <td style="padding-top: 3px;">
                                {{ @$shippingAddress->country->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Pin Code : </td>
                            <td style="padding-top: 3px;">{{ @$shippingAddress->pincode }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">GSTIN NO:</td>
                            <td style="padding-top: 3px;">{{@$organization->gst_number}}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 3px;">PHONE:</td>
                            <td style="padding-top: 3px;">
                                {{ @$shippingAddress->contact_phone_no }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">EMAIL ID:</td>
                            <td style="padding-top: 3px;">
                                {{ @$shippingAddress->contact_email }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">PAN NO:</td>
                            <td style="padding-top: 3px;">
                                {{ @$organization?->pan_number }}
                            </td>
                        </tr>
                        <!-- <tr>
                            <td style="padding-top: 3px;">PAN NO. :</td>
                            <td style="padding-top: 3px;"></td>
                        </tr> -->

                    </table>
                </td>
                <td rowspan="1" style="border: 1px solid #000; border-left: none; padding: 3px; width: 40%; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        @if($mx->issue_type != "Consumption")
                        <tr>
                            <td colspan="3" style="font-weight: 900; font-size: 13px; padding-bottom: 3px;">
                                {{@$mx -> issue_type === "Sub Contracting" || @$mx -> issue_type === "Job Work" ? 'Supplier Details:' : 'Customer Details:'}}
                            </td>
                        </tr>
                        @if($mx->issue_type == "Sub Contracting" || @$mx -> issue_type === "Job Work" )
                        <tr>
                            <td colspan="3" style="padding-top: 3px;">
                                <span style="font-weight: 700; font-size: 13px;">
                                <b>{{ Str::ucfirst(@$mx?->vendor?->company_name ?? "") }}</b>
                                </span>
                            </td>
                        </tr>
                        @endif
                        @if($mx->issue_type == "Sub Contracting" || @$mx -> issue_type === "Job Work" )
                        <tr>
                            <td colspan="3" style="padding-top: 3px;">
                                <span style="font-weight: 700; font-size: 13px;">
                                <b>{{ Str::ucfirst(@$mx?->to_sub_store?->name ?? "") }}</b>
                                </span>
                            </td>
                        </tr>
                        @else
                        <tr>
                            <td colspan="3" style="padding-top: 3px;">
                                <span style="font-weight: 700; font-size: 13px;">
                                <b>{{ Str::ucfirst(@$mx?->to_store?->store_name ?? "") }}</b>
                                </span>
                            </td>
                        </tr>
                        @endif
                        
                        <tr>
                            <td style="padding-top: 15px;">Address: </td>
                            <td style="padding-top: 15px;">
                                {{ Str::ucfirst(@$billingAddress->address) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">City :</td>
                            <td style="padding-top: 3px;">
                                {{ @$billingAddress->city->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">State:</td>
                            <td style="padding-top: 3px;">
                                {{ @$billingAddress->state->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Country:</td>
                            <td style="padding-top: 3px;">
                                {{ @$billingAddress->country->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Pin Code : </td>
                            <td style="padding-top: 3px;">{{ @$billingAddress->pincode }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">PHONE:</td>
                            <td style="padding-top: 3px;">
                                {{ @$billingAddress->contact_phone_no }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">GSTIN NO:</td>
                            <td style="padding-top: 3px;">
                                {{ @$mx ?-> vendor ?-> compliances ?-> gstin_no }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">PAN NO:</td>
                            <td style="padding-top: 3px;">
                                {{ @$mx?-> vendor ?-> pan_number }}
                            </td>
                        </tr>
                        @else
                        <tr>
                            <td colspan="3" style="font-weight: 900; font-size: 13px; padding-bottom: 3px;">
                                Receiver :
                            </td>
                        </tr>
                         <tr>
                            <td>
                            <span style="font-weight: 700; font-size: 13px;">
                            <b>{{ $mx?->requester_name() }}</b>
                        </span></td>
                        </tr>
                        @endif
                    </table>
                </td>
                
                <td style="border: 1px solid #000; padding: 1px; width: 34%; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="3">
                        <tr>
                            <td colspan="3" style="font-weight: 900; font-size: 13px; padding-bottom: 3px; vertical-align:top;">
                                Document Details:
                            </td>
                        </tr>
                        <tr>
                            <td><b>Date:</b></td>
                            <td>
                                {{ $mx->document_date ? date('d-M-y', strtotime($mx->document_date)) : 'N/A' }}
                            </td>
                        </tr>
                        <tr>
                            <td><b>Series:</b></td>
                            <td>{{ @$mx?->book?->book_code }}</td>
                        </tr>
                        <tr>
                            <td><b>Doc No:</b></td>
                            <td>{{ @$mx->document_number }}</td>
                        </tr>
                        <tr>
                            <td><b>Status:</b></td>
                            <td>
                                @if($mx->document_status == 'submitted')
                                    <span style="color: #17a2b8">{{ $mx->display_status }}</span>
                                @elseif($mx->document_status == 'draft')
                                    <span style="color: #6c757d">{{ $mx->display_status }}</span>
                                @elseif($mx->document_status == 'approved' || $mx->document_status == "approval_not_required")
                                    <span style="color: #28a745">Approved</span>
                                @elseif($mx->document_status == 'rejected')
                                    <span style="color: #dc3545">{{ $mx->display_status }}</span>
                                @else
                                    <span style="color: #007bff">{{ $mx->display_status }}</span>
                                @endif
                            </td>
                        </tr>
                       
                        <tr>
                            <td><b>Approved by:</b></td>
                            <td>{{ $approvedBy }}</td>
                        </tr>

                        <tr>
                            <td><b>Type of Challan:</b></td>
                            <td>{{ @$mx->issue_type }}</td>
                        </tr>

                        @if ($jobOrderNos)
                        <tr>
                            <td><b>Job Order No:</b></td>
                            <td>{{ @$jobOrderNos }}</td>
                        </tr>
                    
                        @endif
                        
                    </table>
                </td>
            </tr>
        </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td
                    style="padding: 2px; border: 1px solid #000; width: 20px; background: #80808070; text-align: center; font-weight: bold;">
                    #
                </td>
                <td
                    style="font-weight: bold; width: 150px; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: left;">
                    Item Code
                </td>
                <td
                    style="font-weight: bold; width: 35%; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: left;">
                    Item Name
                </td>
                <td
                    style="font-weight: bold; width: 100px; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: left;">
                    Attributes
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: left;">
                    UOM
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: right;">
                    Quantity
                </td>
                <!-- <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                    Rate
                </td>
                <td
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                    Total
                </td> -->
            </tr>

            @php
                $item_total = 0;
                $waste_total = 0;
                $over_total = 0;
            @endphp
            @foreach($mx->items as $key => $mxItem)
                <tr>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: center;">
                        {{ $key + 1 }}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; text-align:left; border: 1px solid #000; border-top: none; border-left: none;">
                        {{ @$mxItem->item_code }}<br />

                    </td>
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: left;" >
                        {{@$mxItem?->item?->item_name}}
                        <br>
                        @if($mxItem?->item?->specifications->count())
                            {{ $mxItem?->item->specifications->pluck('value')->implode(', ') }}
                        @endif
                        @if(isset($mxItem->specifications))
                            @foreach($mxItem->specifications as $data)
                                @if(isset($data->value))
                                    {{$data->specification_name}}:{{$data->value}}<br>
                                @endif
                            @endforeach
                        @endif
                        {{@$mxItem->remarks? "Remarks : ".$mxItem->remarks : ""}}

                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; text-align:left; border: 1px solid #000; border-top: none; border-left: none;">
                        @if($mxItem?->attributes->count())
                            @php 
                                $html = '';
                                foreach ($mxItem?->attributes as $data) {
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
                    </td>
                    {{-- <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: left;">
                        @if($mxItem?->item?->specifications->count())
                            {{ $mxItem?->item->specifications->pluck('value')->implode(', ') }}
                        @endif
                    </td> --}}
                    <td
                        style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: left;">
                        {{@$mxItem?->item?->uom?->name}}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                        {{@$mxItem->issue_qty}}
                    </td>
                    <!-- <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                        {{@$mxItem->rate}}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                        {{@$mxItem->total_item_amount}}
                    </td>
                    <td
                        style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: left;">
                    </td> -->
                </tr>
            @endforeach
        </table>
        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td colspan="2"
                    style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="font-weight: bold; font-size: 13px;"> <b>Remarks :</b></td>
                        </tr>
                        <tr>
                            <td>
                                <div style="min-height: 80px;">
                                    {{$mx->remarks}}
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
                                    @if($mx->getDocuments() && $mx->getDocuments()->count())
                                    @foreach($mx->getDocuments() as $attachment)
                                    @php
                                    $imageExtensions = ['png', 'jpg', 'jpeg', 'gif', 'bmp'];
                                    @endphp
                                    @if(in_array(pathinfo($attachment->file_name, PATHINFO_EXTENSION),
                                    $imageExtensions))
                                    @php
                                    @endphp
                                    <a href="{{ url($mx->getDocumentUrl($attachment)) }}" target="_blank">
                                        <img src="{{$mx->getDocumentUrl($attachment)}}"
                                            alt="Image : {{$attachment->name}}"
                                            style="max-width: 100%; max-height: 150px; margin-top: 10px;">
                                    </a>
                                    @else
                                    <p>
                                        <a href="{{ url($mx->getDocumentUrl($attachment)) }}" target="_blank">
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
                                {{@$mx->createdBy->name}}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 5px;">Printed By :</td>
                            <td style="padding-top: 5px;">
                                {{ App\Helpers\Helper::getAuthenticatedUser()->name ?? ''}}
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