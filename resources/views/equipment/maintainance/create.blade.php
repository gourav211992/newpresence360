@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
     <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
	        <form id="maintenance" action="{{ route('maintenance.store') }}" method="POST" enctype="multipart/form-data">
		        @csrf
		        <input type ="hidden" name="book_code" id ="book_code_input">
		        <input type="hidden" name="doc_number_type" id="doc_number_type">
		        <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern">
		        <input type="hidden" name="doc_prefix" id="doc_prefix">
		        <input type="hidden" name="doc_suffix" id="doc_suffix">
		        <input type="hidden" name="doc_no" id="doc_no">
		        <div class="content-header pocreate-sticky">
			        <div class="row">
				        <div class="content-header-left col-md-6 mb-2">
					        <div class="row breadcrumbs-top">
						        <div class="col-12">
							        <h2 class="content-header-title float-start mb-0">Maintenance</h2>
							        <div class="breadcrumb-wrapper">
								        <ol class="breadcrumb">
									        <li class="breadcrumb-item"><a href="{{ route('maintenance.index') }}">Home</a>
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
						        {{--                            <button data-bs-toggle="modal" data-bs-target="#amendmentconfirm" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i> Amendment</button>--}}
						        <!--
                            <button class="btn btn-danger btn-sm mb-50 mb-sm-0" data-bs-target="#reject" data-bs-toggle="modal"><i data-feather="x-circle"></i> Reject</button>
							<button class="btn btn-success btn-sm mb-50 mb-sm-0" data-bs-target="#approved" data-bs-toggle="modal"><i data-feather="check-circle" ></i> Approve</button>
