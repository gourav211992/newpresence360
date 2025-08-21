<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>kaizen-evaluation</title>
</head>
<body>
    <div style="width:735px; border: 2px solid #000; font-family:Arial;">

        <table style="width: 100%; font-size: 13px; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
              <tr>
                <td style="width: 20%;  padding: 5px 10px; background: #002060;">
                    <img src="{{ public_path('/img/Sleepwell-logonew2.jpg') }}" height="35px" alt="">
                </td>
                <td style="background: #002060; color: #fff; width: 60%; font-weight: bold; font-size: 18px; text-align: center; padding: 5px 10px;">
                      KAIZEN EVALUATION PARAMETER
                </td>
                <td style=" background: #002060; width: 20%; text-align: right;  padding: 5px 10px;">
                    <img src="{{ public_path('/img/0_sheela.jpeg') }}" height="30px" alt="">
                </td>
              </tr>
             <tr>
                <td style="width: 20%; border: 1px solid #000; color: #fff; padding: 5px 10px; background: #002060; border-right: none;">
                    Rv No: 03
                </td>
                <td style="width: 20%; border: 1px solid #000; color: #fff; padding: 5px 10px; background: #002060; border-right: none; border-left: none; border-right: none;"></td>
                <td style="width: 20%; border: 1px solid #000; color: #fff; padding: 5px 10px; background: #002060; border-right: none; border-left: none; border-right: none;">
                    Date: 14.03.2024
                </td>
             </tr>
        </table>

        <table style="width: 100%; font-size: 12px;" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="font-weight: 500; width: 120px; background: #002060; color: #fff; border: black thin solid; padding: 7px; border-top: none; border-right: none; vertical-align: top; border-left: none; text-align: center;">
                        
                            COST SAVING <br> (IN RS PER ANNUM)

                       
                    </td>
                    <td style="font-weight: 500; background: #002060; color: #fff; border: black thin solid; border-right: none; border-top: none; padding: 7px; vertical-align: top; text-align: center; width: 120px;">INNOVATION</td>
                    <td style="font-weight: 500; background: #002060; color: #fff; border: black thin solid; padding: 7px; border-right: none; border-top: none; vertical-align: top; text-align: center;">QUALITY</td>
                    <td style="font-weight: 500; background: #002060; color: #fff; border: black thin solid; padding: 7px; border-right: none; border-top: none; vertical-align: top; text-align: center;">SAFETY</td>
                    <td style="font-weight: 500; background: #002060; color: #fff; border: black thin solid; border-right: none; padding: 7px; border-top: none; vertical-align: top; text-align: center; width: 150px;">PRODUCTIVITY</td>
                    <td style="font-weight: 500; background: #002060; color: #fff; border: black thin solid; padding: 7px; vertical-align: top; border-top: none; border-right: none; text-align: center;">CRITERIA RATING</td>
                </tr>
                
                @foreach ($data as $datas)
                <tr>
                    <td style="border: black thin solid; padding: 7px; border-top: none; border-right: none; vertical-align: top; border-left: none; text-align: center;"> 
                        {{$datas->cost}}
                    </td>
                    <td style="border: black thin solid; border-right: none; border-top: none; padding: 7px; vertical-align: top; text-align: center;"> 
                        {{$datas->innovation}}
                    </td>
                    <td style="border: black thin solid; padding: 7px; border-right: none; border-top: none; vertical-align: top; text-align: center;">
                         {{$datas->quality}}
                    </td>
                    <td style="border: black thin solid; padding: 7px; border-right: none; border-top: none; vertical-align: top; text-align: center;">
                         {{$datas->safety}}
                    </td>
                    <td style="border: black thin solid; border-right: none; padding: 7px; border-top: none; vertical-align: top; text-align: center;">
                         {{$datas->productivity}}
                    </td>
                    <td style="border: black thin solid; padding: 7px; vertical-align: top; border-top: none; border-right: none; text-align: center;"> {{$datas->marks}}</td>
                </tr>
                @endforeach

                <tr>
                    <td colspan="6" style="background: #002060; color: #fff; font-size: 13px; padding: 5px 7px; border-top: none; border-right: none; vertical-align: top; border-left: none;"> Note: For Problem solving please refer Quality Tools matrix </td>
                </tr>
                
        </table>

    </div>

</body>
</html>