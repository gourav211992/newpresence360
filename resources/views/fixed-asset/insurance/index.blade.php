@extends('layouts.app')


@section('title', 'Fixed Asset')

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
                            <h2 class="content-header-title float-start mb-0">Insurance</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a
                                            href="{{ route('finance.fixed-asset.insurance.index') }}">Home</a></li>
                                    <li class="breadcrumb-item active">Asset List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-end col-md-7 mb-2 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-primary btn-sm mb-50 mb-sm-0" data-bs-target="#filter"
                            data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                        <a class="btn btn-dark btn-sm mb-50 mb-sm-0"
                            href="{{ route('finance.fixed-asset.insurance.create') }}"><i data-feather="file-text"></i>
                            Add New</a>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">


                                <div class="table-responsive">
                                    <table class="datatables-basic table myrequesttablecbox tableistlastcolumnfixed ">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Renewal Date</th>
                                                <th>Asset Name</th>
                                                <th>Asset Code</th>
                                                <th>Location</th>
                                                <th>Cost Center</th>
                                                <th>Insured Value</th>
                                                <th>Expiry Date</th>
                                                <th>Policy No.</th>
                                                <th>Lien / Security Details</th>
                                                <th class="text-end">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (isset($data))
                                                @forelse($data as $key => $item)
                                                    <tr>
                                                        <td class="text-nowrap">{{ $key + 1 }}</td>
                                                        <td class="fw-bolder text-nowrap text-dark">{{ $item->renewal_date }}</td>
                                                        <td class="text-nowrap">{{ $item?->asset?->asset_name }}</td>
                                                        <td class="text-nowrap">{{ $item?->asset?->asset_code }}</td>
                                                        <td class="text-nowrap">{{ $item?->location?->store_name }}</td>
                                                        <td class="text-nowrap">{{ $item?->cost_center?->name }}</td>
                                                        <td class="text-nowrap">{{ $item->insured_value }}</td>
                                                        <td class="text-nowrap">{{ $item->expiry_date }}</td>
                                                        <td class="text-nowrap">{{ $item->policy_no }}</td>
                                                        <td class="text-nowrap">{{ $item->lien_security_details }}</td>
                                                        <td class="tableactionnew">
                                                            <div class="d-flex align-items-center justify-content-end">
                                                                 @if (now()->greaterThan($item->expiry_date))
                                                                 <span class="badge rounded-pill bg-danger">Expired</span>
                                                            @else
                                                             <span class="badge rounded-pill bg-success">Renewed</span>
                                                                
                                                            @endif
                                                            <div class="dropdown">
                                                                <button type="button"
                                                                    class="btn btn-sm dropdown-toggle hide-arrow p-0"
                                                                    data-bs-toggle="dropdown">
                                                                    <i data-feather="more-vertical"></i>
                                                                </button>
                                                                <div class="dropdown-menu dropdown-menu-end">
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('finance.fixed-asset.insurance.edit', $item->id) }}">
                                                                        <i data-feather="edit" class="me-50"></i>
                                                                        <span>View</span>
                                                                    </a>

                                                                </div>
                                                            </div>
                                                        </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="10" class="text-center">No data found</td>
                                                    </tr>
                                                @endforelse
                                            @else
                                                <tr>
                                                    <td colspan="10" class="text-center">No data found</td>
                                                </tr>

                                            @endif
                                        </tbody>


                                    </table>
                                </div>





                            </div>
                        </div>
                    </div>
                    <!-- Modal to add new record -->
                </section>
            </div>
        </div>
    </div>
    <!-- END: Content-->

    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
        <div class="modal-dialog sidebar-sm">
            <!-- Assuming the form is in the same Blade view -->
            <form class="add-new-record modal-content pt-0" method="GET"
                action="{{ route('finance.fixed-asset.insurance.index') }}">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                        <label class="form-label" for="fp-range">Select Date Range</label>
                        <input type="text" id="fp-range" name="date_range" class="form-control flatpickr-range"
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" value="{{ old('date_range') }}" />
                    </div>

                    <div class="mb-1">
                        <label class="form-label" for="asset">Asset Code</label>
                        <select name="asset" id="asset" class="form-select select2">
                            <option value="">Select</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}" @if (old('asset') == $asset->id) selected @endif>
                                    {{ $asset->asset_code }} ({{ $asset->asset_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" name="status">
                            <option value="">Select</option>
                            <option value="expired">Expired</option>
                            <option value="renewed">Renewed</option>

                        </select>
                    </div>
                    <div class="modal-footer justify-content-start">
                        <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
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
        $(function () {
    var dt_basic_table = $('.datatables-basic');

    if (dt_basic_table.length) {
        var dt_basic = dt_basic_table.DataTable({
            order: [], // Disable default sorting
            columnDefs: [
                {
                    orderable: false,
                    targets: [0, -1], // Disable sorting for the first (#) and last (Action) columns
                },
            ],
            dom:
                '<"d-flex justify-content-between align-items-center mx-2 row"' +
                '<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B>' +
                '<"col-sm-12 col-md-3"f>>t' +
                '<"d-flex justify-content-between mx-2 row"' +
                '<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            scollX:true,
            displayLength: 10, // Set initial row display count
            lengthMenu: [10, 25, 50, 75, 100], // Row count options
            buttons: [
                {
                    extend: 'collection',
                    className: 'btn btn-outline-secondary dropdown-toggle',
                    text: feather.icons['share'].toSvg({
                        class: 'font-small-4 mr-50',
                    }) + 'Export',
                    buttons: [
                        {
                            extend: 'excel',
                            text: feather.icons['file'].toSvg({
                                class: 'font-small-4 mr-50',
                            }) + 'Excel',
                            className: 'dropdown-item',
                            filename: 'Asset_Tracking_Report',
                                  exportOptions: {
      columns: ':not(:last-child)' // exclude the last column
    },

                        },
                    ],
                    init: function (api, node, config) {
                        $(node).removeClass('btn-secondary');
                        $(node).parent().removeClass('btn-group');
                        setTimeout(function () {
                            $(node)
                                .closest('.dt-buttons')
                                .removeClass('btn-group')
                                .addClass('d-inline-flex');
                        }, 50);
                    },
                },
            ],
            language: {
                paginate: {
                    previous: '&nbsp;',
                    next: '&nbsp;',
                },
            },
        });

        // Set table header label
        $('div.head-label').html('<h6 class="mb-0">Asset Tracking Report</h6>');

        // Handle delete record functionality
        $('.datatables-basic tbody').on('click', '.delete-record', function () {
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
