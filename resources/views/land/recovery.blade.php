@extends('layouts.app')

@section('title', 'Recovery')
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
                            <h2 class="content-header-title float-start mb-0">Recovery Entry</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{url('/')}}">Home</a></li>  
                                    <li class="breadcrumb-item active">Recovery List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7  mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right"> 
                            <button class="btn btn-primary btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>  
                            <button class="btn btn-danger btn-sm mb-50 mb-sm-0" id="reject-selected" data-bs-target="#reject" data-bs-toggle="modal"><i data-feather="x-circle"></i> Reject</button> 
                            <button class="btn btn-success btn-sm mb-50 mb-sm-0" id="approve-selected" ><i data-feather="check-circle" ></i> Approve</button>  
                            <a class="btn btn-dark btn-sm mb-50 mb-sm-0" href="{{url('/land/recovery/add')}}"><i data-feather="file-text"></i> Add Recovery</a> 
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
                                                        <input class="form-check-input" type="checkbox">
                                                    </div>
                                                </th>
                                                <th>Series</th>
                                                <th>Document No.</th>
                                                <th>Date</th>
                                                <th>Land No.</th>
                                                <th>Customer Name</th>
                                                <th>Khasara No.</th>
                                                <th>Area (sq ft)</th>
                                                <!-- <th>Address</th>
                                                <th>Cost</th> -->
                                                <th>Lease Time</th>
                                                <th>Lease Cost</th>
                                                <th>Recovery AMt</th>
                                                <th>Recovery Bal.</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recovery as $item)
                                                <tr data-id="{{ $item->id }}" 
                                                    data-application-no="{{ $item->document_no }}"
                                                    data-customer-name="{{ $item->customer }}"
                                                    data-bal-lease-amount="{{ $item->bal_lease_cost - $item->received_amount }}"
                                                    data-payment-date="{{ $item->created_at->format('d-m-Y') }}"
                                                    data-recovery-amount="{{ $item->received_amount }}"
                                                    data-payment-mode="{{ $item->payment_mode }}"
                                                    data-reference-no="{{ $item->reference_no }}"
                                                    data-bank-name="{{ $item->bank_name }}"
                                                    data-status="{{ $item->status }}"
                                                    >
                                                    <td class="pe-0">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="checkbox" id="inlineCheckbox{{ $loop->index }}" value="{{ $item->id }}">

                                                        </div>
                                                    </td>
                                                    <td class="fw-bolder text-dark">{{ $item->serie->book_name }}</td>
                                                    <td class="fw-bolder text-dark">{{ $item->document_no }}</td>
                                                    <td class="text-nowrap">{{ $item->created_at->format('d-m-Y') }}</td>
                                                    <td class="fw-bolder text-dark">{{ $item->land->land_no }}</td>
                                                    <td class="fw-bolder text-dark">@if(!empty($item->cust)){{ $item->cust->company_name}}@endif</td>
                                                    <td>{{ $item->khasara_no }}</td>
                                                    <td>{{ $item->area_sqft }}</td>
                                                    <!-- <td><span class="fw-bolder text-dark">{{ $item->plot_details }}</span></td>
                                                    <td>{{ $item->cost }}</td> -->
                                                    <td>{{ $item->lease_time }}</td>
                                                    <td>{{ $item->lease_cost }}</td>
                                                    <td>{{ $item->received_amount }}</td>
                                                    <td>{{ $item->bal_lease_cost - $item->received_amount }}</td>
                                                    <td>
                                                        <span class="badge rounded-pill badge-light-{{ $item->status == 'Approved' ? 'success' : ($item->status == 'Rejected' ? 'danger' : 'info') }} badgeborder-radius">
                                                            {{ $item->status == 'Approved' ? 'Approved' : ($item->status == 'Rejected' ? "Rejected" : 'Submitted') }}
                                                        </span>
                                                    </td>
                                                     <td class="tableactionnew">
                                                        <div class="dropdown">
                                                            <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                                                <i data-feather="more-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item" href="{{url('/land/recovery/edit/'.$item->id)}}">
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
    <!-- END: Content-->

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>

        <div class="modal fade text-start" id="viewdetail" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
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
                                            <th>Application No.</th> 
                                            <th>Customer Name</th>
                                            <th>Bal. Lease Amount</th>
                                            <th>Payment Date</th>
                                            <th>Recovery Amount</th>
                                            <th>Payment Mode</th>
                                            <th>Reference No.</th>
                                            <th>Bank Name</th>                                              
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
                    <button class="btn btn-primary btn-sm" id="tableapprove" onclick="approve()"><i data-feather="check-circle"></i> Approve</button>
                </div>
            </div>
        </div>
    </div>
    
    <input type="hidden" id="approvedid" name="approvedid">
    
    <div class="modal fade" id="approved" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Approve Application</h4>
                        <!-- <p class="mb-0 fw-bold voucehrinvocetxt mt-0">Nishu Garg | 20 Lkh | 29-07-2024</p> -->
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-2">
                    <input type="hidden" id="selected-ids" name="selected_ids">
                    <div class="row mt-1"> 
                        <div class="col-md-12">
                            <div class="mb-1">
                                <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="remarks" name="remarks" maxlength="100"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="button" id="submit-approval" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="reject" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Reject Application</h4>
                        <!-- <p class="mb-0 fw-bold voucehrinvocetxt mt-0">Nishu Garg | 20 Lkh | 29-07-2024</p> -->
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-2">
                    <input type="hidden" id="rejectselected-ids" name="rejectselected_ids">
                    <div class="row mt-1"> 
                        <div class="col-md-12">
                            <div class="mb-1">
                                <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="rejectremarks" name="remarks" maxlength="100"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer justify-content-center">  
                        <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button> 
                    <button type="button" id="reject-approval" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>
    
    
    
    
    
    
    
     
    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
        <div class="modal-dialog sidebar-sm">
            <form class="add-new-record modal-content pt-0" method="GET" action="{{ route('recovery.filter') }}"> 
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
                        <label class="form-label">Customer Name</label>
                         <input type="text" class="form-control" name="customer" value="{{ old('customer', $customer) }}">
                    </div> 
                    
                    <div class="mb-1">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option>Select</option>
                            <option value="Submitted" {{ 'Submitted' == old('status', $selectedStatus) ? 'selected' : '' }}>
                                Submitted</option>
                            <option value="Approved" {{ 'Approved' == old('status', $selectedStatus) ? 'selected' : '' }}>
                                Approved</option>
                            <option value="Rejected" {{ 'Rejected' == old('status', $selectedStatus) ? 'selected' : '' }}>
                                Rejected</option>
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
@section('scripts')
     <script>
        // Select the header checkbox
