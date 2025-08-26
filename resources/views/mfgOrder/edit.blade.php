@extends('layouts.app')
@section('content')
@use(App\Helpers\ConstantHelper)
<form class="ajax-input-form" data-module="mo" method="POST" action="{{ route('mo.update', $bom->id) }}" data-redirect="{{ route('mo.index') }}" enctype='multipart/form-data'>
    @csrf
<input type="hidden" name="so_item_ids" id="so_item_ids">
<div class="app-content content ">
   <div class="content-overlay"></div>
   <div class="header-navbar-shadow"></div>
   <div class="content-wrapper container-xxl p-0">
      <div class="content-header pocreate-sticky">
         <div class="row">
            @include('layouts.partials.breadcrumb-add-edit', [
             'title' => 'Manufacturing order',
             'menu' => 'Home',
             'menu_url' => url('home'),
             'sub_menu' => 'Edit'
             ])
            <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
               <div class="form-group breadcrumb-right">
                  <input type="hidden" name="document_status" id="document_status">
                  <button type="button" onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
                    @if($buttons['draft'])
                        <button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button" name="action" value="draft"><i data-feather='save'></i> Save as Draft</button>
                    @endif
                    @if(!intval(request('amendment') ?? 0) && $bom->document_status != ConstantHelper::DRAFT && $bom->document_status != ConstantHelper::SUBMITTED)
                    <div class="btn-group mb-50 mb-sm-0">
                        <button type="button" class="btn btn-dark btn-sm dropdown-toggle waves-effect waves-float waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer"><polyline points="6 9 6 2 18 2 18 9"></polyline>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <rect x="6" y="14" width="12" height="8"></rect></svg> Print
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('mo.generate-pdf', ['id' => $bom->id, 'pattern' => 'portrait']) }}" target="_blank">
                                    Portrait
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('mo.generate-pdf', ['id' => $bom->id, 'pattern' => 'landscape']) }}" target="_blank">
                                    Landscape
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif
                    @if($buttons['submit'])
                        <button type="submit" class="btn btn-primary btn-sm submit-button" name="action" value="submitted"><i data-feather="check-circle"></i> Submit</button>
                    @endif
                    @if($buttons['approve'])
                        <button type="button" id="reject-button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg> Reject</button>
                        <button type="button" class="btn btn-primary btn-sm" id="approved-button" name="action" value="approved"><i data-feather="check-circle"></i> Approve</button>
                    @endif
                    @if($buttons['amend'] && intval(request('amendment') ?? 0))
                        <button type="button" class="btn btn-primary btn-sm" id="amendmentBtn"><i data-feather="check-circle"></i> Submit</button>
                    @else
                        @if($buttons['amend'])
                        <button type="button" data-bs-toggle="modal" data-bs-target="#amendmentconfirm" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i> Amendment</button>
                        @endif
                    @endif
                    @if($buttons['revoke'])
                        <button id = "revokeButton" type="button" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='rotate-ccw'></i> Revoke</button>
                    @endif
                    {{-- @if($buttons['close'])
                    <button id="closeButton" type="button" class="btn btn-primary btn-sm mb-50 mb-sm-0">
                        <i data-feather="check-square"></i> Close
                    </button>
                    @endif   --}}

                    @if($buttons['post'])
                        <button id="postButton" type = "button" class="btn btn-warning btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Post</button>
                    @endif

                    @if($buttons['voucher'])
                        <button id="postButton" type = "button" class="btn btn-warning btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Voucher</button>
                    @endif
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
                        <div class="border-bottom mb-2 pb-25">
                            <div class="row">
                                 <div class="col-md-6">
                                     <div class="newheader ">
                                         <h4 class="card-title text-theme">Basic Information</h4>
                                         <p class="card-text">Fill the details</p>
                                     </div>
                                 </div>
                                 <div class="col-md-6 text-sm-end">
                                     <span class="badge rounded-pill badge-light-secondary forminnerstatus">

                                         Status : <span class="{{$docStatusClass}}">{{$bom->display_status}}</span>
                                     </span>
                                 </div>
                             </div>
                         </div>
                        <div class="row">
                           <div class="col-md-8">
                              <div class="basic-information">
                                 <div class="row align-items-center mb-1">
                                    <div class="col-md-3">
                                        <label class="form-label">Series <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-5">
                                       <select class="form-select" id="book_id" name="book_id" disabled>
                                          @foreach($books as $book)
                                             <option value="{{$book->id}}" {{$bom->book_id == $book->id ? 'selected' : ''}}>{{ucfirst($book->book_code)}}</option>
                                          @endforeach
                                       </select>
                                       <input type="hidden" name="book_code" value="{{$bom->book_code}}" id="book_code">
                                    </div>
                                 </div>

                                <div class="row align-items-center mb-1">
                                    <div class="col-md-3">
                                        <label class="form-label">Document No. <span class="text-danger">*</span></label>
                                    </div>

                                    <div class="col-md-5">
                                        <input type="text" value="{{$bom->document_number}}" name="document_number" class="form-control" id="document_number">
                                    </div>
                                 </div>
                                 <div class="row align-items-center mb-1">
                                     <div class="col-md-3">
                                         <label class="form-label">Document Date <span class="text-danger">*</span></label>
                                     </div>
                                     <div class="col-md-5">
                                         <input disabled type="date" class="form-control" value="{{$bom->document_date ?? date('Y-m-d')}}" name="document_date">
                                     </div>
                                 </div>
                                 <div class="row align-items-center mb-1">
                                    <div class="col-md-3">
                                        <label class="form-label">Location <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-5">
                                        <select disabled class="form-select" id="store_id" name="store_id">
                                        @foreach($locations as $location)
                                            <option value="{{$location->id}}" {{$bom->store_id == $location->id ? 'selected' : ''}}>{{ $location?->store_name }}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-1 {{$bom->sub_store_id ? '' : 'd-none'}}" id="sub_store_div">
                                    <div class="col-md-3">
                                        <label class="form-label">Sub Location <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-5">
                                        <select disabled class="form-select" id="sub_store_id" name="sub_store_id">
                                            <option value="{{$bom->sub_store_id}}">{{$bom?->sub_store?->name}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-1" id="station_div">
                                    <div class="col-md-3">
                                        <label class="form-label">Station <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-5">
                                        <select disabled class="form-select" id="station_id" name="station_id">
                                        <option value="">Select</option>
                                        @foreach($stations as $station)
                                            <option value="{{$station->id}}" {{$bom->station_id == $station->id ? 'selected' : ''}}>{{ $station?->name }}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-1">
                                    <div class="col-md-3">
                                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-5">
                                        <input disabled type="text" placeholder="Select" value="{{$bom?->item?->item_name}} ({{$bom->item->item_code}})" class="form-control mw-100 ledgerselecct" id="item_code" name="item_code" />
                                        <input type="hidden" value="{{$bom->item_id}}" placeholder="Select" class="form-control mw-100 ledgerselecct" id="item_id" name="item_id" />
                                    </select>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-1 d-none" id="machineDiv">
                                    <div class="col-md-3">
                                        <label class="form-label">Machine</label>
                                    </div>
                                    <div class="col-md-5">
                                        <select class="form-select" id="main_machine_id" name="main_machine_id">
                                            <option value="">Select Machine</option>
                                            @foreach($machines as $machine)
                                                <option value="{{$machine->id}}" {{$bom->machine_id == $machine->id ? 'selected' : ''}}>{{ $machine?->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-1 d-none" id="reference_from">
                                    <div class="col-md-3">
                                        <label class="form-label">Reference from</label>
                                    </div>
                                    <div class="col-md-5 action-button">
                                        <button type="button" @if(!$isEdit) disabled @endif class="btn btn-outline-primary btn-sm mb-0 prSelect"><i data-feather="plus-square"></i> Production Work Order</button>
                                    </div>
                                </div>
                              </div>
                           </div>
                           {{-- History Code --}}
                           @include('partials.approval-history', ['document_status' => $bom->document_status, 'revision_number' => $revision_number])
                        </div>
                     </div>
                  </div>
               </div>

                <div class="col-md-12">
                    {{-- Append Attribute here  --}}

                    <div class="card">
                        <div class="card-body customernewsection-form">
                            <div class="border-bottom mb-2 pb-25" id="componentSection">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="newheader ">
                                        <ul class="nav nav-tabs" id="productTabs" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active fs-5" id="product-details-tab" data-bs-toggle="tab" data-bs-target="#product-details" type="button" role="tab" aria-controls="product-details" aria-selected="false">
                                                    Orders
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link fs-5" id="raw-materials-tab" data-bs-toggle="tab" data-bs-target="#raw-materials" type="button" role="tab" aria-controls="raw-materials" aria-selected="false">
                                                    Consumption
                                                </button>
                                            </li>
                                            @if($productionBomInstructions?->count())
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link fs-5" id="instructions-items-tab" data-bs-toggle="tab" data-bs-target="#instructions-items" type="button" role="tab" aria-controls="instructions-items" aria-selected="false">
                                                    Instructions
                                                </button>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6 text-sm-end">
                                    @if($bom->document_status == ConstantHelper::DRAFT)
                                        <a href="javascript:;" class="btn btn-sm btn-outline-danger me-50" id="deleteBtn">
                                            <i data-feather="x-circle"></i> Delete</a>
                                    @endif
                                    {{-- <a href="javascript:;" id="addNewItemBtn" class="btn btn-sm btn-outline-primary">
                                    <i data-feather="plus"></i> Add Items</a> --}}
                                </div>
                            </div>
                            </div>
                            <div class="tab-content mt-1" id="productTabsContent">
                                <div class="tab-pane fade show active" id="product-details" role="tabpanel" aria-labelledby="product-details-tab">
                                    <div class="table-responsive pomrnheadtffotsticky">
                                    <table id="itemTable" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"
                                    data-json-key="components_json"
                                    data-row-selector="tr[id^='row_']">
                                        <thead>
                                            <tr>
                                                <th>
                                                <div class="form-check form-check-primary custom-checkbox">
                                                    <input type="checkbox" class="form-check-input" id="Email">
                                                    <label class="form-check-label" for="Email"></label>
                                                </div>
                                                </th>
                                                <th width="200px">Series</th>
                                                <th width="150px">Doc No.</th>
                                                <th width="100px">Doc Date</th>
                                                <th width="100px">Location</th>
                                                <th width="150px">Product Code</th>
                                                <th width="300px">Product Name</th>
                                                <th max-width="180px">Attributes</th>
                                                <th >UOM</th>
                                                <th class="text-end">Qty</th>
                                                <th width="200px">Customer</th>
                                                <th width="150px">Order No.</th>
                                                <th width="150px" id="machine_name" class="d-none">Machine</th>
                                                <th width="150px" id="sheets" class="d-none">Sheets</th>
                                                {{-- <th>Order Date</th> --}}
                                        <th width="50px">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="mrntableselectexcel">
                                            @include('mfgOrder.partials.item-row-edit')
                                        </tbody>
                                        <tfoot>
                                            <tr valign="top">
                                                <td colspan="13" id="detailTableFooter">
                                                <table class="table border" id="itemDetailTable">
                                                    <tr>
                                                        <td class="p-0">
                                                            <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Item Details</strong></h6>
                                                        </td>
                                                    </tr>
                                                    <tr class="item_detail_row">

                                                    </tr>
                                                    <tr class="item_detail_row">

                                                    </tr>
                                                </table>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="raw-materials" role="tabpanel" aria-labelledby="raw-materials-tab">
                                    <div class="table-responsive pomrnheadtffotsticky">
                                        <table id="itemTable2" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"
                                        data-json-key="component_json"
                                        data-row-selector="tr[id^='row_']">
                                            <thead>
                                                <tr>
                                                    <th>Order No.</th>
                                                    <th>Item Code</th>
                                                    <th>Item Name</th>
                                                    <th>Item Type</th>
                                                    <th max-width="180px">Attributes</th>
                                                    <th>UOM</th>
                                                    <th class="text-end">Required Qty</th>
                                                    <th class="text-end">Consumed Qty</th>
                                                    @if(in_array($bom->document_status,[ConstantHelper::CLOSED, ConstantHelper::POSTED]))
                                                        <th class="text-end">Rate</th>
                                                        <th class="text-end">Value</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody class="mrntableselectexcel">
                                                @include('mfgOrder.partials.mo-item-row')
                                            </tbody>
                                            <tfoot>
                                                <tr valign="top">
                                                    <td colspan="10">
                                                    <table class="table border" id="itemDetailTable2">
                                                        <tr>
                                                            <td class="p-0">
                                                                <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Item Details</strong></h6>
                                                            </td>
                                                        </tr>
                                                        <tr class="item_detail_row">

                                                        </tr>
                                                        <tr class="item_detail_row">

                                                        </tr>
                                                    </table>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                        </div>
                                </div>
                                @if($productionBomInstructions?->count())
                                <div class="tab-pane fade" id="instructions-items" role="tabpanel" aria-labelledby="instructions-items-tab">
                                    <div class="table-responsive pomrnheadtffotsticky">
                                    <table id="itemTable4" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"
                                        data-json-key="instruction_json"
                                        data-row-selector="tr[id^='row_']">
                                        <thead>
                                            <tr>
                                                {{-- <th>
                                                    <div class="form-check form-check-primary custom-checkbox">
                                                        <input type="checkbox" class="form-check-input" id="Email">
                                                        <label class="form-check-label" for="Email"></label>
                                                    </div>
                                                </th> --}}
                                                <th width="160px">Station</th>
                                                @if(isset($sectionRequired) && $sectionRequired)
                                                    <th width="160px" id="section_required2">Section</th>
                                                @endif
                                                @if(isset($subSectionRequired) && $subSectionRequired)
                                                    <th width="160px" id="sub_section_required2">Sub Section</th>
                                                @endif
                                                <th>Instructions</th>
                                                <th class="text-center align-middle" width="100px">Attachment</th>
                                            </tr>
                                        </thead>
                                        <tbody class="mrntableselectexcel">
                                            @include('mfgOrder.partials.instruction-row')
                                        </tbody>
                                    </table>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Show MO Product Components --}}
                    <div id="componentDetails" style="display: none;">
                        {{-- ref: mo-product-components blade --}}
                    </div>

                    <div class="card">
                        <div class="card-body customernewsection-form">
                            <div class="border-bottom mb-2 pb-25" id="componentSection">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="newheader ">
                                        <h4 class="card-title text-theme">Remarks</h4>
                                        {{-- <p class="card-text">Fill the details</p> --}}
                                    </div>
                                </div>
                            </div>
                            </div>
                            <div class="row mt-2">
                            <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-4">
                                    <div class="mb-1">
                                        <label class="form-label">Upload Document</label>
                                        <input type="file" name="attachment[]" class="form-control" onchange = "addFiles(this,'main_bom_file_preview')" multiple>
                                        <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                                    </div>
                                </div>
                                <div class = "col-md-6" style = "margin-top:19px;">
                                    <div class = "row" id = "main_bom_file_preview">
                                    </div>
                                </div>
                                    </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label">Final Remarks</label>
                                    <textarea maxlength="250" name="remarks" type="text" rows="4" class="form-control" placeholder="Enter Remarks here...">{!! $bom->remarks !!}</textarea>
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
   </div>
</div>
@include('mfgOrder.partials.amendment-modal', ['id' => $bom->id])
@include('mfgOrder.partials.close-modal', ['id' => $bom->id])
</form>

{{-- Attribute popup --}}
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
                  </tbody>
               </table>
            </div>
         </div>
         <div class="modal-footer justify-content-center">
            <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button>
            <button type="button" {{-- data-bs-dismiss="modal" --}} class="btn btn-primary submit_attribute">Select</button>
         </div>
      </div>
   </div>
</div>

{{-- Item Remark Modal --}}
<div class="modal fade" id="itemRemarkModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" >
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
                <h1 class="text-center mb-1" id="shareProjectTitle">Remarks</h1>
                {{-- <p class="text-center">Enter the details below.</p> --}}
                <div class="row mt-2">
                    <div class="col-md-12 mb-1">
                        <label class="form-label">Remarks <span class="text-danger">*</span></label>
                        <input type="hidden" name="row_count" id="row_count">
                        <textarea class="form-control" placeholder="Enter Remarks"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button>
                <button type="button" class="btn btn-primary itemRemarkSubmit">Submit</button>
            </div>
        </div>
    </div>
</div>

@include('mfgOrder.partials.pwo-modal')
@include('mfgOrder.partials.post-voucher')
@include('mfgOrder.partials.approve-modal', ['id' => $bom->id])

{{-- Delete component modal --}}
<div class="modal fade text-start alertbackdropdisabled" id="deleteComponentModal" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog">
       <div class="modal-content">
          <div class="modal-header p-0 bg-transparent">
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body alertmsg text-center warning">
            <i data-feather='alert-circle'></i>
            <h2>Are you sure?</h2>
            <p>Are you sure you want to delete selected <strong>Components</strong>?</p>
            <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
            <button type="button" id="deleteConfirm" class="btn btn-primary" >Confirm</button>
          </div>
       </div>
    </div>
 </div>

@endsection
@section('scripts')
<script>
    let getMachineDetailUrl = "{{route('mo.get.machine.detail')}}";
    @if($bom->machine_id)
        $("#machine_name").removeClass('d-none');
        $("#sheets").removeClass('d-none');
        $("#detailTableFooter").attr("colspan", "15");
        $("#machineDiv").removeClass('d-none');
        $
    @else
        $("#detailTableFooter").attr("colspan", "13");
        $("#machine_name").addClass('d-none');
        $("#sheets").addClass('d-none');
        $("#machineDiv").addClass('d-none');
    @endif
</script>
<script type="text/javascript" src="{{asset('assets/js/modules/common-attr-ui.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/modules/mo.js')}}"></script>
<script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
<script type="text/javascript">

/*Clear local storage*/
setTimeout(() => {
    localStorage.removeItem('deletedPiItemIds');
},0);

@if($buttons['amend'] && intval(request('amendment') ?? 0))

@else
   @if($bom->document_status != 'draft' && $bom->document_status != 'rejected' && $bom->document_status != 'closed' && $bom->document_status != 'posted')
   $(':input').prop('readonly', true);
   $('select').not('.amendmentselect select').prop('disabled', true);
   $('[name*="produced_qty"]').prop('disabled', false);
   $('[name*="produced_qty"]').prop('readonly', false);
   $("#deleteBtn").remove();
   $("#addNewItemBtn").remove();
   $(document).on('show.bs.modal', function (e) {
        if(e.target.id != 'approveModal') {
            if(e.target.id == 'closeModal') {
                $(e.target).find(':input').prop('readonly', false);
                $(e.target).find('select').prop('readonly', false);
            } else {
                if(e.target.id != 'postvoucher') {
                    $(e.target).find('.modal-footer').remove();
                    $('select').not('.amendmentselect select').prop('disabled', true);
                }
            }
        }
        if(e.target.id == 'approveModal') {
           $(e.target).find(':input').prop('readonly', false);
           $(e.target).find('select').prop('readonly', false);
        }

        $('.add-contactpeontxt').remove();
        let text = $(e.target).find('thead tr:first th:last').text();
        if(text.includes("Action")){
           $(e.target).find('thead tr').each(function() {
               $(this).find('th:last').remove();
           });
           $(e.target).find('tbody tr').each(function() {
               $(this).find('td:last').remove();
           });
        }
   });
   @endif
@endif

$(function(){
    initializeAutocomplete2(".comp_item_code");
    initializeAutocompleteCustomer("[name*='[customer_code]']");
   // For product code
    function initializeAutocomplete1(selector, type) {
            $(selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type:'header_item'
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: `${item.item_name} (${item.item_code})`,
                                    code: item.item_code || '',
                                    item_id: item.id
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching customer data:', xhr.responseText);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    var $input = $(this);
                    var itemCode = ui.item.code;
                    var itemName = ui.item.value;
                    var itemId = ui.item.item_id;
                    $input.attr('data-name', itemName);
                    $input.attr('data-code', itemCode);
                    $input.val(itemCode);
                    $("#item_id").val(itemId);
                    // itemCodeChange(itemId);
                    checkBomInhouse();
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#item_id").val('');
                        // $('#itemId').val('');
                        $(this).attr('data-name', '');
                        $(this).attr('data-code', '');
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $("#item_id").val('');
                    $(this).autocomplete("search", "");
                }
            });
    }

    initializeAutocomplete1("#item_code");

    function checkBomInhouse() {
        let itemId = $("#item_id").val();
        let actionUrl = '{{route("mo.check.bom.inhouse")}}'+'?item_id='+itemId;
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                if(!data.data.is_bom) {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || '',
                        icon: 'error',
                    });
                    $("#item_code").attr('data-name', '');
                    $("#item_code").attr('data-code', '');
                    $("#item_code").val('');
                    $("#item_id").val('');
                }
            });
        });
    };

    $(document).on('change','#book_id',(e) => {
      let bookId = e.target.value;
      if (bookId) {
         getDocNumberByBookId(bookId);
      } else {
         $("#document_number").val('');
         $("#book_id").val('');
         $("#document_number").attr('readonly', false);
      }
    });

    if($("#book_id").val()) {
        setTimeout(() => {
            getDocNumberByBookId($("#book_id").val());
        },0);
    }

    function getDocNumberByBookId(bookId) {
      let document_date = $("[name='document_date']").val();
      let actionUrl = '{{route("book.get.doc_no_and_parameters")}}'+'?book_id='+bookId+'&document_date='+document_date;
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                if (data.status == 200) {
                  if(data.data.doc.type == 'Manually') {
                     $("#document_number").attr('readonly', false);
                  } else {
                     $("#document_number").attr('readonly', true);
                  }
                   const parameters = data.data.parameters;
                  setServiceParameters(parameters);
                }
                if(data.status == 404) {
                    $("#book_code").val('');
                    $("#document_number").val('');
                    const docDateInput = $("[name='document_date']");
                    docDateInput.removeAttr('min');
                    docDateInput.removeAttr('max');
                    // docDateInput.val(new Date().toISOString().split('T')[0]);
                    alert(data.message);
                }
            });
        });
    }

    /*Set Service Parameter*/
   function setServiceParameters(parameters) {
       /*Date Validation*/
       const docDateInput = $("[name='document_date']");
       let isFeature = false;
       let isPast = false;
       if (parameters.future_date_allowed && parameters.future_date_allowed.includes('yes')) {
           let futureDate = new Date();
           futureDate.setDate(futureDate.getDate() /*+ (parameters.future_date_days || 1)*/);
        //    docDateInput.val(futureDate.toISOString().split('T')[0]);
           docDateInput.attr("min", new Date().toISOString().split('T')[0]);
           isFeature = true;
       } else {
           isFeature = false;
           docDateInput.attr("max", new Date().toISOString().split('T')[0]);
       }
       if (parameters.back_date_allowed && parameters.back_date_allowed.includes('yes')) {
           let backDate = new Date();
           backDate.setDate(backDate.getDate() /*- (parameters.back_date_days || 1)*/);
        //    docDateInput.val(backDate.toISOString().split('T')[0]);
           // docDateInput.attr("max", "");
           isPast = true;
       } else {
           isPast = false;
           docDateInput.attr("min", new Date().toISOString().split('T')[0]);
       }
       /*Date Validation*/
       if(isFeature && isPast) {
           docDateInput.removeAttr('min');
           docDateInput.removeAttr('max');
       }
       if (parameters.station_wise_consumption && parameters.station_wise_consumption.includes('yes')) {
            $("#station_div").removeClass('d-none');
        } else {
           $("#station_div").addClass('d-none');
       }
       let reference_from_service = parameters.reference_from_service;
        if(reference_from_service.length) {
            let c_bom = '{{ConstantHelper::PWO_SERVICE_ALIAS}}';
            if(reference_from_service.includes(c_bom)) {
                $("#reference_from").removeClass('d-none');
            } else {
                $("#reference_from").addClass('d-none');
            }
            if(reference_from_service.includes('d')) {
                $("#addNewItemBtn").removeClass('d-none');
            } else {
                $("#addNewItemBtn").addClass('d-none');
            }
        } else {
            // Swal.fire({
            //     title: 'Error!',
            //     text: "Please update first reference from service param.",
            //     icon: 'error',
            // });
            // setTimeout(() => {
            //     // location.href = "{{route('mo.index')}}";
            // },1500);
        }
   }

    function itemCodeChange(itemId) {
        let actionUrl = '{{route("mo.item.code")}}'+'?item_id='+itemId;
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                if (data.status == 200) {
                  let item_name = data.data.item?.item_name || '';
                  let item_id = data.data.item?.id || '';
                  let uom_id = data.data.item?.uom_id || '';
                  let uom_name = data.data.item?.uom?.name || '';
                  $("#head_item_name").val(item_name);
                  $("#head_item_id").val(item_id);
                  $("#head_uom_id").val(uom_id);
                  $("#head_uom_name").val(uom_name);
                  $(".heaer_item").remove();
                  $(".quantity").before(data?.data?.html);
                  $('tbody.mrntableselectexcel').html(data?.data?.component_html);
                  let qty = Number($("input[name='quantity']").val()) || 1;
                  updateItemsQty(qty);
                  fetchItemDetails($("tr[id*='row_']").first());
                }
                if (data.status == 404) {
                  $("#head_item_name").val('');
                  $("#head_item_id").val('');
                  $("#head_uom_id").val('');
                  $("#head_uom_name").val('');
                  $("#item_code").val('');
                  $("#item_code").attr('data-name','');
                  $("#item_code").attr('data-code','');
                  $(".heaer_item").remove();
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                    });
                }
            });
        });
    }

