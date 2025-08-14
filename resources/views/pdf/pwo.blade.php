<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Work Order</title>
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
                        <img src="{{ $imagePath }}" height="50px" alt="">
                    @endif
                </td>

                <!--  Production Work Order Text (Center) -->
                <td style="width: 37%; text-align: center; font-size: 24px; font-weight: 100; padding: 0;">
                    Production Work Order
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
                    <div style="font-size: 25px; text-align: center; font-weight: bold; color: #222; letter-spacing: 0.5px; margin-bottom: 3px;">
                        {{ @$organization->name }}
                    </div>
                    <div style="font-size: 15px; border-bottom: #000,solid; text-align: center; color: #444; line-height: 1.5;">
                        {{ @$organizationAddress->getDisplayAddressAttribute() }}
                    </div>
                    <table style="width: 100%;" cellspacing="0" cellpadding="0">
                        <tr>
                            <!-- Left Column -->
                            <td style="width: 50%; vertical-align: top;">
                                <table style="width: 70%;" cellspacing="0" cellpadding="2">
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
                                        <td>{{ date('d-M-y', strtotime($order->document_date)) }}</td>
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
                                    <tr>
                                        <td><b>Created By:</b></td>
                                        <td>{{ $order?->createdBy?->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>Printed By:</b></td>
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
                <tr>
                    @php
                        $colspan = 9;
                    @endphp
                    <td colspan='{{$colspan}}' style="padding: 6px; border: 1px solid #000; background: #80808070; text-align: center; font-weight: bold">
                        Product(s)
                    </td>
                </tr>
                <tr>
                    <td style="padding: 6px; border: 1px solid #000; background: #80808070; text-align: center; font-weight: bold;">
                    #
                    </td>
                    <td style="font-weight: bold; width:23.44% padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                        <div style="">Product</div>
                    </td>
                    <td style="font-weight: bold; padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                        Attribute
                    </td>
                    <td style="font-weight: bold; padding: 4px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                        UOM
                    </td>
                    <td style="font-weight: bold; padding: 4px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                        Qty
                    </td>
                    <td style="font-weight: bold; padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                        Customer
                    </td>
                    <td
                    style="font-weight: bold; width:23.44% padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                        <div style="">So No.</div>
                    </td>
                    <td
                    style="font-weight: bold; width:23.44% padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                        <div style="">So Date.</div>
                    </td>
                    <td
                    style="font-weight: bold; width:23.44% padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center;">
                        <div style="">Party Order No.</div>
                    </td>
                </tr>
                @foreach($products as $key => $val)
                <tr>
                    <td
                    style=" vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: center;">
                        {{ $key + 1 }}
                    </td>
                    <td
                    style="vertical-align: middle; padding:10px 3px; text-align:left; border: 1px solid #000; border-top: none; border-left: none;">
                        <b> {{ isset($val->item) ? @$val -> item -> item_name : "" }}</b><br>
                        <b> {{ isset($val->item_code) ? @$val -> item_code : "" }}</b><br>
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
                        style=" vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                        @if(count($val?->attributes))
                        @php 
                            $html = '';
                            foreach ($val?->attributes as $data) {
                                $attr = $data['attribute_group_name'];
                                $attrValue = $data['attribute_name'];
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
                        style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                        {{@$val->uom->name}}
                    </td>
                    <td
                        style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                        {{@$val->qty}}
                    </td>
                    <td
                        style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                        {{@$val->so->customer->company_name}}
                    </td>
                    <td
                    style=" vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: center;">
                        {{ isset($val->so) ? strtoupper($val->so->book_code . "-" . $val->so->document_number) : " " }}
                    </td>
                    <td
                        style=" vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: center;">
                        {{ isset($val->so) ? strtoupper($val->so->document_date) : " " }}
                    </td>
                    <td
                    style=" vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: center;">
                        {{ isset($val->so) ? strtoupper($val->so->reference_no) : " " }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

{{--

            <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th colspan="6" style="padding: 6px; border: 1px solid #000; background: #80808070; text-align: center; font-weight: bold">
                            Raw Materials
                        </th>
                    </tr>
                    <tr>
                        <th style="padding: 6px; border: 1px solid #000; background: #80808070; text-align: center; font-weight: bold; width: 20px;">#</th>
                        <th style="padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: left; font-weight: bold; width: 150px;">Item</th>
                        <th style="padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center; font-weight: bold; width: 100px;">Attribute</th>
                        <th style="padding: 6px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center; font-weight: bold; width: 50px;">Item Type</th>
                        <th style="padding: 4px; border: 1px solid #000; border-left: none; background: #80808070; text-align: center; font-weight: bold; width: 50px;">UOM</th>
                        <th style="padding: 4px; border: 1px solid #000; border-left: none; background: #80808070; text-align: right; font-weight: bold; width: 40px;">Qty</th></tr>
                </thead>
                <tbody>
                    @foreach($items as $key => $val)
                    <tr>
                        <td style="vertical-align: middle; padding: 10px 3px; border: 1px solid #000; border-top: none; text-align: center;">
                            {{ $key + 1 }}
                        </td>
                        <td style="vertical-align: middle; padding: 10px 3px; text-align:left; border: 1px solid #000; border-top: none; border-left: none;">
                            <b>{{ @$val->item->item_name }}</b><br>
                            {{ @$val->item->item_code }}<br />
                            @if(isset($val->specifications))
                                @foreach($val->specifications as $data)
                                    @if(isset($data->value))
                                        {{ $data->specification_name }}: {{ $data->value }}<br>
                                    @endif
                                @endforeach
                            @endif

                            {{ @$val->remarks }}
                        </td>
                        <td width='40px' style="vertical-align: middle; padding: 10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                            @if($val?->attributes->count())
                                @php 
                                    $html = '';
                                    foreach ($val?->attributes as $data) {
                                        $attr = $data->headerAttribute?->name;
                                        $attrValue = $data->headerAttributeValue?->value;
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
                        <td width='40px' style="vertical-align: middle; padding: 10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                            {{ $val->rm_type == "rm" ? "RM" : "WIP" }}
                        </td>
                        <td width='40px' style="vertical-align: middle; padding: 10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                            {{ @$val->uom->name }}
                        </td>
                        <td style="vertical-align: middle; padding: 10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                            {{ @$val->qty }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
--}}

        
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
                        </tr>
                        <tr>
                            <td style="padding: 5px; border: 1px solid black; border-top: none; text-align: center; font-weight: bold;">Store</td>
                            <td style="padding: 5px; border: 1px solid black; border-top: none; border-left: none; text-align: center; font-weight: bold;">Production</td>
                            <td style="padding: 5px; border: 1px solid black; border-top: none; border-left: none; text-align: center; font-weight: bold;">Operation</td>
                            <td style="padding: 5px; border: 1px solid black; border-top: none; border-left: none; text-align: center; font-weight: bold;">PPC</td>
                            <td style="padding: 5px; border: 1px solid black; border-top: none; border-left: none; text-align: center; font-weight: bold;">Approved By</td>
                        </tr>
                    </table>
                </td>
            </tr> 
        </table>
    </body>

</html>