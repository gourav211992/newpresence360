@extends('layouts.app')

@section('title', 'On-Lease')
@section('content')
<!-- BEGIN: Content-->
    <!-- BEGIN: Content -->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-6 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Land on Lease</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ url('/land/on-lease') }}">Home</a></li>
                                    <li class="breadcrumb-item active">All Request</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-primary btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                        <a href="{{ url('/land/on-lease/add') }}" class="btn btn-dark btn-sm mb-50 mb-sm-0"><i data-feather="file-text"></i> Add New</a>
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
                                                <th class="pe-0">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" id="inlineCheckbox1">
                                                    </div>
                                                </th>
                                                <th>Series</th>
                                                <th>Document No.</th>
                                                <th>Date</th>
                                                <th>Land No.</th>
                                                <th>Customer Name</th>
                                                <th>Khasara No.</th>
                                                <th>Area (sq ft)</th>
                                                <th>Agreement No</th>
                                                <th>Date of Agreement</th>
                                                <th>Lease Time</th>
                                                <th>Lease Cost</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($leases as $lease)
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check form-check-inline">
                                                         <input class="form-check-input" type="checkbox" id="inlineCheckbox{{ $lease->id }}" value="{{ $lease->id }}">
                                                    </div>
                                                </td>
                                                <td class="fw-bolder text-dark">{{ $lease->serie->book_name }}</td>
                                                <td class="fw-bolder text-dark">{{ $lease->lease_no }}</td>
                                                <td class="text-nowrap">{{ date('d-m-Y',strtotime($lease->created_at)) }}</td>
                                                <td class="fw-bolder text-dark">{{ $lease->land->land_no }}</td>
                                                <td class="fw-bolder text-dark">@if(!empty($lease->cust)) {{  $lease->cust->company_name  }} @else N/A @endif</td>
                                                <td>{{ $lease->khasara_no }}</td>
                                                <td>{{ $lease->area_sqft }}</td>
                                                <td>{{ $lease->agreement_no }}</td>
                                                <td>{{ $lease->date_of_agreement }}</td>
                                                <td>{{ $lease->lease_time }}</td>
                                                <td>{{ $lease->lease_cost }}</td>
                                                <td>
                                                    <span class="badge rounded-pill badge-light-{{ $lease->status == 'Allotted' ? 'danger' : 'success' }} badgeborder-radius">
                                                        {{ "Allotted" }}
                                                    </span>
                                                </td>
                                                <td class="tableactionnew">
                                                    <div class="dropdown">
                                                        <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                                            <i data-feather="more-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <a class="dropdown-item" href="{{url('/land/on-lease/edit/'.$lease->id)}}">
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
    <!-- END: Content -->

    <!-- END: Content-->
    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>
    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
        <div class="modal-dialog sidebar-sm">
            <form class="add-new-record modal-content pt-0" method="GET" action="{{ route('lease.filter') }}">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                          <label class="form-label" for="fp-range">Select Date Range</label>
                         <input type="text" id="fp-range" name="date_range" class="form-control flatpickr-range"
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" value="{{ old('date_range', $selectedDateRange) }}" />
                    </div>
                    
                    <div class="mb-1">
                        <label class="form-label">Land No.</label>
                        <input type="text" class="form-control" name="land_no" value="{{ old('land_no', $land_no) }}">
                    </div> 
                    
                    <div class="mb-1">
                        <label class="form-label">Pincode</label>
                        <input type="text" class="form-control" name="pincode" value="{{ old('pincode', $pincode) }}">
                    </div> 
                    
                    <div class="mb-1">
                        <label class="form-label">Status</label>
                        <select class="form-select">
                            <option>Select</option>
                            <option value="Allotted" {{ 'Allotted' == old('status', $selectedStatus) ? 'selected' : '' }}>
                                Allotted</option>
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
@endsection
@section('scripts')
     <script>
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
            },
            {
                targets: 12 // Adjust this index according to your column number
            }
        ],
        dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        displayLength: 7,
        lengthMenu: [7, 10, 25, 50, 75, 100],
        buttons: [
            {
                extend: 'collection',
                className: 'btn btn-outline-secondary dropdown-toggle',
                text: feather.icons['share'].toSvg({
                    class: 'font-small-4 mr-50'
                }) + 'Export',
                buttons: [
                    {
                        extend: 'csv',
                        text: feather.icons['file-text'].toSvg({
                            class: 'font-small-4 mr-50'
                        }) + 'Csv',
                        className: 'dropdown-item',
                        filename: 'On_Lease_Report', // Set filename as needed
                        exportOptions: {
                            columns: function (idx, data, node) {
                                // Exclude the first and last columns from CSV export
                                return idx !== 0 && idx !== 13; // Adjusted index for the last column
                            },
                            format: {
    header: function(data, columnIdx) {
        // Customize headers for CSV export
        switch (columnIdx) {
            case 1:
                return 'Series';
            case 2:
                return 'Document No.';
            case 3:
                return 'Date';
            case 4:
                return 'Land No.';
            case 5:
                return 'Customer Name';
            case 6:
                return 'Khasara No.';
            case 7:
                return 'Area (sq ft)';
            case 8:
                return 'Agreement No';
            case 9:
                return 'Date of Agreement';
            case 10:
                return 'Lease Time';
            case 11:
                return 'Lease Cost';
            case 12:
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
                        text: feather.icons['file'].toSvg({
                            class: 'font-small-4 mr-50'
                        }) + 'Excel',
                        className: 'dropdown-item',
                        filename: 'On_Lease_Report', // Set filename as needed
                        exportOptions: {
                            columns: function (idx, data, node) {
                                // Exclude the first and last columns from Excel export
                                return idx !== 0 && idx !== 13; // Adjusted index for the last column
                            },
                            format: {
    header: function(data, columnIdx) {
        // Customize headers for CSV export
        switch (columnIdx) {
            case 1:
                return 'Series';
            case 2:
                return 'Document No.';
            case 3:
                return 'Date';
            case 4:
                return 'Land No.';
            case 5:
                return 'Customer Name';
            case 6:
                return 'Khasara No.';
            case 7:
                return 'Area (sq ft)';
            case 8:
                return 'Agreement No';
            case 9:
                return 'Date of Agreement';
            case 10:
                return 'Lease Time';
            case 11:
                return 'Lease Cost';
            case 12:
                return 'Status';
            
            default:
                return data;
        }
    }
}

                        }
                    }
                ],
                init: function (api, node, config) {
                    $(node).removeClass('btn-secondary');
                    $(node).parent().removeClass('btn-group');
                    setTimeout(function () {
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
    

        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })
   
        
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
