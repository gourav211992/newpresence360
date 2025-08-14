<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{$body->report_type=="debit"?"Debtor Statment":"Creditor Statment"}}</title>
</head>
<body>
    <p>Dear {{$body->custName}},</p>
       <p>{{$body->remarks}}</p>
       {{-- <p>Please find attached the {{$body->report_type=="debit"?"Debtor Statment":"Creditor Statment"}} Report for the specified period.</p> --}}

    <br>

    <p>Thanks & Regards,</p>
    <p><strong>{{$body->orgName}}</strong></p>
</body>
</html>
