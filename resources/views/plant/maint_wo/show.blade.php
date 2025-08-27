@extends('layouts.app')
@section('content')

    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
				<div class="row">
					<div class="content-header-left col-md-6 mb-2">
						<div class="row breadcrumbs-top">
							<div class="col-12">
								<h2 class="content-header-title float-start mb-0">Maintenance Work Order</h2>
								<div class="breadcrumb-wrapper">
									<ol class="breadcrumb">
										<li class="breadcrumb-item"><a href="{{route('/')}}">Home</a>
										</li>  
										<li class="breadcrumb-item"><a href="{{ route('plant.maint-wo.index') }}">Maintenance Work Orders</a></li>
										<li class="breadcrumb-item active">View</li>
									</ol>
								</div>
							</div>
						</div>
					</div>
					
					<div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
						<div class="form-group breadcrumb-right">
							<a href="{{ route('plant.maint-wo.index') }}"> 
								<button class="btn btn-secondary btn-sm">
									<i data-feather="arrow-left-circle"></i> Back
								</button>
							</a>

							@if($buttons['draft'])
								<a href="{{ route('plant.maint-wo.edit', $data->id) }}">
									<button type="button" class="btn btn-info btn-sm">
										<i data-feather="edit"></i> Edit
									</button>
								</a>
							@endif

							@if($buttons['submit'])
								<button type="button" class="btn btn-success btn-sm" id="submit-button" name="action" value="submitted">
									<i data-feather="send"></i> Submit
								</button>
							@endif

							@if($buttons['approve'])
								<button type="button" class="btn btn-primary btn-sm" id="approved-button" name="action" value="approved">
									<i data-feather="check-circle"></i> Approve
								</button>
								<button type="button" id="reject-button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
									<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle">
										<circle cx="12" cy="12" r="10"></circle>
										<line x1="15" y1="9" x2="9" y2="15"></line>
										<line x1="9" y1="9" x2="15" y2="15"></line>
									</svg> Reject
								</button>
							@endif

							@if($buttons['amend'])
								<button type="button" data-bs-toggle="modal" data-bs-target="#amendmentconfirm" class="btn btn-primary btn-sm mb-50 mb-sm-0">
									<i data-feather='edit'></i> Amendment
								</button>
							@endif						
						</div>
					</div>
				</div>
			</div>

            <div class="content-body">
                <form id="maint-wo-form" method="POST" action="{{ route('plant.maint-wo.update', $data->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="book_code" id="book_code_input" value="{{ $data->book_code }}">
                    <input type="hidden" name="document_status" id="document_status" value="{{ $data->document_status }}">
                    <input type="hidden" name="revision_number" id="revision_number" value="{{ $data->revision_number }}">
                    
				<section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
								<div class="card-body customernewsection-form">  
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between"> 
                                                <div>
                                                    <h4 class="card-title text-theme">Work Order Information</h4>
                                                    <p class="card-text">View maintenance work order details</p>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge {{ $docStatusClass }} me-2">{{ ucfirst($data->document_status) }}</span>
                                                    @if($data->revision_number > 0)
                                                        <span class="badge bg-info">Rev: {{ $data->revision_number }}</span>
                                                    @endif
                                                </div>
                                            </div> 
                                        </div> 

                                        <div class="col-md-8"> 
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3"> 
                                                    <label class="form-label">Series</label>  
                                                </div>  
                                                <div class="col-md-5">  
                                                    <input type="text" class="form-control" value="{{ $data->book_code ?? 'N/A' }}" readonly>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3"> 
                                                    <label class="form-label">Document Number</label>  
                                                </div>  
                                                <div class="col-md-5">  
                                                    <input type="text" class="form-control" value="{{ $data->doc_no ?? 'N/A' }}" readonly>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3"> 
                                                    <label class="form-label">Document Date</label>  
                                                </div>  
                                                <div class="col-md-5">  
                                                    <input type="text" class="form-control" value="{{ $data->doc_date ?? 'N/A' }}" readonly>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3"> 
                                                    <label class="form-label">Work Order Type</label>  
                                                </div>  
                                                <div class="col-md-5">  
                                                    <input type="text" class="form-control" value="{{ $data->wo_type ?? 'N/A' }}" readonly>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3"> 
                                                    <label class="form-label">Priority</label>  
                                                </div>  
                                                <div class="col-md-5">  
                                                    <input type="text" class="form-control" value="{{ $data->priority ?? 'N/A' }}" readonly>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3"> 
                                                    <label class="form-label">Equipment</label>  
                                                </div>  
                                                <div class="col-md-5">  
                                                    <input type="text" class="form-control" value="{{ $data->equipment_name ?? 'N/A' }}" readonly>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3"> 
                                                    <label class="form-label">Location</label>  
                                                </div>  
                                                <div class="col-md-5">  
                                                    <input type="text" class="form-control" value="{{ $data->location_name ?? 'N/A' }}" readonly>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3"> 
                                                    <label class="form-label">Description</label>  
                                                </div>  
                                                <div class="col-md-9">  
                                                    <textarea class="form-control" rows="3" readonly>{{ $data->description ?? 'N/A' }}</textarea>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3"> 
                                                    <label class="form-label">Planned Start Date</label>  
                                                </div>  
                                                <div class="col-md-5">  
                                                    <input type="text" class="form-control" value="{{ $data->planned_start_date ?? 'N/A' }}" readonly>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3"> 
                                                    <label class="form-label">Planned End Date</label>  
                                                </div>  
                                                <div class="col-md-5">  
                                                    <input type="text" class="form-control" value="{{ $data->planned_end_date ?? 'N/A' }}" readonly>
                                                </div>
                                            </div>

                                            @if($data->attachment)
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3"> 
                                                        <label class="form-label">Attachment</label>  
                                                    </div>  
                                                    <div class="col-md-5">  
                                                        <a href="{{ asset('storage/' . $data->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i data-feather="paperclip"></i> View Attachment
                                                        </a>
                                                    </div>
                                                </div>
                                            @endif

                                        </div>

                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title">Document Status</h6>
                                                    <p class="mb-1"><strong>Status:</strong> <span class="badge {{ $docStatusClass }}">{{ ucfirst($data->document_status) }}</span></p>
                                                    <p class="mb-1"><strong>Approval Level:</strong> {{ $data->approval_level ?? 'N/A' }}</p>
                                                    <p class="mb-1"><strong>Revision:</strong> {{ $data->revision_number ?? 0 }}</p>
                                                    <p class="mb-0"><strong>Created:</strong> {{ $data->created_at ? $data->created_at->format('d-m-Y H:i') : 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if(isset($approvalHistory) && count($approvalHistory) > 0)
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Approval History</h5>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>User</th>
                                                        <th>Action</th>
                                                        <th>Level</th>
                                                        <th>Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($approvalHistory as $history)
                                                        <tr>
                                                            <td>{{ $history->created_at ? $history->created_at->format('d-m-Y H:i') : 'N/A' }}</td>
                                                            <td>{{ $history->user_name ?? 'N/A' }}</td>
                                                            <td>
                                                                <span class="badge 
                                                                    @if($history->approval_type == 'approve') bg-success
                                                                    @elseif($history->approval_type == 'reject') bg-danger
                                                                    @else bg-warning
                                                                    @endif
                                                                ">
                                                                    {{ ucfirst($history->approval_type ?? 'N/A') }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $history->approval_level ?? 'N/A' }}</td>
                                                            <td>{{ $history->remarks ?? 'N/A' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </section>
                </form>
            </div>
        </div>
    </div>
    <!-- END: Content-->

    <!-- Amendment Confirmation Modal -->
    <div class="modal fade" id="amendmentconfirm" tabindex="-1" aria-labelledby="amendmentconfirmLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="amendmentconfirmLabel">Amendment Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to create an amendment for this maintenance work order?</p>
                    <div class="mb-3">
                        <label for="amendment-reason" class="form-label">Amendment Reason</label>
                        <textarea class="form-control" id="amendment-reason" rows="3" placeholder="Enter reason for amendment"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirm-amendment">Confirm Amendment</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div class="modal fade" id="rejectionModal" tabindex="-1" aria-labelledby="rejectionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectionModalLabel">Rejection Reason</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejection-reason" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection-reason" rows="3" placeholder="Enter reason for rejection" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirm-rejection">Confirm Rejection</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Submit button functionality
        $('#submit-button').on('click', function() {
            if (confirm('Are you sure you want to submit this maintenance work order?')) {
                $('#document_status').val('submitted');
                $('#maint-wo-form').submit();
            }
        });

        // Approve button functionality
        $('#approved-button').on('click', function() {
            if (confirm('Are you sure you want to approve this maintenance work order?')) {
                $('#document_status').val('approved');
                $('#maint-wo-form').submit();
            }
        });

        // Reject button functionality
        $('#reject-button').on('click', function() {
            $('#rejectionModal').modal('show');
        });

        $('#confirm-rejection').on('click', function() {
            const reason = $('#rejection-reason').val().trim();
            if (!reason) {
                alert('Please enter a reason for rejection.');
                return;
            }
            
            // Add rejection reason to form
            $('<input>').attr({
                type: 'hidden',
                name: 'rejection_reason',
                value: reason
            }).appendTo('#maint-wo-form');
            
            $('#document_status').val('rejected');
            $('#maint-wo-form').submit();
        });

        // Revoke button functionality
        $('#revoke-button').on('click', function() {
            if (confirm('Are you sure you want to revoke this maintenance work order?')) {
                $('#document_status').val('draft');
                $('#maint-wo-form').submit();
            }
        });

        // Delete button functionality
        $('#delete-button').on('click', function() {
            if (confirm('Are you sure you want to delete this maintenance work order? This action cannot be undone.')) {
                $('#document_status').val('deleted');
                $('#maint-wo-form').submit();
            }
        });

        // Post button functionality
        $('#post-button').on('click', function() {
            if (confirm('Are you sure you want to post this maintenance work order?')) {
                $('#document_status').val('posted');
                $('#maint-wo-form').submit();
            }
        });

        // Close button functionality
        $('#close-button').on('click', function() {
            if (confirm('Are you sure you want to close this maintenance work order?')) {
                $('#document_status').val('closed');
                $('#maint-wo-form').submit();
            }
        });

        // Amendment functionality
        $('#confirm-amendment').on('click', function() {
            const reason = $('#amendment-reason').val().trim();
            if (!reason) {
                alert('Please enter a reason for amendment.');
                return;
            }
            
            // Redirect to amendment creation
            window.location.href = `{{ route('plant.maint-wo.create') }}?amend={{ $data->id }}&reason=${encodeURIComponent(reason)}`;
        });

        // Print functionality
        $('#print-button').on('click', function() {
            window.print();
        });

        // Voucher functionality
        $('#voucher-button').on('click', function() {
            // Implement voucher generation logic
            alert('Voucher functionality to be implemented');
        });
    });
</script>
@endsection