@extends('layouts.app')

@section('styles')
@endsection

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
						<h2 class="content-header-title float-start mb-0">Ledgers</h2>
						<div class="breadcrumb-wrapper">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
								<li class="breadcrumb-item active">Ledger List</li>
							</ol>
						</div>
					</div>

				</div>

			</div>
			<div class="content-header-right text-end col-md-7 mb-2 mb-sm-0">
				<div class="form-group breadcrumb-right">
					<button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter"
						data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
					<a href="{{ route('ledger.show.import') }}" class="btn btn-secondary btn-sm mb-50 mb-sm-0">
                            <i data-feather="upload"></i> Import
                        </a> 
					<a class="btn btn-primary btn-sm" href="{{ route('ledgers.create') }}"><i data-feather="plus-circle"></i>
						Add New</a>
				</div>
			</div>
		</div>

		<section id="basic-datatable">
			<div class="row">
				<div class="col-12">
					<div class="card">
						<div class="table-responsive">
							<table class="datatables-basic table myrequesttablecbox">
								<thead>
									<tr>
										<th>Sr. No</th>
										<th>Code</th>
										<th>Name</th>
										<th>Group</th>
										<th>Status</th>
										<th>Created Date</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
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
								<input type="text" id="fp-range" class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
							</div>

							<div class="mb-1">
								<label class="form-label">Group</label>
								<select id="filter-group" class="form-select select2">
									<option value="">Select</option>
									@foreach($groups as $group)
										<option value="{{ $group->id }}">{{ $group->name }}</option>
									@endforeach
								</select>
							</div>

							<div class="mb-1 d-none">
								<label class="form-label">Parent Ledger</label>
								<select id="filter-ledger-name" class="form-select select2">
									<option value="">Select</option>
									@foreach($ledgers as $ledger)
										<option value="{{ $ledger->id }}">{{ $ledger->name }}</option>
									@endforeach
								</select>
							</div>

							<div class="mb-1">
								<label class="form-label">Status</label>
								<select id="filter-status" class="form-select">
									<option value="">Select</option>
									<option value="Active">Active</option>
									<option value="Inactive">Inactive</option>
								</select>
							</div>
							<div class="mb-1">
                                    <label class="form-label">Organization</label>
                                    <select id="filter-organization" class="form-select select2" multiple>
                                        <option value="" disabled>Select</option>
                                        @foreach($mappings as $organization)
                                            <option value="{{ $organization->organization->id }}">{{ $organization->organization->name }}
                                            </option>
                                        @endforeach
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="{{asset('assets/js/modules/finance-table.js')}}"></script>

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

		var keyword='';
		if (dt_basic_table.length) {
			var dt_basic = dt_basic_table.DataTable({
				processing: true,
                serverSide: true,
				ajax: {
					url: "{{ route('ledgers.index') }}",
					data: function (d) {
						d.date = $("#fp-range").val(),
						d.group = $("#filter-group").val(),
						d.ledger = $("#filter-ledger-name").val(),
						d.status = $("#filter-status").val(),
						d.keyword = keyword,
						d.filter_organization = $("#filter-organization").val();
					}
				},
                columns: [
					{
                    data: null,
                    name: 'id',
                    render: function (data, type, row, meta) {
                        return meta.row + 1 + meta.settings._iDisplayStart;
                    }
                },
                    { data: 'code', name: 'code' },
                    { data: 'name', name: 'name' },
                    { data: 'group_name', name: 'group_name' },
                    { data: 'status', name: 'status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
				drawCallback: function() {
                        feather.replace();
                    },
                dom: 'Bfrtip',
				order: [[0, 'desc']],
				dom:
					'<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
				displayLength: 7,
				lengthMenu: [7, 10, 25, 50, 75, 100],
                 buttons:
                [{
                    extend: 'excel',
                            text: feather.icons['file'].toSvg({ class: 'font-small-4 me-50' }) + 'Excel',
                            className: 'btn btn-outline-secondary',
                           exportOptions: { columns: [1,2,3,4,5] },
                                filename: 'Ledgers Report'
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
				// 				exportOptions: { columns: [1,2,3,4,5] },
                //                 filename: 'Ledgers Report'
				// 			},
				// 			{
				// 				extend: 'csv',
				// 				text: feather.icons['file-text'].toSvg({ class: 'font-small-4 mr-50' }) + 'Csv',
				// 				className: 'dropdown-item',
				// 				exportOptions: { columns: [1,2,3,4,5] },
                //                 filename: 'Ledgers Report'
				// 			},
				// 			{
				// 				extend: 'excel',
				// 				text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + 'Excel',
				// 				className: 'dropdown-item',
				// 				exportOptions: { columns: [1,2,3,4,5] },
                //                 filename: 'Ledgers Report'
				// 			},
				// 			{
				// 				extend: 'pdf',
				// 				text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 mr-50' }) + 'Pdf',
				// 				className: 'dropdown-item',
				// 				exportOptions: { columns: [1,2,3,4,5] },
                //                 filename: 'Ledgers Report'
				// 			},
				// 			{
				// 				extend: 'copy',
				// 				text: feather.icons['copy'].toSvg({ class: 'font-small-4 mr-50' }) + 'Copy',
				// 				className: 'dropdown-item',
				// 				exportOptions: { columns: [1,2,3,4,5] },
                //                 filename: 'Ledgers Report'
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
					{ "orderable": false, "targets": [6] }
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

		// Filter record
		$(".apply-filter").on("click", function () {
			// Redraw the table
			dt_basic.draw();

			// Remove the custom filter function to avoid stacking filters
			// $.fn.dataTable.ext.search.pop();

			// Hide the modal
			$(".modal").modal("hide");
		})

		// Delete Record
		$('.datatables-basic tbody').on('click', '.delete-record', function () {
			dt_basic.row($(this).parents('tr')).remove().draw();
		});

		handleRowSelection('.datatables-basic');
	});




</script>

@endsection
