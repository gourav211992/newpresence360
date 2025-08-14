<input type="hidden" name="components[{{$rowCount}}][inventory_uom_code]" value="{{$row?->inventory_uom_code}}">
<input type="hidden" name="components[{{$rowCount}}][inventory_uom_id]" value="{{$row?->inventory_uom_id}}">
<input type="hidden" name="components[{{$rowCount}}][inventory_uom_qty]" value="{{$row?->inventory_uom_qty}}">
<input type="hidden" name="components[{{$rowCount}}][uom_code]" value="{{$row?->uom_code}}">
<input type="hidden" name="components[{{$rowCount}}][uom_id]" value="{{$row?->uom?->id}}">
<input readonly class="form-control" type="text" name="components[{{$rowCount}}][uom_name]" value="{{ucfirst($row?->uom?->name)}}">