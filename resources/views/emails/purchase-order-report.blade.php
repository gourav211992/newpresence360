<!DOCTYPE html>
<html>
<head>
    <title>{{ ucfirst($scheduler->type) }} Purchase Order Report</title>
</head>
<body>
    <h1>{{ ucfirst($scheduler->type) }} Purchase Order Report</h1>
    <p>Report period: {{ $startDate }} to {{ $endDate }}</p>
    <p>Please find attached the Purchase Order report for the specified period.</p>
    <p>This is an automated report. Please do not reply to this email.</p>
</body>
</html>