//    $(document).on('blur', '#item_code', (e) => {
//        if(!e.target.value) {
//            itemCodeChange(null)
//        }
//    });

});

/*Add New Row*/
function initializeAutocomplete2(selector, type) {
    $(selector).autocomplete({
        source: function(request, response) {
            let selectedAllItemIds = [];
            $("#itemTable tbody [id*='row_']").each(function(index, item) {
                if(Number($(item).find('[name*="[item_id]"]').val())) {
                    selectedAllItemIds.push(Number($(item).find('[name*="[item_id]"]').val()));
                }
            });
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type:'header_item',
                    selectedAllItemIds : JSON.stringify(selectedAllItemIds)
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            id: item.id,
                            label: `${item.item_name} (${item.item_code})`,
                            code: item.item_code || '',
                            name: item.item_name || '',
                            uom_name:item.uom?.name,
                            uom_id:item.uom_id,
                            is_attr:item.item_attributes_count,
                        };
                    }));
                },
                error: function(xhr) {
                    console.error('Error fetching customer data:', xhr.responseText);
                }
            });
        },
        minLength: 0,
        select: function(event, ui) {
            let $input = $(this);
            let itemCode = ui.item.code;
            let itemName = ui.item.name;
            let itemId = ui.item.id;
            let uomId = ui.item.uom_id;
            let uomName = ui.item.uom_name;
            $input.val(itemCode);
            $input.closest('tr').find('[name*="[item_id]"]').val(itemId);
            $input.closest('tr').find('[name*="[item_code]"]').val(itemCode);
            $input.closest('tr').find('[name*="[item_name]"]').val(itemName);
            let uomOption = `<option value=${uomId}>${uomName}</option>`;
            $input.closest('tr').find('[name*="[uom_id]"]').empty().append(uomOption);
            $input.closest('tr').find('[name*="[attr_group_id]"]').remove();
            setTimeout(() => {
                if(ui.item.is_attr) {
                    $input.closest('tr').find('.attributeBtn').trigger('click');
                } else {
                    $input.closest('tr').find('[name*="[qty]"]').val('').focus();
                }
                }, 50);
            return false;
        },
        change: function(event, ui) {
            if (!ui.item) {
                $(this).val("");
                $(this).closest('tr').find('[name*="[item_id]"]').val("");
                $(this).closest('tr').find('[name*="[item_code]"]').val("");
            }
        }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
            $(this).closest('tr').find('[name*="[item_id]"]').val("");
            $(this).closest('tr').find('[name*="[item_code]"]').val("");
        }
    });
}

