<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INVOICE</title>
    <style>
        .status{
            display: inline-block;
            font-weight: 900;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: .25rem;
            transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        }
    </style>
</head>
<body>
    <div style="width:700px; font-size: 11px; font-family:Arial;">

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td style="vertical-align: top;">
                    <img src="{{$imagePath}}" height="50px" alt="">
                </td>
                <td style="text-align: right; vertical-align: bottom; font-weight: bold; font-size: 18px;">
                    GRN
                    <br>
                    {{ Str::ucfirst(@$organization->name) }}
                </td>
            </tr>
        </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td rowspan="2" style="border: 1px solid #000; padding: 3px; width: 40%; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td colspan="3" style="font-weight: 900; font-size: 13px; padding-bottom: 3px;">
                                Buyer Name &
                                Address:
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <span style="font-weight: 700; font-size: 13px;">
                                    {{ Str::ucfirst(@$organizationAddress->line_1) }} {{ Str::ucfirst(@$organizationAddress->line_2) }}
                                </span> <br>
                                {{ @$organizationAddress->landmark }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 15px;">Pin Code : </td>
                            <td style="padding-top: 15px; font-weight: 700;">{{ @$organizationAddress->postal_code }}</td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">City:</td>
                            <td style="padding-top: 3px;">
                                {{ @$organizationAddress->city->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">State:</td>
                            <td style="padding-top: 3px;">
                                {{ @$organizationAddress->state->name }}
                            </td>
                        </tr>
                        {{-- <tr>
                            <td style="padding-top: 3px;">GSTIN NO</td>
                            <td style="padding-top: 3px;"></td>
                        </tr>
                        <tr>
                            <td style="padding-top: 3px;">State Code:</td>
                            <td style="padding-top: 3px;">09</td>
                        </tr> --}}
                        <tr>
                            <td style="padding-top: 3px;">Country:</td>
                            <td style="padding-top: 3px;">
                                {{ @$organizationAddress->country->name }}
                            </td>
                        </tr>

                        {{-- <tr>
                            <td style="padding-top: 3px;">GSTIN NO:</td>
                            <td style="padding-top: 3px;">09AAACS0189B1ZM</td>
                        </tr>

                        <tr>
                            <td style="padding-top: 3px;">CIN NO:</td>
                            <td style="padding-top: 3px;">L74899DL1971PLC005679</td>
                        </tr> --}}

                        <tr>
                            <td style="padding-top: 3px;">PHONE:</td>
                            <td style="padding-top: 3px;">
                                {{ @$organizationAddress->mobile }}
                            </td>
                        </tr>

                        <tr>
                            <td style="padding-top: 3px;">EMAIL ID:</td>
                            <td style="padding-top: 3px;">
                                {{ @$organizationAddress->email }}
                            </td>
                        </tr>

                        {{-- <tr>
                            <td style="padding-top: 3px;">PAN NO. :</td>
                            <td style="padding-top: 3px;">AAACS0189B</td>
                        </tr> --}}

                    </table>
                </td>
                <td rowspan="2"
                    style="border: 1px solid #000; padding: 3px; border-left: none; vertical-align: top; width: 40%;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td colspan="2" style="font-weight: 900; vertical-align: top;">Supplier's Name & Address:
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-top: 3px;">
                                JPS PLASTICS PVT LTD <br>
                                SARAI ROAD
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 10px;">City: </td>
                            <td style="padding-top: 10px;">
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
                            <td style="padding-top: 3px;">Country</td>
                            <td style="padding-top: 3px;">
                                {{ @$shippingAddress->country->name }}
                            </td>
                        </tr>

                        <tr>
                            <td style="padding-top: 3px;">Pin code:</td>
                            <td style="padding-top: 3px;">
                                {{ @$shippingAddress->pincode }}
                            </td>
                        </tr>
                        {{-- <tr>
                            <td style="padding-top: 3px;">GSTIN NO:</td>
                            <td style="padding-top: 3px;">22AAWFK1657M1ZS</td>
                        </tr> --}}

                        <tr>
                            <td style="padding-top: 3px;">PHONE:</td>
                            <td style="padding-top: 3px;">
                                {{ @$mrn->vendor->mobile }}
                            </td>
                        </tr>

                        <tr>
                            <td style="padding-top: 3px;">EMAIL ID:</td>
                            <td style="padding-top: 3px;">
                                {{ @$mrn->vendor->email }}
                            </td>
                        </tr>

                        {{-- <tr>
                            <td style="padding-top: 3px;">PAN NO. :</td>
                            <td style="padding-top: 3px;">AAWFK1657M</td>
                        </tr> --}}

                    </table>
                </td>
                <td style="border: 1px solid #000; padding: 3px;float: right; border-left: none; vertical-align: top; width: 20%;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td><b>PO No:</b></td>
                            <td style="font-weight: 900;">
                                {{ @$mrn->document_number }}
                            </td>
                        </tr>
                        <tr>
                            <td><b>PO Date:</b></td>
                            @if($mrn->document_date)
                                <td style="font-weight: 900;">
                                    {{ date('d-M-y', strtotime($mrn->document_date)) }}
                                </td>
                            @endif
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td
                    style="border: 1px solid #000; padding: 3px; border-left: none; vertical-align: top; width: 35%; border-top: none;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>
                                <b style="font-weight: 900;">Status :-</b> 
                                @if($mrn->document_status == 'submitted')
                                    <span class="status" style="color: #17a2b8 ">
                                        {{ ucfirst($mrn->document_status) }}
                                    </span>
                                @elseif($mrn->document_status == 'draft')
                                    <span style="color: #6c757d">
                                        {{ ucfirst($mrn->document_status) }}
                                    </span>
                                @elseif($mrn->document_status == 'approved')
                                    <span style="color: #28a745">
                                        {{ ucfirst($mrn->document_status) }}
                                    </span>
                                @elseif($mrn->document_status == 'rejected')
                                    <span style="color: #dc3545">
                                        {{ ucfirst($mrn->document_status) }}
                                    </span>
                                @else 
                                    <span style="color: #007bff">
                                        {{ ucfirst($mrn->document_status) }}
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
                <td rowspan="2"
                    style="padding: 2px; border: 1px solid #000; border-top: none; background: #80808070; text-align: center; font-weight: bold;">
                    Sr.No</td>
                <td rowspan="2"
                    style="font-weight: bold; width: 150px; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    <div style="">Item</div>
                </td>
                <td rowspan="2"
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    HSN Code</td>
                <td colspan="4"
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; text-align: center; background: #80808070; text-align: center;">
                    Quantity</td>
                <td rowspan="2"
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Rate</td>
                <td rowspan="2"
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Total</td>
                <td rowspan="2" 
                    style="font-weight: bold; width: 120px; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Other Charges</td>
                <td rowspan="2"
                    style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Taxable Value</td>
            </tr>
            <tr>
                <td
                    style="padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Order Qty.</td>
                <td
                    style="padding: 2px; border: 1px solid #000; border-left: none; border-top: none; background: #80808070; text-align: center;">
                    Receipt Qty.</td>
                    <td
                    style="padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center;">
                    Accepted Qty.</td>
                <td
                    style="padding: 2px; border: 1px solid #000; border-left: none; border-top: none; background: #80808070; text-align: center;">
                    Rejected Qty.</td>
            </tr>
            @foreach($mrn->items as $key => $val)
                <tr>

                    <td
                        style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none;  text-align: center; border-bottom: none;">
                        {{ $key + 1 }}</td>
                    <td
                        style="vertical-align: top; width: 150px; padding: 3px; text-align:left; border: 1px solid #000; border-top: none; border-left: none; border-bottom: none;">
                        <b>Item Name :</b> {{ @$val->item_name }}<br/>
                        <b>Item Code :</b> {{ @$val->item_code }}<br/>
                        <b>Uom :</b> {{ucfirst(@$val->item->uom->name)}}<br/>
                        <b>Remark :</b> {{@$val->remarks}}

                    </td>
                    <td
                        style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none; border-bottom: none;  text-align: center;">
                        {{ @$val->item->hsn_code }}
                    </td>
                    <td
                        style=" vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none; border-bottom: none; text-align: center;  text-align: center;">
                        {{@$val->order_qty}}
                    </td>
                    <td
                        style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none; border-bottom: none;  text-align: center;">
                        {{@$val->receipt_qty}}
                    </td>
                    <td
                        style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none; border-bottom: none; text-align: center;">
                        {{@$val->accepted_qty}}
                    </td>
                    <td
                        style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none; border-bottom: none; text-align: center;">
                        {{@$val->rejected_qty}}
                    </td>
                    <td
                        style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none; border-bottom: none; text-align: center;">
                        {{@$val->rate}}
                    </td>
                    <td
                        style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none; border-bottom: none;  text-align: center;">
                        {{$val->basic_value}}
                    </td>
                    <td
                        style="vertical-align: top;width: 120px;  text-align:left; padding: 3px; border: 1px solid #000; border-top: none; border-left: none; border-bottom: none;">
                        <b>Item Disc. :</b> {{$val->discount_amount}}<br/>
                        <b>Header Disc. :</b> {{$val->header_discount_amount}}<br/>
                        <b>Taxes :</b> {{$val->tax_value}}
                    </td>
                    <td
                        style="vertical-align: top; padding: 3px; border: 1px solid #000; border-top: none; border-left: none; border-bottom: none;  text-align: center; text-align: center;">
                        {{$val->net_value}}
                    </td>
                </tr>
            @endforeach

        </table>

        <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
            <tr>
                <td style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td> <b>Total GRN Value (In Words)</b> <br>
                                {{ @$amountInWords }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 15px;"><b>Currency:</b> {{@$mrn->currency->name}} </td>
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
                    <table style="width: 100%; margin-bottom: 0px; margin-top: 10px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="text-align: right;">
                                <b>Total Value:</b>
                            </td>
                            <td style="text-align: right;">
                                {{ $totalItemValue }}
                            </td>
                        </tr>
                        {{-- <tr>
                            <td style="text-align: right; padding-top: 3px;">Freight/Other Charges:
                            </td>
                            <td style="text-align: right; padding-top: 3px;"></td>
                        </tr> --}}
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Item Discount:</b>
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ $totalItemDiscount }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Item Discount:</b>
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ $totalHeaderDiscount }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Total Taxes:</b>
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ $totalTaxes }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Taxable Value:</b>
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ $totalTaxableValue }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Total After Tax:</b>
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ $totalAfterTax }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Expense:</b>
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ $mrn->expense_amount ?? 0.00 }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Total GRN Value:</b>
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ $totalAmount }}
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
                                    {{$mrn->final_remarks}}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td
                    style="padding: 3px; border: 1px solid #000; width: 55%; border-top: none; border-right: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>Price Basis : </td>
                            <td>FOR GREATER NOIDA</td>
                        </tr>
                        <tr>
                            <td style="padding-top: 15px;">Delivery on or before :</td>
                            <td style="padding-top: 15px;"></td>
                        </tr>
                        <tr>
                            <td style="padding-top: 5px;">Mode of Transport :</td>
                            <td style="padding-top: 5px;"></td>
                        </tr>
                        <tr>
                            <td style="padding-top: 5px;">Payment Terms :</td>
                            <td style="padding-top: 5px;">
                                {{@$mrn->paymentTerm->name}}
                            </td>
                        </tr>
                    </table>

                </td>
                <td
                    style="padding: 3px; border: 1px solid #000; border-top: none; border-left: none; vertical-align: top; padding-left: 80px;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>Insurance :</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="padding-top: 5px; width: 80px; ">Pack Charges :
                            </td>
                            <td style="padding-top: 5px;"> INCLUDED
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!--  -->

            <tr>
                <td
                    style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; border-right: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td colspan="2">Instructions to suppliers :</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-top: 5px;">Please turn over for detailed Purchase Order Terms and
                                Conditions. </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 5px;">Created By :</td>
                            <td style="padding-top: 5px;">
                                {{@$mrn->createdBy->name}}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 5px;">Printed By :</td>
                            <td style="padding-top: 5px;">
                                {{@$mrn->createdBy->name}}
                            </td>
                        </tr>
                    </table>

                </td>
                <td
                    style="padding: 3px; border: 1px solid #000; border-top: none; border-left: none; vertical-align: bottom;">
                    <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="text-align: center; padding-bottom: 20px;">FOR SHEELA FOAM LTD </td>
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
                    Regd. Office:604 Ashadeep,9 Hailey Road,New Delhi 110001 <br>
                    Principal office: Plot No 14, Sector 135 Noida Expressway, Noida -201305
                </td>
            </tr>

        </table>

        <div style="page-break-before:always"></div>


        <!-- Third page Forth page -->

        <table style="width: 100%; margin-bottom: 0px; margin-top: 10px; font-size: 13px;" cellspacing="0"
            cellpadding="0">
            <tr>
                <td colspan="2"
                    style="text-align: center; font-weight: bold; text-decoration: underline; font-size: 14px; padding: 5px; padding-bottom: 10px;">
                    TERMS AND CONDITIONS FOR "PURCHASE ORDER-GOODS"</td>
            </tr>
            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">1.</td>
                <td style="line-height: 18px; padding: 5px 0px;"> <b>Acceptance:</b> Any of the following acts by Vendor
                    shall constitute acceptance of this Order and all of its terms and
                    2. Price: Vendor shall furnish the item stated on this Order in accord with the Price, delivery and
                    terms stated on its face.
                    TERMS AND CONDITIONS FOR "PURCHASE ORDER-GOODS"
                    conditions : (i) signing and returning a copy of this Order, (ii) delivery of any items ordered. Any
                    term of condition stated
                    by the Vendor shall not be binding of the Buyer unless specifically accepted in writing.</td>
            </tr>
            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">2.</td>
                <td style="line-height: 18px; padding: 5px 0px;"><b>Price:</b>Vendor shall furnish the item stated on
                    this Order in accord with the Price, delivery and terms stated on its face.
                    All prices & all applicable taxes required by law to be paid by Seller </td>
            </tr>
            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">3.</td>
                <td style="line-height: 18px; padding: 5px 0px;"><b>Quantities & Qualities:</b> The quantity must be
                    delivered according to the ordered quantiity. Partial shipment allowed.
                    Any unauthorised quantity is subject to rejection and return at Vendor's expense.
                    Qualities must be maintained as per prevailing market standards</td>
            </tr>
            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">4.</td>
                <td style="line-height: 18px; padding: 5px 0px;">All goods shall be received subject to Buyer's (The
                    Company's) right of inspection and Rejection. Defective goods or
                    goods not in accordance with Buyer's specifications will be held for Vendor, at Vendor's risk and if
                    Vendor so directs,
                    will be returned at Vendor's expense.</td>
            </tr>

            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">5.</td>
                <td style="line-height: 18px; padding: 5px 0px;"><b>Warranty:</b>

                    quality; conform to applicable specifications, descriptions furnished by Buyer; will be free from
                    defects in material and
                    Vendor warrants that the merchandise sold by the Vendor to Buyer hereunder will be of merchantable
                    workmanship; and will be sufficient and fit for the purposes intended by Buyer. Buyer's approval of
                    design furnished
                    by Vendor shall not relieve Vendor or its obligations under this paragraph. The warranties of
                    Vendor, together with its
                    service guarantees, shall run to Buyer and its divisions subsidiaries and affiliates.
                </td>
            </tr>

            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">6.</td>
                <td style="line-height: 18px; padding: 5px 0px;"><b>Cancellation:</b>
                    Buyer reserves the right to cancel all or any part of the undelivered portion of this Order. if
                    Vendor
                    does not make deliveries as specified , time being of the essence of this contract, of if Vendor
                    breaches any of the
                    terms thereof, including, with limitation, the warranties of Vendor.
                </td>
            </tr>

            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">7.</td>
                <td style="line-height: 18px; padding: 5px 0px;"><b>Delay in Delivery:</b>
                    Vendor will not be liable for delays in delivery due to force majuere. However, in such event Buyer
                    at is option, may either approve a revised delivery schedule or terminate the Order either in whole
                    or in part without
                    liability. The rate of liquidated damages for late delivery shall be 0.5% of total purchase price
                    for every week of delay in
                    delivery or part thereof.

                </td>
            </tr>

            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">8.</td>
                <td style="line-height: 18px; padding: 5px 0px;"><b>Indemnification:</b>
                    Vendor shall be responsible and shall indemnity Buyer against any and all losses, claims or actions
                    for personal injury or property damage caused by items furnished or services performed by Vendor
                    pursuant to this Order.
                </td>
            </tr>

            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">9.</td>
                <td style="line-height: 18px; padding: 5px 0px;"><b>Applicable Laws:</b>
                    Vendor warrants that the merchandise covered by this Order was not manufactured , sold or
                    priced in violation of any applicable law
                </td>
            </tr>

            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">10.</td>
                <td style="line-height: 18px; padding: 5px 0px;"><b>Documents required along with consignment</b> <br>
                    <b>Tax Invoice (GSTIN) :</b>
                    <p style="margin: 0px;">(A) Material Code, Material description, Prices and tax calculation must be
                        matching with purchase Order. </p>
                    <p style="margin: 0px;">(B) You should raise Tax Invoice against our single PO only and not clubbed
                        multiple P.O in one Invoice. </p>
                    <p style="margin: 0px;">(C) Tax Invoice (GSTIN) should be as per norms/rules specified by Govt of
                        india under GST invoice Rules 2017.</p>
                    <p style="margin: 0px;">(D) HSN code of goods should be metioned in Tax invoice(GSTIN) </p>
                </td>
            </tr>

            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">11.</td>
                <td style="line-height: 18px; padding: 5px 0px;">The vendor shall issue credit notes towards any
                    adjustments to the value and the credit note shall also indicate the
                    appropriate GST amounts and the corresponding invoice numbers to which the said credit note is
                    attributable to. In case
                    GST rate is more than existing rate, vendor shall reimburse excess amount of GST charged in the
                    credit note. Further,
                    any reduction either on account of reduced GST rates or on account of availability of GST credits,
                    shall be passed
                    on to SFL and SFL reserves its rights to seek any information that may be appropriate to meet this
                    purpose. </td>
            </tr>

            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">12.</td>
                <td style="line-height: 18px; padding: 5px 0px;">Your statutory registration nos, email and contact
                    details as available in our system are mentioned on PO. If you
                    want it to be corrected or updated, please contact immediately after receipt of the PO, to the
                    concerned purchase
                    officer, Sheela Foam Limited, at the address mentioned on PO along with self-attested copy of
                    PAN/Registration
                    certificate.</td>
            </tr>

            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">13.</td>
                <td style="line-height: 18px; padding: 5px 0px;"><b>Risk Purchase:</b>
                    In the event of your failure or delay to execute the order completely or partly at any point of time
                    during execution of the contract / order, or in the event of rejection of your material at our site,
                    Sheela Foam Limited
                    shall have the right to make an alternate arrangement, which interalia may involve purchase of
                    entire / remaining
                    materials from other source to complete the supply at your risk and cost without assigning any
                    reason and the
                    additional liabilities shall be borne by you.
                </td>
            </tr>

            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">14.</td>
                <td style="line-height: 18px; padding: 5px 0px;">You will indemnify, defend and hold SFL, its
                    affiliates, officers, directors, employees, representative and agents
                    harmless from and against all claims, actions, liabilities, damages, losses, fines, penalties, suits
                    at law or in equity, costs and
                    expenses (including attorney's fees, and all court awards) arising out of or related to: (1) breach
                    of any of its representations
                    or warranties set forth in PO; or (2) Negligence in the performance of obligations mentioned in PO.
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">15.</td>
                <td style="line-height: 18px; padding: 5px 0px;">
                    You will not disclose any confidential information to the third party without consent of SFL.
                    Confidential information
                    shall mean and include all information passed from SFL to the Supplier in manufacturing the product,
                    which Supplier
                    is in need of, including but not limited to product, prototype, Sketches, Diagrams, Principles,
                    apparatus, Designs, Designates,
                    Software both source and object Code, Product Scheme, drawings, descriptive materials,
                    specifications, and other
                    information, which also includes, Product, Cost price fixed to product, Specification, Product code,
                    Canvassing in any form with
                    name of SFL and other related ancillary information.
                </td>
            </tr>

            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">16.</td>
                <td style="line-height: 18px; padding: 5px 0px;">
                    You will not supply confidential material to third party without consent of SFL. Confidential
                    material shall mean and
                    include all tangible materials containing Confidential information, from SFL to Supplier including
                    without limitation, drawings,
                    schematics, written or printed documents, Computer hard disks, tapes and CDs whether or not user
                    readable.
                </td>
            </tr>

            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">17.</td>
                <td style="line-height: 18px; padding: 5px 0px;">
                    Supplier shall not use SFL name and logo or their status as Supplier to SFL for promoting their
                    products in print and
                    electronic media without written permission of SFL.
                </td>
            </tr>

            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">18.</td>
                <td style="line-height: 18px; padding: 5px 0px;">
                    Supplier shall ensure that PO shall not be reproduced to any third party in any form without prior
                    written approval
                    of SFL. The PO shall not be used to promote activities / canvassing business or advertised in the
                    media or forwarded
                    to SFL clientele.
                </td>
            </tr>

            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">19.</td>
                <td style="line-height: 18px; padding: 5px 0px;"><b>Packing & Shipping:</b>
                    Vendor shall not charge for packing , storage or transportation to FOB point. Goods shall be
                    packed , marked and prepared in accord with good comercial practices and marked and abeled as
                    required by
                    applicable laws and regulations. Itemized packing list must accompany each shipment.
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">20.</td>
                <td style="line-height: 18px; padding: 5px 0px;">
                    All suits, claims and other matters arising out of or relating to this Purchase Order may be brought
                    only in the Delhi
                    Jurisdiction courts located in Delhi ,India.
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">21.</td>
                <td style="line-height: 18px; padding: 5px 0px;">
                    Supplier shall control worker exposure to all types of potential safety hazards through proper
                    controls including
                    usage of PPEs.
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top; width: 24px; font-weight: bold; padding: 5px 0px;">22.</td>
                <td style="line-height: 18px; padding: 5px 0px;">
                    Supplier shall adhere to all government laws, rules and regulations with respect to the Environment,
                    Health and
                    Safety as may be applicable from time to time.
                </td>
            </tr>



        </table>

        <div style="page-break-before:always"></div>
        <!-- Fifth page -->

        <table style="width: 100%; margin-bottom: 0px; margin-top: 15px; font-size: 13px;" cellspacing="0"
            cellpadding="0">
            <tr>
                <td colspan="2" style="padding: 8px 5px;">Date:</td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 5px 5px; padding-top: 40px;">To</td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 0px 5px; line-height: 18px;">SHEELA FOAM LTD UNIT-VI (GNA) <br>
                    PLOT NO 51-A, UDYOG VIHAR , GREATER NOIDA, G.B NAGAR
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 2px 5px; padding-top: 20px;">UTTAR PRADESH</td>
            </tr>
            <tr>
                <td style="width: 45px; padding: 2px 5px;">Phone : </td>
                <td style="padding: 2px 5px;">0120-2569291-93</td>
            </tr>
            <tr>
                <td style="width: 45px; padding: 2px 5px;">E-Mail :</td>
                <td style="padding: 2px 5px;"></td>
            </tr>

            <tr>
                <td colspan="2"
                    style="padding: 8px 5px; padding-top: 50px; font-weight: bold; font-size: 15px; text-decoration: underline;">
                    Sub.: Quality Assurance Certificate. </td>
            </tr>

            <tr>
                <td colspan="2" style="padding: 8px 5px; line-height: 25px;">
                    We hereby certify that the goods manufactured / supplied by us against purchase order No. 13
                    dated : 05-04-2024 do conform to specifications / standard mention in the purchase order.
                    of M/s. Sheela Foam Ltd and our Invoice No. <span
                        style="display: inline-block; min-width: 150px; border-bottom: 1px dotted #000;"> </span>

                    dated : <span style="display: inline-block; min-width: 100px; border-bottom: 1px dotted #000;">
                    </span>, do conform to specifications / standard mention in the purchase order.
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 8px 5px;">We assure for the quality of goods supplied as above.</td>
            </tr>

            <tr>
                <td colspan="2" style="padding: 8px 5px; padding-top: 30px;">For JPS PLASTICS PVT LTD</td>
            </tr>

            <tr>
                <td colspan="2" style="padding: 8px 5px; padding-top: 30px;">AUTHORISED SIGNATORY</td>
            </tr>
        </table>

        <table style="width: 100%; margin-bottom: 0px; margin-top: 15px; font-size: 13px;" cellspacing="0" cellpadding="0">
            <tr>
                <td style="padding: 8px 5px; width: 182px; font-weight: bold; font-size: 13px; vertical-align: top;">IMPORTANT INSTRUCTION-: </td>
                <td style="padding: 8px 5px; font-weight: bold; font-size: 13px;" >Please esnure that latest PDIR format with latest revision number is being filled
                    at your end and sent along with each invoice sent to The Company. For any query
                    regarding same, contact Purchase department.</td>
            </tr>
        </table>

        <div style="page-break-before:always"></div>
        <!-- Six page -->

        <table style="width: 100%; margin-bottom: 0px; margin-top: 15px; font-size: 13px;" cellspacing="0" cellpadding="0">
            <tr>
                <td colspan="2" style="padding: 5px 0px; font-weight: bold; text-decoration: underline; font-size: 16px; text-align: center;">SAFETY INSTRUCTIONS</td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 5px 0px; padding-top: 15px;">Following items are strictly prohibited to be brought / used in our factory premises:</td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">1).</td>
                <td style="padding: 5px 0px;">BIDDI, Cigarette. </td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">2).</td>
                <td style="padding: 5px 0px;">Tobacco or any other intoxicant in any form. </td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">3).</td>
                <td style="padding: 5px 0px;">Gutka, Pan Masala.</td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">4).</td>
                <td style="padding: 5px 0px;">Match Sticks or Match Box (Filled or Empty).</td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">5).</td>
                <td style="padding: 5px 0px;">Lighters.</td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">6).</td>
                <td style="padding: 5px 0px;">Alcohal in any form.</td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 5px 0px;">Upon not following above instructions, The Company shall be at liberty to impose the penalties which may please be noted as under:
                </td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">A).</td>
                <td style="padding: 5px 0px;">
                    Penalty of Rs. 250/- (Rupees Two Hundred and Fifty Only) shall be imposed if a pack / bundle or a part of
                    BIDDI or Cigarette is found. This penalty shall multiply with additional packs / bundle.
                </td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">B).</td>
                <td style="padding: 5px 0px;">
                    Penalty of Rs. 250/- (Rupees Two Hundred and Fifty Only) per pouch (Open or Sealed) shall be imposed if a
                    Chewing Tobacco or any other intoxicant in any form is found.
                </td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">C).</td>
                <td style="padding: 5px 0px;">
                    Penalty of Rs. 250/- (Rupees Two Hundred and Fifty Only) per pouch (Open or Sealed) of Gutka, Pan
                    Masala shall be imposed.
                </td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">D).</td>
                <td style="padding: 5px 0px;">
                    Penalty of Rs. 1500/- (Rupees One Thousand Five Hundred Only) shall be imposed if a Match Box (Empty or
                    ttract a penalty of Rs. 1500/- (Rupees One Thousand Five Hundred Only).
                </td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">E).</td>
                <td style="padding: 5px 0px;">
                    Penalty of Rs. 1000/- (Rupees One Thousand Only) shall be imposed on bringing Alcohal inside our factory.

                </td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">F).</td>
                <td style="padding: 5px 0px;">
                    Damage caused to any property inside The Company shall be fully recoverable and the cost of damage shall
                    be acertained by The Company.
                </td>
            </tr>
            <tr>
                <td style="padding: 5px 0px; padding-left: 20px; width: 25px;">G).</td>
                <td style="padding: 5px 0px;">Any other instructions which are given by our Security at entry has to be followed to avoid penalty which is
                    not specifically mentioned above.</td>
            </tr>

            <tr>
                <td colspan="2" style="padding: 5px 0px; padding-top: 20px;"> Repeat offenders shall be considered for black-listing.</td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 5px 0px; padding-top: 20px;">
                    It shall be your responsibility to instruct and make sure that the driver / representative of your vehicle deposit
                    above items if he / she is carrying at our Security. These items shall be returned on vehicle exit from our
                    premises. We shall directly hold you responsible for any lapse on transporters' end on account of above Safety
                    Instructions.
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 5px 0px; padding-top: 20px;">We request your co-operation in the matter.</td>
            </tr>

        </table>

    </div>
</body>

</html>
