<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Production Report</title>
     <style>
        @media print {
            .page-break {
                page-break-before: always;
                break-before: page;
            }
        }

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

        .break-word {
            word-wrap: break-word;
            /* For legacy browsers */
            overflow-wrap: break-word;
            /* Modern standard */
            word-break: break-all;
            /* Force break anywhere */
        }
    </style>
</head>
<body style="margin:0; font-family: DejaVu Sans, Arial, sans-serif; font-size:11px; color:#111;">
  <!-- Header (fixed) -->
  <div style="position: fixed; top: 0; left: 0; right: 0; height: 88px; padding: 12px 16px 8px 16px; border-bottom:1px solid #ccc;" class="page-break">
    @include('pdf.partials.header', [
          'orgLogo' => @$orgLogo,
          'moduleTitle' => $title,
          'imagePath' => $imagePath,
      ])
    <table style="width:100%; border-collapse:collapse;">
      <tr>
  
        <td style="text-align:right; vertical-align:middle;">
          <div style="font-size:12px; color:#111; font-weight:600;">{{$details->store_name}}</div>
          <div style="font-size:10px; color:#666;">Generated on: {{ now()->format('d-m-Y H:i') }}</div>
        </td>
      </tr>
    </table>
  </div>

  <!-- Body padding to avoid overlap with fixed header/footer -->
  <div style="padding: 104px 16px 76px 16px;">

    <!-- Product Summary Card -->
    <table style="width:100%; border-collapse:collapse;">
      <tr>
        <td style="vertical-align:top; width:55%; border:1px solid #000; padding:8px;">
          <div style="font-weight:700; font-size:12px; margin-bottom:6px;">Product Name</div>
          <div style="font-size:12px;">{{ $details->item_name }}</div>
          <div style="margin-top:8px; font-size:11px;"><span style="font-weight:700;">Attributes:</span>
            {{ $details->attributes }}</div> 
          <div style="margin-top:8px; font-size:11px;"><span style="font-weight:700;">Customer Name:</span>
              {{ $details->customer_name }}</div>
          <div style="margin-top:8px; font-size:11px;"><span style="font-weight:700;">SO#:</span>
              {{  $details->so_book_code }}-{{  $details->so_document_number }}</div>
          <div style="margin-top:8px; font-size:11px;"><span style="font-weight:700;">Date:</span>
              {{ date('d-m-Y',strtotime($details->so_document_date))}}</div>
            
        </td>
        <td style="vertical-align:top; width:45%; border:1px solid #000; padding:8px;">
          <table style="width:100%; border-collapse:collapse;">
            <tr>
              <td style="width:45%; background:#f8fafc; border:1px solid #000; padding:6px; font-weight:700;">PWO#</td>
              <td style="border:1px solid #000; padding:6px;">{{ $details->pwo_book_code }}-{{ $details->pwo_document_number }}</td>
            </tr>
            <tr>
              <td style="background:#f8fafc; border:1px solid #000; padding:6px; font-weight:700;">Date</td>
              <td style="border:1px solid #000; padding:6px;">{{ date('d-m-Y',strtotime($details->pwo_document_date))}}</td>
            </tr>
            <tr>
              <td style="background:#f8fafc; border:1px solid #000; padding:6px; font-weight:700;">PWO Qty</td>
              <td style="border:1px solid #000; padding:6px;">{{ number_format( $details->qty) }}</td>
            </tr>  
            <tr>
              <td style="background:#f8fafc; border:1px solid #000; padding:6px; font-weight:700;">Produced Qty</td>
              <td style="border:1px solid #000; padding:6px;">{{ number_format( $details->pslip_qty) }}</td>
            </tr>
            <tr>
              <td style="background:#f8fafc; border:1px solid #000; padding:6px; font-weight:700;">% Completion</td>
              <td style="border:1px solid #000; padding:6px;">{{  $details->completion_percent }}%</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <!-- Detail Table -->
    <div style="margin-top:12px; font-weight:700; font-size:12px;">Manufacturing Orders</div>
    <table style="width:100%; border-collapse:collapse; margin-top:6px;">
    
        <tr>
          <th style="border:1px solid #000; background:#f1f5f9; font-size:10px; text-transform:uppercase;">Sr. No.</th>
          <th style="border:1px solid #000; background:#f1f5f9; font-size:10px; text-transform:uppercase;">MO No.</th>
          <th style="border:1px solid #000; background:#f1f5f9; font-size:10px; text-transform:uppercase;">MO Date</th>
          <th style="border:1px solid #000; background:#f1f5f9; font-size:10px; text-transform:uppercase;">MO Qty</th>
          <th style="border:1px solid #000; background:#f1f5f9; font-size:10px; text-transform:uppercase;">Sub Store</th>
          <th style="border:1px solid #000; background:#f1f5f9; font-size:10px; text-transform:uppercase;">Station</th>
          <th style="border:1px solid #000; background:#f1f5f9; font-size:10px; text-transform:uppercase;">Type</th>
          <th style="border:1px solid #000; background:#f1f5f9; font-size:10px; text-transform:uppercase;">PSLIP No.</th>
          <th style="border:1px solid #000; background:#f1f5f9; font-size:10px; text-transform:uppercase;">PSLIP Date</th>
          <th style="border:1px solid #000; background:#f1f5f9; font-size:10px; text-transform:uppercase;">Produced Qty</th>
          <th style="border:1px solid #000; background:#f1f5f9; font-size:10px; text-transform:uppercase;">Accepted (A)</th>
          <th style="border:1px solid #000; background:#f1f5f9; font-size:10px; text-transform:uppercase;">Sub Standard (B)</th>
          <th style="border:1px solid #000; background:#f1f5f9; font-size:10px; text-transform:uppercase;">Rejected (C)</th>
        </tr>
   
   
        @foreach($get as $keys=>$r)
          <tr>
            <td style="border:1px solid #000; text-align:center;">{{ $keys+1 }}</td>
            <td style="border:1px solid #000;">{{ $r->book_code }}-{{ $r->document_number }}</td>
            <td style="border:1px solid #000;">{{ date('d-m-Y',strtotime($r->document_date)) }}</td>
            <td style="border:1px solid #000; text-align:right;">{{ $r->mo_product_qty }}</td>
            <td style="border:1px solid #000;">{{ $r->sub_store_name }}</td>
            <td style="border:1px solid #000;">{{ $r->station_name }}</td>
            <td style="border:1px solid #000;">{{ $r->type }}</td>
            <td style="border:1px solid #000;">{{ $r->pslip_book_code }}-{{ $r->pslip_document_number }}</td>
            <td style="border:1px solid #000;">{{ date('d-m-Y',strtotime($r->pslip_document_date)) }}</td>
            <td style="border:1px solid #000; text-align:right;">{{ $r->qty? $r->qty : 0}}</td>
            <td style="border:1px solid #000; text-align:right;">{{ $r->accepted_qty? $r->accepted_qty : 0}}</td>
            <td style="border:1px solid #000; text-align:right;">{{ $r->subprime_qty? $r->subprime_qty : 0}}</td>
            <td style="border:1px solid #000; text-align:right;">{{ $r->rejected_qty? $r->rejected_qty : 0}}</td>
          </tr>
        @endforeach
     
    </table>

    <!-- Signature Block -->
    <table style="width:100%; border-collapse:collapse;">
  
     <tr>
        <td
            style="width:50%; padding:10px; border:1px solid #000; vertical-align:top;">
            <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="padding-top: 5px;">Printed By :</td>
                    <td style="padding-top: 5px;">
                        {{ @$user?->name ?? '' }}
                    </td>
                </tr>
            </table>
        </td>
        <td
            style="width:50%; padding:10px; border:1px solid #000; vertical-align:top;"">
            <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="text-align: center; padding-bottom: 20px;">FOR
                        {{ Str::ucfirst(@$organization->name) }} </td>
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
            Regd. Office:{{ $organizationAddress?->display_address ?? '' }}
        </td>
    </tr>
    </table>

  </div>
</body>
</html>
