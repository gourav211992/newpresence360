@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            
             
            <div class="content-body"> 
                  
                <section id="basic-datatable">
                    <div class="card border  overflow-hidden"> 
                        <div class="row">
                            <div class="col-md-12 bg-light border-bottom mb-1 po-reportfileterBox">
                                <div class="row pofilterhead action-button align-items-center">
                                    <div class="col-md-4">
                                        <h3>Shuttle Issued Record</h3>
                                        <p>Apply the Basic Filter</p>
                                    </div>
<!--
                                    <div class="col-md-8 text-sm-end pofilterboxcenter mb-0 d-flex flex-wrap align-items-center justify-content-sm-end"> 
                                        <button data-bs-toggle="modal" data-bs-target="#addcoulmn" class="btn btn-primary btn-sm mb-0 waves-effect"><i data-feather="filter"></i> Advance Filter</button>
                                    </div>
-->
                                </div>
                                
                                <div class="customernewsection-form poreportlistview p-1">
                                    <div class="row">
                                        
                                         
                                        <div class="col-md-3">
                                            <label class="form-label" for="fp-range">Select Period</label>
                                            <input type="text" id="filter-time" class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD" required/>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <div class="mb-1 mb-sm-0"> 
                                                <label class="form-label">Issue To</label>
                                                <select class="form-control select2" id="filter-issue">
                                                    <option value="" readonly>Select</option>
                                                    @foreach($requesters as $requester)
                                                        <option value="{{ $requester['id'] }}">{{ $requester['name'] }}</option>
                                                    @endforeach
                                                </select>
                                             </div>
                                        </div>
                                    </div>
                                </div> 
                            </div>
                          <div class="col-md-12"> 
                                <div class="table-responsive trailbalnewdesfinance po-reportnewdesign trailbalnewdesfinancerightpad">
									<table class="datatables-basic table myrequesttablecbox tableistlastcolumnfixed "> 
                                        <thead>
                                             <tr>
												<th>#</th>
												<th>Date</th>
												<th>Document Number</th>
												<th>Coach Name</th>
												<th>Item NAme</th>
												<th>Type</th>
												<th class="numeric-alignment">Issue</t>
												<th class="numeric-alignment">USED IN TRAINING</th>
												<th class="numeric-alignment">Return</th>
												<th class="numeric-alignment">Scrap</th>
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

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Apply Filter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-1">
                    <label class="form-label">Select Date</label>
                    <input type="text" id="fp-range" class="form-control flatpickr-range" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                </div>
                <div class="mb-1">
                    <label class="form-label">Order No.</label>
                    <select class="form-select">
                        <option>Select</option>
                    </select>
                </div>
                <div class="mb-1">
                    <label class="form-label">Customer Name</label>
                    <select class="form-select select2">
                        <option>Select</option>
                    </select>
                </div>
                <div class="mb-1">
                    <label class="form-label">Status</label>
                    <select class="form-select">
                        <option>Select</option>
                        <option>Open</option>
                        <option>Close</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary apply-filter">Apply</button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/modules/common-datatable.js') }}"></script>
<script>
$(document).ready(function() {
    function renderData(data) {
        return data ? data : ''; 
    }
    var columns = [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'document_date', render: renderData, createdCell: (td) => $(td).addClass('no-wrap') },
        { data: 'document_number', render: renderData, createdCell: (td) => $(td).addClass('no-wrap') },
        { data: 'coach_name', render: renderData, createdCell: (td) => $(td).addClass('no-wrap') },
        { data: 'items', render: renderData, createdCell: (td) => $(td).addClass('no-wrap') },
        { data: 'attribute', render: renderData, createdCell: (td) => $(td).addClass('no-wrap') },
        { data: 'issue_qty', render: renderData, createdCell: (td) => $(td).addClass('text-end').css('padding-right', '30px')  },
        { data: 'used_in_training', render: renderData, createdCell: (td) => $(td).addClass('no-wrap text-end').css('padding-right', '30px')  },
        { data: 'return', render: renderData, createdCell: (td) => $(td).addClass('no-wrap text-end').css('padding-right', '30px')  },
        { data: 'scrap', render: renderData, createdCell: (td) => $(td).addClass('no-wrap text-end').css('padding-right', '30px')  },
    ];
    var filters = {
        issue_to: '#filter-issue',         // Status filter (dropdown)
        time_period: '#filter-time',     // Category filter (dropdown)
    };
    initializeDataTable('.datatables-basic',"{{ route('material.issue.report') }}" ,columns, filters, "Shuttle Issue Records", [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],[],'landscape');
    $('#filter-issue').on('change', function() {
        $('.datatables-basic').DataTable().ajax.reload(null, false); // Reload table without resetting pagination
    });
    $('#filter-time').on('change', function() {
        $('.datatables-basic').DataTable().ajax.reload(null, false); // Reload table without resetting pagination
    });

    // $('.datatables-basic').DataTable().ajax.reload(null, false);
});
</script>
@endsection