$(document).on('click','#addNewItemBtn', (e) => {
    let rowsLength = $("#itemTable > tbody > tr").length;
    /*Check last tr data shoud be required*/
    let lastRow = $('#itemTable .mrntableselectexcel tr:last');
    let lastTrObj = {
      item_id : "",
      attr_require : true,
      row_length : lastRow.length
    };
    if(lastRow.length == 0) {
      lastTrObj.attr_require = false;
      lastTrObj.item_id = "0";
    }
    if(lastRow.length > 0) {
       let item_id = lastRow.find("[name*='[item_id]']").val();
       if(lastRow.find("[name*='[attr_name]']").length) {
          var emptyElements = lastRow.find("[name*='[attr_name]']").filter(function() {
              return $(this).val().trim() === '';
          });
         attr_require = emptyElements?.length ? true : false;
       } else {
         attr_require = true;
       }
       lastTrObj = {
         item_id : item_id,
         attr_require : attr_require,
         row_length : lastRow.length
       };
       if($("tr[id*='row_']:last").find("[name*='[attr_group_id]']").length == 0 && item_id) {
          lastTrObj.attr_require = false;
        }
    }
    let componentAttr = [];
    if($("tr input[type='hidden'][name*='[attr_group_id]']").length) {
        $("tr input[type='hidden'][name*='[attr_group_id]']").each(function () {
            const nameAttr = $(this).attr("name");
            const value = $(this).val();
            const attributeIdMatch = nameAttr.match(/\[attr_group_id]\[(\d+)]/);
            const attributeId = attributeIdMatch ? attributeIdMatch[1] : null;
            componentAttr.push({
                'attr_name' : attributeId,
                'attr_value' : value
            });
        });
    }
    let actionUrl = '{{route("mo.item.row")}}'+'?count='+rowsLength+'&component_item='+JSON.stringify(lastTrObj)+'&comp_attr='+JSON.stringify(componentAttr);
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
                if (rowsLength) {
                    $("#itemTable > tbody > tr:last").after(data.data.html);
                } else {
                    $("#itemTable > tbody").html(data.data.html);
                }
                initializeAutocomplete2(".comp_item_code");
                initializeAutocompleteCustomer("[name*='[customer_code]']");
                $(".prSelect").prop('disabled',true);
            } else if(data.status == 422) {
               Swal.fire({
                    title: 'Error!',
                    text: data.message || 'An unexpected error occurred.',
                    icon: 'error',
                });
            } else {
               console.log("Someting went wrong!");
            }
        });
    });
});

