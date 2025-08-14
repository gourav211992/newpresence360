@extends('layouts.app')
@section('styles')
<style type="text/css">

        #map {
            width: 100%;
            height: 550px;
            border: 10px solid #fff;
            box-shadow: 0 0px 20px rgba(0, 0, 0, 0.1);
        }
</style>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}"></script>
@endsection
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
								<h2 class="content-header-title float-start mb-0">Land Parcel</h2>
								<div class="breadcrumb-wrapper">
									<ol class="breadcrumb">
										<li class="breadcrumb-item"><a href="index.html">Home</a>
										</li>
										<li class="breadcrumb-item active">Add New</li>


									</ol>
								</div>
							</div>
						</div>
					</div>
					<div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
						<div class="form-group breadcrumb-right">
							<button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
							<button form="landparcel-form" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Submit</button>
						</div>
					</div>
				</div>
			</div>
            <div class="content-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


				<section id="basic-datatable">
                <form id="landparcel-form" method="POST" action="{{ route('land-parcel.save') }}" enctype='multipart/form-data'>
                @csrf
                    <div class="row">
                        <div class="col-12">

                            <div class="card">
								 <div class="card-body customernewsection-form">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                                                    <div>
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-8">

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Series <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                        <select class="form-select" name="series" required id="series">
                                                            <option value="" disabled selected>Select</option>
                                                            @foreach($series as $key => $serie)
                                                                <option value="{{ $serie->id }}">{{ $serie->book_name }}</option>
                                                            @endforeach
                                                        </select>

                                                            @error('series')
                                                                <div class="text-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                    </div>

                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Document No. <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                        <input type="text" class="form-control" name="document_no" id="document_no" value="{{ old('document_no') }}"  required readonly>
                                                        @error('document_no')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                        </div>
                                                    </div>

                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Name <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                        <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}"  required>
                                                        @error('name')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                        </div>
                                                    </div>

                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Description</label>
                                                        </div>

                                                        <div class="col-md-5">
                                                          <textarea class="form-control" rows="1" name="description"></textarea>
                                                          @error('description')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                        </div>
                                                    </div>

                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Survey No.</label>
                                                        </div>

                                                        <div class="col-md-5">
                                                        <input type="text" class="form-control" name="surveyno" id="surveyno" value="{{ old('surveyno') }}" >
                                                        @error('name')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                        </div>

                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Status<span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <div class="demo-inline-spacing">
                                                                    <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="customColorRadio3" name="status" class="form-check-input" value="1"
                                                                    @if(empty(old('status'))) checked @endif {{ old('status') == '1' ? 'checked' : '' }} required>
                                                                        <label class="form-check-label fw-bolder" for="customColorRadio3">Active</label>
                                                                    </div>
                                                                    <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="customColorRadio4" name="status" class="form-check-input" value="0"
                                                                    {{ old('status') == '0' ? 'checked' : '' }} required>
                                                                        <label class="form-check-label fw-bolder" for="customColorRadio4">Inactive</label>
                                                                    </div>
                                                                </div>
                                                        </div>

                                                    </div>


                                            </div>

                                            <div class="col-md-4">

                                                    <div class="step-custhomapp bg-light p-1 customerapptimelines customerapptimelinesapprovalpo">
                                                        <h5 class="mb-2 text-dark border-bottom pb-50 d-flex align-items-center justify-content-between">
                                                            <strong><i data-feather="arrow-right-circle"></i> Approval History</strong>
                                                            <strong class="badge rounded-pill badge-light-secondary amendmentselect">Rev. No.
                                                                <select class="form-select">
                                                                    <option>00</option>
                                                                    <option>01</option>
                                                                    <option>02</option>
                                                                    <option>03</option>
                                                                </select>
                                                            </strong>

                                                        </h5>
                                                        <ul class="timeline ms-50 newdashtimline ">
                                                            <li class="timeline-item">
                                                                <span class="timeline-point timeline-point-indicator"></span>
                                                                <div class="timeline-event">
                                                                    <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                        <h6>Deepak Kumar</h6>
                                                                        <span class="badge rounded-pill badge-light-primary">Amendment</span>
                                                                    </div>
                                                                    <h5>(2 min ago)</h5>
                                                                    <p>Description will come here</p>
                                                                </div>
                                                            </li>
                                                            <li class="timeline-item">
                                                                <span class="timeline-point timeline-point-indicator"></span>
                                                                <div class="timeline-event">
                                                                    <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                        <h6>Aniket Singh</h6>
                                                                        <span class="badge rounded-pill badge-light-danger">Rejected</span>
                                                                    </div>
                                                                    <h5>(2 min ago)</h5>
                                                                    <p>Description will come here</p>
                                                                </div>
                                                            </li>
                                                            <li class="timeline-item">
                                                                <span class="timeline-point timeline-point-warning timeline-point-indicator"></span>
                                                                <div class="timeline-event">
                                                                    <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                        <h6>Deewan Singh</h6>
                                                                        <span class="badge rounded-pill badge-light-warning">Pending</span>
                                                                    </div>
                                                                    <h5>(5 min ago)</h5>
                                                                    <p>Description will come here</p>
                                                                </div>
                                                            </li>
                                                            <li class="timeline-item">
                                                                <span class="timeline-point timeline-point-info timeline-point-indicator"></span>
                                                                <div class="timeline-event">
                                                                    <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                        <h6>Brijesh Kumar</h6>
                                                                        <span class="badge rounded-pill badge-light-success">Approved</span>
                                                                    </div>
                                                                    <h5>(10 min ago)</h5>
                                                                    <p>Description will come here</p>
                                                                </div>
                                                            </li>
                                                            <li class="timeline-item">
                                                                <span class="timeline-point timeline-point-danger timeline-point-indicator"></span>
                                                                <div class="timeline-event">
                                                                    <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                        <h6>Deepender Singh</h6>
                                                                       <span class="badge rounded-pill badge-light-success">Approved</span>
                                                                    </div>
                                                                    <h5>(5 day ago)</h5>
                                                                    <p><a href="#"><i data-feather="download"></i></a> Description will come here </p>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>

                                                </div>

                                        </div>


											<div class="border-bottom mt-3 mb-2 pb-25">
                                                     <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="newheader ">
                                                                <h4 class="card-title text-theme">Land Information</h4>
                                                                <p class="card-text">Fill the details</p>
                                                            </div>
                                                        </div>
                                                    </div>

                                             </div>


											<div class="row">
                                                 <div class="col-md-5">



                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Khasara No.</label>
                                                        </div>

                                                        <div class="col-md-8">
                                                        <input type="text" class="form-control" name="khasara_no" value="{{ old('khasara_no') }}" onchange="cleanInput(this)" required>
                                                        @error('khasara_no')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                        </div>
                                                    </div>

                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                             <label class="form-label">Size of Land <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-4">
                                                        <input type="text" class="form-control" name="plot_area"  value="{{ old('plot_area') }}" onchange="cleanInput(this)" required>
                                                        @error('plot_area')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                        </div>

                                                        <div class="col-md-4">
                                                            <select class="form-select" name="area_unit" >
                                                                <option value="" selected disabled>Unit</option>
                                                                <option value="Acres">Acres</option>
                                                                <option value="Hectares">Hectares</option>
                                                                <option value="squarefeet">Square Feet</option>
                                                                <option value="squaremeter">Square Meter</option>
                                                                <option value="bigha">Bigha</option>
                                                            </select>
                                                        </div>

                                                    </div>

                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Dimension <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-8">
                                                          <input type="text" class="form-control" name="dimension" value="{{ old('dimension') }}" onchange="cleanInput(this)" required>
                                                          @error('dimension')
                                                            <div class="text-danger">{{ $message }}</div>
                                                          @enderror
                                                        </div>
                                                    </div>

                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Land Valuation</label>
                                                        </div>

                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="land_valuation" value="{{ old('land_valuation') }}" onchange="cleanInput(this)">
                                                        </div>

                                                    </div>



                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Handover Date <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-8">
                                                          <input type="date" class="form-control" name="handoverdate" value="{{ old('handoverdate') }}" required>
                                                          @error('handoverdate')
                                                            <div class="text-danger">{{ $message }}</div>
                                                          @enderror
                                                        </div>
                                                    </div>

                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Address <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="address" value="{{ old('address') }}" required>
                                                            @error('address')
                                                            <div class="text-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                    </div>

                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">District <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-8">
                                                          <input type="text" class="form-control" name="district" value="{{ old('district') }}" required>
                                                          @error('district')
                                                            <div class="text-danger">{{ $message }}</div>
                                                          @enderror
                                                        </div>
                                                    </div>

                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">State <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-8">
                                                          <input type="text" class="form-control" name="state" value="{{ old('state') }}" required>
                                                          @error('state')
                                                            <div class="text-danger">{{ $message }}</div>
                                                          @enderror
                                                        </div>
                                                    </div>


                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Country <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-8">
                                                          <input type="text" class="form-control" name="country" value="{{ old('country') }}" required>
                                                          @error('country')
                                                            <div class="text-danger">{{ $message }}</div>
                                                          @enderror
                                                        </div>
                                                    </div>

                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Pincode <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="pincode" value="{{ old('pincode') }}" required>
                                                            @error('pincode')
                                                            <div class="text-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                    </div>




                                                     <div class="row  mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Remarks</label>
                                                        </div>

                                                        <div class="col-md-8">
                                                            <textarea type="text" rows="4" class="form-control" name="remarks" placeholder="Enter Remarks here..." ></textarea>
                                                            @error('remarks')
                                                            <div class="text-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>


												</div>

                                                <div class="col-md-7">

                                                    <div class="row align-items-end mb-1">
                                                        <div class="col-md-2">
                                                            <label class="form-label">Latitude:</label> <h4><strong>9876547</strong></h4>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Longitude :</label> <h4><strong>9876547</strong></h4>
                                                        </div>
                                                        <div class="col-md-7 text-sm-end  action-button">
                                                            <a href="{{url('/assets/sample_land_locations.csv')}}" target="_blank" class="font-small-2 mb-1 me-1">
                                                                <i data-feather="download"></i> Download Sample
                                                            </a>
                                                            <div class="image-uploadhide mt-50">
                                                                <a href="attribute.html" class="btn btn-outline-primary btn-sm">
                                                                    <i data-feather="plus"></i> Upload Geofence
                                                                </a>
                                                                <input type="file" name="geofence" class="" />
                                                            </div>

                                                        </div>
                                                    </div>

                                                    <div id="map"></div>
                                              </div>

                                             <div class="col-md-12">
                                                    <div class="border-bottom mb-2 mt-2 pb-25">
                                                        <div class="newheader ">
                                                            <h4 class="card-title text-theme">Upload Supporting Documents</h4>
                                                            <p class="card-text">Fill the details</p>
                                                        </div>
                                                    </div>

                                                    <div class="table-responsive-md">
                                                         <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                            <thead>
                                                                 <tr>
                                                                    <th>#</th>
                                                                    <th>Document Name</th>
                                                                    <th>Upload File</th>
                                                                    <th>Attachments</th>
                                                                    <th width="40px">Action</th>
                                                                  </tr>
                                                                </thead>
                                                                <tbody id="tableBody">
                                                                     <tr>
                                                                        <td>1</td>
                                                                        <td>
                                                                            <input type="text" name="names[0]" class="form-control mw-100">
                                                                        </td>
                                                                        <td>
                                                                            <input type="file" multiple class="form-control mw-100" name="attachments[0]" id="attachments-0">
                                                                         </td>
                                                                          <td id="preview-0">
                                                                         </td>
                                                                         <td><a href="#" class="text-primary addRow"><i data-feather="plus-square"></i></a></td>
                                                                      </tr>

                                                               </tbody>


                                                        </table>
                                                    </div>


                                             </div>


                                             </div>


								</div>
                            </div>
                        </div>
                    </div>
                </form>
                    <!-- Modal to add new record -->

                </section>


            </div>
        </div>
    </div>
    <!-- END: Content-->

    <div class="modal fade text-start" id="rescdule" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select Pending Disbursal</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					 <div class="row">

                         <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Loan Type</label>
                                <select class="form-select">
                                    <option>Select</option>
                                    <option>Home Loan</option>
                                    <option>Vehicle Loan</option>
                                    <option>Term Loan</option>
                                </select>
                            </div>
                        </div>

                         <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Customer Name</label>
                                <select class="form-select select2">
                                    <option>Select</option>
                                </select>
                            </div>
                        </div>

                         <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Application No.</label>
                                <select class="form-select select2">
                                    <option>Select</option>
                                </select>
                            </div>
                        </div>


                         <div class="col-md-3  mb-1">
                              <label class="form-label">&nbsp;</label><br/>
                             <button class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button>
                         </div>

						 <div class="col-md-12">


							<div class="table-responsive">
								<table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
									<thead>
										 <tr>
											<th>#</th>
											<th>Application No.</th>
											<th>Date</th>
                                            <th>Customer Name</th>
											<th>Loan Type</th>
											<th>Disbursal Milestone</th>
											<th>Disbursal Amt.</th>
											<th>Mobile No.</th>
										  </tr>
										</thead>
										<tbody>
											 <tr>
												 <td>
												 	<div class="form-check form-check-primary">
                                                        <input type="radio" id="customColorRadio3" name="customColorRadio3" class="form-check-input" checked="">
                                                    </div>
												 </td>
												<td>HL/2024/001</td>
												<td>20-07-2024</td>
                                                <td class="fw-bolder text-dark">Kundan Kumar</td>
                                                <td>Term</td>
												<td>1st floor completed</td>
												<td>200000</td>
												<td>9876787656</td>
											</tr>

											<tr>
												 <td>
                                                    <div class="form-check form-check-primary">
                                                        <input type="radio" id="customColorRadio3" name="customColorRadio3" class="form-check-input" checked="">
                                                    </div>
												 </td>
												<td>HL/2024/001</td>
												<td>20-07-2024</td>
                                                <td class="fw-bolder text-dark">Kundan Kumar</td>
                                                <td>Term</td>
												<td>2nd floor completed</td>
												<td>200000</td>
												<td>nishu@gmail.com</td>
											</tr>





									   </tbody>


								</table>
							</div>
						</div>


					 </div>
				</div>
				<div class="modal-footer text-end">
					<button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
					<button class="btn btn-primary btn-sm" data-bs-dismiss="modal"><i data-feather="check-circle"></i> Process</button>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('scripts')
