<!DOCTYPE html>
<html>
<head>
    <title>Import Completion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            padding: 20px;
            background-color: #f9f9f9;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 24px;
            color: #444;
            margin-bottom: 20px;
        }
        p {
            margin: 10px 0;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .success {
            color: #28a745;
        }
        .error {
            color: #dc3545;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 0;
            background: #007bff;
            color: #fff;
            border-radius: 5px;
            text-align: center;
        }
        .button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Import Completion</h1>
        <p>Dear User,</p>
        <p>We are pleased to inform you that the import of <strong>{{ $modelName }}</strong> has been completed.</p>

        <div class="success">
            <p><strong>Successful Items ({{ count($successful_items) }})</strong></p>
            @if(count($successful_items) > 0)
                <p><a href="{{ $export_successful_url }}" download class="button">Export Successful Items</a></p>
            @else
                <p>No items were successfully imported.</p>
            @endif
        </div>

        <div class="error">
            <p><strong>Failed Items ({{ count($failed_items) }})</strong></p>
            @if(count($failed_items) > 0)
                <p><a href="{{ $export_failed_url }}" download class="button">Export Failed Items</a></p>
            @else
                <p>No items failed during import.</p>
            @endif
        </div>
    </div>
</body>
</html>
