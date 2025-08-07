<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px; /* Smaller font size for better fit */
            color: #333;
            margin: 20px; /* Reduced margins */
            background-color: #fff;
        }

        h1 {
            text-align: center;
            margin-bottom: 15px; /* Reduced margin */
            font-size: 18px; /* Reduced font size */
            color: #2c3e50;
            border-bottom: 1px solid #ddd; /* Thinner border */
            padding-bottom: 5px; /* Reduced padding */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px; /* Reduced margin */
        }

        th, td {
            padding: 6px 8px; /* Reduced padding */
            border: 1px solid #e1e1e1;
            text-align: left;
            font-size: 10px; /* Smaller font size for data */
        }

        th {
            background-color: #f5f7fa;
            font-weight: 600;
            text-transform: capitalize;
        }

        tr:nth-child(even) td {
            background-color: #fbfbfb;
        }

        tr:hover td {
            background-color: #f0f8ff;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <table>
        <thead>
            <tr>
                @foreach ($columns as $header => $dbColumn)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($finalData as $item)
                <tr>
                    @foreach ($columns as $header => $dbColumn)
                        <td>{{ $item->$header ?? '—' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}" style="text-align: center; color: #999;">
                        No data available.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
