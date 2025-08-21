<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div style=" width:700px; padding: 10px; font-family:Arial;">
         
        <div style=" border: 1px solid #7a48cb; border-radius: 5px; margin-top: 10px;">

            <table style="width: 100%; font-size: 13px; margin-bottom: 10px; padding-top: 20px; border-bottom: 1px solid #787878;" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="width: 75%;">
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
                                       Ph: {{ @$organizationAddress->mobile }}, Email: {{ @$organizationAddress->email }}
                                    </td>
                                </tr>
                        </table>
                    </td>
                    <td style="width: 25%;">
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
                    <td style=" padding: 5px 5px; font-weight: bold; width: 15%; padding-left: 15px;">
                        LR No.
                   </td>
                    <td style=" padding: 5px 5px; width: 30%; border-right: 0.5px solid #787878;">
                       {{ @$lorryReceipt->document_number }}
                         
                    </td>
    
                    <td style=" padding: 5px 5px; font-weight: bold; width: 15%; padding-left: 15px;">
                        Vehicle No.
                    </td>
                    <td style=" padding: 5px 5px; width: 35%;">
                        {{ @$lorryReceipt->vehicle->lorry_no ?? '' }} 
                    </td>
                </tr>
    
                 <tr>
                    <td style=" padding: 5px 5px; font-weight: bold; padding-left: 15px;">
                       Date
                   </td>
                    <td style=" padding: 5px 5px; border-right: 0.5px solid #787878;">
                      
                       {{ date('d-M-y', strtotime($lorryReceipt->document_date)) }}
                    </td>
                    
                </tr>
    
                 <tr>
                    <td style=" padding: 5px 5px; font-weight: bold; padding-left: 15px;">
                       Consignor
                   </td>
                    <td style=" padding: 5px 5px; border-right: 0.5px solid #787878;">
                        {{ @$lorryReceipt->consignor->company_name ?? '' }}
                          
                    </td>
                      <td style=" padding: 5px 5px; font-weight: bold; padding-left: 15px; vertical-align: top;">
                        Consignee
                    </td>
                    <td style=" padding: 5px 5px;">
                       {{ @$lorryReceipt->consignee->company_name ?? '' }}
                         
                    </td> 
                </tr>
    
                <tr>
                     <td style=" padding: 5px 5px; font-weight: bold; vertical-align: top; padding-left: 15px;">
                        Address
                    </td>
                    <td style=" padding: 5px 5px; border-right: 0.5px solid #787878;">
                     {{ @$lorryReceipt->consignor->addresses->first()?->display_address ?? '' }}
                      

                    </td> 
                    <td style=" padding: 5px 5px; font-weight: bold; padding-left: 15px; vertical-align: top;">
                        Address
                    </td>
                    <td style=" padding: 5px 5px; vertical-align: top;">
                      {{ @$lorryReceipt->consignee->addresses->first()?->display_address ?? '' }}
                       
                    </td>
                </tr>
    
                 <tr>
                     <td style=" padding: 5px 5px; font-weight: bold; padding-left: 15px;">
                        From
                    </td>
                    <td style=" padding: 5px 5px; border-right: 0.5px solid #787878;">
                       {{ @$lorryReceipt->source->name ?? '' }}
                       
                    </td> 
                    <td style=" padding: 5px 5px; font-weight: bold; padding-left: 15px;">
                        To
                    </td>
                    <td style=" padding: 5px 5px;">
                       {{ @$lorryReceipt->destination->name ?? '' }}
                        
                    </td>
                </tr>
    
                 <tr>
                     <td style=" padding: 5px 5px; font-weight: bold; padding-left: 15px;">
                        GST No.:
                    </td>
                    <td style=" padding: 5px 5px; border-right: 0.5px solid #787878;">
                       {{ @$lorryReceipt->consignor->compliances->gstin_no ?? '' }} 
                       
                    </td> 
                    <td style=" padding: 5px 5px; font-weight: bold; padding-left: 15px;">
                        GST No.:
                    </td>
                    <td style=" padding: 5px 5px;">
                    {{ @$lorryReceipt->consignee->compliances->gstin_no ?? '' }}
                    </td>
                </tr>
    
                <tr>
                     <td style=" padding: 5px 5px; font-weight: bold; padding-left: 15px;">
                        PAN No.
                    </td>
                    <td style=" padding: 5px 5px; border-right: 0.5px solid #787878;">
                       {{ @$lorryReceipt->consignor->pan_number ?? '' }} 
                    </td> 
                    <td style=" padding: 5px 5px; font-weight: bold; padding-left: 15px; ">
                        PAN No.
                    </td>
                    <td style=" padding: 5px 5px;">
                    {{ @$lorryReceipt->consignee->pan_number ?? '' }}
                    </td>
                </tr>

            </table>
    
            <table style="width: 100%; font-size: 13px; padding-top: 10px;" cellspacing="0" cellpadding="0">
                <tr>
                    <th style="background: #d1d1d1; padding: 8px 8px; text-align: left;">No. of pkgs </th>
                    <th style="background: #d1d1d1; padding: 8px 8px; text-align: left; width: 100px;">Said Contents</th>
                    <th style="background: #d1d1d1; padding: 8px 8px; text-align: left; width: 60px;">Qty</th>
                    <th style="background: #d1d1d1; padding: 8px 8px; text-align: left;">Weight(kg)</th>
                    <th style="background: #d1d1d1; text-align: left; padding: 8px 8px;">Remarks</th>
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
                        <td style="padding: 15px 5px; border-bottom: 1px solid #d6d6d6;">
                            {{ ($totalPkgs + $lorryReceipt->no_of_bundles) ?? '' }}
                              
                        </td>
                        <td style="padding: 15px 5px; border-bottom: 1px solid #d6d6d6;">---</td>
                        <td style="padding: 15px 5px; border-bottom: 1px solid #d6d6d6;">
                            {{ ($totalPkgs + $lorryReceipt->no_of_bundles) ?? '' }}
                            
                        </td>
                        <td style="padding: 15px 5px; border-bottom: 1px solid #d6d6d6;">
                            {{ ($totalWeight + + $lorryReceipt->weight) ?? '' }}
                              
                        </td>
                        <td style="border-bottom: 1px solid #d6d6d6; padding: 15px 5px;">

                            @if($lorryReceipt->billing_type == 'To Pay')
                            {{ @$lorryReceipt->billing_type }}<br>
                            <span>(To be paid by Consignee)</span>
                            @else
                           {{ @$lorryReceipt->billing_type }}
                            <span>(To Be paid By Consignor)</span>
                            @endif
                        </td>
                    </tr>

                    

                <tr>
                    <td style="padding: 15px 5px; border-bottom: 1px solid #d6d6d6;" colspan="4">
                        <span style="font-weight: bold;">GST paid by:</span>  
                         {{ @$lorryReceipt->gst_paid_by }} 
                        <p style="margin: 0px; padding-top: 5px;">We take no responsiblity of any damage brakage or leakage of material in transit. </p>
                    </td>
                    <td style="padding: 15px 5px; text-align: right; border-bottom: 1px solid #d6d6d6; vertical-align: top;">
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
                                    <span
                                     style="display: block; margin-left: 3px; background: #b8e9be; justify-content: center; align-items: center; border: 1px solid #11b722; width: 13px; height: 13px; border-radius: 50%;"
                                     >
                                        <span style="width: 7px; height: 7px; margin: 0 auto; margin-top: 3px; background: #11b722; border-radius: 50%; display: block;"></span>
                                    </span>
                                    <span style="display: inline-block;
                                                    width: 90%;
                                                    background: #c0c0c0;
                                                    height: 1px;
                                                    border-radius: 5px;
                                                    position: absolute;
                                                    left: 16px;
                                                    top: 21px;"></span>
                                </td>

                                <!-- INTERMEDIATE LOCATION POINTS -->
                                @foreach ($lorryReceipt->locations as $location)
                                    <td style="position: relative; vertical-align: bottom; text-align: center;">
                                        <!-- <span style="display: block; background: #d5bdea; border: 1px solid #6a11b7; width: 16px; height: 16px; border-radius: 50%; margin: auto;">
                                            <span style="width: 10px; height: 10px; margin: 3px auto 0; background: #6a11b7; border-radius: 50%; display: block;"></span>
                                        </span>
                                        <span style="display: block; width: 100%; background: #6a11b7; height: 4px; border-radius: 9px; margin-top: 5px;"></span> -->
                                        <span style="display: block; justify-content: center; background: #d5bdea; align-items: center; border: 1px solid #6a11b7; width: 16px; height: 16px; border-radius: 50%;">
                                        <span style="width: 10px; height: 10px; margin: 0 auto; margin-top: 3px; background: #6a11b7; border-radius: 50%; display: block;"></span>
                                    </span>
    
                                     <span style="
                                         display: inline-block;
                                        width: 98%;
                                        background: #6a11b7;
                                        height: 4px;
                                        border-radius: 9px;
                                        position: absolute;
                                        top: 19px;
                                        left: 18px;
                                    "></span>
                                    </td>

                                   

                                @endforeach

                                <!-- TO POINT -->
                                <td style="position: relative; vertical-align: bottom; text-align: center;">
                                    <img src="{{ @$locationPathSecond }}" height="23px" style="margin-bottom: -3px;" alt="">
                                    <span style="display: block;  background: #ffd5d5; border: 1px solid #ff0000; width: 13px; height: 13px; border-radius: 50%;">
                                        <span style="width: 7px; height: 7px; margin: 3px auto 0; background: #ff0000; border-radius: 50%; display: block;"></span>
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <!-- FROM LABEL -->
                                <td style="vertical-align: top; padding-top: 5px; font-size: 11px;">
                                    <strong>From</strong><br>
                                    {{ $lorryReceipt->source->name ?? '' }}
                                     
                                </td>

                                <!-- INTERMEDIATE LOCATIONS DETAILS -->
                                 @php
                                    $totalPointCharges = 0;
                                @endphp

                                @foreach ($lorryReceipt->locations as $location)
                                    @php
                                        $amount = $location->amount ?? 0;
                                        $totalPointCharges += $amount;
                                    @endphp
                                    <td style="padding-top: 5px; vertical-align: top; font-size: 11px;">
                                        <div>
                                            <p style="margin: 0;"><strong>
                                                {{ strtoupper($location->route->name ?? 'N/A') }}
                                            </strong></p>
                                            <p style="margin: 0;"><strong>
                                                {{ $location->type ?? ' ' }} Freight:</strong> Rs. {{ $location->amount ?? '0' }}/-
                                            </p>
                                            <p style="margin: 0;"><strong>
                                                No. of Articles:</strong>
                                                 {{ $location->no_of_articles ?? '0' }}
                                            </p>
                                        </div>
                                    </td>

                                    
                                @endforeach

                                <!-- TO LABEL -->
                                <td style="padding-top: 5px; vertical-align: top; font-size: 11px;">
                                    <strong>To</strong><br>
                                    {{ $lorryReceipt->destination->name ?? '' }}
                                    
                                </td>
                            </tr>
                        </table>
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

                 <table style="width: 100%; font-size: 12px;" cellspacing="0" cellpadding="0">
                       <tr>
                    <td style="padding: 15px 5px; border-bottom: 1px solid #d6d6d6;vertical-align: top; width: 40%; ">
                        <span style="font-weight: bold;">Freight from</span>
                         <p style="margin: 0px; padding-top: 3px; font-size: 11px;"> {{ @$lorryReceipt->source->name ?? '' }} <span style="font-weight: bold;">to</span>  {{ @$lorryReceipt->destination->name ?? '' }}:Rs {{ number_format((@$totalPointCharges ?? 0) + (@$lorryReceipt->freight_charges ?? 0), 2) }} </p>    
                    </td>
                      <td style="padding: 15px 5px; border-bottom: 1px solid #d6d6d6; vertical-align: top; width: 30%;">
                          <span style="font-weight: bold;">LR charges</span> 
                          <p style="margin: 0px; padding-top: 3px; font-size: 11px;"> {{ @$lorryReceipt->lr_charges ?? '' }} </p>
                      </td>
                      <td style="padding: 15px 5px; border-bottom: 1px solid #d6d6d6; vertical-align: top; width: 30%;">
                          <span style="font-weight: bold;">Total Freight</span> 
                           <p style="margin: 0px; padding-top: 3px; font-size: 11px;"> Rs {{ number_format((@$lorryReceipt->lr_charges ?? 0) + (@$lorryReceipt->freight_charges ?? 0) + ($totalPointCharges ?? 0), 2) }} </p>
                      </td>
                </tr>
                <tr>
                    <td  style="padding: 15px 5px; vertical-align: top;">
                        <span style="font-weight: bold; padding-bottom: 10px;">Customer Name</span>
                        <p style="margin: 0px; padding-top: 3px; font-size: 11px;">{{ @$lorryReceipt->consignor->company_name ?? '' }}</p>
                            
                    </td>
                      <td style="padding: 15px 5px; vertical-align: top;">
                          <span style="font-weight: bold;">Driver Name </span>
                          <p style="margin: 0px; padding-top: 3px;  font-size: 11px;">{{ @$lorryReceipt->driver->name ?? '' }} </p>
                         
                      </td>
                      <td style="padding: 15px 5px; vertical-align: top;">
                          <span style="font-weight: bold;">Signature:</span> <br>
                      </td>
                </tr>
                 </table>


        </div>
       
    </div>
    
</body>
</html>