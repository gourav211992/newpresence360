@extends('layouts.app')
@php
    use App\Helpers\Helper;
@endphp

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
            @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


            <div class="content-body">
                <section id="basic-datatable">
                    <div class="card border  overflow-hidden">
                        <div class="row">
                            <div class="col-md-12 bg-light border-bottom mb-1 po-reportfileterBox">
                                <div class="row pofilterhead action-button align-items-center">
                                    <div class="col-md-4">
                                        <h3> {{$ledger_name}} ({{$group_name}}) </h3>
                                        <p style="margin-left: -2px"><span class="badge rounded-pill badge-light-secondary badgeborder-radius my-50"><strong>Credit Days :</strong> {{$credit_days}} Days</span></p>
                                        <p class="my-25"><strong>Time Period:</strong> {{$date2}}</p>
                                    </div>

                                    <div class="col-md-8 text-sm-end pofilterboxcenter mb-0 d-flex flex-wrap align-items-center justify-content-sm-end">
                                        {{-- <a href="javascript: history.go(-1)" class="btn btn-secondary btn-sm"><i
                                            data-feather="arrow-left-circle"></i> Back </a>
                                            &nbsp; --}}
                                        <a id="printButton" data-url="{{route('crdr.report.ledger.print',[$type,$ledger,$group])}}" class="btn btn-dark btn-sm mb-50 mb-sm-0 me-25"><i data-feather='printer'></i> Print</a>
                                        <button data-bs-toggle="modal" data-bs-target="#addcoulmn" class="btn btn-primary btn-sm mb-0 waves-effect"><i data-feather="filter"></i> Advance Filter</button>
                                    </div>
                                </div>

                            </div>
                          <div class="col-md-12">
                                <div class="table-responsive trailbalnewdesfinance po-reportnewdesign gsttabreporttotal">
									<table class="datatables-basic table myrequesttablecbox">
                                        <thead>
                                             <tr>
												<th>#</th>
												<th>Invoice Date</th>
												<th>Invoice No.</th>
												<th>Voucher No.</th>
												<th>O/S Days</th>
                                                <th class="text-end">Invoice Amt.</th>
												<th class="outstanding text-end">Balance Amt.</th>
												<th class="outstanding text-end">Running Bal.</th>
                                                <th class="overdue text-end">Overdue Amt.</th>
												<th class="overdue text-end">Running Bal.</th>
												<th>Action</th>
											  </tr>
											</thead>
											<tbody>
                                                @php $i=0; $runningBalTotal = 0; $runningOverDueTotal = 0; @endphp
                                                @foreach($data as $index=>$d)
                                                @if($d->total_outstanding!=0)
                                                @php $i++;
                                                $currentOutstanding = $d->total_outstanding < 0 ? 0 : $d->total_outstanding;
                                                    $runningBalTotal += $currentOutstanding;
                                                $currentOverdue = $d->overdue < 0 ? 0 : $d->overdue;
                                                    $runningOverDueTotal += $currentOverdue;
                                                @endphp
                                                <tr class="table-row" data-overdue="{{ $d->overdue }}">
													<td>{{$i}}</td>
													<td class="fw-bolder text-dark">
                                                        <div  data-bs-placement="top">{{$d->document_date}}</div>
                                                    </td>
													<td>{{$d->bill_no}}</td>
													<td>{{$d->voucher_no}}</td>
													<td>
                                                        @if($d->overdue_days!="-")
                                                        <span class="badge rounded-pill @if($credit_days<$d->overdue_days) badge-light-danger @else badge-light-secondary @endif  badgeborder-radius">{{$d->overdue_days}}</span>
                                                        @endif
                                                    </td>
                                                        <td class="text-end">@if($d->invoice_amount!=""){{ Helper::formatIndianNumber($d->invoice_amount)}}@endif</td>
                                                    <td class="outstanding text-end">{{ $d->total_outstanding < 0 ? 0: Helper::formatIndianNumber($d->total_outstanding) }}</td>
                                                    <td class="outstanding text-end">{{ Helper::formatIndianNumber($runningBalTotal) }}</td>
                                                    <td class="overdue text-end">
                                                          @if($d->overdue_days!="-")
                                                           {{Helper::formatIndianNumber($d->overdue)}}
                                                           @else
                                                           0
                                                          @endif
                                                        </td>
                                                    <td class="overdue text-end">{{ Helper::formatIndianNumber($runningOverDueTotal) }}</td>
                                                        <td>
                                                        @if($d->view_route)
                                                            <a href="{{ $d->view_route }}" target="_blank">
                                                                <i class="cursor-pointer" data-feather='eye'></i>
                                                            </a>
                                                        @endif
                                                        </td>


                                                    </tr>
                                                @endif
                                                  @endforeach

                                               </tbody>


									</table>
						    </div>
                            </div>
                        </div>
                    </div>

                </section>


            </div>
        </div>
    </div>
    <div class="modal fade text-start" id="invoice-view" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">L & T Infotech Pvt ltd</h4>
                        <p class="mb-0">View the below list</p>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					 <div class="row">


						 <div class="col-md-12">


							<div class="table-responsive">
								<table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
									<thead>
										 <tr>
											<th>#</th>
											<th>Series</th>
											<th>Doc No.</th>
                                            <th>Doc Date</th>
                                            <th>O/S Amount</th>
											<th class="text-end">0-30 Days</th>
                                            <th class="text-end">30-60 days</th>
                                            <th class="text-end">60-90 days</th>
                                            <th class="text-end">90-120 days</th>
                                            <th class="text-end">120-180 days</th>
                                            <th class="text-end">Above 180 days</th>
										  </tr>
										</thead>
										<tbody>
											 <tr>
												<td>1</td>
												<td>PV-1</td>
												<td>2901</td>
												<td>10-04-2023</td>
                                                <td class="text-end"><span class="badge rounded-pill badge-light-warning badgeborder-radius">1,00,000</span></td>
                                                <td class="text-end">10000</td>
                                                <td class="text-end">-</td>
                                                <td class="text-end">50000</td>
                                                <td class="text-end">-</td>
                                                <td class="text-end">20000</td>
                                                <td class="text-end">20000</td>
											</tr>

											<tr>
												<td>2</td>
												<td>PV-1</td>
												<td>2901</td>
												<td>10-04-2023</td>
                                                <td class="text-end"><span class="badge rounded-pill badge-light-warning badgeborder-radius">1,00,000</span></td>
                                                <td class="text-end">10000</td>
                                                <td class="text-end">-</td>
                                                <td class="text-end">50000</td>
                                                <td class="text-end">-</td>
                                                <td class="text-end">20000</td>
                                                <td class="text-end">20000</td>
											</tr>

                                            <tr>
												<td>3</td>
												<td>PV-1</td>
												<td>2901</td>
												<td>10-04-2023</td>
                                                <td class="text-end"><span class="badge rounded-pill badge-light-warning badgeborder-radius">1,00,000</span></td>
                                                <td class="text-end">10000</td>
                                                <td class="text-end">-</td>
                                                <td class="text-end">50000</td>
                                                <td class="text-end">-</td>
                                                <td class="text-end">20000</td>
                                                <td class="text-end">20000</td>
											</tr>

                                            <tr>
												<td>4</td>
												<td>PV-1</td>
												<td>2901</td>
												<td>10-04-2023</td>
                                                <td class="text-end"><span class="badge rounded-pill badge-light-warning badgeborder-radius">1,00,000</span></td>
                                                <td class="text-end">10000</td>
                                                <td class="text-end">-</td>
                                                <td class="text-end">50000</td>
                                                <td class="text-end">-</td>
                                                <td class="text-end">20000</td>
                                                <td class="text-end">20000</td>
											</tr>

                                            <tr>
												<td>5</td>
												<td>PV-1</td>
												<td>2901</td>
												<td>10-04-2023</td>
                                                <td class="text-end"><span class="badge rounded-pill badge-light-warning badgeborder-radius">1,00,000</span></td>
                                                <td class="text-end">10000</td>
                                                <td class="text-end">-</td>
                                                <td class="text-end">50000</td>
                                                <td class="text-end">-</td>
                                                <td class="text-end">20000</td>
                                                <td class="text-end">20000</td>
											</tr>





									   </tbody>


								</table>
							</div>
						</div>


					 </div>
				</div>
				<div class="modal-footer text-end">
					<button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Close</button>
				</div>
			</div>
		</div>
	</div>

    <div class="modal fade text-start filterpopuplabel " id="addcoulmn" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<div>
							<h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Advance Filter</h4>
						</div>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">

