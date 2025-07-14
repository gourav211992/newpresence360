@extends('layouts.app')
@section('content')


<!-- BEGIN: Content-->
<div class="app-content content ">
	<div class="content-overlay"></div>
	<div class="header-navbar-shadow"></div>
	<div class="content-wrapper container-xxl p-0">
		<div class="content-header row">
			<div class="content-header-left col-md-5 mb-2">
				<div class="row breadcrumbs-top">
					<div class="col-12">
						<h2 class="content-header-title float-start mb-0">Cost Centers</h2>
						<div class="breadcrumb-wrapper">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
								<li class="breadcrumb-item active">Cost Centers List</li>
							</ol>
						</div>
					</div>
				</div>
			</div>
			<div class="content-header-right text-end col-md-7 mb-2 mb-sm-0">
				<div class="form-group breadcrumb-right">
					<button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter"
						data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>

					<a class="btn btn-primary btn-sm" href="{{ route('cost-center.create') }}"><i
							data-feather="plus-circle"></i> Add New</a>
				</div>
			</div>
		</div>
		<div class="content-body">
<section id="basic-datatable">
				<div class="row">
					<div class="col-12">
						<div class="card">


							<div class="table-responsive">
								<table class="datatables-basic table myrequesttablecbox ">
									<thead>
										<tr>
											<th>#</th>
											<th>Name</th>
											<th>Cost Group</th>
											<th>Organization</th>
											<th>Locations</th>
											<th>Status</th>
											<th>Created Date</th>
											<th class="d-none">Created At</th>
											<th>Action</th>
										</tr>
									</thead>
									<tbody>
										@foreach ($centers as $index=>$item)
											<tr>
												<td>{{ $index+1 }}</td>
												<td>{{ $item->name  }}</td>
												<td>{{ $item->group?->name ?? ''}}</td>
												<td>{{ implode(', ', $item->organization_names->toArray()) }}</td>
												<td>{{ implode(', ', $item->location_names->toArray()) }}</td>
												<td>{{ Str::ucfirst($item->status) }}</td>
												<td class="fw-bolder text-dark">
													{{ date('d/m/Y', strtotime($item->created_at)) }}
												</td>
												<td class="d-none">{{ $item->created_at}}</td>

												<td class="tableactionnew">
													<div class="dropdown">
														<button type="button"
															class="btn btn-sm dropdown-toggle hide-arrow py-0"
															data-bs-toggle="dropdown">
															<i data-feather="more-vertical"></i>
														</button>
														<div class="dropdown-menu dropdown-menu-end">
															<a class="dropdown-item"
																href="{{ route('cost-center.edit', ['cost_center' => $item->id]) }}">
																<i data-feather="edit-3" class="me-50"></i>
																<span>Edit</span>
															</a>
															{{-- <form action="{{ route('cost-center.destroy', $item->id) }}"
																method="POST">
																	@csrf
																	@method('DELETE')

																	<button class="dropdown-item" href="#">
																		<i data-feather="trash-2" class="me-50"></i>
																		<span>Delete</span>
																	</button>
																</form> --}}
														</div>
													</div>
												</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>

						</div>
					</div>
				</div>
				<div class="modal modal-slide-in fade filterpopuplabel" id="filter">
					<div class="modal-dialog sidebar-sm">
						<form class="add-new-record modal-content pt-0">
							<div class="modal-header mb-1">
								<h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal"
									aria-label="Close">×</button>
							</div>
							<div class="modal-body flex-grow-1">
								<div class="mb-1">
									<label class="form-label" for="fp-range">Select Date</label>
									<input type="text" id="fp-range" class="form-control flatpickr-range bg-white"
										placeholder="YYYY-MM-DD to YYYY-MM-DD" />
								</div>
								<div class="mb-1">
									<label class="form-label">Organization</label>
									<select class="form-select" id="filter-group">
										@foreach ($companies as $organization)
									<option value="{{ $organization->name }}"
										{{ $organization->id == $organizationId ? 'selected' : '' }}>
										{{ $organization->name }}
									</option>
								@endforeach

									</select>
								</div>
								<div class="mb-1">
									<label class="form-label">Name</label>
									<select class="form-select" id="filter-name">
										<option value="">Select</option>
										@foreach($centers as $center)
											<option value="{{ $center->name }}">{{ $center->name }}</option>
										@endforeach
									</select>
								</div>
								<div class="mb-1">
									<label class="form-label">Status</label>
									<select class="form-select" id="filter-status">
										<option value="">Select</option>
										<option value="Active">Active</option>
										<option value="Inactive">Inactive</option>
									</select>
								</div>
							</div>
							<div class="modal-footer justify-content-start">
								<button type="button"
									class="btn btn-primary data-submit mr-1 apply-filter">Apply</button>
								<button type="reset" class="btn btn-outline-secondary"
									data-bs-dismiss="modal">Cancel</button>
							</div>
						</form>
					</div>
				</div>

			</section>


		</div>
	</div>