function initializeAutocompleteCustomer(selector) {
    $(selector).autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type:'customer'
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            id: item.id,
                            label: `${item.company_name} (${item.customer_code})`,
                            code: item.customer_code || '',
                            name:item.display_name || item.company_name,
                        };
                    }));
                },
                error: function(xhr) {
                    console.error('Error fetching customer data:', xhr.responseText);
                }
            });
        },
        minLength: 0,
        select: function(event, ui) {
            var $input = $(this);
            var customerCode = ui.item.code;
            var customerName = ui.item.name;
            var customerId = ui.item.id;
            $input.val(customerCode);
            $input.closest('tr').find("[name*='[customer_id]']").val(customerId)
            return false;
        },
        change: function(event, ui) {
            if (!ui.item) {
                $(this).val("");
                $(this).closest('tr').find("[name*='[customer_id]']").val("");
            }
        }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
            $(this).val("");
            $(this).closest('tr').find("[name*='[customer_id]']").val("");
        }
    });
}

/*Check attrubute*/
$(document).on('click', '.attributeBtn', (e) => {
    let tr = e.target.closest('tr');
    let item_name = tr.querySelector('[name*="[item_code]"]').value;
    let item_id = tr.querySelector('[name*="[item_id]"]').value;
    let selectedAttr = [];
    const attrElements = tr.querySelectorAll('[name*="[attr_name]"]');
    if (attrElements.length > 0) {
        selectedAttr = Array.from(attrElements).map(element => element.value);
        selectedAttr = JSON.stringify(selectedAttr);
    }
    if (item_name && item_id) {
        let rowCount = e.target.getAttribute('data-row-count');
        getItemAttribute(item_id, rowCount, selectedAttr, tr);
    } else {
        alert("Please select first item name.");
    }
});

