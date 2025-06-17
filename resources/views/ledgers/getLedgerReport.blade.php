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
														<label class="form-label">Company</label>
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
														<label class="form-label">Organization</label>
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
														<label class="form-label">Select Ledger</label>
														<select class="form-select select2" id="ledger_id" required>
															<option value="" disabled selected>Select Ledger</option>
														</select>
													</div>
												</div>
                                                <div class="col-md-2 mb-2">
													<div class="mb-1 mb-sm-0">
														<label class="form-label">Ledger Group</label>
														<select class="form-select select2" id="ledger_group" required>
														</select>
													</div>
												</div>
                                                <div class="col-md-2 mb-2">
                                                    <div class="mb-1 mb-sm-0">
                                                    <label class="form-label">Currency</label>
                                                    <select id="currency" class="form-select select2" required>
                                                        <option value="org"> {{strtoupper(CurrencyHelper::getOrganizationCurrency()->short_name) ?? ""}} (Organization)</option>
                                                        <option value="comp">{{strtoupper(CurrencyHelper::getCompanyCurrency()->short_name)??""}} (Company)</option>
                                                        <option value="group">{{strtoupper(CurrencyHelper::getGroupCurrency()->short_name)??""}} (Group)</option>
                                                    </select>
                                                    </div>
                                                </div>

												<div class="col-md-2 mb-2">
													<label class="form-label" for="fp-range">Select Period</label>
													<input type="text" id="fp-range" required class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD" required/>
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
            $('#cost_center_id').prop('required', false);
            let urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('organization_id') == "")
                $('#organization_id').val(urlParams.get('organization_id'));

            if (urlParams.get('cost_center_id') == "")
                $('#cost_center_id').val(urlParams.get('cost_center_id'));

            if (urlParams.get('location_id') == "")
                $('#location_id').val(urlParams.get('location_id'));
        });
        const locations = @json($locations);
        const costCenters = @json($cost_centers);

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
                loadCostCenters(requestedLocationId);
            }

            $locationDropdown.trigger('change');
        }



        function loadCostCenters(locationId) {
               const $costCenter = $('#cost_center_id');
                $costCenter.empty();
            if (locationId) {
                const filteredCenters = costCenters.filter(center => {
                    if (!center.location) return false;

                    const locationArray = Array.isArray(center.location) ?
                        center.location.flatMap(loc => loc.split(',')) : [];

                    return locationArray.includes(String(locationId));
                });
                if (filteredCenters.length === 0) {
                    $costCenter.prop('required', false);
                    $('.cost_center').hide();
                } else {
                    $costCenter.prop('required', true).append('<option value="">Select Cost Center</option>');
                    $('.cost_center').show();

                    filteredCenters.forEach(center => {
                        $costCenter.append(`<option value="${center.id}">${center.name}</option>`);
                    });
                }
                $costCenter.val(@json(request('cost_center_id')) || "");
                $costCenter.trigger('change');

            }
            else{
                 $costCenter.prop('required', false);
                $('.cost_center').hide();

            }
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
            loadCostCenters(locationId);



        });
</script>


@endsection
