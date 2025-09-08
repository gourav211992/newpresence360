<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Invoice Table</title>
</head>

<body>
  <div
    style="border:1px solid #000000; background-color: #ffffff; width: 650px; padding: 10px; margin: 0px auto; font-family:Arial, sans-serif; font-size:14px;">
    <table cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse;">
      <tr>
        <td colspan="6"
          style="padding: 5px; text-align:center; text-decoration: underline; font-size:18px; font-weight:600;">TAX
          INVOICE</td>
      </tr>
      <tr>
        <td colspan="6" style="padding: 5px; text-align:center; font-weight:600; font-size:18px;">{{ $organization->name ?? 'STAQO PRESENCE TESTING' }}
        </td>
      </tr>
      <tr>
        <td colspan="6" style="padding: 5px; text-align:center; font-weight:300; font-size:14px; ">
          FLEET OWNERS AND TRANSPORT CONTRACTORS
        </td>
      </tr>
      <tr>
        <td colspan="6" style="padding: 0px; text-align:center; font-weight:300; font-size:13px; ">
          {{ $order->organization_address->display_address ?? 'Noida' }}
        </td>
      </tr>
      <tr>
        <td colspan="6" style="padding: 5px; text-align:center; font-weight:300; font-size:13px; ">
          PH: {{ $order->organization_address->phone ?? 'N/A' }} , EMAIL: {{ $organization->email ?? 'N/A' }}
        </td>
      </tr>
      <tr>
        <td
          style="font-weight:600; font-size:14px; padding: 4px; border-top: 1px solid #000000; border-left: 1px solid #000000;">
          Name: </td>
        <td colspan="3" style="font-weight:300; font-size:14px; padding: 4px; border-top: 1px solid #000000; "> {{ $order->customer->company_name ?? 'N/A' }}
        </td>
        <td
          style="font-weight:600; font-size:14px; padding: 4px; border-top: 1px solid #000000; border-left: 1px solid #000000;">
          Invoice No. </td>
        <td
          style="font-weight:300; font-size:14px; padding: 4px; border-top: 1px solid #000000; border-right: 1px solid #000000;">
          {{ $order->document_number ?? 'N/A' }}</td>
      </tr>
      <tr>
        <td style="font-weight:600; font-size:14px; padding: 4px; border-left: 1px solid #000000;">Email: </td>
        <td colspan="3" style="font-weight:300; font-size:14px; padding: 4px;">{{ $order->customer_email ?? 'N/A' }} </td>
        <td style="font-weight:600; font-size:14px; padding: 4px; border-left: 1px solid #000000;">Invoice Date. </td>
        <td style="font-weight:300; font-size:14px; padding: 4px; border-right: 1px solid #000000;">
          {{ \Carbon\Carbon::parse($order->document_date)->format('d/m/Y') }}</td>
      </tr>
      <tr>
        <td style="font-weight:600; font-size:14px; padding: 4px; border-left: 1px solid #000000;">Address: </td>
        <td colspan="3" style="font-weight:300; font-size:14px; padding: 4px;">{{ $order->billing_address_details->address ?? 'N/A' }}
        </td>
        <td style="font-weight:600; font-size:14px; padding: 4px; border-left: 1px solid #000000;">State </td>
        <td style="font-weight:300; font-size:14px; padding: 4px; border-right: 1px solid #000000;">{{ $order->organization_address->state->name ?? 'N/A' }} </td>

      </tr>
      <tr>
        <td style="font-weight:600; font-size:14px; padding: 4px; border-left: 1px solid #000000;">Mobile No: </td>
        <td colspan="3" style="font-weight:300; font-size:14px; padding: 4px;">+91 {{$order->customer_phone_no ?? 'N/A'}} </td>
        <td style="font-weight:600; font-size:14px; padding: 4px; border-left: 1px solid #000000;">Code</td>
        <td style="font-weight:300; font-size:14px; padding: 4px; border-right: 1px solid #000000;">N/A</td>
      </tr>
      <tr>
        <td style="font-weight:600; font-size:14px; padding: 4px; border-left: 1px solid #000000;">GSTIN: </td>
        <td colspan="3" style="font-weight:300; font-size:14px; padding: 4px;">{{$order->customer_gstin ?? 'N/A'}} </td>
        <td style="font-weight:600; font-size:14px; padding: 4px; border-left: 1px solid #000000;">GSTIN </td>
        <td style="font-weight:300; font-size:14px; padding: 4px; border-right: 1px solid #000000;">{{ $organization->gst_number ?? 'N/A'}}</td>
      </tr>
      <tr>
        <td style="font-weight:600; font-size:14px; padding: 4px; border-left: 1px solid #000000;">State: </td>
        <td style="font-weight:300; font-size:14px; padding: 4px; ">{{$order->billing_address_details->state->name ?? 'N/A'}}  </td>
        <td style="font-weight:600; font-size:14px; padding: 4px; ">State Code
        </td>
        <td style="font-weight:300; font-size:14px; padding: 4px; "> N/A
        </td>
        <td style="font-weight:600; font-size:14px; padding: 4px; border-left: 1px solid #000000;">
          PAN </td>
        <td style="font-weight:300; font-size:14px; padding: 4px; border-right: 1px solid #000000;">
          N/A</td>
      </tr>

      <tr>
        <td style="font-size: 14px; font-weight: 600; color: #000000; border: 1px solid #000000; padding: 10px;">Date
        </td>
        <td style="font-size: 14px; font-weight: 600; color: #000000; border: 1px solid #000000; padding: 10px;">LR No
        </td>
        <td style="font-size: 14px; font-weight: 600; color: #000000; border: 1px solid #000000; padding: 10px;">From
        </td>
        <td style="font-size: 14px; font-weight: 600; color: #000000; border: 1px solid #000000; padding: 10px;">To</td>
        <td style="font-size: 14px; font-weight: 600; color: #000000; border: 1px solid #000000; padding: 10px;">
          Description</td>
        <td
          style="text-align: right; font-size: 14px; font-weight: 600; color: #000000; border: 1px solid #000000; padding: 10px;">
          Amount
          (Rs)</td>
      </tr>
       @php
      $total = 0;
      $totaldiscount = 0;
      @endphp
      @foreach($order->items as $index => $item)
          @php
              $currentOrder = $item;
              $discountAmtPrev = $item->item_discount_amount ?? 0;
          @endphp
          <tr>
            <td style="font-size: 14px; font-weight: 300; color: #000000; border: 1px solid #000000; padding: 10px;">
              {{ \Carbon\Carbon::parse($order->document_date)->format('d/m/Y') }}</td>
            <td style="font-size: 14px; font-weight: 300; color: #000000; border: 1px solid #000000; padding: 10px;">
              {{ $item->lorry->document_number ?? '' }}</td>
            <td style="font-size: 14px; font-weight: 300; color: #000000; border: 1px solid #000000; padding: 10px;">{{ $item->lorry->source->name ?? '' }}
            </td>
            <td style="font-size: 14px; font-weight: 300; color: #000000; border: 1px solid #000000; padding: 10px;">{{ $item->lorry->destination->name ?? '' }}
            </td>
            <td style="font-size: 14px; font-weight: 300; color: #000000; border: 1px solid #000000; padding: 10px;">{{ $currentOrder->lorry->remarks ?? 'N/A' }}
            </td>
            <td
              style=" text-align: right; font-size: 14px; font-weight: 300; color: #000000; border: 1px solid #000000; padding: 10px;">
              {{ ($currentOrder->lorry->freight_charges + $currentOrder->lorry->sub_total +$item->lorry->lr_charges ?? 0)}}
            </td>
          </tr>
            @php
            $total += ($currentOrder->lorry->freight_charges + $currentOrder->lorry->sub_total + $item->lorry->lr_charges ?? 0) - $discountAmtPrev;
            $totaldiscount += $discountAmtPrev;
            $ordertaxcount = is_array($order->tax) ? count($order->tax) : 0
            @endphp
        @endforeach
      <tr>
        <td colspan="4" rowspan="{{3+$ordertaxcount}}" style="vertical-align: top; border-left: 1px solid #000000; padding: 10px;"><span
            style="text-decoration: underline; font-size: 14px; font-weight: 600; color: #000000;"> Our Bank
            Account
            Details
          </span> <br>
          <span style="font-size: 13px; font-weight: 300; color: #000000;">Bank & Branch : Punjab National Bank, Laxmi
            Nagar, Delhi-92
          </span>
          <br>
          <span style="font-size: 13px; font-weight: 300; color: #000000;">FSC Code: PUNB000232
          </span>
          <br>
          <span style=" font-size: 13px; font-weight: 300; color: #000000;">Account No: 654678566776
          </span>
        </td>

      </tr>
      <tr>
        <td style="font-size: 14px; font-weight: 300; color: #000000; border: 1px solid #000000; padding: 10px;">
          Discount@0.00%</td>
        <td
          style="text-align: right; font-size: 14px; font-weight: 300; color: #000000; border: 1px solid #000000; padding: 10px;">
          0.00</td>
      </tr>
      
      @php
      $totaltax = 0;
      
      @endphp
        @if(!empty($order->tax) && is_array($order->tax))
       
          @foreach($order->tax as $tax)
              <tr>
                          <td style="font-size: 14px; font-weight: 300; color: #000000; border: 1px solid #000000; padding: 10px;">
                              {{ $tax['tax_type'] }}{{'@'}}{{ number_format($tax['tax_percentage'], 2) }}%

                          </td>
                          <td style="text-align: right; font-size: 14px; font-weight: 300; color: #000000; border: 1px solid #000000; padding: 10px;">
                              {{ number_format((($total * $tax['tax_percentage']) / 100), 2) }}
                          </td>
              </tr>
                @php
                $totaltax += ($total * $tax['tax_percentage']) / 100;
                @endphp
           @endforeach
        @endif
      <tr>
         
            <td style="font-size: 14px; font-weight: 300; color: #000000; border: 1px solid #000000; padding: 10px;">
              TAX</td>
            <td
            
              style="text-align: right; font-size: 14px; font-weight: 300; color: #000000; border: 1px solid #000000; padding: 10px;">
              @if(!empty($order->tax) && is_array($order->tax)) {{$totaltax}} @endif</td>
          
      </tr>
      <tr>
        @php
        $formatter = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