/*For comp attr*/
function getItemAttribute(itemId, rowCount, selectedAttr, tr){
    let actionUrl = '{{route("mo.item.attr")}}'+'?item_id='+itemId+`&rowCount=${rowCount}&selectedAttr=${selectedAttr}`;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
                  $("#attribute tbody").empty();
                  $("#attribute table tbody").append(data.data.html);
                  $(tr).find('td:nth-child(2)').find("[name*='[attr_name]']").remove();
                  $(tr).find('td:nth-child(2)').append(data.data.hiddenHtml);
                  if (data.data.attr) {
                     $("#attribute").modal('show');
                     $(".select2").select2();
                  }
                  qtyEnabledDisabled();
            }
        });
    });
}

/*Display item detail*/
function fetchItemDetails(currentTr) {
    let pName = $(currentTr).find("[name*='component_item_name']").val();
    let itemId = $(currentTr).find("[name*='item_id']").val();

    let pwoMappingId = $(currentTr).find("[name*='pwo_mapping_id']").val();
    let storeId = $("#store_id option:selected").val();
    let subStoreId = $("#sub_store_id option:selected").val();

    if (itemId) {
        let selectedAttr = [];
        $(currentTr).find("[name*='attr_name']").each(function(index, item) {
            if ($(item).val()) {
                selectedAttr.push($(item).val());
            }
        });

        let sectionName = $(currentTr).find("[name*='[section_name]']").val() || '';
        let subSectionName = $(currentTr).find("[name*='[sub_section_name]']").val() || '';
        let stationName = $(currentTr).find("[name*='[station_name]']").val() || '';
        let remark = $(currentTr).find("[name*='remark']").val() || '';

        // Norms
        let qty_per_unit = $(currentTr).find("[name*='[qty_per_unit]']").val() || '';
        let total_qty = $(currentTr).find("[name*='[total_qty]']").val() || '';
        let std_qty = $(currentTr).find("[name*='[std_qty]']").val() || '';

        let actionUrl = `{{route("mo.get.itemdetail")}}?item_id=${itemId}&selectedAttr=${JSON.stringify(selectedAttr)}&remark=${remark}&section_name=${sectionName}&sub_section_name=${subSectionName}&station_name=${stationName}&qty_per_unit=${qty_per_unit}&total_qty=${total_qty}&pwo_papping_id=${pwoMappingId}&store_id=${storeId}&sub_store_id=${subStoreId}&std_qty=${std_qty}`;
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                if (data.status == 200) {
                    $(".item_detail_row").remove();
                    if ($("#itemDetailTable tbody tr").length > 2) {
                        $("#itemDetailTable tbody tr").slice(-2).remove();
                    }
                    $("#itemDetailTable tbody tr:first").after(data.data.html);


                     // Show Prodcut Component Details
                    $("#componentDetails").html(data.data.mo_product_component_html);
                    $("#componentDetails").show();
                }
            });
        });
    }
}

