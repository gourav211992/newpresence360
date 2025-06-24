@extends('layouts.app')
@section('content')
<form class="ajax-input-form" method="POST" action="{{ route('logistics.multi-point-fixed.store') }}" data-redirect="{{ url('/logistics/multi-point-pricing') }}">
    @csrf
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
								<h2 class="content-header-title float-start mb-0">New Fixed Charges</h2>
								<div class="breadcrumb-wrapper">
									<ol class="breadcrumb">
										<li class="breadcrumb-item"><a href="index.html">Home</a>
										</li>  
										<li class="breadcrumb-item active">Add New</li>


									</ol>
								</div>
							</div>
						</div>
					</div>
					<div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0"> 
                            <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>  
                            <button type="submit" class="btn btn-primary btn-sm" id="submit-button"><i data-feather="check-circle"></i> Create</button>
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
  
                                                        <div class="col-md-3 mb-sm-0 mb-1"> 
                                                        <input type="text"
                                                                name="source_state_name"
                                                                class="form-control mw-100 state-autocomplete"
                                                                placeholder="Start typing source state..."
                                                                data-type="source" />
                                                            <input type="hidden"
                                                                name="source_state_id"
                                                                class="state-id"
                                                                data-type="source" />
                                                        
                                                        </div>
														<div class="col-md-3"> 
                                                            <input type="text"
                                                                name="source_city_name"
                                                                class="form-control mw-100 city-autocomplete"
                                                                placeholder="Start typing source city..."
                                                                data-type="source" />
                                                            <input type="hidden"
                                                                name="source_city_id"
                                                                class="city-id"
                                                                data-type="source" />
                                                          
                                                        </div>
                                                     </div>
                                                    
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3 mb-sm-0 mb-1"> 
                                                            <label class="form-label">Destination <span class="text-danger">*</span></label>  
                                                        </div>  
  
                                                        <div class="col-md-3 mb-sm-0 mb-1"> 
                                                             <input type="text"
                                                                name="destination_state_name"
                                                                class="form-control mw-100 state-autocomplete"
                                                                placeholder="Start typing destination state..."
                                                                data-type="destination" />
                                                            <input type="hidden"
                                                                name="destination_state_id"
                                                                class="state-id"
                                                                data-type="destination" />
                                                            
                                                        </div>
														<div class="col-md-3"> 
                                                               <input type="text"
                                                                name="destination_city_name"
                                                                class="form-control mw-100 city-autocomplete"
                                                                placeholder="Start typing destination city..."
                                                                data-type="destination" />
                                                            <input type="hidden"
                                                                name="destination_city_id"
                                                                class="city-id"
                                                                data-type="destination" />
                                                        </div>
                                                     </div>
                                                    
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Vehicle Type  <span class="text-danger">*</span></label>  
                                                        </div>  
  
                                                        <div class="col-md-5"> 
                                                             <select name="vehicle_type_id[]" class="form-control mw-100 select2" multiple>
                                                                <option value="">Select Vehicle Type</option>
                                                                @foreach($vehicleTypes as $vehicleType)
                                                                    <option value="{{ $vehicleType->id }}" >{{ $vehicleType->name }} ({{ $vehicleType->capacity }} {{ $vehicleType->unit->name }})</option>
                                                                @endforeach
                                                            </select>
                                                        </div> 
                                                     </div>
													
													<div class="row align-items-center mb-1">
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Customer </label>  
                                                        </div>  
  
                                                        <div class="col-md-5"> 
                                                              <input type="text"
                                                                    name="customer_name"
                                                                    class="form-control mw-100 customer-autocomplete"
                                                                    placeholder="Start typing customer..." />

                                                                <input type="hidden"
                                                                    name="customer_id"
                                                                    class="customer-id" />
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
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="customColorRadio3" name="customColorRadio3" class="form-check-input" checked="">
                                                                    <label class="form-check-label fw-bolder" for="customColorRadio3">Active</label>
                                                                </div> 
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="customColorRadio4" name="customColorRadio3" class="form-check-input">
                                                                    <label class="form-check-label fw-bolder" for="customColorRadio4">Inactive</label>
                                                                </div> 
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
                                                                    <th width="200">State<span class="text-danger">*</span></th>
                                                                    <th width="200">City <span class="text-danger">*</span></th>
                                                                    <th width="200">Rate <span class="text-danger">*</span></th>
                                                                    <th>Action</th> 
                                                                  </tr>
                                                                </thead>
                                                                <tbody id="location-rows"> 
                                                                     <tr>
                                                                        <td>1</td>
                                                                        <td>
                                                                        <input type="text"
                                                                        name="multi_fixed_pricing[0][location_state_name]"
                                                                        class="form-control mw-100 state-autocomplete"
                                                                        placeholder="Start typing  state..."
                                                                        data-type="location" />
                                                                    <input type="hidden"
                                                                        name="multi_fixed_pricing[0][location_state_id]"
                                                                        class="state-id"
                                                                        data-type="location" />
                                                                      </td>
                                                                        <td>
                                                                     <input type="text"
                                                                        name="multi_fixed_pricing[0][location_city_name]"
                                                                        class="form-control mw-100 city-autocomplete"
                                                                        placeholder="Start typing  city..."
                                                                        data-type="location" />
                                                                    <input type="hidden"
                                                                        name="multi_fixed_pricing[0][location_city_id]"
                                                                        class="city-id"
                                                                        data-type="location" />
                                                                      </td>
                                                                        <td>
                                                                            <input type="text" name="multi_fixed_pricing[0][amount]" class="form-control mw-100 amount">
                                                                        </td>

                                                                         <td><a href="javascript:void(0);" class="add-row text-primary"><i data-feather="plus-square"></i></a></td> 
                                                                      </tr>
                                                                      
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
const states = [
    @foreach($states as $state)
        { label: "{{ $state->name }}", value: "{{ $state->name }}", id: {{ $state->id }} },
    @endforeach
];

