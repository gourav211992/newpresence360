@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-5 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Item Master</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>  
                                    <li class="breadcrumb-item active">Item Master List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-end col-md-7 mb-2 mb-sm-0">
                    <div class="form-group breadcrumb-right d-flex justify-content-end align-items-center">
                        <button class="btn btn-warning btn-sm me-1 mb-20 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal">
                            <i data-feather="filter"></i> Filter
                        </button>
                        <a href="{{ route('items.show.import') }}" class="btn btn-secondary btn-sm mb-50 me-1 mb-sm-0">
                            <i data-feather="upload"></i> Import
                        </a> 
                        <a class="btn btn-primary btn-sm mb-20 mb-sm-0" href="{{ route('item.create') }}">
                            <i data-feather="plus-circle"></i> Add New
                        </a>
                    </div>
             </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="table-responsive">
                                    <table class="datatables-basic table myrequesttablecbox tableistlastcolumnfixed">
                                        <thead>
                                             <tr>
                                                <th>S.NO</th>
                                                <th>Item Code</th>
                                                <th>Item Name</th>
                                                <th>Unit</th>
                                                <th>HSN</th>
                                                <th>Type</th>
                                                <th>SubType</th>
                                                <th>Group</th>
                                                <th>Created At</th>
                                                <th>Created By</th>
                                                <th>Updated At</th>
                                                <th class="text-end">Status</th>
                                              </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
                    <div class="modal-dialog sidebar-sm">
                        <form class="add-new-record modal-content pt-0" id="item-filter-form">
                            <div class="modal-header mb-1">
                                <h5 class="modal-title" id="exampleModalLabel">Apply Item Filter</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                            </div>
                            <div class="modal-body flex-grow-1">
                                <div class="mb-1">
                                    <label class="form-label">HSN Code</label>
                                    <select id="filter-hsn" name="hsn_id" class="form-select">
                                        <option value="">Select HSN Code</option>
                                        @foreach($hsns as $hsn)
                                            <option value="{{ $hsn->id }}">{{ $hsn->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-1">
                                    <label class="form-label">SubType</label>
                                    <select id="filter-sub-type" name="sub_type_id" class="form-select">
                                        <option value="">Select SubType</option>
                                        @foreach($subtypes as $subtype)
                                            <option value="{{ $subtype->id }}">{{ $subtype->name }}</option>
                                        @endforeach
                                        <option value="traded_item">Traded Item</option>
                                        <option value="asset">Asset</option> 
                                        <option value="scrap">Scrap</option>   
                                    </select>
                                </div>
                                <div class="mb-1">
                                    <label class="form-label">Category</label>
                                    <select id="filter-category" name="subcategory_id" class="form-select">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-1">
                                    <label class="form-label">Item Type</label>
                                    <select id="filter-type" name="type" class="form-select">
                                        <option value="">Select Item Type</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-1">
                                    <label class="form-label">Status</label>
                                    <select id="filter-status" name="status" class="form-select">
                                        <option value="">Select Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="draft">Draft</option>
                                        <option value="submitted">Submitted</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-start">
                                <button type="button" class="btn btn-primary apply-filter mr-1">Apply</button>
                                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="reset" class="btn btn-outline-secondary" id="reset-filters">Reset</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Content-->
@endsection
@section('scripts')
<script type="text/javascript" src="{{asset('assets/js/modules/common-datatable2.js')}}"></script>
<script>
$(document).ready(function() {
    var dt_basic_table = $('.datatables-basic');
    function renderData(data) {
        return data ? data : 'N/A';
    }
    function exportData(type) {
         let searchValue = $('.dataTables_filter input').val();
        let params = {
            export_type: type,
            status: $('#filter-status').val(),
            hsn_id: $('#filter-hsn').val(),
            sub_type_id: $('#filter-sub-type').val(),
            subcategory_id: $('#filter-category').val(),
            type: $('#filter-type').val(),
            search_value: searchValue 
        };

        let url = '/items/export?' + $.param(params);

        if (type === 'excel' || type === 'csv' || type === 'pdf') {
            window.location = url;
        } else if (type === 'print') {
            $.get(url, function(html){
                var printWindow = window.open('', '_blank');
                printWindow.document.write(html);
                printWindow.document.close();
                printWindow.print();
            });
        } else if (type === 'copy') {
            $.get(url, function(data){
                navigator.clipboard.writeText(JSON.stringify(data, null, 2)).then(function() {
                    alert('Copied to clipboard!');
                });
            });
        }
    }

    if (dt_basic_table.length) {
        dt_basic_table.DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            ajax: {
                url: "{{ route('item.index') }}",
                data: function(d) {
                    d.status = $('#filter-status').val();
                    d.hsn_id = $('#filter-hsn').val();
                    d.sub_type_id = $('#filter-sub-type').val();
                    d.subcategory_id = $('#filter-category').val();
                    d.type = $('#filter-type').val();
                    d.search_value = $('.dataTables_filter input').val();
                }
            },
            createdRow: function(row, data, dataIndex) {
                $(row).find('td').addClass('text-nowrap');
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'item_code', name: 'item_code', render: renderData },
                { data: 'item_name', name: 'item_name', render: renderData },
                { data: 'uom', name: 'uom.name', render: renderData },
                { data: 'hsn.code', name: 'hsn.code', render: renderData },
                { data: 'type', name: 'type', render: renderData },
                { data: 'subtypes', name: 'subtypes', render: renderData },
                { data: 'subCategoryName', name: 'subCategoryName', render: renderData },
                { data: 'created_at', name: 'created_at', render: renderData },
                {
                    data: 'created_by',
                    name: 'created_by',
                    render: function(data, type, row) {
                        return row.auth_user ? row.auth_user.name : 'N/A';
                    }
                },
                { data: 'updated_at', name: 'updated_at', render: renderData },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false
                }
            ],
            dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            buttons: [
                {
                    extend: 'collection',
                    className: 'btn btn-outline-secondary dropdown-toggle',
                    text: feather.icons['share'].toSvg({ class: 'font-small-4 mr-50' }) + ' Export',
                    buttons: [
                        { text: 'Print', className: 'dropdown-item', action: function() { exportData('print'); } },
                        { text: 'CSV', className: 'dropdown-item', action: function() { exportData('csv'); } },
                        { text: 'Excel', className: 'dropdown-item', action: function() { exportData('excel'); } },
                        { text: 'PDF', className: 'dropdown-item', action: function() { exportData('pdf'); } },
                        { text: 'Copy', className: 'dropdown-item', action: function() { exportData('copy'); } }
                    ],
                    init: function(api, node, config) {
                        $(node).removeClass('btn-secondary').parent().removeClass('btn-group');
                        setTimeout(function() {
                            $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
                        }, 50);
                    }
                }
            ],
            drawCallback: function() {
                feather.replace();
            },
            language: {
                paginate: { previous: '&nbsp;', next: '&nbsp;' }
            },
            search: { caseInsensitive: true }
        });
    }

    $('#reset-filters').on('click', function() {
        $('#filter-status').val('');
        $('#filter-hsn').val('');
        $('#filter-sub-type').val('');
        $('#filter-category').val('');
        $('#filter-type').val('');
        dt_basic_table.DataTable().ajax.reload();
    });

    $('.apply-filter').on('click', function() {
        dt_basic_table.DataTable().ajax.reload();
        $('#filter').modal('hide');
    });

    function handleRowSelection(tableSelector) {
        $(tableSelector).on('click', 'tbody tr', function () {
            $(tableSelector).find('tr').removeClass('trselected');
            $(this).addClass('trselected');
        });

        $(document).on('keydown', function (e) {
            const $selected = $(tableSelector).find('.trselected');
            if (e.which == 38) {
                $selected.prev('tr').addClass('trselected').siblings().removeClass('trselected');
            } else if (e.which == 40) {
                $selected.next('tr').addClass('trselected').siblings().removeClass('trselected');
            }
        });
    }
    handleRowSelection('.datatables-basic');
});
</script>

@endsection