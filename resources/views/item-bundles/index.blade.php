@extends('layouts.app')

@section('content')

    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-5 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Item Bundles</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                                    <li class="breadcrumb-item active">List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" onclick='openFiltersModal();'><i
                                data-feather="filter"></i> Filter</button>
                              <a class="btn btn-primary btn-sm mb-50 mb-sm-0" href="{{ route('item-bundles.create') }}">
                                <i data-feather="plus-circle"></i> Create Item Bundle
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
                                                <th>S.No</th>
                                                <th>SKU Code</th>
                                                <th>SKU Name</th>
                                                <th>Front SKU Code</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
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

@section('scripts')
<script>
    $(document).ready(function () {

        function renderData(data) {
            return data ? data : 'N/A';
        }

        $('.datatables-basic').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('item-bundles.index') }}",
                data: function (d) {
                    d.sku_code = $('#sku_code_input').val();
                    d.sku_name = $('#sku_name_input').val();
                    d.front_sku_code = $('#front_sku_code_input').val();
                    d.book_id = $('#book_id_input').val();
                    d.group_id = $('#group_id_input').val();
                    d.company_id = $('#company_id_input').val();
                    d.organization_id = $('#organization_id_input').val();
                    d.status = $('#status_input').val();
                    d.document_status = $('#document_status_input').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'sku_code', name: 'sku_code', render: renderData },
                { data: 'sku_name', name: 'sku_name', render: renderData },
                { data: 'front_sku_code', name: 'front_sku_code', render: renderData },
                {
                    data: 'document_status',
                    name: 'document_status',
                    orderable: false,
                    searchable: false
                },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[1, 'asc']],
            responsive: true
        });

    });
</script>


@endsection
