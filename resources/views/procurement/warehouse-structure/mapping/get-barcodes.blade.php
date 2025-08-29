@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title">
                                    <!-- {{ $level?->store?->store_name }} - {{ $level?->sub_store?->name }}  -->
                                </h4>
                                <div class="d-flex align-items-right breadcrumb-right">
                                    <button id="printBarcodesBtn" class="btn btn-dark" data-location-id="{{ $level->store_id }}" data-store-id="{{ $level->sub_store_id }}" data-level-id="{{ $level->id }}">
                                        🖨️ Print
                                    </button>
                                </div>
                            </div>
                            <div class="card">
                                <div class="table-responsive">
                                    <table id="wh-details-table"
                                        class="table table-striped po-order-detail border w-100">
                                    `   <thead>
                                            <tr>
                                                <th style="width:40px;">
                                                    <div class="form-check m-0">
                                                        <input type="checkbox" class="form-check-input" id="checkAll">
                                                    </div>
                                                </th>
                                                <!-- <th>S.No.</th> -->
                                                <th>Location</th>
                                                <th>Warehouse</th>
                                                <th>Name</th>
                                                <th>Storage No.</th>
                                                <th>Heirarchy</th>
                                                <th>QR Code</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($whDetails as $key => $val)
                                                @php
                                                    $hasStorage = !empty($val->storage_number);
                                                @endphp
                                                <tr data-row-id="{{ $val->id }}">
                                                    {{-- Row checkbox (enabled only if storage_number exists) --}}
                                                    <td>
                                                        <div class="form-check m-0">
                                                            <input
                                                                type="checkbox"
                                                                class="form-check-input row-check"
                                                                value="{{ $val->id }}"
                                                                data-has-storage="{{ $hasStorage ? '1' : '0' }}"
                                                                {{ $hasStorage ? '' : 'disabled' }}
                                                            >
                                                        </div>
                                                    </td>
                                                    <!-- <td>{{ $loop->iteration }}</td> -->
                                                    <td>{{ $val?->store?->store_name }}</td>
                                                    <td>{{ $val?->sub_store?->name }}</td>
                                                    <td>{{ $val?->name }}</td>
                                                    <td>{{ $val?->storage_number }}</td>
                                                    <td>{{ str_replace('-', ' > ', $val?->heirarchy_name) }}</td>
                                                    <td>
                                                        @if($val->storage_number)
                                                            <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($val->storage_number, 'QRCODE') }}"
                                                                class="barcode-img"
                                                                alt="{{ $val->storage_number }}"
                                                                style="height:60px;width:60px;" />
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8">No Record Found</td>
                                                </tr>
                                            @endforelse
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
@endsection
@push('styles')
    {{-- If your layout doesn’t already include DataTables assets, uncomment these CDNs --}}
    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css"> --}}
@endpush

@section('scripts')
    {{-- If your layout doesn’t already include DataTables assets, uncomment these CDNs --}}
    {{-- <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script> --}}

    <script type="text/javascript" src="{{ asset('assets/js/modules/common-datatable.js') }}"></script>
    <script>
        // Feather icons
        $(window).on("load", function () {
            if (window.feather) feather.replace({ width: 14, height: 14 });
        });

        // Keep this near your DataTable code
        const selectedSet = new Set(); // stores selected IDs

        function updatePrintButtonState() {
            const anySelected = selectedSet.size > 0;
            $('#printBarcodesBtn').prop('disabled', !anySelected);
        }

        $(function () {
            // Initialize DataTable
            const table = $('#wh-details-table').DataTable({
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                order: [], // keep original order
                responsive: true,
                columnDefs: [
                    { targets: 0, orderable: false, searchable: false }, // checkbox
                    { targets: -1, orderable: false, searchable: false } // QR column
                ],
                // If you want to localize labels, add "language" here.
            });

            // Persist selected IDs across paging/search
            selectedSet.clear();
            $('#checkAll').prop('checked', false);
            $('#wh-details-table tbody .row-check').prop('checked', false);
            updatePrintButtonState(); // disables button on load

            function syncHeaderCheckbox() {
                const $enabledOnPage = $('#wh-details-table tbody .row-check:enabled');
                const allOnPageChecked =
                    $enabledOnPage.length > 0 &&
                    $enabledOnPage.filter(':checked').length === $enabledOnPage.length;
                $('#checkAll').prop('checked', allOnPageChecked);
            }

            // Restore checks on redraw
            table.on('draw.dt', function () {
                $('#wh-details-table tbody .row-check').each(function () {
                    const id = String(this.value);
                    this.checked = selectedSet.has(id);
                });
                syncHeaderCheckbox();
                updatePrintButtonState(); // keep button state correct after paging/search
            });

            // Row checkbox toggle
            $(document).on('change', '#wh-details-table tbody .row-check', function () {
                const id = String($(this).val());
                if (this.checked) selectedSet.add(id);
                else selectedSet.delete(id);

                syncHeaderCheckbox();
                updatePrintButtonState(); // enable if >0, disable if 0
            });

            // Select All (current page enabled rows only)
            $(document).on('change', '#checkAll', function () {
                const checked = this.checked;
                $('#wh-details-table tbody .row-check:enabled').each(function () {
                    const id = String(this.value);
                    this.checked = checked;
                    if (checked) selectedSet.add(id); else selectedSet.delete(id);
                });

                updatePrintButtonState(); // enable if >0, disable if 0
            });

            // Print
            $(document).on('click', '#printBarcodesBtn', function () {
                const locationId = $(this).data('location-id');
                const storeId    = $(this).data('store-id');
                const levelId    = $(this).data('level-id');

                const base = `/warehouse-mappings/${locationId}/print-barcodes`;
                const params = new URLSearchParams({ sub_store: storeId, wh_level: levelId });

                // Send ids[] array; if empty, backend will print ALL rows having storage_number
                if (selectedSet.size > 0) {
                    [...selectedSet].forEach(id => params.append('ids[]', id));
                }

                $.ajax({
                    url: `${base}?${params.toString()}`,
                    method: 'GET',
                    success: function (response) {
                        if (response.status === 200 && response.html) {
                            const w = window.open('', '', 'width=900,height=600');
                            w.document.write(response.html);
                            w.document.close();
                            w.focus();
                            w.onload = function () { w.print(); /* w.close(); */ };
                        } else {
                            Swal.fire('Error', response.message || 'Failed to generate barcode print view.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'AJAX request failed.', 'error');
                    }
                });
            });
        });
    </script>
@endsection



