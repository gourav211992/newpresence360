@extends('layouts.app')
@section('content')
<form class="ajax-input-form" method="POST" action="{{ route('logistics.multi-point-fixed.update', $multiPricing->id) }}" data-redirect="{{ url('/logistics/multi-point-pricing') }}">
    @csrf
	@method('PUT')
 <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
				<div class="row">
					<div class="content-header-left col-md-6  mb-2">
						<div class="row breadcrumbs-top">
							<div class="col-12">
								<h2 class="content-header-title float-start mb-0">Edit Fixed Charges</h2>
								<div class="breadcrumb-wrapper">
									<ol class="breadcrumb">
										<li class="breadcrumb-item"><a href="#">Home</a>
										</li>  
										<li class="breadcrumb-item active">Edit</li>


									</ol>
								</div>
							</div>
						</div>
					</div>
					<div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0"> 
                            <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>  
                             <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                    data-url="{{ route('logistics.multi-point-fixed.destroy', $multiPricing->id) }}" 
                                    data-redirect="{{ route('logistics.multi-point-pricing.index') }}"
                                    data-message="Are you sure you want to delete this multi-fixed-pricing ?">
                                <i data-feather="trash-2" class="me-50"></i> Delete
                            </button> 
                            <button type="submit" class="btn btn-primary btn-sm" id="submit-button"><i data-feather="check-circle"></i> Update</button>
					</div>
				</div>
			</div>
            <div class="content-body">
                 
                
				
				<section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">  
							
                            <div class="card">
								 <div class="card-body customernewsection-form"> 
											 
											<div class="row">
												<div class="col-md-12">
                                                    <div class="newheader  border-bottom mb-2 pb-25"> 
														<h4 class="card-title text-theme">Basic Information</h4>
														<p class="card-text">Fill the details</p> 
													</div>
                                                </div> 
                                                
                                                <div class="col-md-9"> 
                                                     
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3 mb-sm-0 mb-1"> 
                                                            <label class="form-label">Source <span class="text-danger">*</span></label>  
                                                        </div>  
  
                                                        <div class="col-md-4 mb-sm-0 mb-1"> 
                                                              <input type="text"name="source_route_name"class="form-control mw-100 route-master-autocomplete"
                                                                        placeholder="Start typing  locations..."
                                                                        data-type="source" value="{{ old('source_route_name', optional($multiPricing->sourceRoute)->name ?? '') }}"/>
                                                        <input type="hidden" name="source_route_id"class="route-master-id" data-type="source"  value="{{ old('source_route_id', $multiPricing->source_route_id ?? '') }}"/>
                                                        </div>
                                                     </div>
                                                    
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3 mb-sm-0 mb-1"> 
                                                            <label class="form-label">Destination <span class="text-danger">*</span></label>  
                                                        </div>  
  
                                                        <div class="col-md-4 mb-sm-0 mb-1">
                                                            <input type="text" name="destination_route_name" class="form-control mw-100 route-master-autocomplete"
                                                                        placeholder="Start typing  locations."
                                                                        data-type="destination" value="{{ old('destination_state_name', optional($multiPricing->destinationRoute)->name ?? '') }}" />
                                                        <input type="hidden" name="destination_route_id" class="route-master-id" data-type="destination" value="{{ old('destination_route_id', $multiPricing->destination_route_id ?? '') }}"/> 
                                                            
                                                        </div>
                                                     </div>
                                                    
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Vehicle Type  <span class="text-danger">*</span></label>  
                                                        </div>  
  
                                                        <div class="col-md-4"> 
                                                             <select name="vehicle_type_id[]" class="form-control mw-100 select2" multiple>
                                                                <option value="">Select Vehicle Type</option>
                                                                @foreach($vehicleTypes as $vehicleType)
                                                                <option value="{{ $vehicleType->id }}" {{ in_array($vehicleType->id, old('vehicle_type_id', json_decode($multiPricing->vehicle_type_id ?? '[]'))) ? 'selected' : '' }}>
                                                                   {{ optional($vehicleType)->name ?? '' }} ({{ optional($vehicleType)->capacity ?? '' }} {{ optional(optional($vehicleType)->unit)->name ?? '' }})
                                                                </option>
                                                            @endforeach
                                                            </select>
                                                        </div> 
                                                     </div>
													
													<div class="row align-items-center mb-1">
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Customer </label>  
                                                        </div>  
  
                                                        <div class="col-md-4"> 
                                                              <input type="text"
                                                                    name="customer_name"
                                                                    class="form-control mw-100 customer-autocomplete"
                                                                    placeholder="Start typing customer..."  value="{{ old('customer_name', optional($multiPricing->customer)->company_name ?? '') }}"/>

                                                                <input type="hidden"
                                                                    name="customer_id"
                                                                    class="customer-id" value="{{ old('customer_id', $multiPricing->customer_id ?? '') }}"/>
                                                        </div> 
                                                     </div>
                                                      
												</div>
                                                
                                                
                                                <div class="col-md-3 border-start">
                                                    <div class="row align-items-center mb-1"> 
                                                        <div class="col-md-12"> 
                                                            <label class="form-label">Status</label>  
                                                        </div> 
                                                        
                                                        <div class="col-md-12"> 
                                                            <div class="demo-inline-spacing">
                                                                 @foreach ($status as $statusOption)
                                                                 <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="status_{{ $statusOption }}" 
                                                                    name="status" 
                                                                    value="{{ $statusOption }}" 
                                                                    class="form-check-input"
                                                                    {{ old('status', $multiPricing->status) === $statusOption ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder" for="status_{{ $statusOption }}">
                                                                    {{ ucfirst($statusOption) }}
                                                                </label>
                                                            </div>
                                                             @endforeach
                                                            </div> 
                                                        </div>
                                                    </div>
                                                
                                                </div>
                                                
                                                <div class="col-md-12">
                                                    <div class="newheader d-flex justify-content-between align-items-end mt-2 border-top pt-2">
                                                        <div class="header-left">
                                                            <h4 class="card-title text-theme">Add Location</h4>
                                                            <p class="card-text">Fill the details</p>
                                                        </div> 
                                                    </div>
                                                    
                                                </div>
                                                
                                                <div class="col-md-8">
                                                    
                                                    <div class="table-responsive-md">
                                                         <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable"> 
                                                            <thead>
                                                                 <tr>
                                                                    <th>#</th>
                                                                    <th>Location<span class="text-danger">*</span></th>
                                                                    <th>Rate <span class="text-danger">*</span></th>
                                                                    <th>Action</th> 
                                                                  </tr>
                                                                </thead>
																<tbody id="location-rows">
																	@php $rowIndex = 0; @endphp
																	@forelse($multiPricing->locations as $location)
																		<tr>
																			<td>{{ $loop->iteration }}</td>
																			<td>
																				<input type="hidden" name="multi_fixed_pricing[{{ $rowIndex }}][id]" value="{{ old("multi_fixed_pricing.$rowIndex.id", $location->id ?? '') }}">
																				<input type="text" name="multi_fixed_pricing[{{ $rowIndex }}][location_route_name]" class="form-control mw-100 route-master-autocomplete" placeholder="Start typing location..." data-type="source" value="{{ old("multi_fixed_pricing.$rowIndex.location_route_name", optional($location->route)->name) }}">
																				<input type="hidden" name="multi_fixed_pricing[{{ $rowIndex }}][location_route_id]" class="route-master-id" data-type="source" value="{{ old("multi_fixed_pricing.$rowIndex.location_route_id", $location->location_route_id ?? '') }}">
																			</td>
																			
																			<td>
																				<input type="text" name="multi_fixed_pricing[{{ $rowIndex }}][amount]" class="form-control mw-100 amount" value="{{ old("multi_fixed_pricing.$rowIndex.amount", $location->amount ?? '') }}">
																			</td>
																			<td>
																				@if ($loop->first)
																					<a href="javascript:void(0);" class="add-row text-primary"><i data-feather="plus-square"></i></a>
																				@else
																					<a href="javascript:void(0);" class="delete-row text-danger"><i data-feather="trash-2"></i></a>
																				@endif
																			</td>
																		</tr>
																		@php $rowIndex++; @endphp
																	@empty
																		<tr>
																			<td>1</td>
																			<td>
																				<input type="hidden" name="multi_fixed_pricing[0][id]" value="">
																				 <input type="text"
                                                                                    name="multi_fixed_pricing[0][location_route_name]"
                                                                                    class="form-control mw-100 route-master-autocomplete"
                                                                                    placeholder="Start typing  locations..."
                                                                                    data-type="source" />
                                                                                <input type="hidden"
                                                                                    name="multi_fixed_pricing[0][location_route_id]"
                                                                                    class="route-master-id"
                                                                                    data-type="source" />
																			</td>
																			<td>
																				<input type="text" name="multi_fixed_pricing[0][amount]" class="form-control mw-100 amount" placeholder="Enter Amount">
																			</td>
																			<td>
																				<a href="javascript:void(0);" class="add-row text-primary"><i data-feather="plus-square"></i></a>
																			</td>
																		</tr>
																		@php $rowIndex = 1; @endphp
																	@endforelse
																</tbody>

                                                         </table>
                                                    </div>
                                                
                                                </div>
											</div>
											  
  
 
								
								</div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal to add new record -->
                     
                </section>
                 

            </div>
        </div>
    </div>
    <!-- END: Content-->
</form>
@endsection
@section('scripts')
<script>

  const routeMasters = [
    @foreach($routeMasters as $rm)
        {
            label: "{{ $rm->name }}",
            value: "{{ $rm->name }}",
            id: {{ $rm->id }}
        },
    @endforeach
];

$(document).on('focus', '.route-master-autocomplete', function () {
    const $input = $(this);
    const dataType = $input.data('type');

    if (!$input.data('ui-autocomplete')) {
        $input.autocomplete({
            source: routeMasters,
            minLength: 0,
            select: function (event, ui) {
                $input.val(ui.item.label);

                // Look for the nearest hidden input with same data-type in same td or parent
                const $hidden = $input
                    .closest('td, .col-md-4')
                    .find('.route-master-id[data-type="' + dataType + '"]');

                $hidden.val(ui.item.id);
                return false;
            },
            change: function (event, ui) {
                const text = $input.val().trim();
                const $hidden = $input
                    .closest('td, .col-md-4')
                    .find('.route-master-id[data-type="' + dataType + '"]');

                if (!ui.item) {
                    const match = routeMasters.find(item => item.label === text);
                    if (match) {
                        $hidden.val(match.id);
                    } else {
                        $hidden.val('');
                    }
                }
            }
        }).focus(function () {
            $(this).autocomplete('search', '');
        });
    }

    // Pre-bind existing value (for edit)
    const label = $input.val().trim();
    if (label !== '') {
        const match = routeMasters.find(item => item.label === label);
        const $hidden = $input
            .closest('td, .col-md-4')
            .find('.route-master-id[data-type="' + dataType + '"]');

        if (match) {
            $hidden.val(match.id);
        }
    }
});



//add new row
let multiFixedRowIndex = {{ $rowIndex ?? 1 }};

$(document).on('click', '.add-row', function () {
    const $tbody = $('#location-rows');
    let incomplete = false;

    $tbody.find('tr').each(function () {
        const $row = $(this);
        const sourceInput = $row.find('.route-master-autocomplete[data-type="source"]');
        const amountInput = $row.find('input[name*="[amount]"]');

        if (
            (sourceInput.length && sourceInput.val().trim() === '') ||
            (amountInput.length && amountInput.val().trim() === '')
        ) {
            incomplete = true;
            return false; 
        }
    });

    if (incomplete) {
        Swal.fire({
            icon: 'warning',
            title: 'Incomplete Row',
            text: 'Please fill all required fields in the existing row(s) before adding a new one.',
            confirmButtonText: 'OK'
        });
        return;
    }

    const currentIndex = multiFixedRowIndex++; 

    const newRow = `
        <tr>
            <td>${currentIndex + 1}</td>
            <td>
                <input type="hidden" name="multi_fixed_pricing[${currentIndex}][id]" value="">
                <input type="text"
                    name="multi_fixed_pricing[${currentIndex}][location_route_name]"
                    class="form-control mw-100 route-master-autocomplete"
                    placeholder="Start typing locations..."
                    data-type="source" />
                <input type="hidden"
                    name="multi_fixed_pricing[${currentIndex}][location_route_id]"
                    class="route-master-id"
                    data-type="source" />
            </td>
            <td>
                <input type="text"
                    name="multi_fixed_pricing[${currentIndex}][amount]"
                    class="form-control mw-100 amount"
                    placeholder="Enter Amount" />
            </td>
            <td>
                <a href="javascript:void(0);" class="delete-row text-danger">
                    <i data-feather="trash-2"></i>
                </a>
            </td>
        </tr>
    `;

    $tbody.append(newRow);
    feather.replace(); // refresh icons
});


$(document).on('click', '.delete-row', function () {
    if ($('#location-rows tr').length > 1) {
        $(this).closest('tr').remove();
    } else {
        Swal.fire('Warning', 'At least one row is required.', 'info');
    }
});
</script>


<script>
    //customer autocomplete search code here
const customerList = [
    @foreach($customers as $customer)
        {
            label: "{{ addslashes($customer->company_name) }}",
            value: "{{ addslashes($customer->company_name) }}",
            id: {{ $customer->id }}
        },
    @endforeach
];

 $(document).on('focus', '.customer-autocomplete', function () {
    const $input = $(this);

    if (!$input.data('ui-autocomplete')) {
        $input.autocomplete({
            source: customerList,
            minLength: 0,
            select: function (event, ui) {
                $input.val(ui.item.label);

                const $row = $input.closest('tr');
                if ($row.length) {
                    $row.find('.customer-id').val(ui.item.id);
                } else {
                    $('.customer-id').val(ui.item.id);
                }

                return false;
            }
        }).focus(function () {
            $(this).autocomplete('search', '');
        });
    }

    // 🟡 Clear customer-id when input is cleared manually
    $input.on('input', function () {
        const $row = $input.closest('tr');
        if ($input.val().trim() === '') {
            if ($row.length) {
                $row.find('.customer-id').val('');
            } else {
                $('.customer-id').val('');
            }
        }
    });
});

</script>

@endsection