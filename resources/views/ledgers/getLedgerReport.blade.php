@php
    use App\Helpers\CurrencyHelper;
@endphp

@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Search Ledger</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                                    <li class="breadcrumb-item"><a href="index.html">Finance</a></li>
                                    <li class="breadcrumb-item active">Ledger View</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
				<div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
						<button class="btn btn-primary btn-sm mb-50 mb-sm-0" onclick="exportLedgerReport()"><i data-feather="download-cloud"></i> Export</button>
						{{-- <button class="btn btn-primary btn-sm mb-50 mb-sm-0" onclick="window.print()"><i data-feather="printer"></i> Print</button> --}}
                    </div>
                </div>
            </div>
            <div class="content-body">
                 <div class="row">
					 <div class="col-md-12">
					 	<div class="card">
                            <div class="row">
                                <div class="col-md-12 bg-light border-bottom po-reportfileterBox">
									<form action="#" method="POST" id="form">
										@csrf
										<div class="customernewsection-form poreportlistview p-1">
											<div class="row">
												<div class="col-md-2 mb-2">
													<div class="mb-1 mb-sm-0">
														<label class="form-label">Company <span class="text-danger">*</span></label>
														<select class="form-select select2 companySelect" required id="company_id">
															<option value="" selected disabled>Select Company</option>
															@foreach ($companies as $company)
																<option value="{{ $company->id }}">{{ $company->name }}</option>
															@endforeach
														</select>
													</div>
												</div>
												<div class="col-md-2 mb-2">
													<div class="mb-1 mb-sm-0">
														<label class="form-label">Organization <span class="text-danger">*</span></label>
														<select class="form-select select2" id="organization_id" required>
															<option value="" selected disabled>Select Organization</option>
														</select>
													</div>
												</div>
                                                <div class="col-md-2 mb-2">
													<div class="mb-1 mb-sm-0">
														<label class="form-label">Location</label>
														<select class="form-select select2" id="location_id">
															<option value="" selected disabled>Select Location</option>
														</select>
													</div>
												</div>
                                                <div class="col-md-2 mb-2 cost_group">
													<div class="mb-1 mb-sm-0">
														<label class="form-label">Cost Group <span class="text-danger">*</span></label>
														<select class="form-select select2" id="cost_group_id">
															<option value="" selected disabled>Select Cost Group</option>
														</select>
													</div>
												</div>
                                                <div class="col-md-2 mb-2 cost_center">
													<div class="mb-1 mb-sm-0">
														<label class="form-label">Cost Center</label>
														<select class="form-select select2" id="cost_center_id">
															<option value="" selected disabled>Select Cost Center</option>
														</select>
													</div>
												</div>
												<div class="col-md-2 mb-2">
													<div class="mb-1 mb-sm-0">
														<label class="form-label">Select Ledger <span class="text-danger">*</span></label>
														<select class="form-select select2" id="ledger_id" required>
															<option value="" disabled selected>Select Ledger</option>
														</select>
													</div>
												</div>
                                                <div class="col-md-2 mb-2">
													<div class="mb-1 mb-sm-0">
														<label class="form-label">Ledger Group <span class="text-danger">*</span></label>
														<select class="form-select select2" id="ledger_group" required>
														</select>
													</div>
												</div>
                                                <div class="col-md-2 mb-2">
                                                    <div class="mb-1 mb-sm-0">
                                                    <label class="form-label">Currency <span class="text-danger">*</span></label>
                                                    <select id="currency" class="form-select select2" required>
                                                        <option value="org"> {{strtoupper(CurrencyHelper::getOrganizationCurrency()->short_name) ?? ""}} (Organization)</option>
                                                        <option value="comp">{{strtoupper(CurrencyHelper::getCompanyCurrency()->short_name)??""}} (Company)</option>
                                                        <option value="group">{{strtoupper(CurrencyHelper::getGroupCurrency()->short_name)??""}} (Group)</option>
                                                    </select>
                                                    </div>
                                                </div>

												<div class="col-md-2 mb-2">
													<label class="form-label" for="fp-range">Select Period <span class="text-danger">*</span></label>
													<input type="text" id="fp-range" class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD" required/>
												</div>
												<div class="col-md-2">
													<div class="mt-2">
														<label class="mb-1">&nbsp</label>
														<button class="btn btn-warning btn-sm" type="submit"><i data-feather="filter"></i> Run Report</button>
													</div>
												</div>
											</div>
										</div>
									</form>
                                </div>
                            </div>
							 <div class="card-body">
								 <div class="row">
								 	<div class="col-md-12 earn-dedtable flex-column d-flex trail-balancefinance leadger-balancefinance trailbalnewdesfinance mt-0">
										<div class="table-responsive">
											<table class="table border" id="mytable">
												<thead>
													<tr>
                                                        <th width="100px">Date</th>
                                                        <th>Particulars</th>
                                                        <th>Series</th>
                                                        <th>Vch. Type</th>
                                                        <th>Vch. No.</th>
                                                        <th>Debit</th>
                                                        <th>Credit</th>
													</tr>
												</thead>

											</table>
										</div>
									</div>

								 </div>
							 </div>
						</div>
					 </div>
				 </div>
            </div>
        </div>
    </div>
    <!-- END: Content-->