<script>
           $(".addRow").click(function() {
    var rowCount = $("#tableBody").find('tr').length + 1; // Counter for row numbering, starting at 1

    var newRow = `
    <tr>
        <td>${rowCount}</td>
        <td>
            <input type="text" name="names[${rowCount-1}]" class="form-control mw-100">
        </td>
        <td>
            <input type="file" multiple class="form-control mw-100" name="attachments[${rowCount-1}]" id="attachments-${rowCount-1}">
        </td>
        <td id="preview-${rowCount-1}">
            <!-- File preview icons will be inserted here -->
        </td>
        <td><a href="#" class="text-danger trash"><i data-feather="trash-2"></i></a></td>
    </tr>`;

    $("#tableBody").append(newRow);
    feather.replace();

});


// Use event delegation to handle dynamically added file inputs
$(document).on('change', 'input[type="file"]', function(e) {
    var rowIndex = $(this).attr('id').split('-')[1]; // Extract row index from the file input's id
    handleFileUpload(e, `#preview-${rowIndex}`);
});

// Function to handle file upload preview with delete icon
function handleFileUpload(event, previewElement) {
    var files = event.target.files;
    var previewContainer = $(previewElement); // The container where previews will appear
    previewContainer.empty();  // Clear previous previews

    if (files.length > 0) {
        // Loop through each selected file
        for (var i = 0; i < files.length; i++) {
            // Generate the file preview div dynamically
            var fileIcon = `
                <div class="image-uplodasection expenseadd-sign" data-file-index="${i}">
                    <i data-feather="file-text" class="fileuploadicon"></i>
                    <div class="delete-img text-danger" data-file-index="${i}">
                        <i data-feather="x"></i>
                    </div>
                </div>
            `;
            // Append the generated fileIcon div to the preview container
            previewContainer.append(fileIcon);
        }
        // Replace icons with Feather icons after appending the new elements
        feather.replace();
    }

    // Add event listener to delete the file preview when clicked
    previewContainer.find('.delete-img').click(function() {
        var fileIndex = $(this).parent().data('file-index'); // Get the correct index from parent
        removeFilePreview(fileIndex, previewContainer, event.target);
    });
}

