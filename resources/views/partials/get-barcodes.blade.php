@extends('layouts.app')

@section('content')
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
                                    <b>
                                        Item Labels
                                    </b>
                                </h4>
                                <div class="d-flex align-items-right breadcrumb-right">
                                    <button id="printBarcodesBtn" class="btn btn-dark" disabled
                                        data-header-id="{{ $header->id ?? '' }}" data-module-type="{{ $module_type }}"
                                        data-reference-id="{{ $reference_id }}">
                                        🖨️ Print
                                    </button>
                                </div>
                            </div>

                            <div class="card">
                                <div class="table-responsive">
                                    <table id="wh-details-table" class="table table-striped border w-100">
                                        <thead>
                                            <tr>
                                                <th style="width:40px;">
                                                    <div class="form-check m-0">
                                                        <input type="checkbox" class="form-check-input" id="checkAll">
                                                    </div>
                                                </th>
                                                <th>Item Name</th>
                                                <th>Item Code</th>
                                                <th>Attributes</th>
                                                <th>Lot No</th>
                                                <th>Serial No</th>
                                                <th>UID</th>
                                                <th>Vendor</th>
                                                <th>Store</th>
                                                <th>Sub Store</th>
                                                <th>QR Code</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
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

@section('scripts')
    <script>
        (function() {
            // Bootstrap-time validation popup (from controller in case of early failures)
            const bootstrapError = @json($bootstrapError);
            if (bootstrapError) {
                Swal.fire('Error', bootstrapError, 'error');
                return;
            }

            const headerId = @json($header?->morphable_id ?? 0); // or optional($header)->id ?? 0 on PHP 7
            const jobId = @json($header?->id ?? 0); // or optional($header)->id ?? 0 on PHP 7
            const moduleType = @json($module_type ?? 'grn');
            const reference_id = @json($reference_id);

            const selectedSet = new Set();
            const $btnPrint = $('#printBarcodesBtn');

            function updatePrintButton() {
                $btnPrint.prop('disabled', selectedSet.size === 0);
            }

            const table = $('#wh-details-table').DataTable({
                serverSide: true,
                processing: true,
                pageLength: 25,
                lengthMenu: [
                    [25, 50, 100],
                    [25, 50, 100]
                ],
                ajax: {
                    url: @json(route('barcodes.data', ['id' => $header?->morphable_id ?? 0])),
                    type: 'GET',
                    data: function(d) {
                        d.module_type = moduleType;
                        d.reference_id = reference_id;
                    },
                    error: function(xhr) {
                        let msg = 'Failed to load data.';
                        try {
                            msg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON
                                .message)) || msg;
                        } catch (e) {}
                        Swal.fire('Error', msg, 'error');
                    }
                },
                columns: [{
                        data: 'select',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'item_name'
                    },
                    {
                        data: 'item_code'
                    },
                    {
                        data: 'attributes'
                    },
                    {
                        data: 'batch_no'
                    },
                    {
                        data: 'serial_no'
                    },
                    {
                        data: 'item_uid'
                    },
                    {
                        data: 'vendor'
                    },
                    {
                        data: 'store'
                    },
                    {
                        data: 'sub_store'
                    },
                    {
                        data: 'qr',
                        orderable: false,
                        searchable: false
                    }
                ],
                createdRow: function(row, data) {
                    // restore checkbox state across pages
                    const idMatch = /value="(\d+)"/.exec(data.select);
                    if (idMatch) {
                        const id = idMatch[1];
                        if (selectedSet.has(id)) {
                            $(row).find('.row-check').prop('checked', true);
                        }
                        $(row).attr('data-row-id', id);
                    }
                },
                drawCallback: function() {
                    // sync header checkbox
                    const $checks = $('#wh-details-table tbody .row-check');
                    const $enabled = $checks; // all enabled here
                    const allOn = $enabled.length && $enabled.filter(':checked').length === $enabled.length;
                    $('#checkAll').prop('checked', allOn);
                    updatePrintButton();
                }
            });

            // Row checkbox
            $(document).on('change', '#wh-details-table tbody .row-check', function() {
                const id = String($(this).closest('tr').data('row-id') || this.value);
                if (this.checked) selectedSet.add(id);
                else selectedSet.delete(id);
                updatePrintButton();
                // sync header
                const $enabled = $('#wh-details-table tbody .row-check');
                const allOn = $enabled.length && $enabled.filter(':checked').length === $enabled.length;
                $('#checkAll').prop('checked', allOn);
            });

            // Select all (current page only)
            $(document).on('change', '#checkAll', function() {
                const checked = this.checked;
                $('#wh-details-table tbody .row-check').each(function() {
                    const id = String($(this).closest('tr').data('row-id') || this.value);
                    $(this).prop('checked', checked);
                    if (checked) selectedSet.add(id);
                    else selectedSet.delete(id);
                });
                updatePrintButton();
            });

            // Print
            $(document).on('click', '#printBarcodesBtn', function() {
                if (selectedSet.size === 0) {
                    Swal.fire('Note', 'Please select at least one row to print.', 'info');
                    return;
                }

                const url = `/barcodes/print/${moduleType}/${jobId}`; // <- path params

                const params = new URLSearchParams();
                if (selectedSet.size > 0) {
                    [...selectedSet].forEach(id => params.append('ids[]', id));
                }

                $.ajax({
                    url: `${url}?${params.toString()}`,
                    method: 'GET',
                    success: function(res) {
                        if (res && res.status === 200 && res.html) {
                            const w = window.open('', '', 'width=900,height=600');
                            w.document.write(res.html);
                            w.document.close();
                            w.focus();
                            w.onload = function() {
                                w.print();
                            };
                        } else {
                            Swal.fire('Error', res.message || 'Failed to generate print view.',
                                'error');
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Print request failed.';
                        try {
                            msg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON
                                .message)) || msg;
                        } catch (e) {}
                        Swal.fire('Error', msg, 'error');
                    }
                });
            });

        })();
    </script>
@endsection