document.querySelector('.form-check-input[type="checkbox"]').addEventListener('change', function() {
    // Get all the row checkboxes
    let checkboxes = document.querySelectorAll('tbody .form-check-input[type="checkbox"]');
    
    // Set the checked status of all row checkboxes to the header checkbox's checked status
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = event.target.checked;
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
        // Customize headers for CSV export starting from index 1
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
                return 'Lease Time';
            case 9:
                return 'Lease Cost';
            case 10:
                return 'Recovery AMt';
            case 11:
                return 'Recovery Bal.';
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
        // Customize headers for CSV export starting from index 1
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
                return 'Lease Time';
            case 9:
                return 'Lease Cost';
            case 10:
                return 'Recovery AMt';
            case 11:
                return 'Recovery Bal.';
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
    $('div.head-label').html('<h6 class="mb-0">Recovery Report</h6>');
}


            // Delete Record
            $('.datatables-basic tbody').on('click', '.delete-record', function() {
                dt_basic.row($(this).parents('tr')).remove().draw();
            });
        });
    
        
         
       $(document).on('click', '.view-details', function() {
    var row = $(this).closest('tr');
    var id = row.data('id');

    // Extract data from the row
    var applicationNo = row.data('application-no');
    var customerName = row.data('customer-name');
    var balLeaseAmount = row.data('bal-lease-amount');
    var paymentDate = row.data('payment-date');
    var recoveryAmount = row.data('recovery-amount');
    var paymentMode = row.data('payment-mode');
    var referenceNo = row.data('reference-no');
    var bankName = row.data('bank-name');
    var status = row.data('status');

    // Populate the modal fields
    var modal = $('#viewdetail');
    if(status == 'Approved')
    {
      $("#tableapprove").css('display','none');    
    }
    else
    {
        $("#tableapprove").css('display','block');
    }
    

    $("#approvedid").val(id);
    modal.find('tbody').html(`
        <tr>
            <td>1</td>
            <td>${applicationNo}</td>
            <td class="fw-bolder text-dark">${customerName}</td>
            <td>${balLeaseAmount}</td>
            <td>${paymentDate}</td>
            <td>${recoveryAmount}</td>
            <td>${paymentMode}</td>
            <td>${referenceNo}</td>
            <td>${bankName}</td>
        </tr>
    `);
});

    //approved 
       document.getElementById('approve-selected').addEventListener('click', function () {
    let selectedIds = [];
    document.querySelectorAll('.form-check-input:checked').forEach(function (checkbox) {
        selectedIds.push(checkbox.value);
    });

    if (selectedIds.length === 0) {
        alert('Please select at least one record.');

    } else {
        // Store selected IDs in a hidden input field in the modal
        document.getElementById('selected-ids').value = selectedIds.join(',');
        $("#approved").modal('show');
    }
});

      document.getElementById('submit-approval').addEventListener('click', function () {
        let selectedIds = document.getElementById('selected-ids').value;
        let remarks = document.getElementById('remarks').value;

        if (!remarks) {
            alert('Remarks are required.');
            return;
        }

        // Send the request via AJAX
        fetch("{{url('land/approve-recovery')}}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                ids: selectedIds,
                remarks: remarks
            })
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  alert('Status updated successfully!');
                  location.reload();
              } else {
                  alert('Failed to update status.');
              }
          });
    });

    function approve() 
    {
        let selectedIds = [];
        selectedIds.push(document.getElementById('approvedid').value);
        
        document.getElementById('selected-ids').value = selectedIds.join(',');
        
        $("#approved").modal('show');
    }


          //rejected 
       document.getElementById('reject-selected').addEventListener('click', function () {
    let selectedIds = [];
    document.querySelectorAll('.form-check-input:checked').forEach(function (checkbox) {
        selectedIds.push(checkbox.value);
    });

    if (selectedIds.length === 0) {
        alert('Please select at least one record.');
    } else {
        // Store selected IDs in a hidden input field in the modal
        document.getElementById('rejectselected-ids').value = selectedIds.join(',');
    }
});

      document.getElementById('reject-approval').addEventListener('click', function () {
        let selectedIds = document.getElementById('rejectselected-ids').value;
        let remarks = document.getElementById('rejectremarks').value;

        if (!remarks) {
            alert('Remarks are required.');
            return;
        }

        // Send the request via AJAX
        fetch("{{url('land/reject-recovery')}}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                ids: selectedIds,
                remarks: remarks
            })
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  alert('Status updated successfully!');
                  location.reload();
              } else {
                  alert('Failed to update status.');
              }
          });
    });



 
        
    </script>

@endsection
@endsection