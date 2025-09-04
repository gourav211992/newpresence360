<!DOCTYPE html>
<html>

<head>
    <title>Barcodes</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .page {
            width: 100%;
            height: 100vh;
            /* full viewport height */
            display: flex;
            justify-content: center;
            align-items: center;
            page-break-after: always;
        }

        .qr-card {
            border: 6px solid #007bff;
            /* Bootstrap primary blue */
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
            background: #f9f9f9;
            max-width: 400px;
        }

        .barcode-img {
            width: 350px;
            height: 350px;
            margin-top: 20px;
            /* border: 4px dashed #28a745; */
            /* Green dashed border around QR */
            padding: 10px;
            border-radius: 10px;
            background: #fff;
        }

        .store-details {
            font-size: 25px;
            font-weight: 600;
            margin: 8px 0;
            color: #333;
        }

        .details {
            font-size: 25px;
            font-weight: 600;
            margin: 8px 0;
            color: #333;
        }

        .parent-details {
            font-size: 30px;
            font-weight: 800;
            margin: 8px 0;
            color: #333;
        }

        .attr-badge {
            display: inline-block;
            margin: 2px 4px 0 0;
            padding: 2px 8px;
            font-size: 1.25rem;
            line-height: 1.45;
            border-radius: 9999px;
            background: #eef2ff;
            /* light indigo */
            color: #1d4ed8;
            /* indigo-700 */
            border: 1px solid #c7d2fe;
            /* indigo-200 */
            white-space: nowrap;
        }
    </style>
</head>

<body onload="window.print()">
    @foreach ($barCodes as $val)
        @if ($val->item_uid)
            <div class="page">
                <div class="qr-card">
                    <div class="store-details">{{ $whmJob?->organization?->name }}</div>

                    <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($val->item_uid, 'QRCODE') }}"
                        class="barcode-img" />

                    <div class="parent-details">{{ $val->item_uid }}</div>
                    <div class="details">{{ $val->item_name }}</div>
                    <div class="details">{{ $val->item_code }}</div>
                    <div class="details">
                        @php
                            // Accept either array or JSON string
                            $attrs = $val->item_attributes ?? ($val->attributes ?? []);
                            if (is_string($attrs)) {
                                $attrs = json_decode($attrs, true) ?: [];
                            }
                        @endphp

                        @forelse($attrs as $a)
                            @php
                                $name = trim($a['attribute_name'] ?? '');
                                $value = trim($a['attribute_value'] ?? '');
                            @endphp
                            @if ($name !== '' || $value !== '')
                                <span class="attr-badge">
                                    {{ e($name) }}: {{ e($value) }}
                                </span>
                            @endif
                        @empty
                            {{-- no attributes --}}
                        @endforelse
                    </div>
                    <div class="details">
                        {{ $val?->vendor?->company_name }}
                    </div>

                    {{-- Optional extra info --}}
                    {{-- <div class="details">
                        Document No.: {{ $packet?->mrnHeader?->book_code }}-{{ $packet?->mrnHeader?->document_number }}
                    </div> --}}
                </div>
            </div>
        @endif
    @endforeach
</body>

</html>
