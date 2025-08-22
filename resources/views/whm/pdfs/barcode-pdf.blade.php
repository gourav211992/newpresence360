<!DOCTYPE html>
<html>

<head>
    <title>Barcodes</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            /* horizontal center */
            align-items: center;
            /* vertical center */
            height: 100vh;
            /* full page height */
            margin: 0;
            /* remove default margin */
        }

        .barcode-block {
            width: 200px;
            /* sticker width */
            height: 280px;
            /* sticker height */
            border: 1px solid #000;
            /* sticker border */
            border-radius: 8px;
            /* smooth corners */
            text-align: center;
            padding: 10px;
            box-sizing: border-box;
            page-break-inside: avoid;
        }


        .barcode-img {
            width: 140px;
            height: 140px;
            margin: 0 auto 8px auto;
            /* center horizontally */
            display: block;
        }

        .item-text {
            font-size: 12px;
            margin-top: 2px;
        }

        .item-uid {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .attributes {
            font-size: 11px;
            margin-top: 4px;
        }
    </style>
</head>

<body>
    @foreach ($items as $item)
        <div class="barcode-block">
            <img src="{{ $item->qr_code }}" class="barcode-img" />
            <div class="item-uid">
                {{ $item->item_uid }}
            </div>
            <div class="item-text">
                {{ $item->item_name }}
            </div>
            <div class="item-text">
                {{ $item->item_code }}
            </div>
            <div class="attributes">
                @php
                    $attributes = $item->item_attributes;
                @endphp
                @if (!empty($attributes))
                    @foreach ($attributes as $index => $attr)
                        {{ $attr['attribute_name'] ?? '' }}: {{ $attr['attribute_value'] ?? '' }}@if (!$loop->last)
                            ,
                        @endif
                    @endforeach
                @else
                    N/A
                @endif
            </div>
        </div><br />
    @endforeach
</body>

</html>
