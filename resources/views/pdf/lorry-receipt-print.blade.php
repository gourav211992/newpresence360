<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Docment</title>
</head>
<body>
    <div style=" width:700px; padding: 10px; font-family:Arial;">
         
        <div style="padding: 5px; border: 1px solid #7a48cb; border-radius: 5px; margin-top: 10px;">

            <table style="width: 100%; font-size: 13px; margin-bottom: 10px; padding-top: 20px;" cellspacing="0" cellpadding="0">
                <tr>
                    <td>
                          <table style="width: 100%; font-size: 13px; margin-bottom: 10px; padding-top: 20px;" cellspacing="0" cellpadding="0">
                              <tr>
                    <td style="text-align: center; font-weight: 600; font-size: 18px; text-transform: uppercase;">{{ @$organization->name ?? 'Gulati Roadlines' }}</td>
                </tr>
                <!-- <tr>
                    <td style="text-align: center; text-transform: uppercase; font-size: 14px; font-weight: 600; padding: 5px 0px;">Feet Owner And transport contractors</td>
                </tr> -->
                <tr>
                    <td style="text-align: center; text-transform: uppercase; font-weight: 600;  line-height: 18px;">
                           {{ Str::ucfirst(@$organizationAddress->line_1) }} {{ Str::ucfirst(@$organizationAddress->line_2) }}
                                </span> <br>
                                {{ @$organizationAddress->landmark }}
                </tr>
                 <tr>
                    <td style="text-align: center; font-weight: 600;  line-height: 18px;">
                        ph: {{ @$organizationAddress->mobile }}, email:{{ @$organizationAddress->email }}
                    </td>
                </tr>
                          </table>
                    </td>
                    <td>
                         @if (isset($orgLogo) && $orgLogo)
                    <img src="{!! $orgLogo !!}" alt="" height="50px" />
                    @endif
                    </td>
                </tr>
            </table>
    
            <table style="width: 100%; font-size: 13px; margin-bottom: 10px; padding-top: 10px;" cellspacing="0" cellpadding="0">
                <!-- <tr>
                    <td style=" padding: 5px 5px; font-weight: bold;">
                        Trip No. 
                   </td>
                    <td style=" padding: 5px 5px;">
                         {{ @$lorryReceipt->trip_no ?? '' }}
                    </td>
                </tr> -->
    
                 <tr>
                    <td style=" padding: 5px 5px; font-weight: bold;">
                        LR No.
                   </td>
                    <td style=" padding: 5px 5px;">
                       {{ @$lorryReceipt->document_number }}
                    </td>
    
                    <td style=" padding: 5px 5px; font-weight: bold;">
                        Vehicle No.
                    </td>
                    <td style=" padding: 5px 5px;">
                        {{ @$lorryReceipt->vehicle->lorry_no ?? '' }}
                    </td>
                </tr>
    
                 <tr>
                    <td style=" padding: 5px 5px; font-weight: bold;">
                       Date
                   </td>
                    <td style=" padding: 5px 5px;">
                       {{ date('d-M-y', strtotime($lorryReceipt->document_date)) }}
                    </td>
                    
                </tr>
    
                 <tr>
                    <td style=" padding: 5px 5px; font-weight: bold;">
                       Consignor
                   </td>
                    <td style=" padding: 5px 5px;">
                        {{ @$lorryReceipt->consignor->company_name ?? '' }}
                    </td>
                      <td style=" padding: 5px 5px; font-weight: bold;">
                        Consignee
                    </td>
                    <td style=" padding: 5px 5px;">
                       {{ @$lorryReceipt->consignee->company_name ?? '' }}
                    </td> 
                </tr>
    
                <tr>
                     <td style=" padding: 5px 5px; font-weight: bold;">
                        Address
                    </td>
                    <td style=" padding: 5px 5px;">
                     {{ @$lorryReceipt->consignor->addresses->first()?->display_address ?? '' }}
                    </td> 
                    <td style=" padding: 5px 5px; font-weight: bold;">
                        Address
                    </td>
                    <td style=" padding: 5px 5px;">
                      {{ @$lorryReceipt->consignee->addresses->first()?->display_address ?? '' }}
                    </td>
                </tr>
    
                 <tr>
                     <td style=" padding: 5px 5px; font-weight: bold;">
                        From
                    </td>
                    <td style=" padding: 5px 5px;">
                       {{ @$lorryReceipt->source->name ?? '' }}
                    </td> 
                    <td style=" padding: 5px 5px; font-weight: bold;">
                        To
                    </td>
                    <td style=" padding: 5px 5px;">
                       {{ @$lorryReceipt->destination->name ?? '' }}
                    </td>
                </tr>
    
                 <tr>
                     <td style=" padding: 5px 5px; font-weight: bold;">
                        GST No.:
                    </td>
                    <td style=" padding: 5px 5px;">
                       {{ @$lorryReceipt->consignor->compliances->gstin_no ?? '' }} 
                    </td> 
                    <td style=" padding: 5px 5px; font-weight: bold;">
                        GST No.:
                    </td>
                    <td style=" padding: 5px 5px;">
                    {{ @$lorryReceipt->consignee->compliances->gstin_no ?? '' }}
                    </td>
                </tr>
    
                <tr>
                     <td style=" padding: 5px 5px; font-weight: bold;">
                        PAN No.
                    </td>
                    <td style=" padding: 5px 5px;">
                       {{ @$lorryReceipt->consignor->pan_number ?? '' }} 
                    </td> 
                    <td style=" padding: 5px 5px; font-weight: bold;">
                        PAN No.
                    </td>
                    <td style=" padding: 5px 5px;">
                    {{ @$lorryReceipt->consignee->pan_number ?? '' }}
                    </td>
                </tr>

            </table>
    
            <table style="width: 100%; font-size: 13px; margin-bottom: 10px; padding-top: 10px;" cellspacing="0" cellpadding="0">
                <tr>
                    <th style="background: #d1d1d1; padding: 15px 5px; text-align: left; border-top-left-radius: 5px; border-bottom-left-radius: 5px; ">No. of pkgs </th>
                    <th style="background: #d1d1d1; padding: 15px 5px; text-align: left;">Said Contents</th>
                    <th style="background: #d1d1d1; padding: 15px 5px; text-align: left;">Qty</th>
                    <th style="background: #d1d1d1; padding: 15px 5px; text-align: left;">Weight(kg)</th>
                    <th style="background: #d1d1d1; padding: 15px 5px; text-align: right; border-top-right-radius: 5px; border-bottom-right-radius: 5px;">Remarks</th>
                </tr>
               @php
                    $totalPkgs = 0;
                    $totalWeight = 0;
                @endphp

                @foreach ($lorryReceipt->locations as $location)
                @php
                        $totalPkgs += $location->no_of_articles ;
                        $totalWeight += $location->weight ;
                    @endphp
                @endforeach
                    <tr>
                        <td style="padding: 15px 5px; border-bottom: 1px solid #d6d6d6;">{{ ($totalPkgs + $lorryReceipt->no_of_bundles) ?? '' }}</td>
                        <td style="padding: 15px 5px; border-bottom: 1px solid #d6d6d6;">---</td>
                        <td style="padding: 15px 5px; border-bottom: 1px solid #d6d6d6;">{{ ($totalPkgs + $lorryReceipt->no_of_bundles) ?? '' }}</td>
                        <td style="padding: 15px 5px; border-bottom: 1px solid #d6d6d6;">{{ ($totalWeight + + $lorryReceipt->weight) ?? '' }}</td>
                        <td style="text-align: right; border-bottom: 1px solid #d6d6d6; padding: 15px 5px;">
                           
                            @if($lorryReceipt->billing_type == 'To Pay')
                            {{ @$lorryReceipt->billing_type }}<br>
                            <span>(To be paid by Consignee)</span>
                            @else
                           {{ @$lorryReceipt->billing_type }}<br>
                            <span>(To Be paid By Consignor)</span>
                            @endif
                        </td>
                    </tr>

                    

                <tr>
                    <td style="padding: 15px 5px; border-bottom: 1px solid #d6d6d6;" colspan="4">
                        <span style="font-weight: bold;">GST paid by:</span> {{ @$lorryReceipt->gst_paid_by }} 
                        <p style="margin: 0px; padding-top: 5px;">We take no responsiblity of any damage brakage or leakage of material in transit. </p>
                    </td>
                    <td style="padding: 15px 5px; text-align: right; border-bottom: 1px solid #d6d6d6;">
                        <span style="font-weight: bold;">Receipt for:</span> Consignee 
                        <!-- <p style="text-transform: uppercase; font-weight: bold; margin: 0px; padding-top: 5px;">For Gulaty Roadlines </p> -->
                    </td>
                </tr>
                <tr>
                    <td colspan="5" style="padding: 15px 5px; border-bottom: 1px solid #d6d6d6;">
                        Multiple Pickup Drop Location Details 
    
                         <table style="width: 100%; font-size: 12px; margin-bottom: 10px; padding-top: 20px;" cellspacing="0" cellpadding="0">
    <tr>
        <!-- FROM POINT -->
        <td style="position: relative; vertical-align: bottom; text-align: center;">
            <img src="{{ @$locationPathFirst }}" height="21px" style="margin-bottom: -3px;" alt="">
            <span style="display: block; margin: 5px auto; background: #b8e9be; border: 1px solid #11b722; width: 13px; height: 13px; border-radius: 50%;">
                <span style="width: 7px; height: 7px; margin: 3px auto 0; background: #11b722; border-radius: 50%; display: block;"></span>
            </span>
        </td>

        <!-- INTERMEDIATE LOCATION POINTS -->
        @foreach ($lorryReceipt->locations as $location)
            <td style="position: relative; vertical-align: bottom; text-align: center;">
                <span style="display: block; background: #d5bdea; border: 1px solid #6a11b7; width: 16px; height: 16px; border-radius: 50%; margin: auto;">
                    <span style="width: 10px; height: 10px; margin: 3px auto 0; background: #6a11b7; border-radius: 50%; display: block;"></span>
                </span>
                <span style="display: block; width: 100%; background: #6a11b7; height: 4px; border-radius: 9px; margin-top: 5px;"></span>
            </td>
        @endforeach

        <!-- TO POINT -->
        <td style="position: relative; vertical-align: bottom; text-align: center;">
            <img src="{{ @$locationPathSecond }}" height="23px" style="margin-bottom: -3px;" alt="">
            <span style="display: block; margin: 5px auto; background: #ffd5d5; border: 1px solid #ff0000; width: 13px; height: 13px; border-radius: 50%;">
                <span style="width: 7px; height: 7px; margin: 3px auto 0; background: #ff0000; border-radius: 50%; display: block;"></span>
            </span>
        </td>
    </tr>

    <tr>
        <!-- FROM LABEL -->
        <td style="vertical-align: top; padding-top: 5px; font-size: 11px; text-align: center;">
            <strong>From:</strong><br>{{ $lorryReceipt->source->name ?? '' }}
        </td>

        <!-- INTERMEDIATE LOCATIONS DETAILS -->
        @foreach ($lorryReceipt->locations as $location)
            <td style="padding-top: 5px; vertical-align: top; font-size: 11px;">
                <div style="margin-bottom: 10px; border-bottom: 1px dashed #ccc; padding-bottom: 5px;">
                    <p style="margin: 0;"><strong>{{ strtoupper($location->route->name ?? 'N/A') }}</strong></p>
                    <p style="margin: 0;"><strong>{{ $location->type ?? ' ' }} Freight:</strong> Rs. {{ $location->amount ?? '0' }}/-</p>
                    <p style="margin: 0;"><strong>No. of Articles:</strong> {{ $location->no_of_articles ?? '0' }}/-</p>
                </div>
            </td>
        @endforeach

        <!-- TO LABEL -->
        <td style="padding-top: 5px; vertical-align: top; font-size: 11px; text-align: center;">
            <strong>To:</strong><br>{{ $lorryReceipt->destination->name ?? '' }}
        </td>
    </tr>