</div>
<!-- END: Content-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
	$(window).on('load', function () {
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
                   buttons:
                [{
                    extend: 'excel',
                            text: feather.icons['file'].toSvg({ class: 'font-small-4 me-50' }) + 'Excel',
                            className: 'btn btn-outline-secondary',
                           exportOptions: { columns: [1,2,3,4] },
                            filename: 'Cost Center Report'
                    ,
                    init: function (api, node, config) {
                        $(node).removeClass('btn-secondary');
                        $(node).parent().removeClass('btn-group');
                        setTimeout(function () {
                            $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
                        }, 50);
                    }
                    }],
				// buttons: [
				// 	{
				// 		extend: 'collection',
				// 		className: 'btn btn-outline-secondary dropdown-toggle',
				// 		text: feather.icons['share'].toSvg({ class: 'font-small-4 mr-50' }) + 'Export',
				// 		buttons: [
				// 			{
				// 				extend: 'print',
				// 				text: feather.icons['printer'].toSvg({ class: 'font-small-4 mr-50' }) + 'Print',
				// 				className: 'dropdown-item',
				// 				exportOptions: { columns: [1,2,3,4] },
                //                 filename: 'Cost Center Report'
				// 			},
				// 			{
				// 				extend: 'csv',
				// 				text: feather.icons['file-text'].toSvg({ class: 'font-small-4 mr-50' }) + 'Csv',
				// 				className: 'dropdown-item',
				// 				exportOptions: { columns: [1,2,3,4] },
                //                 filename: 'Cost Center Report'
				// 			},
				// 			{
				// 				extend: 'excel',
				// 				text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + 'Excel',
				// 				className: 'dropdown-item',
				// 				exportOptions: { columns: [1,2,3,4] },
                //                 filename: 'Cost Center Report'
				// 			},
				// 			{
				// 				extend: 'pdf',
				// 				text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 mr-50' }) + 'Pdf',
				// 				className: 'dropdown-item',
				// 				exportOptions: { columns: [1,2,3,4] },
                //                 filename: 'Cost Center Report'
				// 			},
				// 			{
				// 				extend: 'copy',
				// 				text: feather.icons['copy'].toSvg({ class: 'font-small-4 mr-50' }) + 'Copy',
				// 				className: 'dropdown-item',
				// 				exportOptions: { columns: [1,2,3,4] },
                //                 filename: 'Cost Center Report'
				// 			}
				// 		],
				// 		init: function (api, node, config) {
				// 			$(node).removeClass('btn-secondary');
				// 			$(node).parent().removeClass('btn-group');
				// 			setTimeout(function () {
				// 				$(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
				// 			}, 50);
				// 		}
				// 	},

				// ],
				columnDefs: [
					{ "orderable": false, "targets": [5,6] }
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
		$(".apply-filter").on("click", function () {

			// Capture filter values
			var dateRange = $("#fp-range").val(),
				group = $("#filter-group").val(),
				name = $("#filter-name").val(),
				status = $("#filter-status").val();

			// Split date range into start and end dates
			var dates = dateRange.split(" to "),
				startDate = dates[0] ? dates[0] : '',
				endDate = dates[1] ? dates[1] : '';

			// Clear any existing filters
			dt_basic.search('').columns().search('');

			// Apply filters
			dt_basic.column(2).search(group ? group : '', true, false);
			dt_basic.column(1).search(name ? name : '', true, false);
			dt_basic.column(3).search(status ? status : '', true, false);

			// Custom date range filter
			$.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
				var createdAt = data[5];
				if (startDate && endDate) {
					if (createdAt >= startDate && createdAt <= endDate) {
						return true;
					}
					return false;
				}
				return true;
			});

			// Redraw the table
			dt_basic.draw();

			// Remove the custom filter function to avoid stacking filters
			$.fn.dataTable.ext.search.pop();

			// Hide the modal
			$("#filter").modal("hide");
		});

		// Delete Record
		$('.datatables-basic tbody').on('click', '.delete-record', function () {
			dt_basic.row($(this).parents('tr')).remove().draw();
		});



	});
	function showToast(icon, title) {
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                },
            });
            Toast.fire({
                icon,
                title
            });
        }

        @if (session('success'))
            showToast("success", "{{ session('success') }}");
        @endif

        @if (session('error'))
            showToast("error", "{{ session('error') }}");
        @endif

        @if ($errors->any())
            showToast('error',
                "@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach"
            );
        @endif





</script>

@endsection
