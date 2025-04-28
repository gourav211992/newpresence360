<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ucfirst($body->report_type)}} Report</title>
</head>
<body>
    <p>Dear {{$body->custName}},</p>
        <p>{{$body->remarks}}</p>
        {{-- <p>Please find attached the {{ucfirst($body->report_type)}} Report for the specified period.</p> --}}

    <br>

    <p>Thanks & Regards,</p>
    <p><strong>{{$body->orgName}}</strong></p>
</body>
</html>