function fetchItemDetails2(currentTr) {
    let itemId = $(currentTr).find("[name*='[item_id_2]']").val();
    let moItemId = $(currentTr).find("[name*='[mo_item_id_2]']").val();
    if (itemId) {
        let selectedAttr = [];
        $(currentTr).find("[name*='attr_name_2']").each(function(index, item) {
            if ($(item).val()) {
                selectedAttr.push($(item).val());
            }
        });
        let actionUrl = `{{route("mo.get.itemdetail2")}}?item_id=${itemId}&selectedAttr=${JSON.stringify(selectedAttr)}&mo_item_id=${moItemId}`;
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                if (data.status == 200) {
                    $(".item_detail_row2").remove();
                    if ($("#itemDetailTable2 tbody tr").length > 2) {
                        $("#itemDetailTable2 tbody tr").slice(-2).remove();
                    }
                    $("#itemDetailTable2 tbody tr:first").after(data.data.html);
                }
            });
        });
    }
}

fetchItemDetails2($("#itemTable2 tbody tr:first"));

// Attach event listener
$(document).on('input change focus', '#itemTable tr input', function(e) {
    let currentTr = e.target.closest('tr');
    fetchItemDetails(currentTr);
});

// Attach event listener
$(document).on('click', '#itemTable2 tr, #itemTable2 td', function(e) {
    let currentTr = e.target.closest('tr');
    fetchItemDetails2(currentTr);
});

$(document).on('click', '.submit_attribute', (e) => {
    $("#attribute").modal('hide');
});

window.onload = function () {
    localStorage.removeItem('selectedMoProductIds');
    localStorage.removeItem('selectedPiIds');
    localStorage.removeItem('selectedPwoIds');

    let ids = [];
    $("#itemTable tbody tr[id*='row_']").each(function(index, item) {
        let so_item_id = $(item).find('[name*="[pwo_mapping_id]"]').val();
        if(so_item_id) {
            ids.push(so_item_id);
        }
    });
    if(ids.length) {
        localStorage.setItem('selectedMoProductIds', JSON.stringify(ids));
    }
};

/*Open Pr model*/
$(document).on('click', '.prSelect', (e) => {
    $("#prModal").modal('show');
    openBomRequest();
    getPwo();
});

