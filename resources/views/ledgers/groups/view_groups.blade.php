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
						<h2 class="content-header-title float-start mb-0">Ledger Groups</h2>
						<div class="breadcrumb-wrapper">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="index.html">Home</a></li>
								<li class="breadcrumb-item active">Group List</li>
							</ol>
						</div>
					</div>
				</div>
			</div>
			<div class="content-header-right text-end col-md-7 mb-2 mb-sm-0">
				<div class="form-group breadcrumb-right">
					<button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter"
						data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
					<a class="btn btn-primary btn-sm" href="{{ route('ledger-groups.create') }}"><i data-feather="plus-circle"></i> Add
						New</a>
				</div>
			</div>
		</div>
		@if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

		<section id="basic-datatable">
			<div class="row">
				<div class="col-12">
					<div class="card">
						<div class="table-responsive">
							<table class="datatables-basic table myrequesttablecbox ">
								<thead>
									<tr>
										<th>Sr. No</th>
										<th>Parent Group</th>
										<th>Prefix</th>
										<th>Group Name</th>
										<th>Status</th>
										<th>Created Date</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($data as $item)
										<tr>
											<td>{{ $item->id }}</td>
											<td>@if($item?->parent_group_id) {{ $item?->parent?->name }} @endif</td>
											<td>{{ $item->prefix ??"-" }}</td>
											<td>{{ $item->name }}</td>
											<td>{{ Str::ucfirst($item->status) }}</td>
											<td class="fw-bolder text-dark">
												{{ date('d/m/Y', strtotime($item->created_at)) }}
											</td>
											<td class="tableactionnew">
												@if($item->edit)

												<div class="dropdown">
													<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0"
														data-bs-toggle="dropdown">
														<i data-feather="more-vertical"></i>
													</button>
													<div class="dropdown-menu dropdown-menu-end">

														<a class="dropdown-item"
															href="{{ route('ledger-groups.edit', ['ledger_group' => $item->id]) }}">
															<i data-feather="edit-3" class="me-50"></i>
															<span>Edit</span>
														</a>
														<a class="delete-btn dropdown-item"
                                    data-url="{{ route('ledger-groups.destroy', $item->id) }}"
                                    data-redirect="{{ route('ledger-groups.index')}}"
                                    data-message="Are you sure you want to delete this group?">
                                <i data-feather="trash-2" class="me-50"></i> Delete
                            </a>
														{{-- <form action="{{ route('ledger-groups.destroy', $item->id) }}"
															method="POST">
															@csrf
															@method('DELETE')
															<button class="dropdown-item" type="submit">
																<i data-feather="trash-2" class="me-50"></i>
																<span>Delete</span>
															</button>
														</form> --}}
														@endif

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
								<label class="form-label">Parent Group</label>
								<select id="filter-parent-group" class="form-select select2">
									<option value="">Select</option>
									@foreach($parentGroup as $parent_group)
										<option value="{{ $parent_group->name }}">{{ $parent_group->name }}
										</option>
									@endforeach
								</select>
							</div>

							<div class="mb-1">
								<label class="form-label">Group</label>
								<select id="filter-group-name" class="form-select select2">
									<option value="">Select</option>
									@foreach($data as $item)
										<option value="{{ $item->name }}">{{ $item->name }}
										</option>
									@endforeach
								</select>
							</div>

							<div class="mb-1">
								<label class="form-label">Status</label>
								<select id="filter-status" class="form-select">
									<option value="">Select</option>
									<option>Active</option>
									<option>Inactive</option>
								</select>
							</div>
						</div>
						<div class="modal-footer justify-content-start">
							<button type="button" class="btn btn-primary apply-filter mr-1">Apply</button>
							<button type="reset" class="btn btn-outline-secondary"
								data-bs-dismiss="modal">Cancel</button>
						</div>
					</form>
				</div>
			</div>
		</section>
	</div>
</div>
<!-- END: Content-->
@endsection

@section('scripts')
<script>
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
				drawCallback: function() {
                                feather.replace();
                            },

				dom:
					'<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
				displayLength: 7,
				lengthMenu: [7, 10, 25, 50, 75, 100],
                buttons:
                [{
                    extend: 'excel',
                            text: feather.icons['file'].toSvg({ class: 'font-small-4 me-50' }) + 'Excel',
                            className: 'btn btn-outline-secondary',
                            exportOptions: { columns: [1,2,3, 4] },
                            filename: 'Ledger Group Report'
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
				// 				exportOptions: { columns: [1,2,3, 4] },
                //                 filename: 'Ledger Group Report'
				// 			},
				// 			{
				// 				extend: 'csv',
				// 				text: feather.icons['file-text'].toSvg({ class: 'font-small-4 mr-50' }) + 'Csv',
				// 				className: 'dropdown-item',
				// 				exportOptions: { columns: [1,2,3, 4] },
                //                 filename: 'Ledger Group Report'
				// 			},
				// 			{
				// 				extend: 'excel',
				// 				text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + 'Excel',
				// 				className: 'dropdown-item',
				// 				exportOptions: { columns: [1,2,3, 4] },
                //                 filename: 'Ledger Group Report'
				// 			},
				// 			{
				// 				extend: 'pdf',
				// 				text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 mr-50' }) + 'Pdf',
				// 				className: 'dropdown-item',
				// 				exportOptions: { columns: [1,2,3, 4] },
                //                 filename: 'Ledger Group Report'
				// 			},
				// 			{
				// 				extend: 'copy',
				// 				text: feather.icons['copy'].toSvg({ class: 'font-small-4 mr-50' }) + 'Copy',
				// 				className: 'dropdown-item',
				// 				exportOptions: { columns: [1,2,3, 4] },
                //                 filename: 'Ledger Group Report'
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
					{ "orderable": false, "targets": [5] }
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
				parentGroup = $("#filter-parent-group").val(),
				groupName = $("#filter-group-name").val(),
				status = $("#filter-status").val();

			// Split date range into start and end dates
			var dates = dateRange.split(" to "),
				startDate = dates[0] ? dates[0] : '',
				endDate = dates[1] ? dates[1] : '';

			// Clear any existing filters
			dt_basic.search('').columns().search('');

			// Apply filters
			dt_basic.column(1).search(parentGroup ? parentGroup : '', true, false);
			dt_basic.column(2).search(groupName ? groupName : '', true, false);
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
			$(".modal").modal("hide");
		})

		// Delete Record
		$('.datatables-basic tbody').on('click', '.delete-record', function () {
			dt_basic.row($(this).parents('tr')).remove().draw();
		});
	});
</script>
@endsection
