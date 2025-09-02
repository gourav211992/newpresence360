@extends('layouts.app')
@section('content')
     {{-- BEGIN: Content --}}
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-5 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Purchase Indents</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                    <li class="breadcrumb-item active">Indent List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                        @if(count($servicesBooks['services']))
                        <a class="btn btn-primary btn-sm mb-50 mb-sm-0" href="{{ route('pi.create') }}"><i data-feather="plus-circle"></i> Create Indent</a>
                        @endif
                         <a class="btn btn-dark btn-sm mb-50 mb-sm-0" href="{{ route('transactions.report', ['serviceAlias' => 'purchase-indent']) }}"><i data-feather="bar-chart-2"></i>Report</a>
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
                                                <th>Date</th>
                                                <th>Series</th>
                                                <th>Doc No.</th>
                                                <th>Location</th>
                                                <th>Requester</th>
                                                {{-- <th>SO No.</th> --}}
                                                {{-- <th>Revision No</th> --}}
                                                {{-- <th>Reference No</th> --}}
                                                <th>Items</th>
                                                <th style="width:100px">Status</th>
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
    {{-- filter start------}}
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
                    <input type="text" id="fp-range" class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Series</label>
                        <select class="form-select select2" id="filter-book" name="book_id[]" multiple>
                            @foreach($books as $book)
                                <option value="{{ $book->id }}">{{ $book->book_code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-1">
                    <label class="form-label">Location</label>
                    <select class="form-select select2" id="filter-location" name="location_id[]" multiple>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->store_name }}</option>
                            @endforeach
                    </select>
                    </div>
                    <div class="mb-1">
                    <label class="form-label">Requester</label>
                    <select class="form-select select2" id="filter-requester" name="requester_id[]" multiple>
                            @foreach($requesters as $requester)
                                <option value="{{ $requester->id }}">{{ $requester->name }}-({{ $requester->email }})</option>
                            @endforeach
                    </select>
                    </div>
                    <div class="mb-1">
                    <label class="form-label">Organization</label>
                    <select class="form-select select2" id="filter-organization" name="organization_id[]" multiple>
                            @foreach($applicableOrganizations as $applicableOrganization)
                                <option value="{{ $applicableOrganization->id }}">{{ $applicableOrganization->name }}</option>
                            @endforeach
                    </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-start">
                    <button type="button" class="btn btn-primary data-submit mr-1">Apply</button>
                    <button type="reset" class="btn btn-outline-secondary reset-filter" data-bs-dismiss="modal">Clear</button>
                </div>
            </form>
        </div>
    </div>
    {{-- filter end --}}

@endsection
@section('scripts')
<script type="text/javascript" src="{{asset('assets/js/modules/common-datatable.js')}}"></script>
<script>
$(document).ready(function() {
   function renderData(data) {
        return data ? data : '';
    }
    var columns = [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'document_date', name: 'document_date', render: renderData },
        { data: 'book_name', name: 'book_name', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
         },
        { data: 'document_number', name: 'document_number', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
         },
        { data: 'location', name: 'location', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
            $(td).addClass('no-wrap');
        }
        },

        { data: 'department', name: 'department', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
            $(td).addClass('no-wrap');
        }
        },
        { data: 'components', name: 'components', render: renderData },
        { data: 'document_status', name: 'document_status', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        }
    ];
    var filters = {
        book_id: '#filter-book',
        requester_id: '#filter-requester',
        location_id: '#filter-location',
        date_range: '#fp-range',
        organization_id: '#filter-organization',
    };
    var exportColumns = [0, 1, 2, 3, 4, 5, 6]; // Columns to export
    var table = initializeDataTable('.datatables-basic',
        "{{ route('pi.index') }}",
        columns,
        filters,  // Apply filters
        'Purchase Indent',  // Export title
        exportColumns,  // Export columns
        // [[1, "desc"]] // default order

    );
    $(".data-submit").on("click", function () {
        table.ajax.reload();
        $("#filter").modal('hide');
    });

    $(".reset-filter").on("click", function () {
        $("#filter-book").val(null).trigger("change");
        $("#filter-requester").val(null).trigger("change");
        $("#filter-location").val(null).trigger("change");
        $("#filter-organization").val(null).trigger("change");
        $("#fp-range").val("");
        table.ajax.url("{{ route('pi.index') }}").load();
    });
});
</script>
@endsection
