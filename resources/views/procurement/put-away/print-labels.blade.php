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
                                    {{ $mrn->book_code ?? 'GRN' }}-{{ $mrn->document_number }} : 
                                    <span class="badge rounded-pill badge-light-{{$mrn->display_status === 'Posted' ? 'info' : 'secondary'}} forminnerstatus">
                                        <span class = "text-dark" >Status</span> : <span class="{{$docStatusClass}}">{{$mrn->display_status}}</span>
                                    </span>
                                </h4>
                                <div class="d-flex align-items-right breadcrumb-right">
                                    <!-- <button type="button" onClick="javascript: history.go(-1)" class="btn btn-secondary mb-50 mb-sm-0">
                                        <i data-feather="arrow-left-circle"></i> Back
                                    </button> -->
                                    <button id="printBarcodesBtn" class="btn btn-dark" data-mrn-id="{{ $mrn->id }}">
                                        🖨️ Print
                                    </button>
                                </div> 
                            </div>  
                            <div class="card">
                                <div class="table-responsive">
                                    <table class="datatables-basic table myrequesttablecbox tableistlastcolumnfixed">
                                        <thead>
                                            <tr>
                                                <th>S.No.</th>
                                                <!-- <th>Unit</th> -->
                                                <th>Quantity</th>
                                                <th>Packet Number</th>
                                                <th>QR Code/Bar Code</th>
                                                <!-- <th>Status</th> -->
                                            </tr>
                                        </thead>
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
    <script type="text/javascript" src="{{asset('assets/js/modules/common-datatable.js')}}"></script>

    <script>
        $(window).on("load", function () {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14,
                });
            }
        });

        $(document).ready(function() {
            function renderData(data) {
                    return data ? data : '';
                }
                var columns = [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    // { data: 'inventory_uom', code: 'inventory_uom', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
                    //     $(td).addClass('no-wrap');
                    //     }
                    // },
                    { data: 'inventory_uom_qty', name: 'inventory_uom_qty', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('no-wrap');
                        }
                    },
                    { data: 'packet_number', name: 'packet_number', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('no-wrap');
                        }
                    },
                    { data: 'bar_code', code: 'bar_code', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('no-wrap');
                        }
                    },
                    // { data: 'status', name: 'status', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
                    //     $(td).addClass('no-wrap');
                    //     }
                    // },
                ];
                // Define your dynamic filters
                var filters = {
                    status: '#filter-status',         // Status filter (dropdown)
                    category: '#filter-category',     // Category filter (dropdown)
                    item_code: '#filter-item-code'    // Item code filter (input text field)
                };
                var exportColumns = [0, 1, 2, 3, 4, 5]; // Columns to export
                initializeDataTable('.datatables-basic',
                    "{{ route('put-away.print-labels', $mrn->id) }}",
                    columns,
                    filters,  // Apply filters
                    'Material Receipt Print Labels',  // Export title
                    exportColumns  // Export columns
                );
                // Apply filter on button click
                // applyFilter('.apply-filter');
            });

            $(document).on('click', '#printBarcodesBtn', function () {
                const mrnId = $(this).data('mrn-id');

                $.ajax({
                    url: `/put-away/${mrnId}/print-barcodes`,
                    method: 'GET',
                    success: function (response) {
                        if (response.status === 200) {
                            const printWindow = window.open('', '', 'width=900,height=600');
                            printWindow.document.write(response.html);
                            printWindow.document.close();
                        } else {
                            Swal.fire('Error', 'Failed to generate barcode print view.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'AJAX request failed.', 'error');
                    }
                });
            });

    </script>
@endsection
