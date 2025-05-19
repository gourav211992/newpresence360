<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .invoice-box { width: 100%; margin: 0 auto; padding: 20px; border: 1px solid #eee; }
        .header { text-align: center; margin-bottom: 20px; }
        .qr-code { text-align: center; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #ddd; padding: 8px; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <h2>e-Invoice</h2>
            <p><strong>Ack No:</strong> {{ $data['AckNo'] }}</p>
            <p><strong>Ack Date:</strong> {{ $data['AckDt'] }}</p>
            <p><strong>IRN:</strong> {{ $data['Irn'] }}</p>
            <p><strong>Status:</strong> {{ $data['Status'] }}</p>
        </div>
        <div class="qr-code">
            <img src="{{ $qrCodeBase64 }}" width="150" height="150" alt="QR Code">
        </div>

        <table>
            <tr>
                <th>Invoice Details</th>
                <td>{{ $data['SignedInvoice'] }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
