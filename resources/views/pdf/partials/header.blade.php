<table style="width: 100%; margin-bottom: 10px;" cellspacing="0" cellpadding="0">
    <tr>
        <td style="text-align: left; width: 33%;">
            @if (isset($orgLogo) && $orgLogo)
                <img src="{!! $orgLogo !!}" alt="" height="50px" />
            @else
                <img src="{{ $imagePath }}" height="50px" alt="">
            @endif
        </td>
        <td style="text-align: center; width: 34%; font-weight: bold; font-size: 22px;">
            {{ $moduleTitle ?? 'Document' }}
        </td>
        <td style="width: 33%;"></td>
    </tr>
</table>