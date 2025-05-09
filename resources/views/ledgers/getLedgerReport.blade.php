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
												<div class="col-md-2">
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
												<div class="col-md-2">
													<div class="mb-1 mb-sm-0">
														<label class="form-label">Organization</label>
														<select class="form-select select2" id="organization_id" required>
															<option value="" selected disabled>Select Organization</option>
														</select>
													</div>
												</div>
												<div class="col-md-2">
													<div class="mb-1 mb-sm-0">
														<label class="form-label">Select Ledger</label>
														<select class="form-select select2" id="ledger_id" required>
															<option value="" disabled selected>Select Ledger</option>
														</select>
													</div>
												</div>
                                                <div class="col-md-2">
													<div class="mb-1 mb-sm-0">
														<label class="form-label">Ledger Group</label>
														<select class="form-select select2" id="ledger_group" required>
														</select>
													</div>
												</div>
                                                <div class="col-md-2">
                                                    <div class="mb-1 mb-sm-0">
                                                    <label class="form-label">Currency</label>
                                                    <select id="currency" class="form-select select2" required>
                                                        <option value="org"> {{strtoupper(CurrencyHelper::getOrganizationCurrency()->short_name) ?? ""}} (Organization)</option>
                                                        <option value="comp">{{strtoupper(CurrencyHelper::getCompanyCurrency()->short_name)??""}} (Company)</option>
                                                        <option value="group">{{strtoupper(CurrencyHelper::getGroupCurrency()->short_name)??""}} (Group)</option>
                                                    </select>
                                                    </div>
                                                </div>

												<div class="col-md-2">
													<label class="form-label" for="fp-range">Select Period</label>
													<input type="text" id="fp-range" class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD" required/>
												</div>
												<div class="col-md-2">
													<div class="mt-2 mb-sm-0">
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
                ledger_group:$("#ledger_group").val(),
			},
            xhrFields: {
                responseType: 'blob'
            },
            success: function(data, status, xhr) {
                var link = document.createElement('a');
                var url = window.URL.createObjectURL(data);
                link.href = url;
                link.download = 'ledgerReport.xlsx';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
            error: function(xhr, status, error) {
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
            if (data.length > 0) {
                $.each(data, function(key, value) {
                    $("#ledger_id").append("<option value='" + value['id'] + "'>" + value['name'] + "</option>");
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error: " + error);
            showToast('error',"Failed to fetch ledgers. Please try again.");
        }
    });
});


    $(document).on('submit', '#form', function (e) {
		e.preventDefault();

		$('#mytable tbody').remove();
		$('#mytable tfoot').remove();
		if ($('#fp-range').val()=="") {
			showToast('error','Please select time Period!!');
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
                ledger_group:$("#ledger_group").val(),

			},
			success: function(response) {
				$('#mytable').append(response);
			},
			error: function(xhr, status, error) {
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
</script>
@endsection
