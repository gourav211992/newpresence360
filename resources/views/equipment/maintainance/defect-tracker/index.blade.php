@extends('layouts.app')

@section('content')
	<div class="app-content content ">
		<div class="content-overlay"></div>
		<div class="header-navbar-shadow"></div>
		<div class="content-wrapper container-xxl p-0">
			<div class="content-header row">
				<div class="content-header-left col-md-5 mb-2">
					<div class="row breadcrumbs-top">
						<div class="col-12">
							<h2 class="content-header-title float-start mb-0">Defect Tracker</h2>
							<div class="breadcrumb-wrapper">
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="{{ route('maintenance.index') }}">Home</a></li>
									<li class="breadcrumb-item active">Maintenance List</li>
								</ol>
							</div>
						</div>
					</div>
				</div>
				<div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
					<div class="form-group breadcrumb-right">
						<button class="btn btn-primary btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
					</div>
				</div>
			</div>
			<div class="content-body">



				<section id="basic-datatable">
					<div class="row">
						<div class="col-12">
							<div class="card">


								<div class="table-responsive">
									<table class="datatables-basic tasklist table myrequesttablecbox tableistlastcolumnfixed newerptabledesignlisthome">
										<thead>
										<tr>
											<th height="18">#</th>
											<th>Equipment</th>
											<th>Category</th>
											<th>Item Name</th>
											<th>UOM</th>
											<th>Qty</th>
											<th>Description</th>
											<th>Defect Type</th>
											<th>Priority</th>
											<th>Due Date</th>
											<th>Action</th>
										</tr>
										</thead>
										<tbody>
											@forelse ($defects as $index => $item)
												<tr>
													<td>{{ $index + 1 }}</td>
													<td class="fw-bolder text-dark">{{ $item->erpMaintenance->equipment->name ?? '-' }}</td>
													<td>{{ $item->erpMaintenance->equipment->category->name ?? '-' }}</td>
													<td>{{ $item->erpEquipSparepart->item_name ?? '-' }} ({{ $item->erpEquipSparepart->item_code ?? '-' }})</td>
													<td>{{ $item->erpEquipSparepart->uom ?? '-' }}</td>
													<td>{{ $item->erpEquipSparepart->qty ?? '-' }}</td>
													<td>{{ $item->description ?? '-' }}</td>
													<td>{{ $item->defectType->name ?? '-' }}</td>
													<td>
														@php
															$priority = strtolower($item->priority ?? 'medium');
															$badgeClass = match($priority) {
																'high' => 'badge-light-danger',
																'medium' => 'badge-light-warning',
																'low' => 'badge-light-secondary',
																default => 'badge-light-primary',
															};
														@endphp
														<span class="badge rounded-pill {{ $badgeClass }}">{{ ucfirst($priority) }}</span>
													</td>
													<td>{{ \Carbon\Carbon::parse($item->due_date)->format('d-m-Y') }}</td>
													<td>
														<a href="#addacess" data-bs-toggle="modal" class="edit-defect"
														   data-id="{{ $item->id }}"
														   data-status="{{ $item->tracking_status }}"
														   data-remarks="{{ $item->tracking_remarks }}"
														   data-attachment="{{ $item->tracking_attachment }}">
															<i data-feather="edit"></i>
														</a>
													</td>
												</tr>
											@empty
												<tr>
													<td colspan="11" class="text-center text-muted">No defect records found.</td>
												</tr>
											@endforelse
										</tbody>
									</table>
								</div>





							</div>
						</div>
					</div>
					<!-- Modal to add new record -->
					<div class="modal modal-slide-in fade" id="modals-slide-in">
						<div class="modal-dialog sidebar-sm">
							<form class="add-new-record modal-content pt-0">
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
								<div class="modal-header mb-1">
									<h5 class="modal-title" id="exampleModalLabel">New Record</h5>
								</div>
								<div class="modal-body flex-grow-1">
									<div class="mb-1">
										<label class="form-label" for="basic-icon-default-fullname">Full Name</label>
										<input type="text" class="form-control dt-full-name" id="basic-icon-default-fullname" placeholder="John Doe" aria-label="John Doe" />
									</div>
									<div class="mb-1">
										<label class="form-label" for="basic-icon-default-post">Post</label>
										<input type="text" id="basic-icon-default-post" class="form-control dt-post" placeholder="Web Developer" aria-label="Web Developer" />
									</div>
									<div class="mb-1">
										<label class="form-label" for="basic-icon-default-email">Email</label>
										<input type="text" id="basic-icon-default-email" class="form-control dt-email" placeholder="john.doe@example.com" aria-label="john.doe@example.com" />
										<small class="form-text"> You can use letters, numbers & periods </small>
									</div>
									<div class="mb-1">
										<label class="form-label" for="basic-icon-default-date">Joining Date</label>
										<input type="text" class="form-control dt-date" id="basic-icon-default-date" placeholder="MM/DD/YYYY" aria-label="MM/DD/YYYY" />
									</div>
									<div class="mb-4">
										<label class="form-label" for="basic-icon-default-salary">Salary</label>
										<input type="text" id="basic-icon-default-salary" class="form-control dt-salary" placeholder="$12000" aria-label="$12000" />
									</div>
									<button type="button" class="btn btn-primary data-submit me-1">Submit</button>
									<button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
								</div>
							</form>
						</div>
					</div>
				</section>


			</div>
		</div>
	</div>

	<div class="modal fade" id="addacess" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body px-sm-4 mx-50 pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">Update Status</h1>
					<p class="text-center">Enter the details below.</p>

					<div class="row mt-2">


						<div class="col-md-12 mb-1">
							<label class="form-label">Status</label>

							<input type="hidden" id="defect_id" value="">

							<div class="demo-inline-spacing">
								<div class="form-check form-check-primary mt-25">
									<input type="radio" id="status_open" name="tracking_status" value="{{ \App\Helpers\ConstantHelper::OPEN }}" class="form-check-input">
									<label class="form-check-label fw-bolder" for="status_open">Open</label>
								</div>
								<div class="form-check form-check-primary mt-25">
									<input type="radio" id="status_closed" name="tracking_status" value="{{ \App\Helpers\ConstantHelper::CLOSE }}" class="form-check-input">
									<label class="form-check-label fw-bolder" for="status_closed">Closed</label>
								</div>
							</div>
						</div>

						<div class="col-md-12 mb-1">
							<label class="form-label">Attachment <span class="text-danger">*</span></label>
							<input type="file" id="tracking_attachment" name="tracking_attachment" class="form-control" />
						</div>

						<div class="col-md-12 mb-1">
							<label class="form-label">Remarks <span class="text-danger">*</span></label>
							<textarea id="tracking_remarks" name="tracking_remarks" type="text" class="form-control" placeholder="Remarks...."></textarea>
						</div>




					</div>
				</div>

				<div class="modal-footer justify-content-center">
					<button type="button" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary" id="updateDefectBtn">Update</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('scripts')
	<script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })
        $(function () {

            var dt_basic_table = $('.datatables-basic'),
                dt_date_table = $('.dt-date'),
                dt_complex_header_table = $('.dt-complex-header'),
                dt_row_grouping_table = $('.dt-row-grouping'),
                dt_multilingual_table = $('.dt-multilingual'),
                assetPath = '../../../app-assets/';

            if ($('body').attr('data-framework') === 'laravel') {
                assetPath = $('body').attr('data-asset-path');
            }

            // DataTable with buttons
            // --------------------------------------------------------------------

            if (dt_basic_table.length) {
                var dt_basic = dt_basic_table.DataTable({

                    order: [[0, 'asc']],
                    dom:
                        '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    displayLength: 7,
                    lengthMenu: [7, 10, 25, 50, 75, 100],
                    buttons: [
                        {
                            extend: 'collection',
                            className: 'btn btn-outline-secondary dropdown-toggle',
                            text: feather.icons['share'].toSvg({ class: 'font-small-4 mr-50' }) + 'Export',
                            buttons: [
                                {
                                    extend: 'excel',
                                    text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + 'Excel',
                                    className: 'dropdown-item',
                                    exportOptions: { columns: [3, 4, 5, 6, 7] }
                                },
                            ],
                            init: function (api, node, config) {
                                $(node).removeClass('btn-secondary');
                                $(node).parent().removeClass('btn-group');
                                setTimeout(function () {
                                    $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
                                }, 50);
                            }
                        },

                    ],

                    language: {
                        paginate: {
                            // remove previous & next text from pagination
                            previous: '&nbsp;',
                            next: '&nbsp;'
                        }
                    }
                });
                $('div.head-label').html('<h6 class="mb-0">Event List</h6>');
            }

            // Flat Date picker
            if (dt_date_table.length) {
                dt_date_table.flatpickr({
                    monthSelectorType: 'static',
                    dateFormat: 'm/d/Y'
                });
            }

            // Add New record
            // ? Remove/Update this code as per your requirements ?
            var count = 101;
            $('.data-submit').on('click', function () {
                var $new_name = $('.add-new-record .dt-full-name').val(),
                    $new_post = $('.add-new-record .dt-post').val(),
                    $new_email = $('.add-new-record .dt-email').val(),
                    $new_date = $('.add-new-record .dt-date').val(),
                    $new_salary = $('.add-new-record .dt-salary').val();

                if ($new_name != '') {
                    dt_basic.row
                        .add({
                            responsive_id: null,
                            id: count,
                            full_name: $new_name,
                            post: $new_post,
                            email: $new_email,
                            start_date: $new_date,
                            salary: '$' + $new_salary,
                            status: 5
                        })
                        .draw();
                    count++;
                    $('.modal').modal('hide');
                }
            });

            // Delete Record
            $('.datatables-basic tbody').on('click', '.delete-record', function () {
                dt_basic.row($(this).parents('tr')).remove().draw();
            });



        });

        $(".myrequesttablecbox tr").click(function() {
            $(this).addClass('trselected').siblings().removeClass('trselected');
            value = $(this).find('td:first').html();
        });

        $(document).on('keydown', function(e) {
            if (e.which == 38) {
                $('.trselected').prev('tr').addClass('trselected').siblings().removeClass('trselected');
            } else if (e.which == 40) {
                $('.trselected').next('tr').addClass('trselected').siblings().removeClass('trselected');
            }
            $('html, body').scrollTop($('.trselected').offset().top - 100);
        });


        $(document).on('click', '.edit-defect', function () {
            const defectId = $(this).data('id');
            const status = $(this).data('status');
            const remarks = $(this).data('remarks');

            $('#defect_id').val(defectId);
            $('#tracking_remarks').val(remarks);

            // Select the correct radio button
            if (status === '{{ \App\Helpers\ConstantHelper::OPEN }}') {
                $('#status_open').prop('checked', true);
            } else if (status === '{{ \App\Helpers\ConstantHelper::CLOSE }}') {
                $('#status_closed').prop('checked', true);
            }
        });

        $(document).on('click', '#updateDefectBtn', function () {
            let formData = new FormData();
            formData.append('tracking_status', $('input[name="tracking_status"]:checked').val());
            formData.append('tracking_remarks', $('#tracking_remarks').val());
            formData.append('tracking_attachment', $('#tracking_attachment')[0].files[0]);
            formData.append('_token', '{{ csrf_token() }}');

            const defectId = $('#defect_id').val();

            $.ajax({
                url: `defect-tracker/update/${defectId}`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated Successfully',
                        text: response.message
                    }).then(() => location.reload());
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Something went wrong!'
                    });
                }
            });
        });

	</script>
@endsection