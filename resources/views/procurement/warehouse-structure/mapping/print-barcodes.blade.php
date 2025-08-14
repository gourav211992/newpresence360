<!DOCTYPE html>
<html>
    <head>
        <title>Barcodes</title>
        <style>
            .barcode-block {
                display: inline-block;
                text-align: center;
                margin: 10px;
                page-break-inside: avoid;
            }
            .barcode-img {
                width: 150px;
                height: 150px;
            }
        </style>
    </head>
    <body onload="window.print()">
        @foreach($whDetails as $packet)
            @if($packet->storage_number)
                <div class="barcode-block align-items-center">
                    <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($packet->storage_number, 'QRCODE') }}" class="barcode-img" />
                    <div>
                        {{ $packet?->storage_number }}
                    </div>
                    <!-- <div>
                    Document No. : {{ $packet?->mrnHeader?->book_code }}-{{ $packet?->mrnHeader?->document_number }}
                    </div> -->
                </div><br/>
            @endif
        @endforeach
    </body>
</html>
