<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$type}}</title>
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
                        <img src="{!! $orgLogo !!}" alt="" height="20px" />
                    @else
                        <img src="{{$imagePath}}" height="20px" alt="">
                    @endif
                </td>

                <!--  {{$type}} Text (Center) -->
                <td style="width: 34%; text-align: center; font-size: 24px; font-weight: 100; padding: 0;">
                    {{$type}}
                </td>

                <!-- Organization Name (Right) -->
                <td style="width: 33%; text-align: right; font-size: 20px; font-weight: 100; padding: 0;">
                    <!-- {{ @$organization->name }} -->
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
                                    <b>{{ Str::ucfirst(@$order->customer->customer_code) }}</b>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 10px;">
                                {{@$billingAddress->address}}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">
                                {{ @$billingAddress?->city?->name }}, {{ @$billingAddress?->state?->name }}, {{ @$billingAddress?->country?->name }}
                            </td>
                            @if(@$billingAddress->pincode)
                            <td style="padding-top: 3px; width:40%">
                                Pin Code: {{ @$billingAddress->pincode }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td style="padding-top: 3px; width:60%">
                                @if(@$billingAddress->phone)Phone: {{ @$billingAddress->phone }}, @endif @if(@$order?->customer?->email) Email: {{ @$order?->customer?->email }} @endif
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
                                Seller's Name & Address:
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" style="font-weight: 700; font-size: 13px; padding-top: 3px;">
                                <b>{{ Str::ucfirst(@$organization->name) }}</b>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 15px;">Address: </td>
                            <td style="padding-top: 15px;">
                                {{ Str::ucfirst(@$order->location_address_details->address.",") }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">City :</td>
                            <td style="padding-top: 3px;">
                                {{ @$order->location_address_details->city->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">State:</td>
                            <td style="padding-top: 3px;">
                                {{ @$order->location_address_details->state->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Country:</td>
                            <td style="padding-top: 3px;">
                                {{ @$order->location_address_details->country->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">Pin Code : </td>
                            <td style="padding-top: 3px;">{{ @$order->location_address_details->pincode }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">GSTIN NO:</td>
                            <td style="padding-top: 3px;">{{@$organization->compliances->gstin_no}}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 3px;">PHONE:</td>
                            <td style="padding-top: 3px;">
                                {{ @$order->location_address_details->contact_phone_no }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">EMAIL ID:</td>
                            <td style="padding-top: 3px;">
                                {{ @$order->location_address_details->contact_email }}
                            </td>
                        </tr>
                        <!-- <tr>
                            <td style="padding-top: 3px;">PAN NO. :</td>
                            <td style="padding-top: 3px;"></td>
                        </tr> -->

                    </table>
                </td>
                <td style="border: 1px solid #000; padding: 3px; border-left: none; vertical-align: top; width: 35%;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td><b>Series:</b></td>
                            <td >{{ @$order->book_code }}
                            </td>
                        </tr>
                        <tr>
                            <td><b>Document No:</b></td>
                            <td >{{ @$order->document_number }}
                            </td>
                        </tr>
                        <tr>
                            <td><b>Document Date:</b></td>
                            @if($order->document_date)
                                <td >{{ date('d-M-y', strtotime($order->document_date)) }}
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <td><b>Reference:</b></td>
                            @if($order->reference_number)
                                <td>{{ $order->reference_number }}
                                </td>
                            @endif
                        </tr>
                        
                    </table>
                </td>
            </tr>
        <tr>
            <td style="border: 1px solid #000; padding: 3px; width: 40%; vertical-align: top;">
                <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                    <tr>
                        <td colspan="3" style="font-weight: 900; font-size: 13px; padding-bottom: 3px;">
                            Shipping Address:
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 3px;">
                            {{@$shippingAddress->address}}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 3px;">
                            {{ @$shippingAddress?->city?->name }}, {{ @$shippingAddress?->state?->name }}, {{ @$shippingAddress?->country?->name }}
                        </td>
                        @if (@$shippingAddress?->pincode)
                        <td style="padding-top: 3px; width:40%">
                            Pin Code: {{ @$shippingAddress->pincode }}
                        </td>
                        @endif 
                    </tr>
                    <tr>
                        <td style="padding-top: 3px; width:60%">
                            @if(@$shippingAddress->phone)Phone: {{ @$shippingAddress->phone }}, @endif @if(@$organization?->email) Email: {{ @$organization?->email }} @endif
                        </td>
                    </tr>
                </table>
            </td>
            <td
                style="border: 1px solid #000; padding: 3px; border-left: none; vertical-align: top; width: 35%; border-top: none;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>
                                <b style="font-weight: 900;">Status :</b>
                            </td>
                            <td>
                                @if($order->document_status == 'submitted')
                                    <span class="status" style="color: #17a2b8 ">
                                        {{ $order->display_status }}
                                    </span>
                                @elseif($order->document_status == 'draft')
                                    <span style="color: #6c757d">
                                        {{ $order->display_status }}
                                    </span>
                                @elseif($order->document_status == 'approved' || $order->document_status == "approval_not_required")
                                    <span style="color: #28a745">
                                        Approved
                                    </span>
                                @elseif($order->document_status == 'rejected')
                                    <span style="color: #dc3545">
                                        {{ $order->display_status }}
                                    </span>
                                @else
                                    <span style="color: #007bff">
                                        {{ $order->display_status }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><b>{{@$order -> document_status!=App\Helpers\ConstantHelper::APPROVAL_NOT_REQUIRED ? @$order->display_status : "Approved" }} by:</b></td>
                                <td>{{$approvedBy}}
                                </td>
                        </tr>
                        <tr>
                            <td><b>Location:</b></td>
                            @if($order->store?->store_name)
                                <td>{{$order->store?->store_name }}
                                </td>
                            @endif
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td
                    style="padding: 6px; border: 1px solid #000; border-top: none; background: #80808070; text-align: center; font-weight: bold;">
                    #
                </td>
                <td
                    style="font-weight: bold; width: {{$type ==  App\Helpers\ConstantHelper::SERVICE_LABEL[App\Helpers\ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS] ?  "23.44%" : "31.80%"}}; padding: 6px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: left;">
                    <div style="">Item</div>
                </td>
                <td
                style="font-weight: bold; padding: 4px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                UOM
                @if($type == App\Helpers\ConstantHelper::SERVICE_LABEL[App\Helpers\ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS])
                <td
                    style="font-weight: bold; width: 7.81%; padding: 6px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    <div style="">Bundle</div>
                </td>
                @endif
                </td>
                <td
                    style="font-weight: bold; padding: 4px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Quantity
                </td>
            </tr>
            @php 
                $totalCGSTValue = 0.00;
                $totalSGSTValue = 0.00;
                $totalIGSTValue = 0.00;
                $totalTaxValue = 0.00;
            @endphp
            @foreach($order->items as $key => $val)


                <tr>
                    <td rowspan="{{ count($val->bundles) ? count($val->bundles) + 1 : 1 }}"
                        style="width:5%; vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: center;">
                        {{ $key + 1 }}
                    </td>
                    <td rowspan="{{ count($val->bundles) ? count($val->bundles) + 1 : 1 }}"
                        style="width:40%;vertical-align: middle; padding:10px 3px; text-align:left; border: 1px solid #000; border-top: none; border-left: none;">
                        <b> {{ isset($val->customer_item_name) ? @$val -> customer_item_name : @$val -> item_name }}</b><br>
                        {{ isset($val->customer_item_code) ? @$val -> customer_item_code : @$val -> item_code }}<br />
                        @if($val?->attributes->count())
                            @php 
                                $html = '';
                                foreach ($val?->attributes as $data) {
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
                        @if(isset($val->specifications))
                            @foreach($val->specifications as $data)
                                @if(isset($data->value))
                                    {{$data->specification_name}}:{{$data->value}}<br>
                                @endif
                            @endforeach
                        @endif
                        {{@$val->remarks}}
                    </td>
                    
                    <td rowspan="{{ count($val->bundles) ? count($val->bundles) + 1 : 1 }}"
                    style="text-align:center; vertical-align: middle; padding:10px 3px;  border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                    {{@$val->uom->name}}
                    </td>
                    @if($type == App\Helpers\ConstantHelper::SERVICE_LABEL[App\Helpers\ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS])
                    <td
                    style=" vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;">
                    Total
                    </td>
                    @endif
                    <td
                        style="vertical-align: middle; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;">
                        {{@$val->order_qty}}
                    </td>
                    @if(count($val->bundles))
                </tr>
                    <!-- <tr>
                        <td style="vertical-align: middle; padding:3px 3px; border: 1px solid #000; text-align: center;" colspan = "5">Bundle Break Down</td>
                    </tr>     -->
                        @foreach($val->bundles as $qty)
                            <tr @if($loop->last) style="border-bottom: 1px solid #000;" @endif>
                                <td style="vertical-align: middle; padding:3px 3px; border: 1px solid #000; text-align: right;" >{{ $qty->bundle_no }}</td>
                                <td style="vertical-align: middle; padding:3px 3px; border: 1px solid #000; border-left: none; text-align: right;">{{ $qty->qty }}</td>
                            </tr>
                        @endforeach
                    @endif
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
                                    {{$order->remarks}}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>


            <!--  -->

            <tr>
                <td
                    style="padding: 3px; border: 1px solid #000; width: 30%; border-top: none; border-right: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">

                        <tr>
                            <td style="padding-top: 5px;">Created By : {{@$order->createdBy->name}}</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 5px;">Printed By : {{@$user->name}}
                            </td>
                        </tr>
                    </table>

                </td>
                <td
                    style="padding: 3px; border: 1px solid #000; width:70% border-top: none; border-left: none; vertical-align: bottom;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="text-align: center; padding-bottom: 20px;">FOR
                                <b>{{ Str::ucfirst(@$organization->name) }}</b>
                            </td>
                        </tr>
                        <tr>
                        <td style = "font-size:10px;">This is a computer generated document, signature not required </td>
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