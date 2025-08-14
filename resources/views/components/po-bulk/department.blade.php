<input type="hidden" name="components[{{$rowCount}}][department_id]" value="{{$row?->pi?->department_id}}">
<input type="hidden" name="components[{{$rowCount}}][sub_store_id]" value="{{$row?->pi?->sub_store_id}}">
<input type="text" readonly class="form-control" name="components[{{$rowCount}}][department_name]" value="{{ucfirst($row?->pi?->sub_store?->name ?? '')}}">