@forelse($splitData as $split)
    <tr>
        <td>{{ $split->partner_name }}</td>
        <td>{{ $split->supply_percentage }}</td>
        <td>
            <a href="javascript:;" data-url="{{ route('prospects.supply-split.remove',['id' => $split->id]) }}" data-request="remove">
                <img src="{{ asset('/app-assets/images/icons/trash.svg') }}">
            </a>
        </td>
    </tr>
@empty
@endforelse