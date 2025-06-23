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
                                    <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                    <li class="breadcrumb-item active">Asset List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter"
                            data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                            <a href="{{ route('finance.fixed-asset.show.import') }}" class="btn btn-secondary btn-sm mb-50 mb-sm-0">
                            <i data-feather="upload"></i> Import
                        </a> 
                        <a class="btn btn-primary btn-sm mb-50 mb-sm-0"
                            href="{{ route('finance.fixed-asset.registration.create') }}"><i data-feather="plus-circle"></i>
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
                                                <th>Date</th>
                                                <th width="100px">Series</th>
                                                <th width="100px">Doc. No</th>
                                                <th>Asset Name</th>
                                                <th>Asset Code</th>
                                                <th>Dep. Method</th>
                                                <th>Cap. Date</th>
                                                <th>Qty</th>
                                                <th>Location</th>
                                                <th>Cost Center</th>
                                                <th>Ledger Name</th>
                                                <th>Book Date</th>
                                                <th class="text-end">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @isset($data)
                                                @forelse($data as $asset)
                                                    <tr>
                                                        <td class="text-nowrap">{{ $loop->iteration }}</td>
                                                        <td class="fw-bolder text-dark text-nowrap">
                                                            {{ \Carbon\Carbon::parse($asset->document_date)->format('d-m-Y') ?? '-' }}
                                                        </td>
                                                        <td class="text-nowrap">{{ $asset?->book?->book_code ?? '-' }}</td>
                                                        <td class="text-nowrap">{{ $asset->document_number ?? '-' }}</td>
                                                        <td class="text-nowrap">
                                                            {{ $asset->asset_name ?? '-' }}</td>
                                                        <td class="text-nowrap">{{ $asset->asset_code ?? '-' }}</td>
                                                        <td class="text-nowrap">{{ $asset->depreciation_method ?? '-' }}</td>
                                                        <td class="text-nowrap">
                                                            {{ $asset->capitalize_date != null ? \Carbon\Carbon::parse($asset->capitalize_date)->format('d-m-Y') : '-' }}
                                                        </td>
                                                        <td class="text-nowrap">{{ $asset->quantity ?? '-' }}</td>
                                                        <td class="text-nowrap">{{ $asset?->location?->store_name ?? '-' }}
                                                        </td>
                                                        <td class="text-nowrap">{{ $asset?->cost_center?->name ?? '-' }}</td>
                                                        <td class="text-nowrap">{{ $asset->ledger->name ?? '-' }}</td>
                                                        <td class="text-nowrap">
                                                            {{ $asset->book_date != null ? \Carbon\Carbon::parse($asset->book_date)->format('d-m-Y') : '-' }}
                                                        </td>
                                                        <td class="tableactionnew">
                                                            <div class="d-flex align-items-center justify-content-end">
                                                                @php $statusClasss = App\Helpers\ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$asset->document_status??"draft"];  @endphp
                                                                <span
                                                                    class='badge rounded-pill {{ $statusClasss }} badgeborder-radius'>
                                                                    @if ($asset->document_status == App\Helpers\ConstantHelper::APPROVAL_NOT_REQUIRED)
                                                                        Approved
                                                                    @else
                                                                        {{ ucfirst($asset->document_status) }}
                                                                    @endif
                                                                </span>


                                                                <div class="dropdown">
                                                                    <button type="button"
                                                                        class="btn btn-sm dropdown-toggle hide-arrow p-0"
                                                                        data-bs-toggle="dropdown">
                                                                        <i data-feather="more-vertical"></i>
                                                                    </button>
                                                                    <div class="dropdown-menu dropdown-menu-end">
                                                                        @if ($asset->document_status == 'draft')
                                                                            <a class="dropdown-item"
                                                                                href="{{ route('finance.fixed-asset.registration.edit', $asset->id) }}">
                                                                                <i data-feather="edit" class="me-50"></i>
                                                                                <span>View</span>
                                                                            </a>
                                                                        @else
                                                                            <a class="dropdown-item"
                                                                                href="{{ route('finance.fixed-asset.registration.show', $asset->id) }}">
                                                                                <i data-feather="edit" class="me-50"></i>
                                                                                <span>View</span>
                                                                            </a>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="14" class="text-center">No data available</td>
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
                        <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
        <div class="modal-dialog sidebar-sm">
            <form class="add-new-record modal-content pt-0" method="POST"
                action="{{ route('finance.fixed-asset.registration.filter') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                        <label class="form-label" for="fp-range">Select Date</label>
                        <input type="text" id="fp-range" name="date" value="{{ request('date') }}"
                            class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Asset Code</label>
                        <select class="form-select" name="filter_asset">
                            <option value="">Select</option>
                            @foreach ($assetCodes as $assetCode)
                                <option value="{{ $assetCode->id }}"
                                    {{ request('filter_asset') == $assetCode->id ? 'selected' : '' }}>
                                    {{ $assetCode->asset_code }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Ledger Name</label>
                        <select class="form-select" name="filter_ledger">
                            <option value="">Select</option>
                            @foreach ($ledgers as $ledger)
                                <option value="{{ $ledger->id }}"
                                    {{ request('filter_ledger') == $ledger->id ? 'selected' : '' }}>
                                    {{ $ledger->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="filter_status">
                            <option value="">Select</option>
                            @foreach (App\Helpers\ConstantHelper::DOCUMENT_STATUS as $key => $status)
                                <option value="{{ $status }}"
                                    {{ request('filter_status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
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
                    <div class="modal modal-slide-in fade" id="modals-slide-in">
                        <div class="modal-dialog sidebar-sm">
                            <form class="add-new-record modal-content pt-0">
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close">×</button>
                                <div class="modal-header mb-1">
                                    <h5 class="modal-title" id="exampleModalLabel">New Record</h5>
                                </div>
                                <div class="modal-body flex-grow-1">
                                    <div class="mb-1">
                                        <label class="form-label" for="basic-icon-default-fullname">Full Name</label>
                                        <input type="text" class="form-control dt-full-name"
                                            id="basic-icon-default-fullname" placeholder="John Doe" aria-label="John Doe" />
                                    </div>
                                    <div class="mb-1">
                                        <label class="form-label" for="basic-icon-default-post">Post</label>
                                        <input type="text" id="basic-icon-default-post" class="form-control dt-post"
                                            placeholder="Web Developer" aria-label="Web Developer" />
                                    </div>
                                    <div class="mb-1">
                                        <label class="form-label" for="basic-icon-default-email">Email</label>
                                        <input type="text" id="basic-icon-default-email" class="form-control dt-email"
                                            placeholder="john.doe@example.com" aria-label="john.doe@example.com" />
                                        <small class="form-text"> You can use letters, numbers & periods </small>
                                    </div>
                                    <div class="mb-1">
                                        <label class="form-label" for="basic-icon-default-date">Joining Date</label>
                                        <input type="text" class="form-control dt-date" id="basic-icon-default-date"
                                            placeholder="MM/DD/YYYY" aria-label="MM/DD/YYYY" />
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label" for="basic-icon-default-salary">Salary</label>
                                        <input type="text" id="basic-icon-default-salary"
                                            class="form-control dt-salary" placeholder="$12000" aria-label="$12000" />
                                    </div>
                                    <button type="button" class="btn btn-primary data-submit me-1">Submit</button>
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
@endsection
@section('scripts')
    <script type="text/javascript" src="{{ asset('assets/js/modules/finance-table.js') }}"></script>
    <script>
       $(function() {
        // Get all request parameters from Laravel and convert to query string
        const requestParams = @json(request()->all());
        const queryString = new URLSearchParams(requestParams).toString();

        // Compose the export URL with query params
        const exportUrl = '{{ route("finance.fixed-asset.export") }}' + (queryString ? '?' + queryString : '');

        // Initialize DataTable
        const dt = initializeBasicDataTable('.datatables-basic', 'Asset_RegistrationReport', exportUrl);

        // Set label
        $('div.head-label').html('<h6 class="mb-0">Asset Registration</h6>');
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
