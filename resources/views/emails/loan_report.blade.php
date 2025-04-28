<!DOCTYPE html>
<html>
<head>
    <title>Loan Report</title>
</head>
<body>
    {{-- <h1>Loan Report</h1>
    <p>Please find attached the Loan report for the specified period.</p>
    <p>This is an automated report. Please do not reply to this email.</p> --}}


    <h1>{{ isset($scheduler) ? ucfirst($scheduler->type) : '' }} Loan Report</h1>
    @if (isset($startDate) && isset($endDate))
    <p>Report period: {{ $startDate }} to {{ $endDate }}</p>
    @endif
    <p>Please find attached the Loan report for the specified period.</p>
    <p>This is an automated report. Please do not reply to this email.</p>
</body>
</html>