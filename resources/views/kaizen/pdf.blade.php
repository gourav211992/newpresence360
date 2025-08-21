<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kaizen Implementation Sheet</title>
    <style>
        @page {
            margin: 20px;
        }

        body {
            margin: 0;
            padding: 0;
        }

        td,
        th {
            word-break: break-word;
        }

        img {
            max-width: 100%;
            height: auto;
        }
    </style>

</head>

<body>
    <div
        style="margin: 0 auto; width:700px; border-collapse:collapse; font-family:Arial, sans-serif; font-size: 12px; border: 1px solid #000000; color: #000000;">
        <table style="width:100%;" cellspacing="0" cellpadding="0">
            <tr>
                <td colspan="2" style="text-align: left;"><img style="height: 40px;"
                        src="{{ public_path('/img/logo-1.svg') }}" alt=""></td>
                <td colspan="3" style="text-align:center; font-weight:bold; font-size:18px;">KAIZEN IMPLEMENTATION
                    SHEET
                </td>
                <td colspan="3" style="text-align: right;"><img style="height: 40px;"
                        src="{{ public_path('/img/sheelafoam-img.png') }}" alt=""></td>
            </tr>
            <tr>
                <td
                    style="border: 1px solid #000000; border-left: none; text-align:center; padding: 10px; font-weight: 600;">
                    Department</td>
                <td style="border: 1px solid #000000; text-align:center; padding: 10px; font-weight: 600;">
                    Document type</td>
                <td style="border: 1px solid #000000; text-align:center; padding: 10px; font-weight: 600;">
                    Document number</td>
                <td style="border: 1px solid #000000; text-align:center; padding: 10px; font-weight: 600;">First
                    issue date</td>
                <td style="border: 1px solid #000000; text-align:center; padding: 10px; font-weight: 600;">
                    Revision number</td>
                <td style="border: 1px solid #000000; text-align:center; padding: 10px; font-weight: 600;">
                    Revision date</td>
                <td colspan="2" style="border: 1px solid #000000; text-align:center; padding: 10px; font-weight: 600;">Page
                    No.</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000000; text-align:center; padding: 10px; border-left: none;">
                    {{ isset($kaizen->department->name) ? $kaizen->department->name : '-' }}</td>
                <td style="border: 1px solid #000000; text-align:center; padding: 10px;">Format</td>
                <td style="border: 1px solid #000000; text-align:center; padding: 10px;">-</td>
                <td style="border: 1px solid #000000; text-align:center; padding: 10px;">-</td>
                <td style="border: 1px solid #000000; text-align:center; padding: 10px;">1</td>
                <td style="border: 1px solid #000000; text-align:center; padding: 10px;">-</td>
                <td colspan="2" style="border: 1px solid #000000; text-align:center; padding: 10px;">1 of 1</td>
            </tr>
            <tr>
                <td colspan="3"
                    style="border: 1px solid #000000; text-align: center; padding: 10px; border-left: none; "><span
                        style="font-weight: 600;">KAIZEN NUMBER:</span> {{ @$kaizen->kaizen_no }}</td>
                <td></td>
                <td colspan="4" style="border: 1px solid #000000; text-align: center; padding: 10px;"><span
                        style="font-weight: 600;">KAIZEN DATE:</span>
                    {{ @$kaizen->kaizen_date ? App\Helpers\CommonHelper::dateFormat2(@$kaizen->kaizen_date) : '' }}</td>
            </tr>
            <tr>
                <td colspan="3"
                    style="border: 1px solid #000000; text-align: center; padding: 10px; border-left: none;"><span
                        style="font-weight: 600;">DEPARTMENT:</span>
                    {{ isset($kaizen->department->name) ? $kaizen->department->name : '-' }}</td>
                <td></td>
                <td colspan="4" style="border: 1px solid #000000; text-align: center; padding: 10px;"><span
                        style="font-weight: 600;">KAIZEN TEAM:</span>
                    @php
                        $kaizenTeam = $kaizen->kaizenTeam->pluck('name')->toArray();
                      
                    @endphp
                    {{ implode(',', $kaizenTeam) }}</td>
            </tr>
            <tr>
                <td colspan="3"
                    style="border-left: none; border: 1px solid #000000; background-color: #fc1900; text-align: center; font-weight:700; padding: 10px; color: #ffffff;">
                    BEFORE KAIZEN</td>
                <td></td>
                <td colspan="4"
                    style="border: 1px solid #000000; background-color: #008000; text-align: center; font-weight:700; padding: 10px; color: #ffffff;">
                    AFTER KAIZEN</td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: center; border: 1px solid #000000; border-left: none;">
                    @foreach ($attachments['before kaizen'] as $file)
                        @php
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                             
                        @endphp

                        @if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                            <img src="{{ asset($file) }}" alt="Before Kaizen"
                                style="max-height: 150px; max-width: 100%; margin-bottom:10px;">
                        @elseif ($ext === 'pdf')
                            <a href="{{ asset($file) }}" target="_blank"
                                style="display: block; margin-bottom: 10px; color: blue; text-decoration: underline;">
                                <img src="{{ asset('app-assets/images/icons/pdf.png') }}" />
                            </a>
                        @endif
                    @endforeach
                </td>
                <td></td>
                <td colspan="4" style="text-align: center; border: 1px solid #000000;">
                    @foreach ($attachments['after kaizen'] as $file)
                        @php
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                          
                        @endphp

                        @if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                            <img src="{{ asset($file) }}" alt="After Kaizen"
                                style="max-height: 150px; max-width: 100%; margin-bottom:10px;">
                        @elseif ($ext === 'pdf')
                            <a href="{{ asset($file) }}" target="_blank"
                                style="display: block; margin-bottom: 10px; color: blue; text-decoration: underline;">
                                <img src="{{ asset('app-assets/images/icons/pdf.png') }}" />
                            </a>
                        @endif
                    @endforeach
                </td>
            </tr>
            <tr>
                <td colspan="2"
                    style="border-left: none; border: 1px solid #000000; padding: 10px; background:#fc1900; color: #ffffff; text-align:center; font-weight:700;">
                    PROBLEM</td>
                <td colspan="3"
                    style=" border: 1px solid #000000; padding: 10px; background:#f1c40f; color:#000000; text-align:center; font-weight:700;">
                    ANALYSIS &
                    COUNTERMEASURE</td>
                <td colspan="3"
                    style="border: 1px solid #000000; padding: 10px; background:#008000; color:white; text-align:center; font-weight:700;">
                    EFFECT
                    & FINANCIAL
                    BENEFITS</td>
            </tr>
            <tr>
                <td colspan="2"
                    style="vertical-align:top; line-height: 18px; padding: 10px; border: 1px solid #000000;">
                    {{ @$kaizen->problem }}
                </td>
                <td colspan="3"
                    style="vertical-align:top; line-height: 25px; padding: 10px; border: 1px solid #000000;">
                    {{ @$kaizen->counter_measure }}
                </td>
                <td colspan="3"
                    style="vertical-align:top; line-height: 25px; padding: 10px; border: 1px solid #000000;">
                    {{ @$kaizen->benefits }}
                </td>
            </tr>
            <tr>
                <td style="padding: 5px; text-align: center; font-weight: 700; border: 1px solid #000000; border-left: none;">
                    Improvement In:
                </td>
                <td style="border: 1px solid #000000; padding: 10px; background-color: {{ \App\Helpers\CommonHelper::impactKaizenBg($kaizen->productivity) }}; color: #000000; text-align: center;">
                    P</td>
                <td style="border: 1px solid #000000; padding: 10px; background-color: {{ \App\Helpers\CommonHelper::impactKaizenBg($kaizen->quality) }}; color: #000000; text-align: center;">
                    Q</td>
                <td style="border: 1px solid #000000; padding: 10px; background-color: {{ \App\Helpers\CommonHelper::impactKaizenBg($kaizen->cost) }}; color: #000000; text-align: center;">
                    C
                </td>
                <td style="border: 1px solid #000000; padding: 10px;  background-color: {{ \App\Helpers\CommonHelper::impactKaizenBg($kaizen->delivery) }}; color: #000000; text-align: center;">
                    D
                </td>
                <td style="border: 1px solid #000000; padding: 10px;  background-color: {{ \App\Helpers\CommonHelper::impactKaizenBg($kaizen->safety) }}; color: #000000; text-align: center;">
                    S
                </td>
                <td style="border: 1px solid #000000; padding: 10px; background-color: {{ \App\Helpers\CommonHelper::impactKaizenBg($kaizen->moral) }}; color: #000000; text-align: center;">
                    M
                </td>
                <td style="border-right: 1px solid #000000; padding: 10px; background-color: {{ \App\Helpers\CommonHelper::impactKaizenBg($kaizen->innovation) }}; color: #000000; text-align: center;">
                    I
                </td>
            </tr>
            <tr>
                
                <td
                    style="padding:5px; text-align: center; font-weight: 700; border: 1px solid #000000; border-left: none;">
                    (TICK AS APPLICABLE)
                </td>
               
                <td style="border:1px solid #000;padding:10px;background-color:{{ \App\Helpers\CommonHelper::impactKaizenBg($kaizen->productivity) }};color: #000000; text-align: center;"></td>

                <td
                    style="border: 1px solid #000000; padding: 10px; background-color: {{ \App\Helpers\CommonHelper::impactKaizenBg(@$kaizen->quality) }}; color: #000000; text-align: center;">
                </td>
                <td
                    style="border: 1px solid #000000; padding: 10px; background-color: {{ \App\Helpers\CommonHelper::impactKaizenBg(@$kaizen->cost) }}; color: #000000; text-align: center;">
                </td>
                <td
                    style="border: 1px solid #000000; padding: 10px;  background-color: {{ \App\Helpers\CommonHelper::impactKaizenBg(@$kaizen->delivery) }}; color: #000000; text-align: center;">
                </td>
                 <td
                    style="border: 1px solid #000000; padding: 10px;  background-color: {{ \App\Helpers\CommonHelper::impactKaizenBg(@$kaizen->safety) }}; color: #000000; text-align: center;">
                </td>
                
                <td
                    style="border: 1px solid #000000; padding: 10px; background-color: {{ \App\Helpers\CommonHelper::impactKaizenBg(@$kaizen->moral) }}; color: #000000; text-align: center;">
                </td>
                <td
                    style="border: 1px solid #000000; padding: 10px; background-color: {{ \App\Helpers\CommonHelper::impactKaizenBg(@$kaizen->innovation) }}; color: #000000; text-align: center;">
                </td>
            </tr>
            
            <tr>
                <td colspan="4" style="padding:10px; color: #000000; border: 1px solid #000000; border-left: none;">
                    Rating/Evaluation
                </td>
                <td colspan="4" style="padding:10px; color: #000000;  border: 1px solid #000000;">
                    <b>{{ $kaizen->score }}/{{ $kaizen->total_score }}</b>
                </td>
            </tr>

            <tr>
                <td colspan="2" style="padding:10px; color: #000000; border: 1px solid #000000; border-left: none;">
                    <b>Occurence</b>
                </td>
                <td colspan="2" style="padding:10px; color: #000000;  border: 1px solid #000000;">
                    <b>Cost Saving</b>
                </td>
                <td colspan="2" style="padding:10px; color: #000000;  border: 1px solid #000000;">
                    <b>Approved By</b>
                </td>
                <td colspan="2"
                    style="padding:10px; color: #000000;  border: 1px solid #000000; border-right: none;">
                    <b>HOU</b>
                </td>
            </tr>
            <tr>
                <td colspan="2"
                    style="padding:10px; color: #000000; border: 1px solid #000000; border-left: none; border-bottom: none;">
                    {{ ucfirst(@$kaizen->occurence) }}
                </td>
                <td colspan="2"
                    style="padding:10px; color: #000000;  border: 1px solid #000000; border-bottom: none;">
                    {{ @$kaizen->cost_saving_amt }}
                </td>
                <td colspan="2"
                    style="padding:10px; color: #000000;  border: 1px solid #000000; border-bottom: none;">
                    {{ isset($kaizen->approver->name) ? $kaizen->approver->name : '-' }}
                </td>
                <td colspan="2"
                    style="padding:10px; color: #000000;  border: 1px solid #000000; border-bottom: none; border-right: none;">
                    -
                </td>
            </tr>
                       
        </table>
    </div>
</body>
</html>

