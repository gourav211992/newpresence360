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
            margin-bottom: 20px;
            /* border: 4px dashed #28a745; Green dashed border around QR */
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
    </style>
</head>

<body onload="window.print()">
    @foreach ($whDetails as $packet)
        @if ($packet->storage_number)
            <div class="page">
                <div class="qr-card">
                    <div class="store-details">{{ $packet?->sub_store?->name }}</div>
                    <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($packet->storage_number, 'QRCODE') }}"
                        class="barcode-img" />

                    <div class="details">{{ $packet->storage_number }}</div>
                    <div class="parent-details">{{ $packet->heirarchy_name }}</div>

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