// Function to remove a single file from the FileList
function removeFilePreview(fileIndex, previewContainer, inputElement) {
    var dt = new DataTransfer(); // Create a new DataTransfer object to hold the remaining files
    var files = inputElement.files;

    // Loop through the files and add them to the DataTransfer object, except the one to delete
    for (var i = 0; i < files.length; i++) {
        if (i !== fileIndex) {
            dt.items.add(files[i]); // Add file to DataTransfer if it's not the one being deleted
        }
    }

    // Update the input element with the new file list
    inputElement.files = dt.files;

    // Remove the preview of the deleted file
    previewContainer.children(`[data-file-index="${fileIndex}"]`).remove();

    // Now re-index the remaining file previews
    var remainingPreviews = previewContainer.children();
    remainingPreviews.each(function(index) {
        $(this).attr('data-file-index', index); // Update data-file-index correctly
        $(this).find('.delete-img').attr('data-file-index', index); // Also update delete button index
    });

    // Debugging logs
    console.log(`Remaining files after deletion: ${dt.files.length}`);
    console.log(`Remaining preview elements: ${remainingPreviews.length}`);

    // If no files are left after deleting, reset the file input
    if (dt.files.length === 0) { // Check the updated DataTransfer's files length
        inputElement.value = ""; // Clear the input value to reset it
    }
}