$amountInWords = ucfirst($formatter->format($totaltax + $total));
        @endphp
        <td colspan="4" rowspan="1"
          style="vertical-align: top; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-top: 1px solid #000000; padding: 10px; font-size: 14px; font-weight: 600; color: #000000;">
          Total Amount(in Words): <span style="font-size: 13px; font-weight: 300;">{{ $amountInWords }}.</span>
        </td>
         <td style="font-size: 14px; font-weight: 300; color: #000000; border: 1px solid #000000; padding: 10px;">
          Grand Total</td>
        <td
          style="text-align: right; font-size: 14px; font-weight: 300; color: #000000; border: 1px solid #000000; padding: 10px;">
           {{$totaltax+$total}}</td>
    
      </tr>
  
      <tr>
        <td colspan="3" style="font-weight:600; font-size: 13px; padding-top: 10px;">Terms & Conditions:</td>
        <td colspan="1" style="font-weight:600; font-size: 13px; padding-top: 10px;">E&OE</td>
        <td colspan="2" style="text-align: right; font-weight:600; font-size: 14px; padding-top: 10px;">For {{ $organization->name ?? 'STAQO PRESENCE TESTING' }}</td>
      </tr>
      <tr>
        <td colspan="3" style=" font-size: 13px; color: #000000;">
          <ol style="margin: 0; padding-left: 18px;">
            <li>Goods once sent will not be taken back or exchanged</li>
            <li>Interest will be charged @ 18% p.a if this bill is not paid within 7 days</li>
            <li>Our risk and responsibility ceases after delivery of the goods</li>
            <li>Inspection may be carried out before dispatch</li>
            <li>Dispute if any, subject to Delhi, Jurisdiction only</li>
            <li>Lorem ipsum dolor, sit amet consectetur</li>
            <li>Lorem ipsum dolor, sit amet consectetur</li>
          </ol>
        </td>
        <td colspan="3"
          style="text-align:right; vertical-align: bottom; font-size: 13px; color: #000000; font-weight: 300;">
          <i>Authorized Signatory</i>
        </td>
      </tr>

      <tr>
        <td colspan="6" style="text-align:center; padding-top: 25px; font-size: 16px; font-weight: 600;">The Gulati
          Group... Velocity Redefined</td>
      </tr>
      <tr>
        <td colspan="6" style="padding-bottom: 10px; text-align:center; font-size: 13px; font-weight: 600;">visit us at:
          www.gulatiroadways.com</td>
      </tr>
    </table>
  </div>
</body>

</html>