function openBomRequest()
{
    initializeAutocompleteQt("customer_code_input_qt", "customer_id_qt_val", "customer", "customer_code", "company_name");
    initializeAutocompleteQt("book_code_input_qt", "book_id_qt_val", "book_pwo", "book_code", "");
    initializeAutocompleteQt("document_no_input_qt", "document_id_qt_val", "bom_document_qt", "document_number", "");
    // initializeAutocompleteQt("item_name_input_qt", "item_id_qt_val", "header_item", "item_code", "item_name");
    // initializeAutocompleteQt("department_po", "department_id_po", "department", "name", "");

}
function initializeAutocompleteQt(selector, selectorSibling, typeVal, labelKey1, labelKey2 = "")
{
    $("#" + selector).autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type: typeVal,
                    vendor_id : $("#vendor_id_qt_val").val(),
                    header_book_id : $("#book_id").val()
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            id: item.id,
                            label: `${item[labelKey1]} ${labelKey2 ? (item[labelKey2] ? '(' + item[labelKey2] + ')' : '') : ''}`,
                            code: item[labelKey1] || '',
                        };
                    }));
                },
                error: function(xhr) {
                    console.error('Error fetching customer data:', xhr.responseText);
                }
            });
        },
        appendTo : '#soModal',
        minLength: 0,
        select: function(event, ui) {
            var $input = $(this);
            $input.val(ui.item.label);
            $("#" + selectorSibling).val(ui.item.id);
            getPwo();
            return false;
        },
        change: function(event, ui) {
            if (!ui.item) {
                $(this).val("");
                $("#" + selectorSibling).val("");
            }
        }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
        }
    }).on("input", function () {
        if ($(this).val().trim() === "") {
            $(this).val("");
            $("#" + selectorSibling).val("");
            getPwo();
        }
    });
}

// function getPwo()
// {
//     let itemId = $("#item_id").val() || '';
//     let storeId = $("#filter_store_id").val() || '';
//     let header_book_id = $("#book_id").val() || '';
//     let stationId = $("#station_id").val() || '';
//     let series_id = $("#pwo_book_id_qt_val").val() || ''; // pwo
//     let document_number = $("#pwo_document_no_input_qt").val() || '';// pwo
//     let so_series_id = $("#so_book_id_qt_val").val() || ''; // so
//     let so_document_number = $("#so_document_no_input_qt").val() || '';// so
//     let actionUrl = '{{ route("mo.get.pwo.create") }}';
//     let customerId = $("#customer_id_qt_val").val() || '';
//     let fullUrl = `${actionUrl}?series_id=${encodeURIComponent(series_id)}&document_number=${encodeURIComponent(document_number)}&so_series_id=${encodeURIComponent(so_series_id)}&so_document_number=${encodeURIComponent(so_document_number)}&header_book_id=${encodeURIComponent(header_book_id)}&customer_id=${customerId}&item_id=${itemId}&store_id=${storeId}`;
//     fetch(fullUrl).then(response => {
//         return response.json().then(data => {
//             $("#itemTable .mrntableselectexcel").empty().append(data.data.pis);
            // setTimeout(() => {
            //     $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
            //         let currentIndex = index + 1;
            //         setAttributesUIHelper(currentIndex,"#itemTable");
            //     });
            // },100);
//         });
//     });
// }

function getPwo()
{
    let itemId = $("#item_id").val() || '';
    let selectedPiIds = localStorage.getItem('selectedMoProductIds') ?? '[]';
    selectedPiIds = JSON.parse(selectedPiIds);
    selectedPiIds = encodeURIComponent(JSON.stringify(selectedPiIds));
    let header_book_id = $("#book_id").val() || '';
    let store_id = $("#store").val() || '';
    let series_id = $("#book_id_qt_val").val() || '';
    let stationId = $("#station_id").val() || '';
    let document_number = $("#document_no_input_qt").val() || '';
    let actionUrl = '{{ route("mo.get.pwo") }}';
    let customerId = $("#customer_id_qt_val").val() || '';
    let fullUrl = `${actionUrl}?series_id=${encodeURIComponent(series_id)}&document_number=${encodeURIComponent(document_number)}&header_book_id=${encodeURIComponent(header_book_id)}&customer_id=${customerId}&selected_pwo_ids=${selectedPiIds}&station_id=${stationId}&item_id=${itemId}&store_id=${store_id}`;
    fetch(fullUrl).then(response => {
        return response.json().then(data => {
            $(".po-order-detail #soDataTable").empty().append(data.data.pis);
        });
    });
}

$(document).on('click', '.clearPiFilter', (e) => {
    $("#book_code_input_qt").val('');
    $("#book_id_qt_val").val('');
    $("#document_no_input_qt").val('');
    $("#document_id_qt_val").val('');
    $("#customer_code_input_qt").val('');
    $("#customer_id_qt_val").val('');
    getPwo();
});

/*Checkbox for pi item list*/
$(document).on('change','.po-order-detail > thead .form-check-input',(e) => {
  if (e.target.checked) {
      $(".po-order-detail > tbody .form-check-input").each(function() {
          $(this).prop('checked',true);
      });
  } else {
      $(".po-order-detail > tbody .form-check-input").each(function() {
          $(this).prop('checked',false);
      });
  }
});
$(document).on('change','.po-order-detail > tbody .form-check-input',(e) => {
  if(!$(".po-order-detail > tbody .form-check-input:not(:checked)").length) {
      $('.po-order-detail > thead .form-check-input').prop('checked', true);
  } else {
      $('.po-order-detail > thead .form-check-input').prop('checked', false);
  }
});


