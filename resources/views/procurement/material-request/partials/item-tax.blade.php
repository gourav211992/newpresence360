@foreach($taxDetails as $tax_key => $taxDetail)
<input type="hidden" value="{{$taxDetail['id']}}" name="components[{{$rowCount}}][taxes][{{$tax_key+1}}][t_d_id]">
<input type="hidden" value="{{$taxDetail['applicability_type']}}" name="components[{{$rowCount}}][taxes][{{$tax_key+1}}][applicability_type]">
<input type="hidden" value="{{$taxDetail['tax_code']}}" name="components[{{$rowCount}}][taxes][{{$tax_key+1}}][t_code]">
<input type="hidden" value="{{$taxDetail['tax_type']}}" name="components[{{$rowCount}}][taxes][{{$tax_key+1}}][t_type]">
<input type="hidden" value="{{$taxDetail['tax_percentage']}}" name="components[{{$rowCount}}][taxes][{{$tax_key+1}}][t_perc]"/>
<input type="hidden" value="" name="components[{{$rowCount}}][taxes][{{$tax_key+1}}][t_value]" class="form-control mw-100" />
@endforeach
