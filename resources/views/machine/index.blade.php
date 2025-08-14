{{-- filepath: c:\Staqo_pr\erp_presence360\resources\views\machine\index.blade.php --}}
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
                        <h2 class="content-header-title float-start mb-0">
                            {{$typeName}}
                        </h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>  
                                <li class="breadcrumb-item active">List</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                <div class="form-group breadcrumb-right">
                    <button class="btn btn-warning btn-sm mb-50 mb-sm-0"><i data-feather="filter"></i> Filter</button> 

                    {{-- @if ($create_button) --}}
                        <a class="btn btn-primary btn-sm mb-50 mb-sm-0" href="{{ $create_route }}">
                            <i data-feather="plus-circle"></i> Create
                        </a>
                    {{-- @endif --}}
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
                                            <th>Machine</th>
                                            <th>Attribute</th>
                                            <th>Values</th>
                                            <th style="text-align:center">Status</th>
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
<script type="text/javascript" src="{{ asset('assets/js/modules/common-datatable.js') }}"></script>
<script>
    $(document).ready(function() {
        function renderData(data) {
            return data ? data : 'N/A'; 
        }

        var columns = [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'Machine', name: 'Machine', render: renderData },
            { data: 'Attribute', name: 'Attribute', render: renderData },
            { data: 'Values', name: 'Values', render: renderData },
            { data: 'status', name: 'status', render: renderData }
        ];

        initializeDataTable('.datatables-basic', 
            "{{ $redirect_url }}", 
            columns,
            {},  // No filters for now
            "{{ $typeName }}",  // Export title
            [0, 1, 2, 3, 4],  // Export columns
            [], // Default order
            'landscape'
        );
    });
</script>
@endsection