function getSelectedPiIDS()
{
    let ids = [];
    $('.pi_item_checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

$(document).on('click', '.soProcess', (e) => {
    let ids = getSelectedPiIDS();
    if (!ids.length) {
        $("[name='so_item_ids']").val('');
        $("#soModal").modal('hide');
        Swal.fire({
            title: 'Error!',
            text: 'Please select at least one one quotation',
            icon: 'error',
        });
        return false;
    }

    ids = JSON.stringify(ids);
    let d_date = $("input[name='document_date']").val() || '';
    let book_id = $("#book_id").val() || '';
    let type = '{{ $servicesBooks['services'][0]?->alias }}';
    let rowCount = $("#itemTable tbody tr[id*='row_']").length;
    let itemId = $("#item_id").val() || '';
    let actionUrl = '{{ route("mo.process.pwo-item") }}'+'?ids=' + ids+'&type='+type+'&d_date='+d_date+'&book_id='+book_id+'&rowCount='+rowCount+'&item_id='+itemId;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if(data.status == 200) {
                if ($("#itemTable > tbody tr").length) {
                    $("#itemTable > tbody > tr:last").after(data.data.pos);
                } else {
                    $("#itemTable > tbody").empty().append(data.data.pos);
                }

                $("#prModal").modal('hide');
                // $(".prSelect").prop('disabled',true);
                // $('#itemTable > tbody .form-check-input').removeAttr('data-id');
                initializeAutocomplete2(".comp_item_code");
                initializeAutocompleteCustomer("[name*='[customer_code]']");
                let newIds = getSelectedPiIDS();
                let existingIds = localStorage.getItem('selectedMoProductIds');
                if (existingIds) {
                    existingIds = JSON.parse(existingIds);
                    const mergedIds = Array.from(new Set([...existingIds, ...newIds]));
                    localStorage.setItem('selectedMoProductIds', JSON.stringify(mergedIds));
                } else {
                    localStorage.setItem('selectedMoProductIds', JSON.stringify(newIds));
                }

                $('#item_code').prop('disabled',true);
                $('#station_id').prop('disabled',true);
                $('#store_id').prop('disabled',true);
            }
            if(data.status == 422) {
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error',
                });
                return false;
            }
        });
    });
});

// Close modal
$(document).on('click','#closeButton', (e) => {
    $("#closeModal").modal('show');
});

/*Amendment btn submit*/
$(document).on('click', '#closeBtnSubmit', (e) => {
    e.preventDefault();

    let modal = $("#closeModal");
    let remark = modal.find('[name="close_remarks"]').val();
    let files = modal.find('[name="close_attachment[]"]')[0].files;
    // if (!remark) {
    //     $("#closeRemarkError").removeClass("d-none");
    //     return false;
    // } else {
    //     $("#closeRemarkError").addClass("d-none");
    // }

    let formData = new FormData();
    formData.append('close_remarks', remark);
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('id', '{{ $bom->id }}');

    let rows = document.querySelectorAll("#production-items tr[id^='row_']");

    rows.forEach((row, index) => {
        let rowIndex = row.dataset.index;
        let itemId = row.querySelector(`[name="productions[${rowIndex}][item_id]"]`)?.value || '';
        let uomId = row.querySelector(`[name="productions[${rowIndex}][uom_id]"]`)?.value || '';
        let qty1 = row.querySelector(`[name="productions[${rowIndex}][produced_qty]"]`)?.value || '';
        let productionItemId = row.querySelector(`[name="productions[${rowIndex}][mo_production_item_id]"]`)?.value || '';
        formData.append(`productions[${index}][item_id]`, itemId);
        formData.append(`productions[${index}][uom_id]`, uomId);
        formData.append(`productions[${index}][produced_qty]`, qty1);
        formData.append(`productions[${index}][mo_production_item_id]`, productionItemId);
    });

    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }
    let actionUrl = '{{ route("mo.close.document") }}';
    fetch(actionUrl, {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            $("#closeModal").modal('hide');
            if(data.errors) {
                $(".error-message").remove(); // Clear previous errors
                data.errors.forEach(error => {
                    let inputField = $(`#itemTable2 [name="${error.field.replace(/\[/g, '\\[').replace(/\]/g, '\\]')}"]`);
                    inputField.after(`<div class="text-danger error-message">${error.message}</div>`);
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error',
                });
            }
        } else {
            $("#closeModal").modal('hide');
            Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success',
            });
            setTimeout(() => {
                location.href = '{{route("mo.index")}}';
            },1500);

        }
    })
    .catch(error => console.error('Error:', error));
});

// Click on post
$(document).on('click', '#postButton', (e) => {
    let bookId = $("select[name='book_id']").val() || '';
    let documentId = '{{$bom->id}}';
    @if($bom->document_status == 'closed')
        let type = 'get';
    @else
        let type = 'view';
    @endif
    let actionUrl = '{{ route("mo.posting.get") }}'+'?book_id='+bookId+'&document_id='+documentId+'&type='+type;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (!data.status) {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || data.data.message || '',
                    icon: 'error',
                });
                return;
            }
            $("#postvoucher").modal('show');
            $("#postvoucher").find('#posting-table').html(data.data.html);
            $("#voucher_book_code").val(data.data.book_code);
            $("#voucher_doc_no").val(data.data.document_number);
            $("#voucher_date").val(data.data.document_date);
            $("#voucher_currency").val(data.data.currency_code);
            if(type == 'view') {
                $("#posting_button").closest('div').remove();
            }
        });
    });
});

// #posting_button
$(document).on('click', '#posting_button', (e) => {
    let bookId = $("select[name='book_id']").val() || '';
    let documentId = '{{$bom->id}}';
    let actionUrl = '{{ route("mo.posting.post") }}'+'?book_id='+bookId+'&document_id='+documentId;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            const response = data.data;
                if (response.status) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                    });
                    location.reload();
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error',
                    });
                }
        });
    });
});

/*Delete server side rows*/
$(document).on('click','#deleteConfirm', (e) => {
    let ids = e.target.getAttribute('data-ids');
    ids = JSON.parse(ids);
    let storedIds = JSON.parse(localStorage.getItem('deletedPiItemIds')) || [];
    let mergedIds = [...new Set([...storedIds, ...ids])];
    localStorage.setItem('deletedPiItemIds', JSON.stringify(mergedIds));
    $("#deleteComponentModal").modal('hide');
    if(ids.length) {
        ids.forEach((id,index) => {
            $(`.form-check-input[data-id='${id}']`).closest('tr').remove();
        });
    }

    if(!$("tr[id*='row_']").length) {
          $("#itemTable > thead .form-check-input").prop('checked',false);
          $('#item_code').prop('disabled',false);
          $('#station_id').prop('disabled',false);
          $('#store_id').prop('disabled',false);
      }
});

setTimeout(() => {
    $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
        let currentIndex = index + 1;
        setAttributesUIHelper(currentIndex,"#itemTable");
    });
    $("#itemTable2 .mrntableselectexcel tr").each(function(index, item) {
        let currentIndex = index + 1;
        setAttributesUIHelper(currentIndex,"#itemTable2");
    });
},100);

// Revoke Document
$(document).on('click', '#revokeButton', (e) => {
    let actionUrl = '{{ route("mo.revoke.document") }}'+ '?id='+'{{$bom->id}}';
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if(data.status == 'error') {
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error',
                });
            } else {
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success',
                });
            }
            location.reload();
        });
    });
});
</script>
@endsection
