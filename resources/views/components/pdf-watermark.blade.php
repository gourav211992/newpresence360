@if (isset($status) && !in_array($status, ['approved', 'approval_not_required', 'posted']))
    <style>
        /* === Optimized PDF Watermark Styles === */
        body {
            font-family: DejaVu Sans, sans-serif;
            position: relative;
        }

        .pdf-watermark {
            position: fixed;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-55deg);
            font-size: 200px;
            color: rgba(0, 0, 0, 0.08);
            /* Light gray watermark */
            font-weight: bold;
            z-index: -1;
            pointer-events: none;
            white-space: nowrap;
            text-align: center;
            page-break-inside: avoid;
        }
    </style>

    <div class="pdf-watermark">
        DRAFT
    </div>
@endif