</table>

                    </td>
    
                </tr>
                <tr>
                    <td colspan="2" style="padding: 15px 5px; border-bottom: 1px solid #d6d6d6;">
                        <span style="font-weight: bold;">Freight from</span> {{ @$lorryReceipt->source->name ?? '' }} <span style="font-weight: bold;">to</span> {{ @$lorryReceipt->destination->name ?? '' }}:Rs {{ @$lorryReceipt->freight_charges ?? '' }}
                    </td>
                      <td colspan="2" style="padding: 15px 5px; border-bottom: 1px solid #d6d6d6;">
                          <span style="font-weight: bold;">LR charges</span>:Rs {{ @$lorryReceipt->lr_charges ?? '' }}
                      </td>
                      <td style="padding: 15px 5px; border-bottom: 1px solid #d6d6d6;">
                          <span style="font-weight: bold;">Total Freight</span>:Rs {{ number_format((@$lorryReceipt->lr_charges ?? 0) + (@$lorryReceipt->freight_charges ?? 0), 2) }}
                      </td>
                </tr>
                <tr>
                    <td  style="padding: 15px 5px;">
                        <span style="font-weight: bold;">Customer Name: {{ @$lorryReceipt->consignor->company_name ?? '' }}</span>
                    </td>
                      <td colspan="3" style="padding: 15px 5px;">
                          <span style="font-weight: bold;">Driver Name: {{ @$lorryReceipt->driver->name ?? '' }}</span>
                      </td>
                      <td style="padding: 15px 5px;">
                          <span style="font-weight: bold;">Signature:</span>
                      </td>
                </tr>
                <!-- <tr>
                    <td colspan="5" style="padding: 15px 5px; text-align: center; font-weight: bold;">
                        The Gulati  Group...Velocity Redefined 
                        <p style="margin: 0px; padding-top: 5px;">Visit us at:www.gulatiroadways.com</p>
                        <p style="margin: 0px; padding-top: 5px;">A Product By Staqo. </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding-top: 5px;">
                        This is copy Receipt
                    </td>
                </tr> -->
            </table>
        </div>
       
    </div>
    
</body>
</html>