@endsection

@section('scripts')
<script>
    var companies = {!! json_encode($companies) !!};

	function exportLedgerReport(){
        $('.preloader').show();
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            type    :"POST",
            url     :"{{route('exportLedgerReport')}}",
			data: {
				company_id:$('#company_id').val(),
				organization_id:$('#organization_id').val(),
				ledger_id:$('#ledger_id').val(),
				date:$('#fp-range').val(),
				'_token':'{!!csrf_token()!!}',
                location_id: $('#location_id').val(),
                cost_center_id: $('#cost_center_id').val(),
                ledger_group:$("#ledger_group").val(),
			},
            xhrFields: {
                responseType: 'blob'
            },
            success: function(data, status, xhr) {
                $('.preloader').hide();
                var link = document.createElement('a');
                var url = window.URL.createObjectURL(data);
                link.href = url;
                link.download = 'ledgerReport.xlsx';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
            error: function(xhr, status, error) {
                $('.preloader').hide();
                console.log('Export failed:', error);
            }
        });
    }

    $(document).on('change', '.companySelect', function () {
        var organizations = [];
        const company_id = $(this).val();

        $.each(companies, function (key, value) {
            if (value['id'] == company_id) {
                organizations = value['organizations'];
            }
        });

        $("#organization_id").html("");
        $("#organization_id").append("<option disabled selected value=''>Select Organization</option>");
        $.each(organizations, function (key, value) {
            $("#organization_id").append("<option value='" + value['id'] + "'>" + value['name'] + "</option>");
        });
    });
    $(document).on('change', '#ledger_id', function () {
        groupDropdown = $("#ledger_group");

    let ledgerId = $(this).val(); // Get the selected organization ID
    $.ajax({
                            url: '{{ route('voucher.getLedgerGroups') }}',
                            method: 'GET',
                            data: {
                                ledger_id: ledgerId,
                                _token: $('meta[name="csrf-token"]').attr(
                                    'content') // CSRF token
                            },
                            success: function(response) {
                                $('.preloader').hide();
                                groupDropdown.empty(); // Clear previous options

                                response.forEach(item => {
                                    groupDropdown.append(
                                        `<option value="${item.id}" data-ledger="${ledgerId}">${item.name}</option>`
                                    );
                                });
                                groupDropdown.data('ledger', ledgerId);
                                //handleRowClick(rowId);

                            },
                            error: function(xhr) {
                                $('.preloader').hide();
                                let errorMessage =
                                'Error fetching group items.'; // Default message

                                if (xhr.responseJSON && xhr.responseJSON.error) {
                                    errorMessage = xhr.responseJSON
                                    .error; // Use API error message if available
                                }
                                showToast("error", errorMessage);

                                
                            }
                        });

});


    $(document).on('change', '#organization_id', function () {
        const selectedOrgIds = $(this).val() || [];
        updateLocationsDropdown(selectedOrgIds);
    $("#ledger_id").html("");
    $("#ledger_id").append("<option disabled selected value=''>Select Ledger</option>");

    let orgId = $(this).val(); // Get the selected organization ID
    let url = "{{ route('get_org_ledgers', ':id') }}".replace(':id', orgId); // Replace the placeholder with the orgId

    $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        type: "GET",
        url: url, // Use the dynamically constructed URL
        dataType: "JSON",
        success: function(data) {
            $('.preloader').hide();
            if (data.length > 0) {
                $.each(data, function(key, value) {
                    $("#ledger_id").append("<option value='" + value['id'] + "'>" + value['name'] + "</option>");
                });
            }
        },
        error: function(xhr, status, error) {
            $('.preloader').hide();
            console.error("Error: " + error);
            showToast('error',"Failed to fetch ledgers. Please try again.");
        }
    });
});


    $(document).on('submit', '#form', function (e) {
		e.preventDefault();
        $('.preloader').show();

		$('#mytable tbody').remove();
		$('#mytable tfoot').remove();
		if ($('#fp-range').val()=="") {
			showToast('error','Please select time Period!!');
              $('.preloader').hide();
			return false;
		}

		$.ajax({
			url: "{{ route('filterLedgerReport') }}",
			method: 'POST',
			data: {
				company_id:$('#company_id').val(),
				organization_id:$('#organization_id').val(),
				ledger_id:$('#ledger_id').val(),
				date:$('#fp-range').val(),
                currency:$('#currency').val(),
                location_id: $('#location_id').val(),
                cost_center_id: $('#cost_center_id').val(),
                cost_group_id: $('#cost_group_id').val(),
                ledger_group:$("#ledger_group").val(),

			},
			success: function(response) {
                $('.preloader').hide();
				$('#mytable').append(response);
			},
			error: function(xhr, status, error) {
                $('.preloader').hide();
				showToast('error',"Somthing went wrong, try again!!");
				console.error(error);
			}
		});
	});
    function showToast(icon, title) {
            Swal.fire({
                        title:'Alert!',
                        text: title,
                        icon: icon
                    });
}

        $(document).ready(function() {
            $('.cost_center').hide();
            $('.cost_group').hide();
            $('#cost_center_id').prop('required', false);
            $('#cost_group_id').prop('required', false);
            
            // Auto-select first company on page load if no URL parameters
            let urlParams = new URLSearchParams(window.location.search);
            
            // If no company is selected via URL parameters, select the first company
            if (!urlParams.get('company_id')) {
                // Select the first company if available
                if (companies && companies.length > 0) {
                    const firstCompany = companies[0];
                    $('#company_id').val(firstCompany.id).trigger('change');
                    
                    // After company change is triggered, select the first organization
                    setTimeout(function() {
                        if (firstCompany.organizations && firstCompany.organizations.length > 0) {
                            $('#organization_id').val(firstCompany.organizations[0].id).trigger('change');
                        }
                    }, 100); // Small delay to ensure company change event completes
                }
            } else {
                // Handle URL parameters if they exist
                if (urlParams.get('organization_id') == "")
                    $('#organization_id').val(urlParams.get('organization_id'));
            }
            
            if (urlParams.get('cost_center_id') == "")
                $('#cost_center_id').val(urlParams.get('cost_center_id'));
            if (urlParams.get('cost_group_id') == "")
                $('#cost_group_id').val(urlParams.get('cost_group_id'));

            if (urlParams.get('location_id') == "")
                $('#location_id').val(urlParams.get('location_id'));
        });
        const locations = @json($locations);
        const costCenters = @json($cost_centers);
        const costGroups = @json($cost_groups);

        function updateLocationsDropdown(selectedOrgIds) {
            selectedOrgIds = $('#organization_id').val() || [];

            const requestedLocationId = @json(request('location_id')) || "";

            const filteredLocations = locations.filter(loc =>
                selectedOrgIds.includes(String(loc.organization_id))
            );

            const $locationDropdown = $('#location_id');
            $locationDropdown.empty().append('<option value="">Select Location</option>');


            filteredLocations.forEach(loc => {
                const isSelected = String(loc.id) === String(requestedLocationId) ? 'selected' : '';
                $locationDropdown.append(`<option value="${loc.id}" ${isSelected}>${loc.store_name}</option>`);
            });

            // Load cost centers if location was pre-selected
            if (requestedLocationId) {
                loadCostGroupsByLocation(requestedLocationId);
            }

            $locationDropdown.trigger('change');
        }



       function loadCostGroupsByLocation(locationId) {
        const costCenter = $('#cost_center_id');
        costCenter.val(@json(request('cost_center_id')) || "");
        const filteredCenters = costCenters.filter(center => {
            if (!center.location) return false;
            const locationArray = Array.isArray(center.location)
                ? center.location.flatMap(loc => loc.split(','))
                : [];
            return locationArray.includes(String(locationId));
        });

        const costGroupIds = [...new Set(filteredCenters.map(center => center.cost_group_id))];
        
        const filteredGroups = costGroups.filter(group => costGroupIds.includes(group.id));
        console.log(filteredCenters,costGroupIds,filteredGroups);

        const $groupDropdown = $('#cost_group_id');
        $groupDropdown.empty().append('<option value="">Select Cost Group</option>');

        if (filteredGroups.length > 0) {
            $('.cost_group').show();
            $('#cost_group_id').prop('required', true);

            filteredGroups.forEach(group => {
                const isSelected = String(group.id) === String(@json(request('cost_group_id'))) ? 'selected' : '';
                $groupDropdown.append(`<option value="${group.id}" ${isSelected}>${group.name}</option>`);
            });

            $('#cost_group_id').trigger('change');
        } else {
            $('.cost_group').hide();
            $('#cost_group_id').prop('required', false);
            $('.cost_center').hide();
            costCenter.prop('required', false);
        }
    }

    function loadCostCentersByGroup(locationId, groupId) {
        const costCenter = $('#cost_center_id');
        costCenter.empty();

        const filteredCenters = costCenters.filter(center => {
            if (!center.location || center.cost_group_id !== groupId) return false;

            const locationArray = Array.isArray(center.location)
                ? center.location.flatMap(loc => loc.split(','))
                : [];

            return locationArray.includes(String(locationId));
        });

        if (filteredCenters.length === 0) {
            $('.cost_center').hide();
            costCenter.prop('required', false);
        } else {
            $('.cost_center').show();
            //costCenter.prop('required', true);
            costCenter.append('<option value="">Select Cost Center</option>');
            filteredCenters.forEach(center => {
                const isSelected = String(center.id) === String(@json(request('cost_center_id'))) ? 'selected' : '';
                costCenter.append(`<option value="${center.id}" ${isSelected}>${center.name}</option>`);
            });
        }
        costCenter.val(@json(request('cost_center_id')) || "");
        costCenter.trigger('change');
    }
        //$('#organization_id').trigger('change');
        

        // On page load, check for preselected orgs
        const preselectedOrgIds = $('#organization_id').val() || [];
        if (preselectedOrgIds.length > 0) {
            updateLocationsDropdown(preselectedOrgIds);
        }
        // On location change, load cost centers
        $('#location_id').on('change', function() {
            const locationId = $(this).val();
            if (!locationId) {
                $('#cost_center_id').empty().append('<option value="">Select Cost Center</option>');
                // $('.cost_center').hide(); // Optional: hide the section if needed
                $('#cost_center_id').prop('required', false);
                $('.cost_center').hide();
                return;
            }
            loadCostGroupsByLocation(locationId);



        });
         $('#cost_group_id').on('change', function () {
                const locationId = $('#location_id').val();
                const groupId = parseInt($(this).val());

                if (!locationId || !groupId) {
                    $('#cost_center_id').empty().append('<option value="">Select Cost Center</option>');
                    return;
                }

                loadCostCentersByGroup(locationId, groupId);
            });
</script>


@endsection
