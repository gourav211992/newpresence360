@extends('layouts.app')

@section('title', 'Land')
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
             <div class="content-header row">
                <div class="content-header-left col-md-6 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Land Plot</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{url('/')}}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">All Request
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
				 <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
							<button class="btn btn-primary btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
							<a href="{{url('land-plot/add')}}" class="btn btn-dark btn-sm mb-50 mb-sm-0"><i data-feather="file-text" ></i> Add New</a>
                    </div>
                </div>
            </div>
            <div class="content-body">
                 <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">

                                <div class="table-responsive">
                                        <table class="datatables-basic table myrequesttablecbox loanapplicationlist">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Document No.</th>
                                                    <th> Land Name</th>
                                                    <th>Plot Name</th>
                                                    <th>Size of LAnd (Acr)</th>
                                                    <th>Area of Plot (Acr)</th>
                                                    <th>Address</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            @php
                                                use App\Helpers\Helper;
                                            @endphp

                                            <tbody>
                                                @foreach ($lands as $key => $land)
                                                @php
                                                    $mainBadgeClass = match ($land->approvalStatus) {
                                                        'approved' => 'success',
                                                        'approval_not_required' => 'success',
                                                        'draft' => 'warning',
                                                        'submitted' => 'info',
                                                        'partially_approved' => 'warning',
                                                        default => 'danger',
                                                    };
                                                @endphp
                                            <tr>
                                                    <td>{{ $key +1  }}</td>
                                                    <td class="fw-bolder text-dark">{{ $land->document_no }}</td>
                                                    <td>{{ $land->landParcel->name }}</td>
                                                    <td>{{ $land->plot_name }}</td>
                                                    <td>{{ $land->land_size }}</td>
                                                    <td>{{ $land->plot_area }}({{ $land->area_unit }})</td>
                                                    <td>{{ $land->address }}</td>
                                                    <td>
                                                        <span
                                                            class="badge rounded-pill badge-light-{{ $mainBadgeClass }} badgeborder-radius">{{ $land->approvalStatus == 'approval_not_required' ? 'Approved' : Helper::formatStatus($land->approvalStatus) }}</span>
                                                    </td>
                                                    <td class="tableactionnew">
                                                        <div class="dropdown">
                                                            <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                                                <i data-feather="more-vertical"></i>
                                                            </button>

                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                @if($land->approvalStatus=="draft")
                                                                <a class="dropdown-item" href="{{ url('/land-plot/edit/' . $land->id) }}">
                                                                    <i data-feather="check-circle" class="me-50"></i>
                                                                    <span>Edit</span>
                                                                </a>
                                                                @endif

                                                                <a class="dropdown-item" href="{{ url('/land-plot/view/' . $land->id) }}">
                                                                    <i data-feather="check-circle" class="me-50"></i>
                                                                    <span>View Detail</span>
                                                                </a>

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

                </section>


            </div>
        </div>
    </div>
    <!-- END: Content-->
	<div class="modal modal-slide-in fade filterpopuplabel" id="filter">
		<div class="modal-dialog sidebar-sm">
			<form class="add-new-record modal-content pt-0" method="GET" action="{{ route('land-plot.filter') }}">>
				<div class="modal-header mb-1">
					<h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
				</div>
				<div class="modal-body flex-grow-1">
					<div class="mb-1">
						  <label class="form-label" for="fp-range">Select Date Range</label>
						  <input type="text" id="fp-range" class="form-control flatpickr-range" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
					</div>

					<div class="mb-1">
						<label class="form-label">Plot No.</label>
						<select class="form-select select2" name="land_no">
							<option value="">Select</option>
                            @foreach ($land_no as $option)
                            <option value="{{ $option }}" {{ $option == old('land_no') ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
						</select>
					</div>

                    <div class="mb-1">
						<label class="form-label">Pincode</label>
						<select class="form-select select2" name="pincode">
							<option value="">Select</option>
                            @foreach ($pincode as $option)
                            <option value="{{ $option }}" {{ $option == old('pincode') ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
						</select>
					</div>

                    <div class="mb-1">
						<label class="form-label">Status</label>
						<select class="form-select" name="selectedStatus">
							<option value="">Select</option>
                            @foreach ($selectedStatus as $option)
                            <option value="{{ $option }}" {{ $option == old('selectedStatus') ? 'selected' : '' }}>{{ $option=="approval_not_required"?"Approved":Helper::formatStatus($option) }}</option>
                        @endforeach
						</select>
					</div>

				</div>
				<div class="modal-footer justify-content-start">
					<button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
					<button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
				</div>
			</form>
		</div>
	</div>
        <!-- <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Recovery History</h4>
                        <p class="mb-0">View the details below</p>
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
                                            <th>Land No.</th>
                                            <th>Date</th>
                                            <th>Plot No.</th>
                                            <th>Khasara No.</th>
                                            <th>Area (sq ft)</th>
                                            <th>Address</th>
                                            <th>Pincode</th>
                                            <th>Latitude</th>
                                            <th>Longitude</th>
                                            <th>Cost</th>
                                            <th>Status</th>
                                          </tr>
                                        </thead>
                                        <tbody>


                                       </tbody>


                                </table>
                            </div>
                        </div>


                     </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
                </div>
            </div>
        </div> -->
    </div>
@section('scripts')
<script>

    $(document).on('click', '.view-details', function() {
    var row = $(this).closest('tr');
    var id = row.data('id');

    // Extract data from the row
    var land_no = row.data('land_no');
    var created_at = row.data('created_at');
    var plot_no = row.data('plot_no');
    var khasara_no = row.data('khasara_no');
    var area = row.data('area');
    var address = row.data('address');
    var pincode = row.data('pincode');
    var latitude = row.data('latitude');
    var longitude = row.data('longitude');
    var cost = row.data('cost');
    var status = row.data('status');


    // Populate the modal fields
    var modal = $('#viewdetail');
    console.log(modal);

    modal.find('tbody').html(`
        <tr>
            <td>${id}</td>
            <td>${land_no}</td>
            <td>${created_at}</td>
            <td>${plot_no}</td>
            <td>${khasara_no}</td>
            <td>${area}</td>
            <td>${address}</td>
            <td>${pincode}</td>
            <td>${latitude}</td>
            <td>${longitude}</td>
            <td>${cost}</td>
            <td>${status}</td>
        </tr>
    `);
});

        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })

        $(function() {
            var dt_basic_table = $('.datatables-basic'),
                assetPath = '../../../app-assets/';

            if ($('body').attr('data-framework') === 'laravel') {
                assetPath = $('body').attr('data-asset-path');
            }

            if (dt_basic_table.length) {
                var dt_basic = dt_basic_table.DataTable({
                    order: [], // Disable default sorting
                    columnDefs: [
                        {
                            orderable: false,
                            targets: [0, -1] // Disable sorting on the first and last columns
                        }
                    ],
                    dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    displayLength: 7,
                    lengthMenu: [7, 10, 25, 50, 75, 100],
                    buttons: [
                        {
                            extend: 'collection',
                            className: 'btn btn-outline-secondary dropdown-toggle',
                            text: feather.icons['share'].toSvg({ class: 'font-small-4 mr-50' }) + 'Export',
                            buttons: [
                                {
                                    extend: 'csv',
                                    text: feather.icons['file-text'].toSvg({ class: 'font-small-4 mr-50' }) + 'Csv',
                                    className: 'dropdown-item',
                                    filename: 'Land_Report', // Set filename as needed
                                    exportOptions: {
                                        columns: function(idx, data, node) {
                                            // Exclude the first and last columns (Action) from CSV export
                                            return idx !== 0 && idx !== 9;
                                        },
                                        format: {
                                            header: function(data, columnIdx) {
                                                switch (columnIdx) {
                                                    case 1:
                                                        return 'Document No.';
                                                    case 2:
                                                        return 'Land Name';
                                                    case 3:
                                                        return 'Plot Name.';
                                                    case 4:
                                                        return 'Land Size (ACR)';
                                                    case 5:
                                                        return 'Plot Area';
                                                    case 6:
                                                        return 'Address';
                                                    case 7:
                                                        return 'Status';
                                                    default:
                                                        return data;
                                                }
                                            }
                                        }
                                    }
                                },
                                {
                                    extend: 'excel',
                                    text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + 'Excel',
                                    className: 'dropdown-item',
                                    filename: 'Land_Report', // Set filename as needed
                                    exportOptions: {
                                        columns: function(idx, data, node) {
                                            // Exclude the first and last columns (Action) from Excel export
                                            return idx !== 0 && idx !== 9;
                                        },
                                        format: {
                                            header: function(data, columnIdx) {
                                                switch (columnIdx) {
                                                    case 1:
                                                        return 'Document No.';
                                                    case 2:
                                                        return 'Land Name';
                                                    case 3:
                                                        return 'Plot Name.';
                                                    case 4:
                                                        return 'Land Size (ACR)';
                                                    case 5:
                                                        return 'Plot Area';
                                                    case 6:
                                                        return 'Address';
                                                    case 7:
                                                        return 'Status';
                                                    default:
                                                        return data;
                                                }
                                            }
                                        }
                                    }
                                }
                            ],
                            init: function(api, node, config) {
                                $(node).removeClass('btn-secondary');
                                $(node).parent().removeClass('btn-group');
                                setTimeout(function() {
                                    $(node).closest('.dt-buttons').removeClass('btn-group')
                                        .addClass('d-inline-flex');
                                }, 50);
                            }
                        }
                    ],
                    language: {
                        paginate: {
                            previous: '&nbsp;',
                            next: '&nbsp;'
                        }
                    }
                });

                // Update the label for the table
                $('div.head-label').html('<h6 class="mb-0">Land Report</h6>');
            }


            // Delete Record
            $('.datatables-basic tbody').on('click', '.delete-record', function() {
                dt_basic.row($(this).parents('tr')).remove().draw();
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left: 'prev,next',
                center: 'title',
                right: 'dayGridWeek,listWeek'
            },
            initialView: 'dayGridWeek',
            editable: true,
            dayMaxEvents: true, // allow "more" link when too many events
            eventClick:  function(event, jsEvent, view) {
                alert();
            },
            //dateClick: function(info) {
                //alert();
            //},
            eventContent: function( info ) {
              return {html: info.event.title};
            },
            events: [
                {
                title:
                    '<div class="team-leavecalen-week"><span class="badge badge-light-secondary">Sakshi Maan<br/>(SL)</span><span class="badge badge-light-primary">Ashish Kumar<br/>(AL)</span><span class="badge badge-light-success">Kundan Tiwari<br/>(OL)</span></div>',
                start: '2023-01-10'
                },
                {
                title: '<div class="team-leavecalen-week"><span class="badge badge-light-primary">Pankaj Tripathi<br />(AL)</span><span class="badge badge-light-secondary">Deepak Singh<br/>(SL)</span><span class="badge badge-light-warning">Ashish Kumar<br/>(EL)</span><span class="badge badge-light-info">Nishu Garg<br />(CL)</span><span class="badge badge-light-success">Rahul Upadhyay<br />(OL)</span></div> ',
                start: '2023-01-11'
                },

            ]
            });

            calendar.render();
        });


        $(function() {
           $("input[name='loanassesment']").click(function() {
             if ($("#Disbursement1").is(":checked")) {
               $(".selectdisbusement").show();
               $(".cibil-score").hide();
             } else {
               $(".selectdisbusement").hide();
               $(".cibil-score").show();
             }
           });
         });

        $(function() {
           $("input[name='LoanSettlement']").click(function() {
             if ($("#Dispute1").is(":checked")) {
               $("#dispute-settle").show();
               $("#normal-settle").hide();
             } else {
               $("#dispute-settle").hide();
               $("#normal-settle").show();
             }
           });
         });


    </script>
@endsection
@endsection
