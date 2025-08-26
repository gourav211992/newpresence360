@forelse($pickupSchedules as $pickupSchedule)
    <tr data-pickup-schedule-id="{{ $pickupSchedule->id }}">
        <td>
            <div class="form-check form-check-inline me-0">
                <input class="form-check-input analyze_row" type="checkbox" name="pick_up_schdule_id" value="{{ $pickupSchedule->id }}">
            </div>
        </td>
        <td>{{ $pickupSchedule->getFormattedDate('document_date') }}</td>
        <td>{{ $pickupSchedule->document_number }}</td>
        <td>{{ $pickupSchedule->vehicle_no }}</td>
        <td>{{ $pickupSchedule->trip_no }}</td>
        <td>{{ $pickupSchedule->champ }}</td>
        <td class="text-end">{{ number_format($pickupSchedule->total_item_count, 2) }}</td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center">No record found!</td>
    </tr>
@endforelse