-->

						        <button type="button" onclick="submitForm('draft')" id="draft" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0"><i data-feather='save'></i> Save as Draft</button>
						        <button type="button" onclick="submitForm('submitted')" class="btn btn-primary btn-sm mb-50 mb-sm-0" id="submitted"><i data-feather="check-circle"></i>Submit</button>
						        <input type="hidden" name="status" id="status">
						        <input id="submitButton" type="submit" value="Submit" class="hidden" />
					        </div>
				        </div>
			        </div>
		        </div>
		        <div class="content-body">



			        <section id="basic-datatable">
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
												        <!-- Series Dropdown -->
												        <select class="form-select" name="book_id" id="book_id" required>
													        @if ($series)
														        @foreach ($series as $index => $ser)
															        <option value="{{ $ser->id }}"
																	        {{ old('book_id') == $ser->id ? 'selected' : '' }}>
																        {{ $ser->book_code }}
															        </option>
														        @endforeach
													        @endif
												        </select>
											        </div>
										        </div>

										        <div class="row align-items-center mb-1">
											        <div class="col-md-3">
												        <label class="form-label">Doc No <span class="text-danger">*</span></label>
											        </div>

											        <div class="col-md-5">
												        <input type="text" class="form-control" name="document_number"
												               id="document_number" value="{{ old('document_number') }}"
												               readonly required>
											        </div>
										        </div>

										        <div class="row align-items-center mb-1">
											        <div class="col-md-3">
												        <label class="form-label">Doc Date <span class="text-danger">*</span></label>
											        </div>

											        <div class="col-md-5">
												        <input type="date" name="doc_date" class="form-control">
											        </div>
										        </div>


										        <div class="row align-items-center mb-1">
											        <div class="col-md-3">
												        <label class="form-label">Category <span class="text-danger">*</span></label>
											        </div>

											        <div class="col-md-5">
												        <!-- Category Dropdown -->
												        <select name="category" class="form-select " id="categoryDropdown">
													        <option value="">Select</option>
													        @foreach($categories as $item)
														        <option value="{{ $item->id }}">{{ $item->name }}</option>
													        @endforeach
												        </select>
											        </div>
										        </div>

										        <div class="row align-items-center mb-1">
											        <div class="col-md-3">
												        <label class="form-label">Equipment <span class="text-danger">*</span></label>
											        </div>

											        <div class="col-md-5">
												        <!-- Equipment Dropdown -->
												        <select name="equipment" class="form-select" id="equipmentDropdown">
													        <option value="">Select</option>
												        </select>
											        </div>
										        </div>




										        <!--
																									<div class="row align-items-center mb-1">
																										<div class="col-md-3">
																											<label class="form-label">Maintenance Type <span class="text-danger">*</span></label>
																										</div>

																										<div class="col-md-5">
																											<select class="form-select">
																												<option>Select</option>
																											</select>
																										</div>
																									 </div>

																									<div class="row align-items-center mb-1">
																										<div class="col-md-3">
																											<label class="form-label">Due Date </label>
																										</div>

																										<div class="col-md-5">
																											<input type="date" class="form-control">
																										</div>
																									 </div>
												-->


									        </div>

									        {{--                                            <div class="col-md-4"> --}}

									        {{--                                                    <div class="step-custhomapp bg-light p-1 customerapptimelines customerapptimelinesapprovalpo">--}}
									        {{--                                                        <h5 class="mb-2 text-dark border-bottom pb-50 d-flex align-items-center justify-content-between">--}}
									        {{--                                                            <strong><i data-feather="arrow-right-circle"></i> Approval History</strong>--}}
									        {{--                                                            <strong class="badge rounded-pill badge-light-secondary amendmentselect">Rev. No. --}}
									        {{--                                                                <select class="form-select">--}}
									        {{--                                                                    <option>00</option>--}}
									        {{--                                                                    <option>01</option>--}}
									        {{--                                                                    <option>02</option>--}}
									        {{--                                                                    <option>03</option>--}}
									        {{--                                                                </select>--}}
									        {{--                                                            </strong>--}}
									        {{--                                                            --}}
									        {{--                                                        </h5>--}}
									        {{--                                                        <ul class="timeline ms-50 newdashtimline ">--}}
									        {{--                                                            <li class="timeline-item">--}}
									        {{--                                                                <span class="timeline-point timeline-point-indicator"></span>--}}
									        {{--                                                                <div class="timeline-event">--}}
									        {{--                                                                    <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">--}}
									        {{--                                                                        <h6>Deepak Kumar</h6> --}}
									        {{--                                                                        <span class="badge rounded-pill badge-light-primary">Amendment</span>--}}
									        {{--                                                                    </div>--}}
									        {{--                                                                    <h5>(2 min ago)</h5>--}}
									        {{--                                                                    <p>Description will come here</p> --}}
									        {{--                                                                </div>--}}
									        {{--                                                            </li>--}}
									        {{--                                                            <li class="timeline-item">--}}
									        {{--                                                                <span class="timeline-point timeline-point-indicator"></span>--}}
									        {{--                                                                <div class="timeline-event">--}}
									        {{--                                                                    <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">--}}
									        {{--                                                                        <h6>Aniket Singh</h6> --}}
									        {{--                                                                        <span class="badge rounded-pill badge-light-danger">Rejected</span>--}}
									        {{--                                                                    </div>--}}
									        {{--                                                                    <h5>(2 min ago)</h5>--}}
									        {{--                                                                    <p>Description will come here</p> --}}
									        {{--                                                                </div>--}}
									        {{--                                                            </li>--}}
									        {{--                                                            <li class="timeline-item">--}}
									        {{--                                                                <span class="timeline-point timeline-point-warning timeline-point-indicator"></span>--}}
									        {{--                                                                <div class="timeline-event">--}}
									        {{--                                                                    <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">--}}
									        {{--                                                                        <h6>Deewan Singh</h6>--}}
									        {{--                                                                        <span class="badge rounded-pill badge-light-warning">Pending</span>--}}
									        {{--                                                                    </div>--}}
									        {{--                                                                    <h5>(5 min ago)</h5>--}}
									        {{--                                                                    <p>Description will come here</p> --}}
									        {{--                                                                </div>--}}
									        {{--                                                            </li>--}}
									        {{--                                                            <li class="timeline-item">--}}
									        {{--                                                                <span class="timeline-point timeline-point-info timeline-point-indicator"></span>--}}
									        {{--                                                                <div class="timeline-event">--}}
									        {{--                                                                    <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">--}}
									        {{--                                                                        <h6>Brijesh Kumar</h6>--}}
									        {{--                                                                        <span class="badge rounded-pill badge-light-success">Approved</span>--}}
									        {{--                                                                    </div>--}}
									        {{--                                                                    <h5>(10 min ago)</h5>--}}
									        {{--                                                                    <p>Description will come here</p> --}}
									        {{--                                                                </div>--}}
									        {{--                                                            </li> --}}
									        {{--                                                            <li class="timeline-item">--}}
									        {{--                                                                <span class="timeline-point timeline-point-danger timeline-point-indicator"></span>--}}
									        {{--                                                                <div class="timeline-event">--}}
									        {{--                                                                    <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">--}}
									        {{--                                                                        <h6>Deepender Singh</h6>--}}
									        {{--                                                                       <span class="badge rounded-pill badge-light-success">Approved</span>--}}
									        {{--                                                                    </div>--}}
									        {{--                                                                    <h5>(5 day ago)</h5>--}}
									        {{--                                                                    <p><a href="#"><i data-feather="download"></i></a> Description will come here </p> --}}
									        {{--                                                                </div>--}}
									        {{--                                                            </li>--}}
									        {{--                                                        </ul>--}}
									        {{--                                                    </div>--}}

									        {{--                                                </div>--}}

								        </div>
							        </div>
						        </div>





						        <div class="card">
							        <div class="card-body customernewsection-form">


								        <div class="border-bottom mb-2 pb-25">
									        <div class="row">
										        <div class="col-md-6">
											        <div class="newheader ">
												        <h4 class="card-title text-theme">Checklist and Defect Detail</h4>
												        <p class="card-text">Fill the details</p>
											        </div>
										        </div>
										        <!--
																										<div class="col-md-6 text-sm-end">
																											<a href="#" class="btn btn-sm btn-outline-danger me-50">
																												<i data-feather="x-circle"></i> Delete</a>
																											<a href="#" class="btn btn-sm btn-outline-primary">
																												<i data-feather="plus"></i> Add New Item</a>
																										 </div>
												-->
									        </div>
								        </div>



								        <div class="step-custhomapp bg-light">
									        <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
										        <li class="nav-item">
											        <a class="nav-link active" data-bs-toggle="tab" href="#payment">Checklist</a>
										        </li>
										        <li class="nav-item">
											        <a class="nav-link" data-bs-toggle="tab" href="#attachment">Defect</a>
										        </li>
									        </ul>
								        </div>

								        <div class="tab-content pb-1">
									        <div class="tab-pane active" id="payment">
										        <div class="row">
											        <div class="col-md-12">
												        <div class="table-responsive pomrnheadtffotsticky">
													        <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
														        <thead>
														        <tr>
															        <th style="width: 30px">#</th>
															        <th width="200">Maintenance Type</th>
															        <th width="300px">Checklist</th>
															        <th>Frequency</th>
														        </tr>
														        </thead>
														        <tbody class="mrntableselectexcel">

														        </tbody>

													        </table>
												        </div>
											        </div>
										        </div>
									        </div>
									        <div class="tab-pane" id="attachment">
										        <div class="row">
											        <div class="col-md-12">
												        <div class="table-responsive pomrnheadtffotsticky">
													        <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
														        <thead>
														        <tr>
															        <th>
																        #
															        </th>
															        <th width="150">Item Code</th>
															        <th width="150">Item Name</th>
															        <th>Attribute</th>
															        <th>UOM</th>
															        <th>Qty</th>
															        <th>Defect Type</th>
															        <th>Priority</th>
															        <th>Due Date</th>
															        <th>Description</th>
														        </tr>
														        </thead>
														        <tbody class="mrntableselectexcel">
														        </tbody>
													        </table>
												        </div>

											        </div>

										        </div>
									        </div>
								        </div>

								        <div class="row mt-2">
									        <div class="col-md-12">
										        <div class="col-md-4">
											        <div class="mb-1">
												        <label class="form-label">Upload Document</label>
												        <input type="file" name="upload_document" class="form-control">
											        </div>
										        </div>
									        </div>

									        <div class="col-md-12">
										        <div class="mb-1">
											        <label class="form-label">Final Remarks</label>
											        <textarea type="text" rows="4" name="final_remarks" class="form-control" placeholder="Enter Remarks here..."></textarea>

										        </div>
									        </div>

								        </div>



							        </div>
						        </div>






					        </div>
				        </div>
				        <!-- Modal to add new record -->

			        </section>


		        </div>
	        </form>
        </div>
    </div>
    <!-- END: Content-->
    <!-- Modal for Attributes -->
      <div class="modal fade" id="attribute" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog  modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body px-sm-2 mx-50 pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">Select Attribute</h1>
					<p class="text-center">Enter the details below.</p>

					<div class="table-responsive-md customernewsection-form">
								<table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail"> 
									<thead>
										 <tr>  
											<th>Attribute Name</th>
											<th>Attribute Value</th>
										  </tr>
										</thead>
										<tbody>
                                        <tr>
                                            <td>Color</td>
                                            <td>
                                                <select class="form-select select2 attribute-select" data-attribute="color">
                                                    <option>Select</option>
                                                    <option>Black</option>
                                                    <option>White</option>
                                                    <option>Red</option>
                                                    <option>Golden</option>
                                                    <option>Silver</option>
                                                </select>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Size</td>
                                            <td>
                                                <select class="form-select select2 attribute-select" data-attribute="size">
                                                    <option>Select</option>
                                                    <option>5.11"</option>
                                                    <option>5.10"</option>
                                                    <option>5.09"</option>
                                                    <option>5.00"</option>
                                                    <option>6.20"</option>
                                                </select>
                                            </td>
                                        </tr>
											
											
											 
											 

									   </tbody>


								</table>
							</div>
				</div>
				
				<div class="modal-footer justify-content-center">  
                    <button type="button" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal" >Cancel</button>
					<button type="button" class="btn btn-primary" data-bs-dismiss="modal" >Select</button>
				</div>
			</div>
		</div>
	</div>
    
    {{-- <div class="modal fade" id="attribute" tabindex="-1" aria-labelledby="attributeModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title fw-bolder text-dark" id="attributeModalTitle">Item Attributes</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-center">Enter the attribute details for this item.</p>
                    <input type="hidden" id="attribute-row-id" value="">
                    
                    <div class="table-responsive customernewsection-form">
                        <table class="mt-1 table myrequesttablecbox table-striped">
                            <thead>
                                <tr>
                                    <th>Attribute Name</th>
                                    <th>Attribute Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Color</td>
                                    <td>
                                        <select class="form-select attribute-select" data-attribute="color">
                                            <option value="">Select</option>
                                            <option value="Black">Black</option>
                                            <option value="White">White</option>
                                            <option value="Red">Red</option>
                                            <option value="Blue">Blue</option>
                                            <option value="Green">Green</option>
                                            <option value="Golden">Golden</option>
                                            <option value="Silver">Silver</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Size</td>
                                    <td>
                                        <select class="form-select attribute-select" data-attribute="size">
                                            <option value="">Select</option>
                                            <option value="5.11 Inch">5.11 Inch</option>
                                            <option value="5.10 Inch">5.10 Inch</option>
                                            <option value="5.9 Inch">5.9 Inch</option>
                                            <option value="5.8 Inch">5.8 Inch</option>
                                            <option value="5.7 Inch">5.7 Inch</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Weight</td>
                                    <td>
                                        <select class="form-select attribute-select" data-attribute="weight">
                                            <option value="">Select</option>
                                            <option value="100 gm">100 gm</option>
                                            <option value="200 gm">200 gm</option>
                                            <option value="300 gm">300 gm</option>
                                            <option value="400 gm">400 gm</option>
                                            <option value="500 gm">500 gm</option>
                                        </select>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td>Material</td>
                                    <td>
                                        <select class="form-select attribute-select" data-attribute="material">
                                            <option value="">Select</option>
                                            <option value="Metal">Metal</option>
                                            <option value="Plastic">Plastic</option>
                                            <option value="Wood">Wood</option>
                                            <option value="Glass">Glass</option>
                                            <option value="Ceramic">Ceramic</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex mt-2 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
                        <button type="button" class="btn btn-primary" id="save-attributes"><i data-feather="check-circle"></i> Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
    <!-- END: Modal for Attributes -->
    <!-- Modal for Checklist -->
    <div class="modal fade text-start" id="checklist" tabindex="-1" aria-labelledby="myModalLabel17"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select
                            Checklist</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-4">
                            <div class="mb-1">
                                <label class="form-label">Checklist <span class="text-danger">*</span></label>
                                <select class="form-select select2">
                                    <option>Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3  mb-1">
                            <label class="form-label">&nbsp;</label><br />
                            <button class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button>
                        </div>

                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                    <thead>
                                        <tr>
                                            <th width="40px" class="customernewsection-form">
                                                <div class="form-check form-check-primary custom-checkbox">
                                                    <input type="checkbox" class="form-check-input" id="Email">
                                                    <label class="form-check-label" for="Email"></label>
                                                </div>
                                            </th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($checklists as $checklist)
                                            <tr class="trail-bal-tabl-none">
                                                <td class="customernewsection-form">
                                                    <div class="form-check form-check-primary custom-checkbox">
                                                        <input type="checkbox" value="{{ $checklist->id }}" class="form-check-input" id="Email">
                                                        <label class="form-check-label" for="Email"></label>
                                                    </div>
                                                </td>
                                                <td>{{ $checklist->name }}</td>
                                                <td>{{ $checklist->description }}</td>
                                                <td><span class="badge rounded-pill badge-light-secondary">{{ $checklist->type }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i
                            data-feather="x-circle"></i> Cancel</button>
                    <button class="btn btn-primary btn-sm" data-bs-dismiss="modal"><i data-feather="check-circle"></i>
                        Submit</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Modal for Checklist -->
@endsection

@section('scripts')
    <style>
        .is-invalid {
            border-color: #ea5455 !important;
            padding-right: calc(1.45em + 0.876rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23ea5455'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23ea5455' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.3625em + 0.219rem) center;
            background-size: calc(0.725em + 0.438rem) calc(0.725em + 0.438rem);
        }
        
        .hidden {
            display: none;
        }
    </style>
    <script>
        $(document).ready(function () {
			var equipments = @json($equipments);
            var deductTypes = @json($defectTypes);

            $('#book_id').on('change', function() {
                let currentDate = new Date().toISOString().split('T')[0];
                let bookId = $('#book_id').val();
                let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId +
                    "&document_date=" + currentDate;
                fetch(actionUrl).then(response => {
                    return response.json().then(data => {
                        if (data.status == 200) {
                            $("#book_code_input").val(data.data.book_code);
                            if (!data.data.doc.document_number) {
                                $("#document_number").val('');
                                $('#doc_number_type').val('');
                                $('#doc_reset_pattern').val('');
                                $('#doc_prefix').val('');
                                $('#doc_suffix').val('');
                                $('#doc_no').val('');
                            } else {
                                $("#document_number").val(data.data.doc.document_number);
                                $('#doc_number_type').val(data.data.doc.type);
                                $('#doc_reset_pattern').val(data.data.doc.reset_pattern);
                                $('#doc_prefix').val(data.data.doc.prefix);
                                $('#doc_suffix').val(data.data.doc.suffix);
                                $('#doc_no').val(data.data.doc.doc_no);
                            }
                            if (data.data.doc.type == 'Manually') {
                                $("#document_number").attr('readonly', false);
                            } else {
                                $("#document_number").attr('readonly', true);
                            }

                        }
                        if (data.status == 404) {
                            $("#document_number").val('');
                            $('#doc_number_type').val('');
                            $('#doc_reset_pattern').val('');
                            $('#doc_prefix').val('');
                            $('#doc_suffix').val('');
                            $('#doc_no').val('');
                            showToast('error', data.message);
                        }
                    });
                });
            });
            $('#book_id').trigger('change');

            $('#categoryDropdown').on('change', function () {
				const categoryId = $(this).val();
				if (!categoryId) return;

				// get equipments by matching the category id with categoryId
				const filteredEquipments = equipments.filter(e => e.category_id === parseInt(categoryId));

				const $equipmentDropdown = $('#equipmentDropdown');
				$equipmentDropdown.empty();
				$equipmentDropdown.append('<option value="">Select Equipment</option>');
				filteredEquipments.forEach(equipment => {
					$equipmentDropdown.append(`<option value="${equipment.id}">${equipment.name}</option>`);
				});
			});

            $('#equipmentDropdown').on('change', function () {
                const equipmentId = $(this).val();
                if (!equipmentId) return;

                const equipment = equipments.find(e => e.id === parseInt(equipmentId));

                // console.log(equipment);
                renderChecklistTable(equipment.maintenance_details);
                renderDefectsTable(equipment.spare_parts);
            });

            function renderChecklistTable(maintenanceDetails) {
                const $tbody = $('.tab-pane#payment tbody.mrntableselectexcel');
                $tbody.empty();
				console.log(maintenanceDetails, 'maintenanceDetails');
                maintenanceDetails.forEach((detail, index) => {
                    $tbody.append(`
			            <tr>
			                <td>${index + 1}</td>
			                <td class="poprod-decpt p-50"><strong class="font-small-4">${detail.maintenance_type?.name ?? ''}</strong></td>
			                <td class="poprod-decpt p-50"><strong class="font-small-4">${detail.name ?? ''}</strong></td>
			                <td class="poprod-decpt p-50">${detail.frequency}</td>
			            </tr>
			        `);
                    detail.checklists.forEach((checklist, i) => {
						console.log(checklist, i, 'here');
                        const input = checklist.type === 'text'
                            ? `<input type="text" name="checklist_answers[${checklist.id}][text]" class="form-control mw-100" value="${checklist.value ?? ''}" placeholder="Enter answer">`
                            : `<div class="form-check form-check-primary custom-checkbox ms-50">
                        <input type="checkbox" value="yes" name="checklist_answers[${checklist.id}][checkbox]" class="mt-25 form-check-input" ${checklist.value === 'yes' ? 'checked' : ''} />
                        <label class="mb-50 mt-25 form-check-label">Yes/No</label>
                   </div>`;
                        $tbody.append(`
                <tr>
                    <td></td>
                    <td class="ps-1">Checklist ${i + 1}</td>
                    <td class="poprod-decpt">${input}</td>
                    <td></td>
                </tr>
            `);
                    });
                });
            }

            function renderDefectsTable(spareparts) {
                const $tbody = $('.tab-pane#attachment tbody.mrntableselectexcel');
                $tbody.empty();

                const deductTypeOptions = deductTypes.map(defect =>
                    `<option value="${defect.id}" data-priority="${defect.priority}" data-days="${defect.estimated_time}">
				                ${defect.name}
				             </option>`
                ).join('');

                spareparts.forEach((part, index) => {
	                let spareAttributes = JSON.parse(part.attributes);
	                const attributes = Object.entries(spareAttributes)
	                    .map(([name, value]) =>
	                        `<span class="badge rounded-pill badge-light-primary"><strong>${name}</strong>: ${value}</span>`
	                    ).join(' ');

	                $tbody.append(`
			            <tr>
			                <td>${index + 1}</td>
			                <td class="poprod-decpt"><input type="text" value="${part.item_code}" class="form-control mw-100" readonly /></td>
			                <td class="poprod-decpt"><input type="text" value="${part.item_name}" class="form-control mw-100" readonly /></td>
			                <td class="poprod-decpt">${attributes}</td>
			                <td><select class="form-select"><option selected>${part.uom}</option></select></td>
			                <td class="poprod-decpt">${part.qty}</td>
			                <td>
								<select class="form-select mw-100 defect-type-select" name="defects[${part.id}][deduct_type]">
									<option value="">Select</option>
									${deductTypeOptions}
								</select>
							</td>
			                <td class="poprod-decpt">
								<span class="priority-cell"></span>
								<input type="hidden" name="defects[${part.id}][priority]" />
							</td>
       						<td class="poprod-decpt">
								<span class="due-date-cell"></span>
								<input type="hidden" name="defects[${part.id}][due_date]" />
							</td>
			                <td class="poprod-decpt">
								<input type="text" class="form-control mw-100" placeholder="Enter Description" name="defects[${part.id}][description]" />
							</td>
			            </tr>
			        `);
                });

                const nextIndex = spareparts.length + 1;
                $tbody.append(`
				        <tr data-row-id="row-${nextIndex}" class="editable-final-row">
				            <td>${nextIndex}</td>
				            <td class="poprod-decpt"><input type="text" placeholder="-" class="form-control mw-100" /></td>
				            <td class="poprod-decpt"><input type="text" placeholder="-" class="form-control mw-100" /></td>
				            <td>-</td>
				            <td>-</td>
				            <td class="poprod-decpt"><input type="number" placeholder="-" class="form-control mw-100" /></td>
				            <td>
					            <select class="form-select mw-100 defect-type-select" name="defects[custom_final][deduct_type]">
					                <option value="">Select</option>
					                ${deductTypeOptions}
					            </select>
					        </td>
					        <td class="poprod-decpt">
					            <span class="priority-cell"></span>
					            <input type="hidden" name="defects[custom_final][priority]" />
					        </td>
					        <td class="poprod-decpt">
					            <span class="due-date-cell"></span>
					            <input type="hidden" name="defects[custom_final][due_date]" />
					        </td>
					        <td class="poprod-decpt">
					            <input type="text" placeholder="Enter Description" class="form-control mw-100" name="defects[custom_final][description]" />
					        </td>
				        </tr>
				    `);

                $('.defect-type-select').on('change', function () {
                    console.log('erer')
                    const selectedOption = $(this).find('option:selected');
                    const priority = selectedOption.data('priority') || '-';
                    const dueDays = selectedOption.data('days') || '-';

                    const $row = $(this).closest('tr');

                    const dueDate = new Date();
                    dueDate.setDate(dueDate.getDate() + dueDays);
                    const formattedDueDate = dueDate.toISOString().split('T')[0];

                    console.log($row, $row.find('.priority-cell'))
                    // Update visible cells
                    $row.find('.priority-cell').text(priority);
                    $row.find('.due-date-cell').text(formattedDueDate);

                    // Update hidden inputs
                    $row.find('input[name^="defects"][name$="[priority]"]').val(priority);
                    $row.find('input[name^="defects"][name$="[due_date]"]').val(formattedDueDate);
                });
            }

            window.submitForm = function(status) {
                $('#status').val(status);

                let isValid = true;
                let errorMessage = '';

                // Basic field validations
                if ($('select[name="series"]').val() === '') {
                    isValid = false;
                    errorMessage += 'Series is required.<br>';
                }
                if ($('#categoryDropdown').val() === '' && isValid) {
                    isValid = false;
                    errorMessage += 'Category is required.<br>';
                }
                if ($('#equipmentDropdown').val() === '' && isValid) {
                    isValid = false;
                    errorMessage += 'Equipment is required.<br>';
                }
                if ($('input[name="document_number"]').val().trim() === '' && isValid) {
                    isValid = false;
                    errorMessage += 'Doc No is required.<br>';
                }
                if ($('input[name="doc_date"]').val() === '' && isValid) {
                    isValid = false;
                    errorMessage += 'Doc Date is required.<br>';
                }

                // Defects table validation
                $('.tab-pane#attachment tbody tr').each(function (index, row) {
                    const $row = $(row);
                    const isLastRow = $row.hasClass('editable-final-row');
                    const defectType = $row.find('.defect-type-select').val();
                    const itemCode = $row.find('td:eq(1) input').val()?.trim();
                    const itemName = $row.find('td:eq(2) input').val()?.trim();

                    if (!isLastRow && (itemCode || itemName) && defectType === '') {
                        isValid = false;
                        errorMessage += `Defect Type is required for defect row ${index + 1}.<br>`;
                    }
                });

                // Show error if any validation failed
                if (!isValid) {
                    Swal.fire({
                        title: 'Validation Error',
                        html: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }

                // Confirmation messages
                const confirmOptions = {
                    draft: {
                        title: 'Save as Draft',
                        text: 'Are you sure you want to save this maintenance as draft?',
                        confirmButtonText: 'Yes, save it!'
                    },
                    submitted: {
                        title: 'Submit Maintenance',
                        text: 'Are you sure you want to submit this maintenance?',
                        confirmButtonText: 'Yes, submit it!'
                    }
                };

                const options = confirmOptions[status];

                // Final confirmation before submit
                Swal.fire({
                    title: options.title,
                    text: options.text,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: options.confirmButtonText,
                    cancelButtonText: 'No, cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#submitButton').click();
                    }
                });
            }



            function showToast(icon, title) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    },
                });
                Toast.fire({
                    icon,
                    title
                });
            }

	        @if (session('success'))
            showToast("success", "{{ session('success') }}");
	        @endif

	        @if (session('error'))
            showToast("error", "{{ session('error') }}");
	        @endif

	        @if ($errors->any())
            showToast('error', "@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach");
	        @endif

        });
    </script>
@endsection