// Remove row functionality
$("#tableBody").on("click", ".trash", function(event) {
    event.preventDefault(); // Prevent default action for <a> tag
    $(this).closest('tr').remove(); // Remove the closest <tr> element
});

</script>
<script>
    // Initialize map
    var map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 20.0, lng: 77.0 }, // Set to a default location
        zoom: 5,
    });

    var lineSymbol = {
  path: 'M 0,-1 0,1',
  strokeOpacity: 1,
  scale: 4
};

    // Array to hold coordinates for the polyline
    var lineCoordinates = [];

    // Add coordinates for each location
    @foreach ($locations as $location)
        lineCoordinates.push({ lat: {{ $location->latitude }}, lng: {{ $location->longitude }} });
    @endforeach

    // Create polyline to connect markers
    var polyline = new google.maps.Polyline({
        path: lineCoordinates,
        geodesic: false,
        strokeColor: '#000000', // Line color
        strokeOpacity: 1.0,
        strokeWeight: 0,
        icons: [{
                icon: lineSymbol,
                offset: '0',
                repeat: '20px'
            }],
    });

    polyline.setMap(map); // Add the polyline to the map

    // Function to remove the polyline (if needed)
    function removePolyline() {
        if (polyline) {
            polyline.setMap(null); // Remove the polyline from the map
            polyline = null; // Clear the polyline variable
        }
    }

    // Example usage: call removePolyline() when needed


    $('#series').on('change', function() {
            var book_id = $(this).val();
            var request = $('#document_no');

            request.val(''); // Clear any existing options

            if (book_id) {
                $.ajax({
                    url: "{{ url('get-land-request') }}/" + book_id,
                    type: "GET",
                    dataType: "json",
                    success: function(data)
                    {
                        console.log(data);
                            if (data.requestno) {
                            request.val(data.requestno);
                        }
                    }
                });
            }
        });

</script>


@endsection