<!--
								<div class="row">
									<div class="col-md-7 mt-1">
										<div class="form-check form-check-success mb-1">
											<input type="checkbox" class="form-check-input" id="colorCheck1" checked="">
											<label class="form-check-label fw-bolder text-dark" for="colorCheck1">All Columns</label>
										</div>
									</div>
								</div>
-->


                                <div class="step-custhomapp bg-light">
                                    <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#Bank" role="tab" ><i data-feather="bar-chart"></i> Apply Filter</a>
                                        </li>

                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#Location" role="tab" ><i data-feather="calendar"></i> Scheduler</a>
                                        </li>

                                    </ul>
                                </div>

                                <div class="tab-content tablecomponentreport">

                                    <div class="tab-pane active" id="Bank">

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="customernewsection-form">
                                                    <div class="demo-inline-spacing">
                                                        <div class="form-check form-check-primary mt-0">
                                                            <input type="radio" id="customColorRadio1" name="goodsservice" value="outstanding" class="form-check-input" checked="">
                                                            <label class="form-check-label fw-bolder" for="customColorRadio1">Total Outstanding</label>
                                                        </div>
                                                        <div class="form-check form-check-primary mt-0">
                                                            <input type="radio" id="service" name="goodsservice" value="overdue" class="form-check-input">
                                                            <label class="form-check-label fw-bolder" for="service">Overdue</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mt-1">
                                              <label class="form-label" for="fp-range">Select Period</label>
                                              <input type="text" id="fp-range" class="form-control flatpickr-range bg-white" value="{{$date}}" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                                            </div>

                                        </div>

                                        <div class="modal-footer mt-2 pe-0">
                                            <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn btn-primary data-submit mr-1" onClick="filter()">Apply</button>
                                        </div>

                                    </div>
                                    <div class="tab-pane" id="Location">
                                        <div class="row">
                                            <div class="col-md-12">
                                                 <div class="compoenentboxreport advanced-filterpopup customernewsection-form mb-1">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-check ps-0">
                                                                <label class="form-check-label">Add Scheduler</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row camparboxnewcen mt-1">
                                                        <div class="col-md-8 mb-1">
                                                            <label class="form-label">To <label class="text-danger">*</label></label>
                                                            <select disabled class="form-select select2" name="to" id="to" required>
                                                                @if($to_user_mail)
                                                                <option value="{{$to_users}}" data-type="{{$to_type}}">{{$to_user_mail}}</option>
                                                                @else
                                                                <option value="">Select</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="col-md-8 mb-1">
                                                            <label class="form-label">CC <label class="text-danger">*</label></label>
                                                            @php
                                                                $selectedCc = $scheduler && $scheduler->cc
                                                                    ? json_decode($scheduler->cc, true)
                                                                    : [App\Helpers\Helper::getAuthenticatedUser()->auth_user_id];
                                                            @endphp

                                                            <select class="form-select select2" name="cc" multiple>
                                                                <option disabled>Select</option>
                                                                @foreach($cc_users as $cc)
                                                                    <option value="{{ $cc->id }}" {{ in_array($cc->id, $selectedCc) ? 'selected' : '' }}>
                                                                        {{ $cc->email }}
                                                                    </option>
                                                                @endforeach
                                                            </select>

                                                        </div>
                                                     </div>
                                                     <input name="ledger_id" type="hidden" value="{{$ledger}}">
                                                     <input name="ledger_group_id" type="hidden" value="{{$group}}">
                                                     <input name="report_type" type="hidden" value="{{$type}}">

                                                     <input name="ledger_id" type="hidden" value="{{$ledger}}">
                                                     <input name="ledger_group_id" type="hidden" value="{{$group}}">
                                                     <input name="report_type" type="hidden" value="{{$type}}">

                                                     <div class="row camparboxnewcen">
                                                        @php
                                                            $selectedType = old('type', $scheduler->type ?? '');
                                                        @endphp

                                                        <div class="col-md-4">
                                                            <label class="form-label">Type <label class="text-danger">*</label></label>
                                                            <select class="form-select" name="type" id="type" required>
                                                                <option value="">Select</option>
                                                                <option value="daily" {{ $selectedType == 'daily' ? 'selected' : '' }}>Daily</option>
                                                                <option value="weekly" {{ $selectedType == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                                                <option value="monthly" {{ $selectedType == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                            </select>
                                                        </div>


                                                        <div class="col-md-4">
                                                            <label class="form-label">Select Date <label class="text-danger">*</label></label>
                                                            <input
                                                                type="datetime-local"
                                                                class="form-select"
                                                                name="date"
                                                                min="{{ now()->format('Y-m-d\TH:i') }}"
                                                                value="{{ old('date', isset($scheduler) ? \Carbon\Carbon::parse($scheduler->date)->format('Y-m-d\TH:i') : '') }}"
                                                                required
                                                            />
                                                        </div>

                                                        <div class="col-md-12">
                                                            <label class="form-label">Remarks  <label class="text-danger">*</label></label>
                                                            <textarea
                                                                class="form-control"
                                                                placeholder="Enter Remarks"
                                                                id="remarks"
                                                                name="remarks"
                                                                required
                                                            >{{ old('remarks', $scheduler->remarks ?? '') }}</textarea>
                                                        </div>




                                                    </div>

                                                </div>
                                             </div>


                                         </div>

                                        <div class="modal-footer mt-2 pe-0">
                                            <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="button" id="applyBtn" class="btn btn-primary data-submit mr-1">Submit</button>
                                        </div>

                                    </div>
                                </div>

					</div>


				</div>
			</div>
		</div>

@endsection

@section('scripts')
    <script src="{{ url('/app-assets/js/jquery-ui.js') }}"></script>
    <!-- BEGIN: Dashboard Custom Code JS-->
    <script src="https://unpkg.com/feather-icons"></script>

    <script>
        let baseUrl = "{{ route('crdr.report.ledger.print', [$type, $ledger, $group,'outstanding']) }}";
        printButton.setAttribute("data-url", baseUrl);
        $(".overdue").hide();

function filter() {
    let range = $('#fp-range').val();
    let currentUrl = new URL(window.location.href);
    if (range !== "") {
        currentUrl.searchParams.set('date', range);
        window.location.href = currentUrl.toString();

    } else {
        filterTable();

        currentUrl.searchParams.delete('date');

    }
}

var dt_basic_table = $('.datatables-basic');

if (dt_basic_table.length) {
    var dt_basic = dt_basic_table.DataTable({
        order: [[0, 'asc']], // Sort by Date column (index 1)
        dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-3"l><"col-sm-12 col-md-6 withoutheadbuttin dt-action-buttons text-end pe-0"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        "drawCallback": function(settings) {
            feather.replace(); // Re-initialize icons if needed
        },
        displayLength: 10,
        lengthMenu: [10, 25, 50, 75, 100],

        buttons: [{
            extend: 'collection',
            className: 'btn btn-outline-secondary dropdown-toggle',
            text: feather.icons['share'].toSvg({
                class: 'font-small-3 me-50'
            }) + 'Export',
            buttons: [
                {
                    extend: 'excel',
                    text: feather.icons['file'].toSvg({
                        class: 'font-small-4 me-50'
                    }) + 'Excel',
                    className: 'dropdown-item',
                    filename: 'Billing Report',
                    exportOptions: {
        columns: function (idx, data, node) {
            // Determine which radio is selected
            let isServiceSelected = document.querySelector('input[type="radio"]#service')?.checked;

            // Hide last column (assumed action column)
            const isLastColumn = node.cellIndex === node.parentNode.cells.length - 1;

            if (isLastColumn) {
                return false;
            }

            // If 'service' is selected, hide column 7 (index 6)
            // Else hide column 6 (index 5)
            console.log(isServiceSelected,'here')
            if (isServiceSelected && node.cellIndex === 6 || isServiceSelected && node.cellIndex === 7) {
                return false;
            } else if (!isServiceSelected && node.cellIndex === 8 || !isServiceSelected && node.cellIndex === 9) {
                return false;
            }

            return true;
        }
    },
                }   ],
            init: function(api, node, config) {
                $(node).removeClass('btn-secondary');
                $(node).parent().removeClass('btn-group');
                setTimeout(function() {
                    $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
                }, 50);
            }
        }],
        language: {
            search: '',
            searchPlaceholder: "Search...",
            paginate: {
                previous: '&nbsp;',
                next: '&nbsp;'
            }
        }
    });

    $('div.head-label').html('<h6 class="mb-0">Billing Report</h6>');
}
function getSelectedData() {
                let selectedData = [];

                $('select[name="to"] option:selected').each(function() {
                    selectedData.push({
                        id: $(this).val(),
                        type: $(this).data('type')
                    });
                });

                return selectedData;
            }
            function getColumnVisibilitySettings() {
    const columnVisibility = [];
    $(".sortable .form-check-input").each(function () {
        columnVisibility.push({
            id: $(this).attr("id"),
            visible: $(this).is(":checked"),
        });
    });

    return columnVisibility;
}

 // Trigger column order save when Apply button is clicked
 $('#applyBtn').on('click', function (e) {

            // Close the modal
            var filterModal = bootstrap.Modal.getInstance(document.getElementById('addcoulmn'));

            // Optionally handle the response here
            e.preventDefault();

            // Get the date value
            const dateValue = $('input[name="date"]').val();
            const today = new Date().toISOString().split('T')[0];

            let selectedData = getSelectedData();
            // Gather form data
            var formData = {
                to: selectedData,
                type: $('select[name="type"]').val(),
                cc: $('select[name="cc"]').val(),
                date: $('input[name="date"]').val(),
                remarks: $('textarea[name="remarks"]').val(),
                ledger_id: $('input[name="ledger_id"]').val(),
                ledger_group_id: $('input[name="ledger_group_id"]').val(),
                report_type: $('input[name="report_type"]').val(),
            };
            let type = $('select[name="type"]').val();
            let date = $('input[name="date"]').val();
            let remarks= $('textarea[name="remarks"]').val();
            let to = $('select[name="to"]').val();
            let cc = $('select[name="cc"]').val();

            var requiredFields = {
            "To": to,
            "CC": cc,
            "Type": type,
            "Date": date,
            "Remarks": remarks,
        };

        // Check for missing values
        // var missingFields = [];
        // $.each(requiredFields, function (key, value) {
        //     if (!value) {
        //         missingFields.push(key);
        //     }
        // });


        // // If missing fields exist, show an alert and stop execution
        // if (missingFields.length > 0) {
        //     alert("Please fill in the required fields: " + missingFields.join(", "));
        //     return;
        // }

            if (formData.to && formData.to.length > 0 || formData.type || formData.date) {


                // AJAX request
                let isValid=true;
                const fields = ['to', 'type', 'date', 'cc', 'remarks'];


                fields.forEach(field => {
                    var inputField = $('[name="'+field+'"]');
                    var errorMessage = inputField.closest('.col-md-8, .col-md-4, .col-md-12').find('.invalid-feedback');

                    if (inputField.hasClass('select2-hidden-accessible')) {
                        // Select2 elements validation
                        if (!inputField.val() || inputField.val().length === 0) {
                            inputField.next('.select2-container').addClass('is-invalid');
                            errorMessage.show();
                            isValid=false;
                        } else {
                            inputField.next('.select2-container').removeClass('is-invalid');
                            errorMessage.hide();
                        }
                    } else {
                        // Standard input fields validation
                        if (!inputField.val().trim()) {
                            console.log(field);
                            inputField.addClass('is-invalid');
                            errorMessage.show();
                            isValid=false;
                        } else {
                            inputField.removeClass('is-invalid');
                            errorMessage.hide();
                        }
                    }
                });
                if(isValid){
                    if (dateValue < today) {
                    var inputField = $('[name="date"]');

                    // For normal inputs, remove previous error and append new one
                    inputField.removeClass('is-invalid').addClass(
                        'is-invalid');
                    inputField.next('.invalid-feedback')
                        .remove(); // Remove any previous error
                    inputField.after(
                        '<div class="invalid-feedback">Please select a future date.</div>');
                    return; // Stop form submission
                }
                $.ajax({
                    url: "{{ route('crdr.add.scheduler') }}",
                    method: 'POST',
                    data: formData,
                    success: function (response) {
                        // Show success message
                        const Toast = Swal.mixin({
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.onmouseenter = Swal.stopTimer;
                                toast.onmouseleave = Swal.resumeTimer;
                            }
                        });
                        Toast.fire({
                            icon: "success",
                            title: response.success
                        });

                        // Optionally reset the form
                        // $('select[name="type"]').val(null).trigger('change');
                        // $('select[name="cc"]').val(null).trigger('change');
                        // $('input[name="date"]').val('');
                        // $('textarea[name="remarks"]').val('');

                        if (filterModal) {
                            filterModal.hide();
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;

                            // Handle and display validation errors
                            for (var field in errors) {
                                if (errors.hasOwnProperty(field)) {
                                    var errorMessages = errors[field];

                                    // Find the input field
                                    var inputField = $('[name="' + field + '"]');

                                    // If the field has the select2 class
                                    if (inputField.hasClass('select2')) {
                                        // Remove any previous error messages
                                        inputField.closest('.select2-wrapper').find(
                                            '.invalid-feedback').remove();

                                        // Append the error message after the select2 container
                                        inputField.closest('.select2-wrapper').append(
                                            '<div class="invalid-feedback d-block">' +
                                            errorMessages.join(', ') + '</div>');

                                        // Add is-invalid class to highlight the error
                                        inputField.next('.select2-container').addClass(
                                            'is-invalid');
                                    } else {
                                        // For normal inputs, remove previous error and append new one
                                        inputField.removeClass('is-invalid').addClass(
                                            'is-invalid');
                                        inputField.next('.invalid-feedback')
                                            .remove(); // Remove any previous error
                                        inputField.after(
                                            '<div class="invalid-feedback">' +
                                            errorMessages.join(', ') + '</div>');
                                    }
                                }
                            }
                        }


                    }
                });
            }
            } else {
                if (filterModal) {
                    filterModal.hide();
                }
            }
        });

function filterTable() {
    let selectedValue = $("input[name='goodsservice']:checked").attr("id");
    let anyVisible = false;

    $(".table-row").each(function () {
    let outstandingText = $(this).find("td.outstanding").text().trim();
    let overdueText = $(this).find("td.overdue").text().trim();

    let outstandingAmount = parseFloat(removeCommas(outstandingText)) || 0;
    let overdueAmount = parseFloat(removeCommas(overdueText)) || 0;
        if (selectedValue === "customColorRadio1") {
            $('.overdue').hide();
                $('.outstanding').show();
            if (outstandingAmount > 0) {
                $(this).show();
                anyVisible = true;
            } else {
                $(this).hide();
            }
        } else if (selectedValue === "service") {
            $('.overdue').show();
            $('.outstanding').hide();
            if (overdueAmount > 0) {
                $(this).show();
                anyVisible = true;
            } else {
                $(this).hide();
            }
        }
    });

    // Remove previous "no data" row if it exists
    $("#no-data-row").remove();

    // Show specific message based on filter
    if (!anyVisible) {
        let colspan = $(".table thead tr th").length;
        let message = "";

        if (selectedValue === "customColorRadio1") {
            message = "No Balance Amt.";
        } else if (selectedValue === "service") {
            message = "No Overdue Amt.";
        }

        $(".table tbody").append(`
            <tr id="no-data-row">
                <td colspan="${colspan}" class="text-center fw-bold">
                    ${message}
                </td>
            </tr>
        `);
    }
if ($('#addcoulmn').length && $('#addcoulmn').hasClass('show')) {
    $('#addcoulmn').modal('hide');
}}


    document.addEventListener("DOMContentLoaded", function () {
    const printButton = document.getElementById("printButton");
    const radios = document.querySelectorAll("input[name='goodsservice']");

    // Set initial href based on default bill type
    let defaultBillType = document.querySelector("input[name='goodsservice']:checked")?.value || 'outstanding';
    let baseUrl = `{{ route('crdr.report.ledger.print', [$type, $ledger, $group]) }}/${defaultBillType}`;
    printButton.setAttribute("data-url", baseUrl);

    // Handle radio button changes
    radios.forEach(radio => {
        radio.addEventListener("change", function () {
            let billType = document.querySelector("input[name='goodsservice']:checked")?.value;
            if (billType) {
                baseUrl = `{{ route('crdr.report.ledger.print', [$type, $ledger, $group]) }}/${billType}`;
                printButton.setAttribute("data-url", baseUrl);
            }
        });
    });

    // Intercept the click to call the route via AJAX
    document.getElementById("printButton").addEventListener("click", function (e) {
    e.preventDefault();

    const baseUrl = this.getAttribute("data-url");

    $.ajax({
        url: baseUrl,
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function () {
            window.open(baseUrl, '_blank');
        },
        error: function (xhr) {
            let errorMessage = 'An unexpected error occurred.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Print Error',
                html: errorMessage,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'OK'
            });
        }
    });
});

});

    filterTable();




    </script>
@endsection
