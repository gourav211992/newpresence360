@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <form id="equipmentForm" action="{{ route('equipment.update', $equipment->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Equipment</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="{{ route('equipment.index') }}">Home</a>
                                            </li>
                                            <li class="breadcrumb-item active">Edit</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                            @if($buttons['submit'])
                                    <button type="button" onclick="submitForm('draft');" id="draft"
                                        class="btn btn-outline-primary btn-sm mb-50 mb-sm-0"><i data-feather='save'></i> Save as
                                        Draft</button>
                                   
                                        <button type="button" onclick="submitForm('submitted');"
                                        class="btn btn-primary btn-sm mb-50 mb-sm-0" id="submitted"><i
                                            data-feather="check-circle"></i>
                                        Submit</button>
                                        @endif
                                    @if ($buttons['approve'])
                                        <a type="button" id="reject-button" data-bs-toggle="modal"
                                            data-bs-target="#approveModal" onclick = "setReject();"
                                            class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i
                                                data-feather="x-circle"></i> Reject</a>
                                        <a type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#approveModal" onclick = "setApproval();"><i
                                                data-feather="check-circle"></i> Approve</a>
                                    @endif
                    
                  
                                    @if($buttons['amend'])
                                    <a type="button" data-bs-toggle="modal" data-bs-target="#amendmentconfirm" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i> Amendment</a>
                                    @endif
                    
                                   
                                        <input id="submitButton" type="submit" value="Submit" class="hidden" />
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="status" id="status">
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

                <div class="content-body">
                    <section id="basic-datatable">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body customernewsection-form">
                                        <div class="row">
                                            <div class="col-md-12">
                                                 <div
                                                    class="newheader d-flex justify-content-between border-bottom mb-2 pb-25">
                                                    <div>
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                    <div class="header-right">
                                                        @php
                                                            use App\Helpers\Helper;
                                                        @endphp
                                                        <div class="col-md-6 text-sm-end">
                                                            <span class="badge rounded-pill {{App\Helpers\ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$equipment->document_status] ?? ''}} forminnerstatus">
                                                                <span class="text-dark">Status</span>
                                                                 : <span class="{{App\Helpers\ConstantHelper::DOCUMENT_STATUS_CSS[$equipment->document_status] ?? ''}}">
                                                                    @if ($equipment->document_status == App\Helpers\ConstantHelper::APPROVAL_NOT_REQUIRED)
                                                                    Approved
                                                                @else
                                                                    {{ ucfirst($equipment->document_status) }}
                                                                @endif
                                                            </span>
                                                            </span>        
                                                    </div>
                                             </div>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Organization <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" id="organization_id" name="organization_id">
                                                            <option value="">Select</option>

                                                            @foreach ($userOrganizations as $organization)
                                                                <option value="{{ $organization->organization->id }}" @selected (old('organization_id', $equipment->organization_id) == $organization->organization->id) >
                                                                    {{ $organization->organization->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Location <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" id="location_id" name="location_id">
                                                            <option value="">Select</option>
                                                            @foreach ($locations as $loc)
                                                                @if ($loc->organization_id == $equipment->organization_id)
                                                                    <option value="{{ $loc->id }}" {{ $equipment->location_id == $loc->id ? 'selected' : '' }}>
                                                                        {{ $loc->store_name }}
                                                                    </option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Asset Code</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" id="asset_code_id" name="asset_code_id">
                                                            <option value="">Select</option>
                                                            @if(isset($fixedAssetRegistration))
                                                                @foreach($fixedAssetRegistration as $assetCode)
                                                                    <option value="{{ $assetCode->id }}" {{ (old('asset_code_id', $equipment->asset_code_id ?? '') == $assetCode->id) ? 'selected' : '' }}>
                                                                        {{ $assetCode->asset_code }}
                                                                    </option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Category <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" id="category_id" name="category_id">
                                                            <option value="">Select</option>
                                                            @foreach($categories as $category)
                                                                @if ($category->organization_id == $equipment->organization_id)
                                                                    <option value="{{ $category->id}}" {{ $equipment->category_id == $category->id ? 'selected' : '' }}>
                                                                        {{ $category->name }}
                                                                    </option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                               

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Name <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="name" value="{{ old('name', $equipment->name) }}">
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Alias</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="alias" value="{{ old('alias', $equipment->alias) }}">
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Description</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="description" value="{{ old('description', $equipment->description) }}">
                                                    </div>
                                                </div>
                                            </div>
                                        @include('partials.approval-history', ['document_status' =>$equipment->document_status, 'revision_number' => $equipment->revision_number])
                                 
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body customernewsection-form">

                                        <div class="border-bottom mb-2 pb-25">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader ">
                                                        <h4 class="card-title text-theme">Maintenance Detail
                                                        </h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-sm-end @if(!$buttons['submit']) d-none @endif">
                                                    <a href="javascript:void(0);" id="deleteRowBtn"
                                                        class="btn btn-sm btn-outline-danger me-50">
                                                        <i data-feather="x-circle"></i> Delete</a>
                                                    <a href="javascript:void(0);" id="addRowBtn"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i data-feather="plus"></i> Add New Item</a>

                                                </div>
                                            </div>
                                        </div>

                                        

                                        <div class="tab-content pb-1">
                                            <div class="tab-pane active" id="Maintenance">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="table-responsive pomrnheadtffotsticky">
                                                            <table
                                                                class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                               <thead>
                                                                    <tr>
                                                                        <th width="62" class="customernewsection-form">
                                                                            <div
                                                                                class="form-check form-check-primary custom-checkbox">
                                                                                <input type="checkbox"
                                                                                    class="form-check-input" id="Email">
                                                                                <label class="form-check-label"
                                                                                    for="Email"></label>
                                                                            </div>
                                                                        </th>
                                                                        <th width="285">Maint Type</th>
                                                                        <th width="208">Frequency</th>
                                                                        <th width="208">Start Date</th>
                                                                        <th width="269">Time</th>
                                                                        <th width="208">Maintenance BOM</th>
                                                                        <th width="329">Checklist</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="mrntableselectexcel" id="maintenanceRows">

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Spare Part Tab -->
                                            <div class="tab-pane" id="Spare">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="table-responsive pomrnheadtffotsticky">
                                                            <table
                                                                class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                                <thead>
                                                                    <tr>
                                                                        <th width="62" class="customernewsection-form">
                                                                            <div
                                                                                class="form-check form-check-primary custom-checkbox">
                                                                                <input type="checkbox"
                                                                                    class="form-check-input" id="Email">
                                                                                <label class="form-check-label"
                                                                                    for="Email"></label>
                                                                            </div>
                                                                        </th>
                                                                        <th width="285">Item Code</th>
                                                                        <th width="208">Item Name</th>
                                                                        <th width="269">Attributes</th>
                                                                        <th width="329">UOM</th>
                                                                        <th>Qty</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="mrntableselectexcel" id="spareRows">
                                                                </tbody>

                                                                <tfoot>
                                                                    <tr class="totalsubheadpodetail">
                                                                        <td colspan="6"></td>
                                                                    </tr>
                                                                    <tr valign="top">
                                                                        <td colspan="6">
                                                                            <table class="table border">
                                                                                <tr>
                                                                                    <td class="p-0">
                                                                                        <h6
                                                                                            class="text-dark mb-0 bg-light-primary py-1 px-50">
                                                                                            <strong>Part Details</strong>
                                                                                        </h6>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="poprod-decpt">
                                                                                        <div
                                                                                            class=" mw-100">
                                                                                            <strong>Name</strong>:
                                                                                            <span id="part-detail-name"></span>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="poprod-decpt">
                                                                                        <div
                                                                                            class="badge rounded-pill badge-light-primary"><strong>HSN</strong>:
                                                                                            <span id="part-detail-hsn"></span>
                                                                                        </div>
                                                                                        <div
                                                                                            class="badge rounded-pill badge-light-primary">
                                                                                            <strong>Color</strong>:
                                                                                            <span id="part-detail-color"></span>
                                                                                        </div>
                                                                                        <div
                                                                                            class="badge rounded-pill badge-light-primary"><strong>Size</strong>:
                                                                                            <span id="part-detail-size"></span>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="poprod-decpt">
                                                                                        <div
                                                                                            class="badge rounded-pill badge-light-primary"><strong>Inv.
                                                                                                UOM</strong>:
                                                                                            <span id="part-detail-uom"></span>
                                                                                        </div>
                                                                                        <div
                                                                                            class="badge rounded-pill badge-light-primary"><strong>Qty.</strong>:
                                                                                            <span id="part-detail-qty"></span>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="poprod-decpt">
                                                                                        <div
                                                                                            class="badge rounded-pill badge-light-secondary">
                                                                                            <strong>Remarks</strong>:
                                                                                            <span id="part-detail-remarks"></span>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Spare Part Tab -->
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="col-md-4">
                                                    <div class="mb-1">
                                                        <label class="form-label">Upload Document</label>
                                                        <input type="file" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">Final Remarks</label>
                                                    <textarea type="text" rows="4" class="form-control" placeholder="Enter Remarks here...">{{ old('remarks', $equipment->remarks) }}</textarea>
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
                <div class="modal-body" id="checkListPortion">
                    <div class="row checklist-portion">

                        <div class="col-md-4">
                            <div class="mb-1">
                                <label class="form-label">Checklist <span class="text-danger">*</span></label>
                                <select class="form-select select2">
                                    <option>Select</option>
                                    @foreach($checklists as $checklist)
                                    <option value="{{ $checklist->id }}">{{ $checklist->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3  mb-1">
                            <label class="form-label">&nbsp;</label><br />
                            <button class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button>
                        </div>

                        <div class="col-md-12">
                            <div class="table-responsive">
                                Select Checklist
                                <div class="text-end" style="margin-top: -30px"><a href="#" class="text-primary add-contactpeontxt mt-50" onclick="addPortion()"><i data-feather='plus'></i> Add Checklist</a></div>
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

    <div class="modal fade text-start" id="viewChecklist" tabindex="-1" aria-labelledby="myModalLabel18"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel18">Select
                            Checklist</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewCheckListPortion">
                    <div class="row checklist-portion">

                        <div class="col-md-4">
                            <div class="mb-1">
                                <label class="form-label">Checklist <span class="text-danger">*</span></label>
                                <select class="form-select select2">
                                    <option>Select</option>
                                    @foreach($checklists as $checklist)
                                    <option value="{{ $checklist->id }}">{{ $checklist->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3  mb-1">
                            <label class="form-label">&nbsp;</label><br />
                            <button class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button>
                        </div>

                        <div class="col-md-12">
                            <div class="table-responsive">
                                Select Checklist
                                <div class="text-end" style="margin-top: -30px"><a href="#" class="text-primary add-contactpeontxt mt-50" onclick="addPortion()"><i data-feather='plus'></i> Add Checklist</a></div>
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
      <!-- END: Content-->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form class="ajax-input-form" method="POST" action="{{ route('equipment.approval') }}"
                    data-redirect="{{ route('equipment.index') }}" enctype='multipart/form-data'>
                    @csrf
                    <input type="hidden" name="action_type" id="action_type">
                    <input type="hidden" name="id" value="{{ $equipment->id }}">
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17"></h4>
                            <p class="mb-0 fw-bold voucehrinvocetxt mt-0">{{ Carbon\Carbon::now()->format('d-m-Y') }}
                            </p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pb-2">
                        <div class="row mt-1">
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                    <textarea name="remarks" class="form-control"></textarea>
                                </div>
                                <div class="mb-1">
                                    <label class="form-label">Upload Document</label>
                                    <input type="file" multiple class="form-control" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submit-button">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Amendment Modal --}}
    <div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body alertmsg text-center warning">
                    <i data-feather='alert-circle'></i>
                    <h2>Are you sure?</h2>
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>Equipment</strong>? After Amendment this action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
      </div>
        <!-- END: Content-->

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
            

            var allLocations = @json($locations);
            // var allCategories = @json($categories);
            var maintenanceTypes = @json($maintenanceTypes);
            var maintenanceBOM = @json($maintenanceBOM);
            let items = @json($items);
            let checkListIds = @json($checkListIds);
            let mainChecklistNames = @json($mainChecklistNames ?? []);

            var existingMaintenanceDetails = @json($equipment->maintenanceDetails)??{};
            var existingSpareParts = @json($equipment->spareParts)??{};


            if (existingMaintenanceDetails.length > 0) {
                existingMaintenanceDetails.forEach((m) => {
                    $('#maintenanceRows').append(getMaintenanceRow(m));
                });
            } else {
                $('#maintenanceRows').append(getMaintenanceRow());
            }

            if (existingSpareParts.length > 0) {
                existingSpareParts.forEach((p) => {
                    $('#spareRows').append(getSparePartRow(p));
                });
            } else {
                $('#spareRows').append(getSparePartRow());
            }

             @if(!$buttons['submit'])
        $('#equipmentForm').find('input, select,button,textarea').prop('disabled', true);
       
        $('#revisionNumber').prop('disabled', false);
        $('#back').prop('disabled', false);
        @endif

         $(function() {
            $("#revisionNumber").change(function() {
                const fullUrl = "{{ route('equipment.edit', $equipment->id) }}?revisionNumber=" +
                    $(this)
                    .val();
                window.open(fullUrl, "_blank");
            });
        });

            // On organization change, filter locations
            $('#organization_id').on('change', function () {
                var orgId = $(this).val();
                var locationSelect = $('#location_id');
                locationSelect.html('<option value="">Select</option>');
                // $('#category_id').html('<option value="">Select</option>');

                if (orgId) {

                    allLocations.forEach(function (loc) {
                        if (loc.organization_id == orgId) {
                            locationSelect.append('<option value="' + loc.id + '">' + loc
                                            .store_name +
                                    '</option>');
                        }
                    });
                }
            });

            $('#location_id').on('change', function () {
                var locationId = $(this).val();
                var categorySelect = $('#category_id');
                categorySelect.html('<option value="">Select</option>');
                var categories = [
                    {
                        id: 1,
                        store_name: 'Category 1'
                    },
                    {
                        id: 2,
                        store_name: 'Category 2'
                    },
                    {
                        id: 3,
                        store_name: 'Category 3'
                    }
                ];

                if (locationId) {
                    categories.forEach(function (cat) {
                        // if (cat.id == locationId) {
                        categorySelect.append('<option value="' + cat.id + '">' + cat
                                        .store_name +
                                '</option>');
                        // }
                    });
                }
            });


            function getMaintenanceRow(data = {}) {

                const rowId = 'row-' + Math.random().toString(36).substring(2, 10);

                // Build options from maintenanceTypes
                let typeOptions = `<option value="">Select</option>`;
                maintenanceTypes.forEach(function (type) {
                    typeOptions += `<option value="${type.id}" ${data.maintenance_type_id == type.id ? 'selected' : ''}>${type.name}</option>`;
                });
            let bomOptions = `<option value="">Select</option>`;
            maintenanceBOM.forEach(function (type) {
                bomOptions += `<option value="${type.id}" ${data.maintenance_bom_id == type.id ? 'selected' : ''}>${type.name}</option>`;
            });
                
                // Extract checklist data properly from checklist_detail JSON
                let checklistData = [];
                let selectedNames = [];
                let selectedIds = [];
                
                if (data.checklists && Array.isArray(data.checklists)) {
                    data.checklists.forEach(checklist => {
                        let checklistDetail = null;
                        
                        // Parse checklist_detail JSON if it exists
                        if (checklist.checklist_detail) {
                            try {
                                checklistDetail = JSON.parse(checklist.checklist_detail);
                            } catch (e) {
                                console.error('Error parsing checklist_detail:', e);
                            }
                        }
                        
                        if (checklistDetail) {
                            // Use data from checklist_detail JSON (preferred)
                            checklistData.push({
                                checklist_id: checklistDetail.checklist_id,
                                checklist_detail_id: checklistDetail.checklist_detail_id,
                                name: checklistDetail.main_checklist_name || checklist.name,
                                description: checklistDetail.description,
                                type: checklistDetail.data_type
                            });
                            selectedNames.push(checklistDetail.main_checklist_name || checklist.name);
                            selectedIds.push(checklistDetail.checklist_detail_id);
                        } else {
                            // Fallback to direct checklist data
                            checklistData.push({
                                checklist_id: null,
                                checklist_detail_id: checklist.id,
                                name: checklist.name,
                                description: checklist.description,
                                type: checklist.type
                            });
                            selectedNames.push(checklist.name);
                            selectedIds.push(checklist.id);
                        }
                    });
                }
                
                let selectedIdsString = selectedIds.join(',');

                // Create badges from unique main checklist names
                let uniqueNames = [...new Set(selectedNames.filter(name => name))];
                let badgesHtml = '';
                
                if (uniqueNames.length > 0) {
                    badgesHtml = `<span class="badge rounded-pill badge-light-primary">${uniqueNames[0]}</span>`;
                    if (selectedNames.length > 1) {
                        badgesHtml += ` <span class="badge rounded-pill badge-light-primary">+${selectedNames.length - 1}</span>`;
                    }
                }
                let row = `<tr data-row-id="${rowId}">
                            <td class="customernewsection-form">
                                <div class="form-check form-check-primary custom-checkbox">
                                    <input type="checkbox" class="form-check-input row-checkbox">
                                    <label class="form-check-label"></label>
                                </div>
                            </td>
                            <td class="poprod-decpt">
                                <select name="maintenance[${rowId}][type]" class="form-select mw-100 maintenance-type">
                                    ${typeOptions}
                                </select>
                            </td>
                            <td class="poprod-decpt">
                                <select name="maintenance[${rowId}][frequency]" class="form-select mw-100">
                                    <option value="">Select</option>
                                    <option value="Daily" ${data.frequency == 'Daily' ? 'selected' : ''}>Daily</option>
                                    <option value="Weekly" ${data.frequency == 'Weekly' ? 'selected' : ''}>Weekly</option>
                                    <option value="Monthly" ${data.frequency == 'Monthly' ? 'selected' : ''}>Monthly</option>
                                    <option value="Quarterly" ${data.frequency == 'Quarterly' ? 'selected' : ''}>Quarterly</option>
                                    <option value="Semi-Annually" ${data.frequency == 'Semi-Annually' ? 'selected' : ''}>Semi-Annually</option>
                                    <option value="Annually" ${data.frequency == 'Annually' ? 'selected' : ''}>Annually</option>
                                </select>
                            </td>
                            <td class="poprod-decpt">
                                    <input type="date" name="maintenance[${rowId}][date]" value="${data.start_date || ''}" required class="form-control mw-100 mb-25" />
                                </td>
                            <td class="poprod-decpt">
                                <input type="time" name="maintenance[${rowId}][time]" value="${data.time || ''}" placeholder="Enter Time" class="form-control mw-100 mb-25" />
                            </td>
                             <td class="poprod-decpt">
                                    <select name="maintenance[${rowId}][bom]" required  class="form-select mw-100 maintenance-bom">
                                        ${bomOptions}
                                    </select>
                                </td>
                            <td class="poprod-decpt checklist-cell">
                                <span class="checklist-badges">${badgesHtml}</span>
                                <button type="button" class="btn p-25 btn-sm btn-outline-secondary @if($buttons['submit']) open-checklist-modal @else view-checklist-modal @endif" style="font-size: 10px" @if(!$buttons['submit']) onclick="getPopupChecklistData(${data.erp_equipment_id},${data.id})" @endif>Add Checklist</button>
                                <input type="hidden" class="selected-checklists" value="${selectedIdsString}" />
                                <!-- Store checklist data for form submission -->
                                <div class="existing-checklist-data" style="display: none;">
                                    ${checklistData.map((item, index) => `
                                        <input type="hidden" name="maintenance[${rowId}][checklists][${index}][checklist_id]" value="${item.checklist_id || ''}">
                                        <input type="hidden" name="maintenance[${rowId}][checklists][${index}][checklist_detail_id]" value="${item.checklist_detail_id}">
                                    `).join('')}
                                </div>
                                <!-- Legacy hidden inputs removed - only modal-driven inputs will be used -->
                            </td>
                        </tr>`;

                $(function () {
                    $(".ledgerselecct").autocomplete({
                        source: maintenanceTypes.map(item => item.name),
                        minLength: 0
                    }).focus(function () {
                        if (this.value == "") {
                            $(this).autocomplete("search");
                        }
                    });
                });

                return row;
            }

            // Global variables for checklist management
            let currentRowId = null;
            let checklistContexts = {};
            let selectedChecklistIds = [];
            let portionChecklistData = {}; // Store checklist data per portion

            $('.open-checklist-modal').prop('disabled', false);
            $('.view-checklist-modal').prop('disabled', false);


            $(document).on('click', '.open-checklist-modal', function () {
                checklistRowRef = $(this).closest('tr');
                
                // Get row ID from input name or data attribute
                currentRowId = getRowId(checklistRowRef);
                
                if (!currentRowId) {
                    console.error('Could not determine row ID');
                    return;
                }
                
                // Initialize context for this row if not exists
                if (!checklistContexts[currentRowId]) {
                    checklistContexts[currentRowId] = {
                        mainChecklistId: null,
                        mainChecklistName: null,
                        selectedItems: []
                    };
                }
                
                // Reset modal state
                resetChecklistModal();
                
                // Only load existing selections if this row has saved checklist data
                const hasExistingData = checklistRowRef.find('.selected-checklists').val();
                if (hasExistingData && hasExistingData.trim() !== '') {

                    loadExistingSelections(currentRowId);
                } else {

                }
                
                $('#checklist').modal('show');
            });
            
            /**
             * Get row ID from maintenance row element
             */
            function getRowId(rowElement) {
                // Try to get from input name first
                const inputName = rowElement.find('input, select').first().attr('name');
                if (inputName) {
                    const match = inputName.match(/maintenance\[(.*?)\]/);
                    if (match) return match[1];
                }
                
                // Fallback to data attribute
                const dataRowId = rowElement.attr('data-row-id');
                if (dataRowId) return dataRowId;
                
                // Generate temporary ID as last resort
                return 'temp-' + Date.now();
            }
            
            /**
             * Reset checklist modal to clean state
             */
            function resetChecklistModal() {
                $('#checklist .modal-body select').val('');
                $('#checklist .modal-body tbody').empty();
                $('#checklist .modal-body input[type="checkbox"]').prop('checked', false);
                selectedChecklistIds = [];
                
                // Clear portion checklist data for current row
                portionChecklistData = {};

            }
            
            /**
             * Load existing checklist selections for the current row
             */
            function loadExistingSelections(rowId) {
                if (!checklistRowRef) return;
                
                const selectedIds = checklistRowRef.find('.selected-checklists').val();
                if (selectedIds) {
                    const ids = selectedIds.split(',').filter(Boolean);
                    
                    // Pre-check existing selections when modal content loads
                    setTimeout(() => {
                        $('#checklist .modal-body input[type="checkbox"]').each(function () {
                            if (ids.includes($(this).val())) {
                                $(this).prop('checked', true);
                            }
                        });
                    }, 100);
                }
            }

          

            /**
             * Handle checklist modal close - save selections
             */
            $('#checklist').on('hide.bs.modal', function (e) {
                // Only proceed if submit button was clicked
                if ($(document.activeElement).hasClass('btn-primary')) {
                    saveChecklistSelections();
                }
            });
            
            /**
             * Save checklist selections to the maintenance row
             */
            function saveChecklistSelections() {
                if (!currentRowId || !checklistRowRef) {
                    console.error('No active row for checklist selection');
                    return;
                }
                
                const selectedData = collectSelectedChecklistData();
                
                // Skip update if no selections made
                if (selectedData.length === 0) {
                    return;
                }
                
                // Update the row with selected checklist data
                updateRowWithChecklistData(selectedData);
            }
            
            /**
             * Collect selected checklist data from modal
             * Only collect essential IDs - backend will fetch details
             */
            function collectSelectedChecklistData() {
                const selectedData = [];
                
                // Loop through all checklist portions
                $('#checklist .modal-body .checklist-portion').each(function() {
                    const portion = $(this);
                    const portionId = portion.attr('id') || 'portion_default';
                    const portionData = portionChecklistData[portionId];
                    
                    if (!portionData) {

                        return;
                    }
                    

                    
                    // Find checked checkboxes in this portion
                    portion.find('input[type="checkbox"]:checked').each(function () {
                        const checkbox = $(this);
                        
                        const item = {
                            checklist_id: portionData.checklistId,
                            checklist_detail_id: checkbox.val()
                        };
                        

                        selectedData.push(item);
                    });
                });
                

                return selectedData;
            }
            
            /**
             * Update maintenance row with checklist data
             */
            function updateRowWithChecklistData(selectedData) {
                const context = checklistContexts[currentRowId] || {};
                const selectedIds = selectedData.map(item => item.checklist_detail_id);
                
                // Create badge display
                let badgesHtml = '';
                if (context.mainChecklistName) {
                    badgesHtml = `<span class="badge rounded-pill badge-light-primary">${context.mainChecklistName}</span>`;
                    if (selectedData.length > 1) {
                        badgesHtml += ` <span class="badge rounded-pill badge-light-primary">+${selectedData.length - 1}</span>`;
                    }
                } else if (mainChecklistNames.length > 0) {
                    // Fallback to saved main checklist names if context not available
                    badgesHtml = `<span class="badge rounded-pill badge-light-primary">${mainChecklistNames[0]}</span>`;
                    if (mainChecklistNames.length > 1) {
                        badgesHtml += ` <span class="badge rounded-pill badge-light-primary">+${mainChecklistNames.length - 1}</span>`;
                    }
                }
                
                // Create hidden inputs for form submission - only essential IDs
                let hiddenInputs = '';
                selectedData.forEach((item, index) => {
                    hiddenInputs += `
                        <input type="hidden" name="maintenance[${currentRowId}][checklists][${index}][checklist_id]" value="${item.checklist_id || ''}">
                        <input type="hidden" name="maintenance[${currentRowId}][checklists][${index}][checklist_detail_id]" value="${item.checklist_detail_id}">
                    `;
                });
                
                // Update row HTML
                const newHtml = `
                    <span class="checklist-badges">${badgesHtml}</span>
                    <button type="button" class="btn p-25 btn-sm btn-outline-secondary @if($buttons['submit']) open-checklist-modal @else view-checklist-modal @endif" style="font-size: 10px">Add Checklist</button>
                    <input type="hidden" class="selected-checklists" value="${selectedIds.join(',')}" />
                    ${hiddenInputs}
                `;
                
                checklistRowRef.find('.checklist-cell').html(newHtml);
            }

            /**
             * Handle search button click for checklist details
             */
            $(document).on('click', '.btn-warning', function(e) {
                e.preventDefault();
                
                const dropdown = $(this).closest('.row').find('select');
                const checklistId = dropdown.val();
                const checklistName = dropdown.find('option:selected').text();
                
                if (!checklistId) {
                    return;
                }
                
                // Get the current portion ID
                const portionElement = $(this).closest('.checklist-portion');
                const portionId = portionElement.attr('id') || 'portion_default';
                

                
                // Store checklist data for this specific portion
                portionChecklistData[portionId] = {
                    checklistId: checklistId,
                    checklistName: checklistName
                };
                

                
                // Store checklist context for current row (keep for compatibility)
                if (checklistContexts[currentRowId]) {
                    checklistContexts[currentRowId].mainChecklistId = checklistId;
                    checklistContexts[currentRowId].mainChecklistName = checklistName;
                }
                
                // Check if checklist already selected
                if (selectedChecklistIds.includes(checklistId)) {
                    dropdown.val('');
                    return;
                }
                
                // Load checklist details via AJAX
                loadChecklistDetails(checklistId, $(this));
            });
            
            /**
             * Load checklist details from server
             */
            function loadChecklistDetails(checklistId, buttonElement) {
                $.ajax({
                    url: '{{ route("equipment.get-checklist-details") }}',
                    method: 'POST',
                    data: {
                        checklist_id: checklistId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.success && response.data.checklist) {
                            populateChecklistTable(response.data, buttonElement);
                        } else {
                            showChecklistError(response.message || 'No checklist data found', buttonElement);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        showChecklistError('Error loading checklist details', buttonElement);
                    }
                });
            }
            
            /**
             * Populate checklist table with data
             */
            function populateChecklistTable(data, buttonElement) {
                const tbody = buttonElement.closest('.row').find('tbody');
                tbody.empty();
                
                // Get existing selected IDs
                let existingSelectedIds = checkListIds || [];
                $('#maintenanceRows .selected-checklists').each(function() {
                    const val = $(this).val();
                    if (val) {
                        existingSelectedIds = existingSelectedIds.concat(val.split(',').filter(Boolean));
                    }
                });
                
                const checklists = data.checklist || [];
                
                if (checklists.length > 0) {
                    checklists.forEach(function (checklist) {
                        const isPreSelected = existingSelectedIds.includes(checklist.id.toString());
                        
                        const tableRow = `
                            <tr>
                                <td class="customernewsection-form">
                                    <div class="form-check form-check-primary custom-checkbox">
                                        <input type="checkbox" class="form-check-input" value="${checklist.id}" ${isPreSelected ? 'checked' : ''}>
                                        <label class="form-check-label"></label>
                                    </div>
                                </td>
                                <td>${checklist.name || ''}</td>
                                <td>${checklist.description || ''}</td>
                                <td>
                                    <span class="badge rounded-pill badge-light-info">
                                        ${checklist.data_type || ''}
                                    </span>
                                </td>
                            </tr>
                        `;
                        tbody.append(tableRow);
                    });
                    
                    updateAllDropdowns();
                } else {
                    tbody.html('<tr><td colspan="4" class="text-center text-muted">No checklist data found</td></tr>');
                }
            }
            
            /**
             * Show error message in checklist table
             */
            function showChecklistError(message, buttonElement) {
                const tbody = buttonElement.closest('.row').find('tbody');
                tbody.html(`<tr><td colspan="4" class="text-center text-danger">${message}</td></tr>`);
            }

            /**
             * Update all dropdowns by removing selected options
             */
            function updateAllDropdowns() {
                let allSelectedIds = [];
                
                // Get currently selected checklist IDs from all portions
                $('.checklist-portion select').each(function() {
                    const val = $(this).val();
                    if (val && val !== 'Select') {
                        allSelectedIds.push(val);
                    }
                });
                
                // Remove duplicates
                allSelectedIds = [...new Set(allSelectedIds)];
                
                // Update each dropdown
                $('.form-select.select2').each(function() {
                    const currentSelect = $(this);
                    const currentValue = currentSelect.val();
                    
                    // Remove all options except the first one (Select)
                    currentSelect.find('option:not(:first)').remove();
                    
                    let addedCount = 0;
                    // Add back all checklists that are not selected in other portions
                    @foreach($checklists as $checklist)
                        // Allow current selection but prevent selection in other portions
                        if (!allSelectedIds.includes('{{ $checklist->id }}') || currentValue === '{{ $checklist->id }}') {
                            currentSelect.append(`<option value="{{ $checklist->id }}">{{ $checklist->name }}</option>`);
                            addedCount++;
                        }
                    @endforeach
                    

                    
                    // Restore current value if it's still valid
                    if (currentValue && currentValue !== 'Select') {
                        currentSelect.val(currentValue);
                    }
                });
            }

            // Add Portion function (same as create page) - Add new checklist sections in modal
            window.addPortion = function() {
                // First get currently selected checklist IDs to exclude them
                let selectedIds = [];
                


                
                $('.checklist-portion select').each(function(index) {
                    const val = $(this).val();

                    if (val && val !== 'Select') {
                        selectedIds.push(val);
                    }
                });
                

                
                const portionId = 'portion_' + Date.now(); // Unique ID for each portion
                
                // Build available checklists array from PHP
                const allChecklists = [
                    @foreach($checklists as $index => $checklist)
                    {
                        id: '{{ $checklist->id }}',
                        name: '{{ addslashes($checklist->name) }}'
                    }@if(!$loop->last),@endif
                    @endforeach
                ];
                

                
                // Build options dynamically excluding already selected ones
                let optionsHtml = '<option>Select</option>';
                allChecklists.forEach(function(checklist) {
                    if (!selectedIds.includes(checklist.id)) {
                        optionsHtml += `<option value="${checklist.id}">${checklist.name}</option>`;
                    }
                });
                

                
                const newPortionHtml = `
                    <div class="row checklist-portion" id="${portionId}">
                        <div class="col-md-4">
                            <div class="mb-1">
                                <label class="form-label">Checklist <span class="text-danger">*</span></label>
                                <select class="form-select select2">
                                    ${optionsHtml}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 mb-1">
                            <label class="form-label">&nbsp;</label><br />
                            <button class="btn btn-warning btn-sm waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg> Search</button>
                        </div>

                        <div class="col-md-12">
                            <div class="table-responsive">
                                Select Checklist
                                <div class="text-end" style="margin-top: -30px">
                                    <a href="#" class="text-primary add-contactpeontxt mt-50 me-2" onclick="addPortion()"><i data-feather='plus'></i> Add Checklist</a>
                                    <a href="#" class="text-danger remove-contactpeontxt mt-50" onclick="removePortion('${portionId}')"><i data-feather='minus'></i> Remove Checklist</a>
                                </div>
                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                    <thead>
                                        <tr>
                                            <th width="40px" class="customernewsection-form">
                                                <div class="form-check form-check-primary custom-checkbox">
                                                    <input type="checkbox" class="form-check-input" id="Email_${portionId}">
                                                    <label class="form-check-label" for="Email_${portionId}"></label>
                                                </div>
                                            </th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                       
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;

                $('#checkListPortion').append(newPortionHtml);
                
                // Re-initialize Select2 for new dropdowns
                $('.select2').select2();
                
                // Don't call updateAllDropdowns() here as we already built filtered options

            }

            // Remove Portion function
            window.removePortion = function(portionId) {
                $('#' + portionId).remove();
                // Update all dropdowns after removing a portion
                updateAllDropdowns();

            }

            // Handle modal select all checkbox
            $('.myrequesttablecbox thead input[type="checkbox"]').on('change', function () {
                var tbody = $(this).closest('table').find('tbody');
                var checked = $(this).is(':checked');
                tbody.find('input.form-check-input').prop('checked', checked);
            });

            // Handle dropdown change to update other dropdowns (prevent duplicates)
            $(document).on('change', '.checklist-portion select', function() {
                updateAllDropdowns();
            });

            // Collect checklist data and associate with maintenance rows (same as create page)
            function collectChecklistData() {
                let checklistData = [];
                let detailIdCounter = 1;
                

                
                // Loop through all checklist portions
                $('#checkListPortion .row').each(function(index) {
                    const portion = $(this);
                    const checklistId = portion.find('select').val();
                    const checklistName = portion.find('select option:selected').text();
                    

                    
                    // Only include if a checklist is selected
                    if (checklistId && checklistId !== 'Select') {
                        // Add all items from this checklist (only checked items)
                        portion.find('tbody tr').each(function(itemIndex) {
                            const row = $(this);
                            const checkbox = row.find('input[type="checkbox"]');
                            
                            // Only collect checked items
                            if (checkbox.is(':checked')) {
                                const itemChecklistId = row.find('td:nth-child(6)').text().trim();// This is the actual checklist detail ID
                                const itemName = row.find('td:nth-child(5)').text().trim();
                                const itemDescription = row.find('td:nth-child(3)').text().trim();
                                const itemType = row.find('td:nth-child(4)').text().trim();
                                

                                
                                checklistData.push({
                                    checklist_id: checklistId, // Header checklist ID from dropdown
                                    checklist_detail_id: itemChecklistId, // Actual detail ID from checkbox
                                    name: itemName,
                                    description: itemDescription,
                                    type: itemType
                                });
                                detailIdCounter++;
                            }
                        });
                    }
                });
                


                
                return checklistData;
            }

            // Update maintenance rows with checklist data (same as create page)
            /**
             * Update checklist inputs for form submission
             * This function ensures all checklist data is properly included in the form
             */
            function updateChecklistInputs() {
                // This function is now handled by the modal close handler
                // which directly updates the row with hidden inputs
                // No additional processing needed here as data is already in the form

            }

            /**
             * Handle form submission - ensure checklist data is preserved
             */
            $('form').on('submit', function(e) {
                // Always collect and update checklist data before form submission
                updateChecklistInputs();
                

                
                // Print detailed analysis for each row
                if (typeof allMaintenanceData !== 'undefined') {
                    Object.keys(allMaintenanceData).forEach(rowId => {
                        const rowData = allMaintenanceData[rowId];
                        
                        // Check for duplicate IDs
                        if (rowData && rowData.checklists) {
                            rowData.checklists.forEach((checklist, index) => {
                                // Process checklist data if needed
                            });
                        }
                    });
                }
                
                // Check for duplicate IDs across rows
                const allChecklistIds = [];
                const allDetailIds = [];
                Object.keys(allMaintenanceData).forEach(rowId => {
                    allMaintenanceData[rowId].checklists.forEach(checklist => {
                        allChecklistIds.push({rowId, checklist_id: checklist.checklist_id, name: checklist.name});
                        allDetailIds.push({rowId, checklist_detail_id: checklist.checklist_detail_id, name: checklist.name});
                    });
                });
                


                
                // Alert with summary
                const summary = Object.keys(allMaintenanceData).map(rowId => {
                    const rowData = allMaintenanceData[rowId];
                    const checklistSummary = rowData.checklists.map(c => 
                        `ID:${c.checklist_detail_id} Name:${c.name}`
                    ).join(', ');
                    return `Row ${rowId}: ${rowData.checklistCount} checklists (${checklistSummary})`;
                }).join('\n');
                
                // alert(`FORM SUBMISSION STOPPED FOR DEBUGGING\n\nMaintenance Rows Summary:\n${summary}\n\nCheck console for detailed data.`); // Removed for actual submission
                

                updateChecklistInputs();
            });

            // Template row for Spare Part
            // function getSparePartRow() {
            //     return `<tr>
            //                 <td class="customernewsection-form">
            //                     <div class="form-check form-check-primary custom-checkbox">
            //                         <input type="checkbox" class="form-check-input row-checkbox">
            //                         <label class="form-check-label"></label>
            //                     </div>
            //                 </td>
            //                 <td class="poprod-decpt"><input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25" /></td>
            //                 <td class="poprod-decpt"><input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25" /></td>
            //                 <td class="poprod-decpt">
            //                     <button data-bs-toggle="modal" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
            //                 </td>
            //                 <td><select class="form-select"><option>Select</option><option selected>KG</option></select></td>
            //                 <td><input type="text" value="10" class="form-control mw-100" /></td>
            //             </tr>`;
            // }

            function getSparePartRow(data = {}) {
               let itemOptions = `<option value="">Select</option>`;
                items.forEach(function (item) {
                    itemOptions += `<option value="${item.id}" data-name="${item.item_name}" data-code="${item.item_code}" ${data.item_code == item.id ? 'selected' : ''}>${item.item_code}</option>`;
                });

                const rowId = 'spare-' + Math.random().toString(36).substring(2, 10);
                let badgeHtml = '';


                let attributes = JSON.parse(data.attributes??null);
                if(attributes==null) return;
               
                Object.entries(attributes).forEach(([key, value]) => {
                    badgeHtml += `<span class="badge rounded-pill badge-light-primary">${key}: ${value}</span> `;
                });
                return `<tr data-row-id="${rowId}">
                    <td class="customernewsection-form">
                        <div class="form-check form-check-primary custom-checkbox">
                            <input type="checkbox" class="form-check-input row-checkbox">
                            <label class="form-check-label"></label>
                        </div>
                    </td>
                    <td class="poprod-decpt">
                        <select class="form-select mw-100 item-code-dropdown" name="spareparts[${rowId}][item_code]">
                            ${itemOptions}
                        </select>
                    </td>
                    <td class="poprod-decpt">
                        <input type="text" class="form-control mw-100 item-name-input" name="spareparts[${rowId}][item_name]" value="${data.item_name}" />
                    </td>
                    <td class="poprod-decpt">
                        ${badgeHtml}
                        <button type="button" data-row-id="${rowId}" class="btn p-25 btn-sm btn-outline-secondary open-attribute-modal" style="font-size: 10px">Attributes</button>
                        <input type="hidden" name="spareparts[${rowId}][attributes]" class="attributes-input" value="${data.attributes}" />
                    </td>
                    <td>
                        <select class="form-select" name="spareparts[${rowId}][uom]">
                            <option value="">Select</option>
                            <option value="KG" ${data.uom == 'KG' ? 'selected' : ''}>KG</option>
                            <option value="PCS" ${data.uom == 'PCS' ? 'selected' : ''}>PCS</option>
                            <option value="BOX" ${data.uom == 'BOX' ? 'selected' : ''}>BOX</option>
                            <option value="UNIT" ${data.uom == 'UNIT' ? 'selected' : ''}>UNIT</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="spareparts[${rowId}][qty]" value="1" min="0" step="0.01" class="form-control mw-100" value="${data.qty}" />
                    </td>
                </tr>`;
            }

            let attributeRowRef = null;
            $(document).on('click', '.open-attribute-modal', function () {
                attributeRowRef = $(this).closest('tr');

                const rowId = attributeRowRef.data('row-id');

                $('.attribute-select').val('');

                // Load existing attributes if any
                const input = $(`input[name="spareparts[${rowId}][attributes]"]`);
                if (input.length && input.val()) {
                    try {
                        const attributes = JSON.parse(input.val());
                        Object.entries(attributes).forEach(([key, value]) => {
                            $(`.attribute-select[data-attribute="${key}"]`).val(value);
                        });
                    } catch (e) {
                        console.error('Invalid JSON in attributes input:', e);
                    }
                }


                $('#attribute').modal('show');

            });

            $('#attribute').on('hide.bs.modal', function (e) {
                if ($(document.activeElement).hasClass('btn-primary')) {
                    if (!attributeRowRef) return;

                    const attributes = {};

                    // Collect all selected attributes
                    $('.attribute-select').each(function () {
                        const attrName = $(this).data('attribute');
                        const attrValue = $(this).val();

                        if (attrValue) {
                            attributes[attrName] = attrValue;
                        }
                    });

                    const rowId = attributeRowRef.data('row-id');

                    // Store as JSON in the hidden input
                    const input = $(`input[name="spareparts[${rowId}][attributes]"]`);
                    input.val(JSON.stringify(attributes));

                    // Display selected attributes in the same row (column 4)
                    let badgeHtml = '';
                    Object.entries(attributes).forEach(([key, value]) => {
                        badgeHtml += `<span class="badge rounded-pill badge-light-primary">${key}: ${value}</span> `;
                    });

                    const cellHtml = `
                        ${badgeHtml}
                        <button type="button" data-row-id="${rowId}" class="btn p-25 btn-sm btn-outline-primary open-attribute-modal" style="font-size: 10px">Attributes </button>
                        <input type="hidden" name="spareparts[${rowId}][attributes]" class="attributes-input" value='${JSON.stringify(attributes)}' />
                    `;

                    $(`tr[data-row-id="${rowId}"]`).find('td').eq(3).html(cellHtml);

                }
            });

            $(document).on('change', '.item-code-dropdown', function () {
                let selectedOption = $(this).find('option:selected');
                let itemName = selectedOption.data('name') || '';
                $(this).closest('tr').find('.item-name-input').val(itemName);

                let selectedItem = items.find(item => item.id === parseInt(selectedOption.val()));

                if (selectedItem) {
                    $('#part-detail-name').html(selectedItem.item_name);
                    $('#part-detail-hsn').html(selectedItem.hsn_id);
                    $('#part-detail-color').html(selectedItem.color ?? 'N/A');
                    $('#part-detail-size').html(selectedItem.size ?? 'N/A');
                    $('#part-detail-uom').html(selectedItem.uom_id ?? 'N/A');
                    $('#part-detail-qty').html(selectedItem.qty ?? 'N/A');
                    $('#part-detail-remarks').html(selectedItem.item_remark ?? 'N/A');
                }
            });

            $(document).on('click', '.open-attribute-modal', function () {
                const rowId = $(this).data('row-id');
                $('#attribute-row-id').val(rowId);

                // Reset all attribute selects
                $('.attribute-select').val('');

                // Load existing attributes if any
                const attributesInput = $(`input[name="spareparts[${rowId}][attributes]"]`);
                if (attributesInput.length && attributesInput.val()) {
                    try {
                        const attributes = JSON.parse(attributesInput.val());

                        // Set values in the modal
                        for (const [key, value] of Object.entries(attributes)) {
                            $(`.attribute-select[data-attribute="${key}"]`).val(value);
                        }
                    } catch (e) {
                        console.error('Error parsing attributes:', e);
                    }
                }

                $('#attribute').modal('show');
            });

            $('#save-attributes').on('click', function () {
                const rowId = $('#attribute-row-id').val();
                if (!rowId) return;

                const attributes = {};

                // Collect all selected attributes
                $('.attribute-select').each(function () {
                    const attrName = $(this).data('attribute');
                    const attrValue = $(this).val();

                    if (attrValue) {
                        attributes[attrName] = attrValue;
                    }
                });

                // Store as JSON in the hidden input
                $(`input[name="spareparts[${rowId}][attributes]"]`).val(JSON.stringify(attributes));

                // Show a visual indicator that attributes are set
                const attributeCount = Object.keys(attributes).length;
                const attributeBtn = $(`.open-attribute-modal[data-row-id="${rowId}"]`);

                if (attributeCount > 0) {
                    attributeBtn.removeClass('btn-outline-secondary').addClass('btn-outline-primary');
                    attributeBtn.html(`Attributes (${attributeCount})`);
                } else {
                    attributeBtn.removeClass('btn-outline-primary').addClass('btn-outline-secondary');
                    attributeBtn.html('Attributes');
                }

                // Close the modal
                $('#attribute').modal('hide');
            });


            // Add row based on active tab
            $('#addRowBtn').on('click', function (e) {
                e.preventDefault();
                var activeTab = $('.tab-pane.active').attr('id');
                if (activeTab === 'Maintenance') {
                    $('#maintenanceRows').append(getMaintenanceRow());
                } else if (activeTab === 'Spare') {
                    $('#spareRows').append(getSparePartRow());
                }
            });

            // Delete selected rows from active tab
            $('#deleteRowBtn').on('click', function (e) {
                e.preventDefault();

                let activeTab = $('.tab-pane.active').attr('id');
                let checkboxes, table;

                if (activeTab === 'Maintenance') {
                    checkboxes = $('#maintenanceRows').find('input.row-checkbox:checked');
                    table = $('#maintenanceRows').closest('table');
                } else if (activeTab === 'Spare') {
                    checkboxes = $('#spareRows').find('input.row-checkbox:checked');
                    table = $('#spareRows').closest('table');
                }

                if (checkboxes.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No rows selected',
                        text: 'Please select at least one row to delete.',
                    });
                    return;
                }

                checkboxes.closest('tr').remove();
                table.find('thead input[type="checkbox"]').prop('checked', false);
            });

            // (Optional) "Select All" checkbox per table
            $('.myrequesttablecbox thead input[type="checkbox"]').on('change', function () {
                var tbody = $(this).closest('table').find('tbody');
                var checked = $(this).is(':checked');
                tbody.find('input.row-checkbox').prop('checked', checked);
            });
        });

            function submitForm(status) {
                $('#status').val(status);

                let isValid = true;
                let errorMessage = '';

                // Basic Information validation
                if ($('#organization_id').val() === '') {
                    isValid = false;
                    errorMessage += 'Organization is required.<br>';
                }

                if ($('#location_id').val() === '' && isValid) {
                    isValid = false;
                    errorMessage += 'Location is required.<br>';
                }

                if ($('#category_id').val() === '' && isValid) {
                    isValid = false;
                    errorMessage += 'Category is required.<br>';
                }

                if ($('input[name="name"]').val() === '' && isValid) {
                    isValid = false;
                    errorMessage += 'Name is required.<br>';
                }

                // Validate maintenance rows if any exist
                $('#maintenanceRows tr').each(function () {
                    const typeSelect = $(this).find('select[name^="maintenance"][name$="[type]"]');
                    const frequencyInput = $(this).find('input[name^="maintenance"][name$="[frequency]"]');

                    if (typeSelect.val() !== '' || frequencyInput.val() !== '' && isValid) {
                        if (typeSelect.val() === '') {
                            isValid = false;
                            errorMessage += 'Maintenance type is required for all maintenance rows.<br>';
                        }

                        if (frequencyInput.val() === '') {
                            isValid = false;
                            errorMessage += 'Frequency is required for all maintenance rows.<br>';
                        }
                    }
                });

                // Validate spare parts rows if any exist
                $('#spareRows tr').each(function () {
                    const itemCodeSelect = $(this).find('select[name^="spareparts"][name$="[item_code]"]');
                    const itemNameInput = $(this).find('input[name^="spareparts"][name$="[item_name]"]');
                    const uomInput = $(this).find('input[name^="spareparts"][name$="[uom]"]');
                    const qtyInput = $(this).find('input[name^="spareparts"][name$="[qty]"]');

                    if (itemCodeSelect.val() !== '' || itemNameInput.val() !== '' && isValid) {
                        if (itemCodeSelect.val() === '') {
                            isValid = false;
                            errorMessage += 'Item code is required for all spare part rows.<br>';
                        }

                        if (itemNameInput.val() === '') {
                            isValid = false;
                            errorMessage += 'Item name is required for all spare part rows.<br>';
                        }

                        if (uomInput.val() === '') {
                            isValid = false;
                            errorMessage += 'UOM is required for all spare part rows.<br>';
                        }

                        if (qtyInput.val() === '' || parseFloat(qtyInput.val()) < 0) {
                            isValid = false;
                            errorMessage += 'Valid quantity is required for all spare part rows.<br>';
                        }
                    }
                });

                if (!isValid) {
                    Swal.fire({
                        title: 'Validation Error',
                        html: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }

                // If draft, confirm with user
                if (status === 'draft') {
                    Swal.fire({
                        title: 'Save as Draft',
                        text: 'Are you sure you want to save this equipment as draft?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, save it!',
                        cancelButtonText: 'No, cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#submitButton').click();
                        }
                    });
                } else {
                    // If submitting, confirm with user
                    Swal.fire({
                        title: 'Submit Equipment',
                        text: 'Are you sure you want to submit this equipment?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, submit it!',
                        cancelButtonText: 'No, cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#submitButton').click();
                        }
                    });
                }
            }

            function check_amount() {

                $('#draft').attr('disabled', true);
                $('#submitted').attr('disabled', true);
                $('.preloader').show();
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

            function setApproval() {
            document.getElementById('action_type').value = "approve";
            $('#myModalLabel17').text('Approve Voucher');

        }

        function setReject() {
            document.getElementById('action_type').value = "reject";
            $('#myModalLabel17').text('Reject Voucher');

        }
         $(document).on('click', '#amendmentSubmit', (e) => {
            let actionUrl = "{{ route('equipment.amendment', $equipment->id) }}";
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success'
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error'
                        });
                    }
                    location.reload();
                });
            });
        });

   
        function getPopupChecklistData(equipId, mainTypeId){
            if(equipId && mainTypeId){
                $.ajax({
                    url: "{{ route('equipment.popup-checklist-data') }}",
                    type: "post",
                    data: {
                        equipment_id: equipId,
                        id: mainTypeId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {


                        $("#viewCheckListPortion").empty();

                        if(response.success && response.data.length > 0){
                            // parse maintenanceChecklist for easier lookup
                            let selectedDetails = [];
                            if(response.maintenanceChecklist && response.maintenanceChecklist.length > 0){
                                response.maintenanceChecklist.forEach(mc => {
                                    try {
                                        let parsed = JSON.parse(mc.checklist_detail);
                                        if(parsed && parsed.checklist_detail_id){
                                            selectedDetails.push(parsed.checklist_detail_id.toString());
                                        }
                                    } catch(e){
                                        console.error("Invalid checklist_detail JSON", e);
                                    }
                                });
                            }



                            response.data.forEach(function(checklist){
                                let portionId = `portion_${checklist.id}`;
                                let tableRows = "";

                                if(checklist.details && checklist.details.length > 0){
                                    checklist.details.forEach(function(detail){
                                        let isChecked = selectedDetails.includes(detail.id.toString()) ? "checked" : "";

                                        tableRows += `
                                            <tr>
                                                <td>
                                                    <div class="form-check form-check-primary custom-checkbox">
                                                        <input type="checkbox" 
                                                            class="form-check-input" 
                                                            value="${detail.id}" ${isChecked}>
                                                    </div>
                                                </td>
                                                <td>${detail.name || ''}</td>
                                                <td>${detail.description || ''}</td>
                                                <td><span class="badge rounded-pill badge-light-info">${detail.data_type || ''}</span></td>
                                            </tr>
                                        `;
                                    });
                                } else {
                                    tableRows = `<tr><td colspan="4" class="text-muted text-center">No details found</td></tr>`;
                                }

                                let portionHtml = `
                                    <div class="row checklist-portion mb-2" id="${portionId}">
                                        <div class="col-md-12">
                                            <h5 class="mb-1">${checklist.name}</h5>
                                            <div class="table-responsive">
                                                <table class="mt-1 table table-striped border">
                                                    <thead>
                                                        <tr>
                                                            <th width="40px">
                                                                <div class="form-check form-check-primary custom-checkbox">
                                                                    <input type="checkbox" class="form-check-input select-all">
                                                                </div>
                                                            </th>
                                                            <th>Name</th>
                                                            <th>Description</th>
                                                            <th>Type</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        ${tableRows}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                `;

                                $("#viewCheckListPortion").append(portionHtml);
                            });
                        } else {
                            $("#viewCheckListPortion").html(`<p class="text-center text-danger">No checklist data found</p>`);
                        }

                        $("#viewChecklist").modal("show");
                    }
                });
            }
        }





        
       
       
        

    </script>
@endsection
