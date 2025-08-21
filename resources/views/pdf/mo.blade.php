<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manufacturing Order</title>
    <style>
        .status {
            font-weight: 900;
            text-align: center;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div style="width:100%; font-size: 11px; font-family:Arial;">

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

                <!--  Manufacturing Order Text (Center) -->
                <td style="width: 34%; text-align: center; font-size: 24px; font-weight: 100; padding: 0;">
                    Manufacturing Order
                </td>

                <!-- Organization Name (Right) -->
                <td style="width: 33%; text-align: right; font-size: 20px; font-weight: 100; padding: 0;">
                    <!-- {{ @$organization->name }} -->
                </td>
            </tr>
        </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td style="border: 1px solid #000;  vertical-align: top; width: 35%;">
                    <div style="font-size: 25px; text-align: center; font-weight: bold; color: #222; letter-spacing: 0.5px; margin-bot7m: 3px;">
                        {{ @$organization->name }}
                    </div>
                    <div style="font-size: 15px; border-bottom: #000,solid; text-align: center; color: #444; line-height: 1.5;">
                        {{ @$organizationAddress->getDisplayAddressAttribute() }}
                    </div>
                    <table style="width: 100%;" cellspacing="0" cellpadding="0">
                        <tr>
                            <!-- Left Column -->
                            <td style="width: 50%; vertical-align: top;">
                                <table style="width: 100%;" cellspacing="0" cellpadding="2">
                                    @if($order->store_location?->store_name)
                                    <tr>
                                        <td><b>Location:</b></td>
                                        <td>{{ $order?->store_location?->store_name }}</td>
                                    </tr>
                                    @endif

                                    @if($order->sub_store?->store_name)
                                    <tr>
                                        <td><b>Store:</b></td>
                                        <td>{{ $order?->sub_store?->store_name }}</td>
                                    </tr>
                                    @endif

                                    <tr>
                                        <td><b>Series:</b></td>
                                        <td>{{ @$order->book_code }}</td>
                                    </tr>

                                    <tr>
                                        <td><b>Document No:</b></td>
                                        <td>{{ @$order->document_number }}</td>
                                    </tr>
                                    @if($order->document_date)
                                    <tr>
                                        <td><b>Document Date:</b></td>
                                        <td>{{ $order->document_date ? date('d-M-y H:i:s', strtotime($order->document_date)) : '' }}</td>
                                    </tr>
                                    @endif

                                </table>
                            </td>

                            <!-- Right Column -->
                            <td style="width: 50%; border-left:black solid; vertical-align: top;">
                                <table style="width: 50%;" cellspacing="0" cellpadding="2">
                                    
                                    @if($order?->so?->reference_number)
                                    <tr>
                                        <td><b>Reference:</b></td>
                                        <td>{{ $order?->so?->reference_number }}</td>
                                    </tr>
                                    @endif

                                    <tr>
                                        <td><b>Status:</b></td>
                                        <td>
                                            @if($order->document_status == 'submitted')
                                                <span style="color: #17a2b8">{{ $order->display_status }}</span>
                                            @elseif($order->document_status == 'draft')
                                                <span style="color: #6c757d">{{ $order->display_status }}</span>
                                            @elseif($order->document_status == 'approved' || $order->document_status == 'approval_not_required')
                                                <span style="color: #28a745">Approved</span>
                                            @elseif($order->document_status == 'rejected')
                                                <span style="color: #dc3545">{{ $order->display_status }}</span>
                                            @else
                                                <span style="color: #007bff">{{ $order->display_status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($order->createdBy)
                                    <tr>
                                        <td><b>Created By:</b></td>
                                        <td>{{ $order?->createdBy?->name }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td><b>CRM Name:</b></td>
                                        <td>{{ $user->name }}</td>
                                    </tr>
                                    
                                    <tr>
                                        <td><b>Approved By:</b></td>
                                        <td>{{$approvedBy}}</td>
                                    </tr>
                                </table>
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
            <tbody>
                    @php
                        $colspan = 7;
                        $sheet_check = $products->filter(function($prod){
                                return $prod->number_of_sheet;
                            });
                        $machine_check = $products->filter(function($prod){
                                return $prod->machine_id;
                            });
                        if(count($sheet_check))
                        {
                            $colspan++;
                        }
                        if(count($machine_check))
                        {
                            $colspan++;
                        }
                    @endphp
                <tr>
                    <td colspan="{{ $colspan - 1 }}" style="padding: 6px; border-left: 1px solid #000; background: #80808070; text-align: left; font-weight: bold">
                        So NO : {{ isset($order) ? $order->book_code . '-' . $order->document_number : '' }}<br>
                        So Date : {{ $order?->document_date ? date('d-M-y', strtotime($order->document_date)) : '' }}
                    </td>
                    <td colspan="1" style="padding: 6px; border-right: 1px solid #000; background: #80808070; text-align: left; font-weight: bold">
                        Delivery Date : {{ isset($order?->soItem?->item_deliveries?->delivery_date) ? date('d-M-y', strtotime($order?->soItem?->item_deliveries?->delivery_date )) : '' }}<br>
                        Party Order No:     {{ isset($val->so) ? strtoupper($val->so->reference_number) : " " }}
                    </td>
                </tr>

                <tr>
                    <td style="padding: 6px; border: 1px solid #000; background: #80808070; text-align: center; font-weight: bold;">
                    #
                    </td>
                    <td style="font-weight: bold; padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                        Customer
                    </td>
                    <td style="font-weight: bold; padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                        Attribute
                    </td>
                    <td style="font-weight: bold; width:23.44% padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                        <div style="">Product</div>
                    </td>
                    <td style="font-weight: bold; padding: 4px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                        UOM
                    </td>
                    <td style="font-weight: bold; padding: 4px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                        Qty
                    </td>
                    @if(count($machine_check))
                    <td style="font-weight: bold; padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                        Machine
                    </td>
                    @endif
                    @if(count($sheet_check))
                    <td style="font-weight: bold; padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                        Sheets#
                    </td>
                    @endif
                    <td
                    style="font-weight: bold; width:20.44% padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                        <div style="">Actual GSM</div>
                    </td>
                </tr>
                @foreach($products as $key => $val)
                <tr>
                    <td
                    style="width:5%; vertical-align: middle; padding:7px 3px; border: 1px solid #000; border-top: none;  text-align: center;">
                        {{ $key + 1 }}
                    </td>
                    <td
                        style="vertical-align: middle; width:10%; padding:7px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                        {{@$val->customer->company_name}}
                    </td>
                    <td
                        style=" vertical-align: middle; padding:7px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                        @if($val?->attributes->count())
                        @php 
                            $html = '';
                            foreach ($val?->attributes as $data) {
                                $attr = $data->dis_attribute_name;
                                $attrValue = $data->dis_attribute_value;
                                if ($attr && $attrValue) {
                                    if ($html) {
                                        $html .= ' , ';
                                    }
                                    $html .= "<b>$attr</b> : $attrValue";
                                } else {
                                    $html .= ":";
                                }
                            }
                        @endphp
                        {!! $html !!}
                        @endif
                    </td>
                    <td
                    style="vertical-align: middle; padding:7px 3px; text-align:left; border: 1px solid #000; border-top: none; border-left: none;">
                        <b> {{ isset($val->item_name) ? @$val -> item_name : "" }}</b>(
                        <b> {{ isset($val->item_code) ? @$val -> item_code : "" }}</b>)<br>
                        @if(isset($val->item->specifications))
                            @foreach($val->item->specifications as $data)
                                @if(isset($data->value))
                                {{$data->specification_name}}:{{$data->value}}<br>
                                @endif
                            @endforeach
                        @endif
                        {{@$val->remarks}}
                    </td>
                    <td
                        style="vertical-align: middle; width:8%; padding:7px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                        {{@$val->uom->name}}
                    </td>
                    <td
                        style="vertical-align: middle; width:8%; padding:7px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                        {{@$val->qty}}
                    </td>
                    @if(count($machine_check))
                        <td
                            style="vertical-align: middle; padding:7px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                            {{@$val->machine->name}}
                        </td>
                    @endif
                    @if(count($sheet_check))
                        <td
                            style="vertical-align: middle; padding:7px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                            {{@$val->number_of_sheet}}
                        </td>
                    @endif
                    <td
                        style=" width:15%; vertical-align: middle; padding:7px 3px; border: 1px solid #000; border-top: none;  text-align: center;">

                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        
        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th colspan="7" style="padding: 6px; border: 1px solid #000; background: #80808070; text-align: center; font-weight: bold">
                        Components
                    </th>
                </tr>
                <tr>
                    <th style="padding: 6px; border: 1px solid #000; background: #80808070; text-align: center; font-weight: bold; width: 20px;">#</th>
                    <th style="padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center; font-weight: bold; width: 60px;">Process</th>
                    <th style="padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: left; font-weight: bold; width: 150px;">Item</th>
                    <th style="padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center; font-weight: bold; width: 100px;">Attribute</th>
                    <th style="padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center; font-weight: bold; width: 50px;">Item Type</th>
                    <th style="padding: 4px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center; font-weight: bold; width: 50px;">UOM</th>
                    <th style="padding: 4px; border: 1px solid #000; border-left: none; background: #80808070; text-align: right; font-weight: bold; width: 40px;">Qty</th></tr>
            </thead>
            <tbody>
                @foreach($items as $key => $val)
                <tr>
                    <td style="vertical-align: middle; padding: 7px 3px; border: 1px solid #000; border-top: none; text-align: center;">
                        {{ $key + 1 }}
                    </td>
                    <td width='140px' style="vertical-align: middle; padding:7px 3px; border: 1px solid #000; border-top: none; text-align: center;">
                        {{ $val->station->name }}
                    </td>
                    <td width="150px" style="vertical-align: middle; padding: 7px 3px; text-align:left; border: 1px solid #000; border-top: none; border-left: none;">
                      <b>{{ @$val->item->item_name }}</b>(
                        {{ @$val->item->item_code }})<br />
                        @if(isset($val->specifications))
                            @foreach($val->specifications as $data)
                                @if(isset($data->value))
                                    {{ $data->specification_name }}: {{ $data->value }}<br>
                                @endif
                            @endforeach
                        @endif

                        {{ @$val->remarks }}
                    </td>
                    <td width='40px' style="vertical-align: middle; padding: 7px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                        @if($val?->attributes->count())
                            @php 
                                $html = '';
                                foreach ($val?->attributes as $data) {
                                    $attr = $data->attr_name;
                                    $attrValue = $data->attr_value;
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
                            {{ $html }}<br>
                        @endif
                    </td>
                    <td width='40px' style="vertical-align: middle; padding: 7px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                        {{ $val->rm_type == "rm" ? "RM" : "WIP" }}
                    </td>
                    <td width='40px' style="vertical-align: middle; padding: 7px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                        {{ @$val->uom->name }}
                    </td>
                    <td style="vertical-align: middle; padding: 7px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                        {{ @$val->qty }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>


        
        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td style="padding-right: 1px; width: 99.86%; vertical-align: top;">
                    <div style="width: 100%; border: 1px solid black; border-bottom:none;  border-top: none;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="font-weight: bold; font-size: 13px;"> <b>Remark :</b></td>
                        </tr>
                        <tr>
                            <td>
                                <div style="min-height: 20px;">
                                    {{$order->remarks}}
                                </div>
                            </td>
                        </tr>
                    </table>
                    </div>
                </td>
            </tr>
            <!-- Signature Section -->
            <tr>
                <td colspan="2">
                    <table cellspacing="0" cellpadding="0" style="width: 100%;">
                        <tr>
                            <td style="padding: 5px; border: 1px solid black; text-align: center; font-weight: bold;"><p></p></td>
                            <td style="padding: 5px; border: 1px solid black; border-left: none; text-align: center; font-weight: bold;"><p></p></td>
                            <td style="padding: 5px; border: 1px solid black; border-left: none; text-align: center; font-weight: bold;"><p></p></td>
                            <td style="padding: 5px; border: 1px solid black; border-left: none; text-align: center; font-weight: bold;"><p></p></td>
                            <td style="padding: 5px; border: 1px solid black; border-left: none; text-align: center; font-weight: bold;"><p></p></td>
                            <td style="padding: 5px; border: 1px solid black; border-left: none; text-align: center; font-weight: bold;"><p></p></td>
                        </tr>
                        <tr>
                            <td style="padding: 5px; border: 1px solid black; border-top: none; text-align: center; font-weight: bold;">Store</td>
                            <td style="padding: 5px; border: 1px solid black; border-top: none; border-left: none; text-align: center; font-weight: bold;">PPC</td>
                            <td style="padding: 5px; border: 1px solid black; border-top: none; border-left: none; text-align: center; font-weight: bold;">Production</td>
                            <td style="padding: 5px; border: 1px solid black; border-top: none; border-left: none; text-align: center; font-weight: bold;">Operation</td>
                            <td style="padding: 5px; border: 1px solid black; border-top: none; border-left: none; text-align: center; font-weight: bold;">Quality</td>
                            <td style="padding: 5px; border: 1px solid black; border-top: none; border-left: none; text-align: center; font-weight: bold;">Approved By</td>
                        </tr>
                    </table>
                </td>
            </tr> 
        </table>
        <div style="page-break-inside: avoid;">
            <table style="width: 100%; padding-top:10px; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                <tr>
                    <td style=" margin-top: 10px; text-transform: uppercase; padding: 5px; border: black thin solid;background: #80808070; text-align: center; font-weight: bold;">roll No.</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; font-weight: bold; background: #80808070; text-align: center; ">meter</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; font-weight: bold;background: #80808070; text-align: center;">roll no.</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; font-weight: bold;background: #80808070; text-align: center;">meter</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; font-weight: bold;background: #80808070; text-align: center;">roll no.</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; font-weight: bold;background: #80808070; text-align: center;">meter</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; font-weight: bold;background: #80808070; text-align: center;">roll no</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; font-weight: bold;background: #80808070; text-align: center;">meter</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; font-weight: bold;background: #80808070; text-align: center;">roll no</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; font-weight: bold;background: #80808070; text-align: center;">meter</td>
                </tr>
                <tr>
                    <td style=" margin-top: 10px; text-transform: uppercase; padding: 5px; border: black thin solid; text-align: center; border-top: none;">1</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none;  text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">7</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">13</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">19</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">25</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                </tr>

                <tr>
                    <td style=" margin-top: 10px; text-transform: uppercase; padding: 5px; border: black thin solid; text-align: center; border-top: none;">2</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none;  text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">8</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">14</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">20</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">26</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                </tr>
                <tr>
                    <td style=" margin-top: 10px; text-transform: uppercase; padding: 5px; border: black thin solid; text-align: center; border-top: none;">3</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none;  text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">9</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">15</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">21</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">27</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                </tr>
                <tr>
                    <td style=" margin-top: 10px; text-transform: uppercase; padding: 5px; border: black thin solid; text-align: center; border-top: none;">4</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none;  text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">10</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">16</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">22</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">28</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                </tr>
                <tr>
                    <td style=" margin-top: 10px; text-transform: uppercase; padding: 5px; border: black thin solid; text-align: center; border-top: none;">5</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none;  text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">11</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">17</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">23</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">29</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                </tr>

                <tr>
                    <td style=" margin-top: 10px; text-transform: uppercase; padding: 5px; border: black thin solid; text-align: center; border-top: none;">6</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none;  text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">12</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">18</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">24</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;">30</td>
                    <td style="text-transform: uppercase; padding: 5px; border: black thin solid; border-left: none; text-align: center;  border-top: none;"></td>
                </tr>
            </table>
        </div>                        
    </body>
</html>