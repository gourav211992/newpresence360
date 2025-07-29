<!DOCTYPE html>
<html>
<head>
    <title>Receipt Approval Status</title>
</head>
<body style="background:#D0D0CE5a"> 

    <div
        style="width:680px; margin:0 auto; padding:50px 50px; background:#D0D0CE4a; font-family:Arial, Helvetica, sans-serif">

        <table cellpadding="0" cellspacing="0" width="100%" style="background: #7415ae">
            <tr>
                <td>
                    <p style="text-align: center; margin-bottom: 6px; margin: 30px"><img src="https://login.thepresence360.com/images/thepresence.png" height="30px" />
                    </p>

                </td>
            </tr>
        </table>
        <table cellpadding="0" cellspacing="0"
            style="background:#fff; box-shadow: 0 0 14px 0 #00000033; border:#ececec thin solid; padding:10px 20px; font-family:Tahoma, Geneva, sans-serif; border-bottom: 6px #7415ae solid"
            width="100%">
            <tbody>

                <tr>
                    <td>
                        <table cellpadding="0" cellspacing="0" width="100%">
                            <tbody>
                                <tr>
                                    <td>
                                        <h2 style="color: #2c3e50;">Your Lorry Receipt</h2>
                                        <p style="font-size: 16px; color: #555;">Dear {{ $name ?? 'User' }},</p>
                                        <p style="font-size: 15px; color: {{ $status == 'success' ? '#28a745' : '#e74c3c' }};">
                                            {{ $remarks }}
                                        </p>

                                        @if($status === 'success' && $remarks == 'Your Lorry Receipt has been already approved!.')
                                            <p style="font-size: 15px; color: #333;">The receipt has been already approved. You may close this window.</p>
                                        @elseif($status === 'success')
                                            <p style="font-size: 15px; color: #333;">The receipt has been approved successfully. You may close this window.</p>
                                        @else
                                            <p style="font-size: 15px; color: #333;">You are not authorized to approve this receipt.</p>
                                        @endif


                                        
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td> 

            </tbody>


        </table>







    </div>
</body>
</html>
