@php
$class = $selectedAddress->id ? 'disabled-input' : '';
@endphp
<div class="modal-content">
	<div class="modal-header p-0 bg-transparent">
		<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	</div>
	<div class="modal-body px-sm-2 mx-50 pb-2">
		<h1 class="text-center mb-1" id="addresEditTitle">Edit Address</h1>
		<p class="text-center">Enter the details below.</p>
		<div class="row mt-2">
			<div class="col-md-12 mb-1">
				<input type="hidden" name="address_type" id="address_type">
				<input type="hidden" name="hidden_vendor_id" id="hidden_vendor_id">
				<label class="form-label">Select Address <span class="text-danger">*</span></label>
				<select class="select2 form-select" name="address_id"> 
					@if($type == 'delivery_address')
						<option value="">Add New</option>
					@endif
					@foreach($addresses as $addr)
					<option value="{{$addr->id}}" {{$selectedAddress?->id == $addr->id ? 'selected' : ''}}>{{$addr->display_address}}</option>
					@endforeach
				</select> 
			</div>
			<div class="col-md-6 mb-1">
				<label class="form-label">Country <span class="text-danger">*</span></label>
				<select class="select2 form-select {{$class}}" name="country_id" id="country_id">
					<option id="{{$selectedAddress?->country?->id}}">{{$selectedAddress?->country?->name}}</option>
				</select>
			</div>
			<div class="col-md-6 mb-1">
				<label class="form-label">State <span class="text-danger">*</span></label>
				<select class="select2 form-select {{$class}}" name="state_id" id="state_id">
					<option value="{{$selectedAddress?->state?->id}}">{{$selectedAddress?->state?->name}}</option> 
				</select>
			</div>
			<div class="col-md-6 mb-1">
				<label class="form-label">City <span class="text-danger">*</span></label>
				<select class="select2 form-select {{$class}}" name="city_id" id="city_id">
					<option value="{{$selectedAddress?->city?->id}}">{{$selectedAddress?->city?->name}}</option> 
				</select>
			</div>
			<div class="col-md-6 mb-1">
				<label class="form-label w-100">Pincode <span class="text-danger">*</span></label>
				<input type="text" id="pincode" name="pincode" class="form-control {{$class}}" value="{{$selectedAddress?->pincode}}" placeholder="Enter Pincode" />
			</div> 
			<div class="col-md-12 mb-1">
				<label class="form-label">Address <span class="text-danger">*</span></label>
				<textarea maxlength="250" id="address" name="address" class="form-control {{$class}}" placeholder="Enter Address">{!!$selectedAddress?->address !!}</textarea>
			</div> 
		</div>
	</div>
	<div class="modal-footer justify-content-center">  
		<button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button> 
		<button type="button" class="btn btn-primary submitAddress">Submit</button>
	</div>
</div>