const cityCache = {}; 

$(document).ready(function () {

    // State Autocomplete
  $(document).on('focus', '.state-autocomplete', function () {
    const $input = $(this);

    if (!$input.data('ui-autocomplete')) {
        $input.autocomplete({
            source: states,
            minLength: 0,
            select: function (event, ui) {
                $input.val(ui.item.label);

                // Detect if inside a table row
                const $row = $input.closest('tr');

                if ($row.length) {
                    // Dynamic table row: update only within this row
                    $row.find('.state-id').val(ui.item.id);
                    $row.find('.city-autocomplete').val('');
                    $row.find('.city-id').val('');

                    const $cityInput = $row.find('.city-autocomplete');
                    loadCities(ui.item.id, null, function () {
                        applyCityAutocomplete(ui.item.id, null, $cityInput);
                    });

                } else {
                    // Static section (source/destination): use data-type
                    const type = $input.data('type');
                    $(`.state-id[data-type="${type}"]`).val(ui.item.id);
                    $(`.city-autocomplete[data-type="${type}"]`).val('');
                    $(`.city-id[data-type="${type}"]`).val('');

                    const $cityInput = $(`.city-autocomplete[data-type="${type}"]`);
                    loadCities(ui.item.id, type, function () {
                        applyCityAutocomplete(ui.item.id, type, $cityInput);
                    });
                }

                return false;
            }
        }).focus(function () {
            $(this).autocomplete("search", "");
        });
    }
});



    // City Autocomplete (dynamically binds based on selected state)
  $(document).on('focus', '.city-autocomplete', function () {
    const $input = $(this);
    const $row = $input.closest('tr');

    let stateId;

    if ($row.length) {
        // From dynamic row
        stateId = $row.find('.state-id').val();
    } else {
        // From static field using data-type
        const type = $input.data('type');
        stateId = $(`.state-id[data-type="${type}"]`).val();
    }

    if (!stateId) {
        $input.autocomplete({ source: [] });
        return;
    }

    if (cityCache[stateId]) {
        applyCityAutocomplete(stateId, null, $input);
    } else {
        loadCities(stateId, null, function () {
            applyCityAutocomplete(stateId, null, $input);
        });
    }
});


});

// Load cities via AJAX
function loadCities(stateId, type, callback = null) {
    $.ajax({
        url: "{{ route('logistics.multi-point-fixed.get-cities-by-state') }}",
        method: "GET",
        data: { state_id: stateId },
        success: function (response) {
            if (response.status) {
                cityCache[stateId] = response.data.map(city => ({
                    label: city.name,
                    value: city.name,
                    id: city.id
                }));
                if (callback) callback();
            }
        },
        error: function () {
            alert('Error loading cities');
        }
    });
}

function applyCityAutocomplete(stateId, type, $input) {
    const cities = cityCache[stateId] || [];

    $input.autocomplete({
        source: cities,
        minLength: 0,
        select: function (event, ui) {
            $input.val(ui.item.label);
            const $row = $input.closest('tr');

            if ($row.length) {
                $row.find('.city-id').val(ui.item.id);
            } else {
                const dataType = $input.data('type');
                $(`.city-id[data-type="${dataType}"]`).val(ui.item.id);
            }

            return false;
        }
    }).focus(function () {
        $(this).autocomplete('search', '');
    });
}



//add new row

$(document).on('click', '.add-row', function () {
    const $lastRow = $('#location-rows tr:last');
    const stateId = $lastRow.find('.state-id').val();
    const cityId = $lastRow.find('.city-id').val();
    const amount = $lastRow.find('.amount').val();

    if (!stateId || !cityId || !amount || parseFloat(amount) <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Data',
            text: 'Please fill state, city, and amount before adding a new row.'
        });
        return;
    }

    const rowIndex = $('#location-rows tr').length;

    const newRow = `
        <tr>
            <td>${rowIndex + 1}</td>
            <td>
                <input type="text" name="multi_fixed_pricing[${rowIndex}][location_state_name]" class="form-control mw-100 state-autocomplete" placeholder="Start typing state..." />
                <input type="hidden" name="multi_fixed_pricing[${rowIndex}][location_state_id]" class="state-id" />
            </td>
            <td>
                <input type="text" name="multi_fixed_pricing[${rowIndex}][location_city_name]" class="form-control mw-100 city-autocomplete" placeholder="Start typing city..." />
                <input type="hidden" name="multi_fixed_pricing[${rowIndex}][location_city_id]" class="city-id" />
            </td>
            <td><input type="text" name="multi_fixed_pricing[${rowIndex}][amount]"  class="form-control mw-100 amount" placeholder="Enter Amount"/></td>
            <td><a href="javascript:void(0);" class="delete-row text-danger"><i data-feather="trash-2"></i></a></td>
        </tr>
    `;

    $('#location-rows').append(newRow);
    feather.replace();
});


$(document).on('click', '.delete-row', function () {
    if ($('#location-rows tr').length > 1) {
        $(this).closest('tr').remove();
    } else {
        Swal.fire('Warning', 'At least one row is required.', 'info');
    }
});

$(document).ready(function () {
    bindAutocomplete($('#location-rows tr'));
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
});

</script>

@endsection