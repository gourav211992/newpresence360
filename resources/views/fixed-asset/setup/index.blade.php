@extends('layouts.app')



@section('title', 'Fixed Asset Setup')

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
                            <h2 class="content-header-title float-start mb-0">Setup</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a
                                            href="{{ route('finance.fixed-asset.setup.index') }}">Home</a></li>
                                    <li class="breadcrumb-item active">Setup List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter"
                            data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                        <a class="btn btn-primary btn-sm mb-50 mb-sm-0"
                            href="{{ route('finance.fixed-asset.setup.create') }}"><i data-feather="plus-circle"></i> Add
                            New</a>
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
                                                <th>Asset Category</th>
                                                <th>Act Type</th>
                                                <th>Ledger</th>
                                                <th>Ledger Group</th>
                                                <th>Expected Life</th>
                                                <th>Salvage %</th>
                                                {{-- <th>Dep. Method</th>
                                                <th>Dep. %</th>
                                                <th>Maint Schedule</th> --}}
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($data as $index => $asset)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="fw-bolder text-dark">{{ $asset->assetCategory->name ?? '-' }}</td>
                                                <td>{{ $asset->act_type=="income_tax"?"Income Tax":"Company" }}</td>
                                                <td>{{ $asset->ledger->name ?? '-' }}</td>
                                                <td>{{ $asset->ledgerGroup->name ?? '-' }}</td>
                                                <td>{{ $asset->expected_life_years }}</td>
                                                <td>{{ $asset->salvage_percentage??'-' }}</td>
                                                {{-- <td><span class="badge rounded-pill badge-light-secondary badgeborder-radius">{{ $asset->depreciation_method }}</span></td>
                                                <td>{{ $asset->depreciation_percentage }}</td>
                                                <td>{{ $asset->maintenance_schedule }}</td> --}}
                                                <td><span class="badge rounded-pill badge-light-{{ $asset->status == 'active' ? 'success' : 'danger' }} badgeborder-radius">{{ ucfirst($asset->status) }}</span></td>
                                                <td class="tableactionnew">
                                                    <div class="dropdown">
                                                        <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                                            <i data-feather="more-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            {{-- <a class="dropdown-item" href="{{ route('finance.fixed-asset.setup.show', $asset->id) }}">
                                                                <i data-feather="edit" class="me-50"></i>
                                                                <span>View Detail</span>
                                                            </a> --}}
                                                            <a class="dropdown-item" href="{{ route('finance.fixed-asset.setup.edit', $asset->id) }}">
                                                                <i data-feather="edit-3" class="me-50"></i>
                                                                <span>View</span>
                                                            </a>
                                                            <a class="dropdown-item" href="{{ route('finance.fixed-asset.setup.destroy', $asset->id) }}" onclick="event.preventDefault(); document.getElementById('delete-form-{{ $asset->id }}').submit();">
                                                                <i data-feather="trash-2" class="me-50"></i>
                                                                <span>Delete</span>
                                                            </a>
                                                            <form id="delete-form-{{ $asset->id }}" action="{{ route('finance.fixed-asset.setup.destroy', $asset->id) }}" method="POST" style="display: none;">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
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
            <form class="add-new-record modal-content pt-0">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                        <label class="form-label" for="fp-range">Select Date</label>
                        <!--                        <input type="text" id="fp-default" class="form-control flatpickr-basic" placeholder="YYYY-MM-DD" />-->
                        <input type="text" id="fp-range" class="form-control flatpickr-range bg-white"
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Category</label>
                        <select class="form-select">
                             <option value="">Select</option>
                            @foreach($categories as $cat)
                            <option value="{{$cat->id}}">{{$cat->name}}</option>
                            @endforeach
                     
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Status</label>
                        <select class="form-select">
                            <option>Select</option>
                            <option>Active</option>
                            <option>Inactive</option>
                        </select>
                    </div>

                </div>
                <div class="modal-footer justify-content-start">
                    <button type="button" class="btn btn-primary data-submit mr-1">Apply</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    </div>
@endsection

@section('scripts')
<script type="text/javascript" src="{{asset('assets/js/modules/finance-table.js')}}"></script>

    <script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })
        $(function() {
    var dt_basic_table = $('.datatables-basic');

    if (dt_basic_table.length) {
        var dt_basic = dt_basic_table.DataTable({
            order: [], // Disable default sorting
            columnDefs: [
                {
                    orderable: false,
                    targets: [0, -1] // Disable sorting for the first (#) and last (Action) columns
                },
                {
                    targets: 6, // Status column
                    render: function(data, type, row, meta) {
                        if (type === 'export') {
                            return data; // Return raw data for export
                        }
                 return data;
                    }
                }
            ],
            dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            displayLength: 10, // Set initial row display count
            lengthMenu: [10, 25, 50, 75, 100], // Row count options
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
                            filename: 'Asset_TrackingReport',
                                  exportOptions: {
      columns: ':not(:last-child)' // exclude the last column
    },
         
                        }
                    ],
                    init: function(api, node, config) {
                        $(node).removeClass('btn-secondary');
                        $(node).parent().removeClass('btn-group');
                        setTimeout(function() {
                            $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
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

        // Set table header label
        $('div.head-label').html('<h6 class="mb-0">Asset Tracking Report</h6>');

        // Handle delete record functionality
        $('.datatables-basic tbody').on('click', '.delete-record', function() {
            dt_basic.row($(this).parents('tr')).remove().draw();
        });
    }
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
             handleRowSelection('.datatables-basic');

    </script>


@endsection
