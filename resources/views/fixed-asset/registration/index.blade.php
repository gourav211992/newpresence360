@extends('layouts.app')
@section('css')
    <style type="text/css">
        .image-uplodasection {
            position: relative;
            margin-bottom: 10px;
        }

        .fileuploadicon {
            font-size: 24px;
        }



        .delete-img {
            position: absolute;
            top: 5px;
            right: 5px;
            cursor: pointer;
        }

        .preview-image {
            max-width: 100px;
            max-height: 100px;
            display: block;
            margin-top: 10px;
        }
    </style>
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
                            <h2 class="content-header-title float-start mb-0">Fixed Asset Registration</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{route('/')}}">Home</a></li>
                                    <li class="breadcrumb-item active">Asset List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
						<a class="btn btn-primary btn-sm mb-50 mb-sm-0" href="{{route('finance.fixed-asset.registration.create')}}"><i data-feather="plus-circle"></i> Add New</a>
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
                                                <th>Asset Name</th>
                                                <th>Asset Code</th>
                                                <th>Ledger Name</th>
                                                <th>Book Date</th>
                                                <th>Location</th>
                                                <th>Qty</th>
                                                <th>Cap. Date</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @isset($data)
                                            @forelse($data as $asset)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td class="fw-bolder text-dark">{{ $asset->asset_name??"-" }}</td>
                                                    <td>{{ $asset->asset_code??"-" }}</td>
                                                    <td>{{ $asset->ledger->name??"-" }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($asset->book_date)->format('d-m-Y')??"-" }}</td>
                                                    <td>{{ $asset->location ??"-" }}</td>
                                                    <td>{{ $asset->quantity ??"-" }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($asset->capitalize_date)->format('d-m-Y') ??"-"}}</td>
                                                   
                                                    <td>
                                                        @php $statusClasss = App\Helpers\ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$asset->document_status??"draft"];  @endphp
                                                        <span
                                                            class='badge rounded-pill {{ $statusClasss }} badgeborder-radius'>
                                                            @if ($asset->document_status == App\Helpers\ConstantHelper::APPROVAL_NOT_REQUIRED)
                                                                Approved
                                                            @else
                                                                {{ ucfirst($asset->document_status) }}
                                                            @endif
                                                        </span>
                                                    </td>

                                                    <td class="tableactionnew">
                                                        <div class="dropdown">
                                                            <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                                                <i data-feather="more-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                @if($asset->document_status=='draft')
                                                                <a class="dropdown-item" href="{{route('finance.fixed-asset.registration.edit', $asset->id)}}">
                                                                    <i data-feather="edit-3" class="me-50"></i>
                                                                    <span>Edit</span>
                                                                </a>
                                                                @else
                                                                <a class="dropdown-item" href="{{route('finance.fixed-asset.registration.show', $asset->id)}}">
                                                                    <i data-feather="edit" class="me-50"></i>
                                                                    <span>View Detail</span>
                                                                </a>
                                                                @endif    
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="10" class="text-center">No data available</td>
                                                </tr>
                                            @endforelse
                                            @endisset
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
            columnDefs: [{
                    orderable: false,
                    targets: [0, -1] // Disable sorting on the first and last columns (Action and # columns)
                },
                {
                    targets: 8, // Adjust this index according to your column number (Status column)
                    render: function(data, type, row, meta) {
                        if (type === 'export') {
                            var $node = $('<div>').html(data);
                            return $node.find('.usernames').text();
                        }
                        return data;
                    }
                }
            ],
            dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            displayLength: 7,
            lengthMenu: [7, 10, 25, 50, 75, 100],
            buttons: [{
                extend: 'collection',
                className: 'btn btn-outline-secondary dropdown-toggle',
                text: feather.icons['share'].toSvg({
                    class: 'font-small-4 mr-50'
                }) + 'Export',
                buttons: [{
                        extend: 'csv',
                        text: feather.icons['file-text'].toSvg({
                            class: 'font-small-4 mr-50'
                        }) + 'Csv',
                        className: 'dropdown-item',
                        filename: 'Asset_RegistrationReport', // Set filename as needed
                        exportOptions: {
                            columns: function(idx, data, node) {
                                // Exclude the first and last columns from CSV export
                                return idx !== 0 && idx !== 9; // Assuming 9 is the Action column
                            },
                            format: {
                                header: function(data, columnIdx) {
                                    // Customize headers for CSV export
                                    switch (columnIdx) {
                                        case 1:
                                            return 'Asset Name';
                                        case 2:
                                            return 'Asset Code';
                                        case 3:
                                            return 'Ledger Name';
                                        case 4:
                                            return 'Book Date';
                                        case 5:
                                            return 'Location';
                                        case 6:
                                            return 'Quantity';
                                        case 7:
                                            return 'Capitalization Date';
                                        case 8:
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
                        filename: 'Asset_RegistrationReport', // Set filename as needed
                        exportOptions: {
                            columns: function(idx, data, node) {
                                // Exclude the first and last columns from Excel export
                                return idx !== 0 && idx !== 9; // Assuming 9 is the Action column
                            },
                            format: {
                                header: function(data, columnIdx) {
                                    // Customize headers for Excel export
                                    switch (columnIdx) {
                                        case 1:
                                            return 'Asset Name';
                                        case 2:
                                            return 'Asset Code';
                                        case 3:
                                            return 'Ledger Name';
                                        case 4:
                                            return 'Book Date';
                                        case 5:
                                            return 'Location';
                                        case 6:
                                            return 'Quantity';
                                        case 7:
                                            return 'Capitalization Date';
                                        case 8:
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
            }],
            language: {
                paginate: {
                    previous: '&nbsp;',
                    next: '&nbsp;'
                }
            }
        });

        // Update the label for the table to "Asset Registration"
        $('div.head-label').html('<h6 class="mb-0">Asset Registration</h6>');
    }

    // Flat Date picker (if necessary)
    if (dt_date_table.length) {
        dt_date_table.flatpickr({
            monthSelectorType: 'static',
            dateFormat: 'm/d/Y'
        });
    }

    // Delete Record (if applicable)
    $('.datatables-basic tbody').on('click', '.delete-record', function() {
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