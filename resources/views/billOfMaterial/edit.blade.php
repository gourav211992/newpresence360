@extends('layouts.app')
@section('content')
@use(\App\Helpers\ConstantHelper)
@php
$routeAlias = $servicesBooks['services'][0]?->alias ?? null;
if($routeAlias == ConstantHelper::BOM_SERVICE_ALIAS)
{
   $routeAlias = 'bill-of-material';
} else {
   $routeAlias = 'quotation-bom';
}
@endphp
<form id="BomEditForm" data-module="bom"  class="ajax-input-form bom_form" method="POST" action="{{ route('bill.of.material.update', $bom->id) }}" data-redirect="{{ url($routeAlias) }}" enctype='multipart/form-data'>
    @csrf
    <input type="hidden" name="orverhead_level_count"/>
    <input type="hidden" name="consumption_method" id="consumption_method" value=""/>
    <input type="hidden" name="quote_bom_id" id="quote_bom_id" value=""/>
    <input type="hidden" name="type" value="{{$serviceAlias}}">
    <div class="app-content content ">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header pocreate-sticky">
            <div class="row">
                @include('layouts.partials.breadcrumb-add-edit', [
                    'title' => $routeAlias == 'quotation-bom' ? 'Quotation BOM' : 'Production BOM',
                    'menu' => 'Home',
                    'menu_url' => url('home'),
                    'sub_menu' => 'Edit'
                ])
                <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                <div class="form-group breadcrumb-right">
                    <input type="hidden" name="document_status" value="{{$bom->document_status}}" id="document_status">
                    <button type="button" onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>

                    @if($buttons['draft'] || $buttons['amend'] && intval(request('amendment') ?? 0))
                        <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                            data-url="{{ url('bill-of-material/') }}/{{ $bom->id }}/{{ $buttons['amend'] ? $buttons['amend'] : 0 }}"
                            data-redirect="{{ url($routeAlias) }}"
                            data-message="Are you sure you want to delete this record?">
                        <i data-feather="trash-2" class="me-50"></i> Delete
                        </button>
                        <button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button" name="action" value="draft"><i data-feather='save'></i> Save as Draft</button>
                    @endif
                    @if(!intval(request('amendment') ?? 0) && $bom->document_status != ConstantHelper::DRAFT && $bom->document_status != ConstantHelper::REJECTED)
                    @if($bom->type == ConstantHelper::BOM_SERVICE_ALIAS)
                        <a href="{{ route('bill.of.material.export', $bom->id) }}" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                            <i data-feather="file-text"></i> Export
                        </a>
                    @else
                        <a href="{{ route('quotation-bom.export', $bom->id) }}" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                            <i data-feather="file-text"></i> Export
                        </a>
                    @endif

                        <a href="{{ route('bill.of.material.generate-pdf', $bom->id) }}" target="_blank" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                            <i data-feather="printer"></i> Print
                        </a>
                            @if($bom->type == ConstantHelper::BOM_SERVICE_ALIAS)
                                <a target="_blank" href="{{ route('bill.of.material.copy', ['id' => $bom->id]) }}" class="btn btn-warning btn-sm">Copy BOM</a>
                            @else
                                <a target="_blank" href="{{ route('quotation-bom.copy', ['id' => $bom->id]) }}" class="btn btn-warning btn-sm">Copy BOM</a>
                            @endif
                        @endif
                    @if($buttons['submit'])
                        <button type="submit" class="btn btn-primary btn-sm submit-button" name="action" value="submitted"><i data-feather="check-circle"></i> Submit</button>
                    @endif
                    @if($buttons['approve'])
                        <button type="button" id="reject-button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg> Reject
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" id="approved-button" name="action" value="approved">
                            <i data-feather="check-circle"></i> Approve
                        </button>
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
                                        <input type="hidden" name="book_id" class="form-control" id="book_id" value="{{$bom->book_id}}" readonly>
                                        <input readonly type="text" name="book_code" class="form-control" value="{{$bom->book_code}}" id="book_code">
                                        </div>
                                    </div>

                                    <div class="row align-items-center mb-1">
                                        <div class="col-md-3">
                                            <label class="form-label">BOM No <span class="text-danger">*</span></label>
                                        </div>

                                        <div class="col-md-5">
                                            <input readonly type="text" name="document_number" class="form-control" id="document_number" value="{{$bom->document_number}}">
                                        </div>
                                    </div>

                                    <div class="row align-items-center mb-1">
                                        <div class="col-md-3">
                                            <label class="form-label">BOM Date <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="date" class="form-control" value="{{ $bom->document_date }}" name="document_date">
                                        </div>
                                    </div>
                                    <div class="row align-items-center mb-1 d-none" id="reference_from">
                                        <div class="col-md-3">
                                            <label class="form-label">Reference from</label>
                                        </div>
                                        <div class="col-md-5 action-button">
                                            <button type="button" @if(!$isEdit) disabled @endif class="btn btn-outline-primary btn-sm mb-0 prSelect" {{$bom->bomItems->count() ? 'disabled' : ''}}><i data-feather="plus-square"></i> Quotation Bom</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Approval History Section --}}
                            @include('partials.approval-history', ['document_status' => $bom->document_status, 'revision_number' => $revision_number])

                            </div>
                        </div>
                    </div>

                    {{-- Product Detail --}}
                </div>

                        <div class="col-md-12" id="vendor_section">
                            <div class="card quation-card">
                                <div class="card-header newheader">
                                    <div>
                                        <h4 class="card-title">Product Details</h4>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label">Product Code <span class="text-danger">*</span></label>
                                                <input type="text" value="{{$bom->item?->item_code}}" placeholder="Select" class="form-control mw-100 ledgerselecct" id="item_code" name="item_code" data-name="{{$bom->item?->item_name ?? ''}}" data-code="{{$bom->item?->item_code ?? ''}}" {{ $bom->document_status != 'draft' ? 'readonly' : ' ' }}/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                                <input type="hidden" value="{{$bom->item?->id}}" name="item_id" id="head_item_id">
                                                <input type="text" value="{{$bom->item?->item_name}}" id="head_item_name" placeholder="Select" class="form-control mw-100 ledgerselecct" name="item_name" readonly />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label">UOM <span class="text-danger">*</span></label>
                                                <input type="hidden" id="head_uom_id" class="form-control" name="uom_id" value="{{$bom->uom?->id}}" readonly  />
                                                <input type="text" id="head_uom_name" class="form-control" name="uom_name" value="{{$bom->uom?->name}}" readonly  />
                                            </div>
                                        </div>

                                        @include('billOfMaterial.partials.header-attribute-edit')

                                        @if($servicesBooks['services'][0]?->alias != ConstantHelper::BOM_SERVICE_ALIAS)
                                        <div class="col-md-3 customer_div">
                                            <div class="mb-1">
                                                <label class="form-label">Customer <span class="text-danger">*</span></label>
                                                <input type="hidden" name="customer_id" id="customer_id" value="{{$bom?->customer?->id}}">
                                                <input type="text" id="customer" placeholder="Select" value="{{$bom?->customer?->customer_code}}" class="form-control mw-100 ledgerselecct" name="customer" readonly/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                                <input type="text" id="customer_name" value="{{$bom?->customer?->company_name}}" placeholder="Select" class="form-control mw-100 ledgerselecct" name="customer_name" readonly />
                                            </div>
                                        </div>
                                        @endif
                                        @if($servicesBooks['services'][0]?->alias == ConstantHelper::BOM_SERVICE_ALIAS)
                                        <div class="col-md-3 production_type_div">
                                            <div class="mb-1">
                                                <label class="form-label">Production Type <span class="text-danger">*</span></label>
                                                <select class="form-select" id="production_type" name="production_type" {{ $bom->document_status != 'draft' ? 'disabled' : ' ' }}>
                                                    @foreach($productionTypes as $productionType)
                                                    <option value="{{$productionType}}" {{$bom->production_type == $productionType ? 'selected' : ''}}>{{ucfirst($productionType)}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        @endif
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label">Production Route <span class="text-danger">*</span></label>
                                                <select class="form-select" id="production_route_id" name="production_route_id" {{ $bom->document_status != 'draft' ? 'disabled' : ' ' }}>
                                                    @foreach($productionRoutes as $productionRoute)
                                                        @if($bom->production_route_id)
                                                            <option value="{{$productionRoute->id}}" {{$bom->production_route_id == $productionRoute->id ? 'selected' :  ''}} data-perc="{{$productionRoute->safety_buffer_perc}}">{{ucfirst($productionRoute->name)}}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label">Safety Buffer (%)</label>
                                                <input type="text" id="safety_buffer_perc" value="{{$bom->safety_buffer_perc}}" class="form-control mw-100 ledgerselecct" name="safety_buffer_perc"/>
                                            </div>
                                        </div>
                                        @if($servicesBooks['services'][0]?->alias == ConstantHelper::BOM_SERVICE_ALIAS)
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label">Customizable <span class="text-danger">*</span></label>
                                                <select class="form-select" id="customizable" name="customizable">
                                                    {{-- <option value="">Select</option> --}}
                                                    @foreach($customizables as $customizable)
                                                        <option value="{{$customizable}}" {{$customizable == $bom->customizable ? 'selected' : ''}}>{{ucfirst($customizable)}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body customernewsection-form px-0">
                                    <div class="border-bottom mb-2 pb-25 pocreate-sticky" id="componentSection">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="newheader ">
                                                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link active fs-5" id="raw-materials-tab" data-bs-toggle="tab" data-bs-target="#raw-materials" type="button" role="tab" aria-controls="raw-materials" aria-selected="true">
                                                            Consumption
                                                        </button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link fs-5" id="instruction-items-tab" data-bs-toggle="tab" data-bs-target="#instruction-items" type="button" role="tab" aria-controls="instruction-items" aria-selected="false">
                                                            Instruction
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-sm-end">
                                            @if(($buttons['draft'] || $buttons['submit']) || ($buttons['amend'] && intval(request('amendment') ?? 0)) || $buttons['approve'])
                                            <a href="javascript:;" class="btn btn-sm btn-outline-danger me-50 tab-action d-none" id="deleteBtn" data-tab="raw-materials">
                                            <i data-feather="x-circle"></i> Delete</a>
                                            <a href="javascript:;" id="addNewItemBtn" class="btn btn-sm btn-outline-primary tab-action d-none" data-tab="raw-materials">
                                            <i data-feather="plus"></i> Add Component</a>
                                            <a href="javascript:;" class="btn btn-sm btn-outline-danger me-50 tab-action d-none" id="deleteInstructionBtn" data-tab="instruction-items">
                                                <i data-feather="x-circle"></i> Delete</a>
                                                <a href="javascript:;" id="addNewInstructionBtn" class="btn btn-sm btn-outline-primary tab-action d-none" data-tab="instruction-items">
                                                <i data-feather="plus"></i> Add Instruction</a>
                                            @endif
                                        </div>
                                    </div>
                                    </div>

                                    <div class="tab-content mt-1" id="productTabsContent">
                                        <div class="tab-pane fade show active" id="raw-materials" role="tabpanel" aria-labelledby="raw-materials-tab">
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
                                                            <th style="width: 100px;" id="section_required">Section</th>
                                                            <th style="width: 100px;" id="sub_section_required">Sub Section</th>
                                                            <th style="min-width: 110px;">Item Code</th>
                                                            <th style="min-width: 150px;">Item Name</th>
                                                            <th>Attributes</th>
                                                            <th style="width: 30px;">UOM</th>
                                                            <th>Consumption</th>
                                                            <th class="{{$canView ? '' : 'd-none'}}">Cost</th>
                                                            <th class="{{$canView ? '' : 'd-none'}}">Item Value</th>
                                                            <th class="{{$canView ? '' : 'd-none'}}" id="component_overhead_required">Overheads</th>
                                                            <th class="{{$canView ? '' : 'd-none'}}">Total Cost</th>
                                                            <th style="min-width: 100px;" id="station_required">Station</th>
                                                            <th style="min-width: 100px;">Vendor</th>
                                                            <th style="min-width: 100px;" id="th_bacth_inherit_requird">Inherit <br/>Batch</th>
                                                            <th style="width: 20px;"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="mrntableselectexcel">
                                                        @include('billOfMaterial.partials.item-row-edit')
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="totalsubheadpodetail {{$canView ? '' : 'd-none'}}">
                                                            <td colspan="9"></td>
                                                            <td class="text-end" id="totalItemValue">{{number_format($bom->total_item_value,6)}}</td>
                                                            <td class="text-end" id="totalOverheadAmountValue">{{number_format($bom->item_overhead_amount,2)}}</td>
                                                            <td class="text-end" id="totalCostValue">{{number_format(($bom->total_item_value +  $bom->item_overhead_amount),2)}}</td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr valign="top">
                                                            <td @if($canView) colspan="11" @else colspan="15" @endif rowspan="10">
                                                            <table class="table border" id="itemDetailTable">
                                                                <tr>
                                                                    <td class="p-0">
                                                                        <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Item Details</strong></h6>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                </tr>
                                                                <tr>
                                                                </tr>
                                                            </table>
                                                            </td>
                                                            @if($canView)
                                                            <td colspan="4">
                                                            <table class="table border mrnsummarynewsty">
                                                                <tr>
                                                                    <td colspan="2" class="p-0">
                                                                        <h6 class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between">
                                                                        <strong>BOM Summary</strong>
                                                                        @if($canView)
                                                                        <div class="addmendisexpbtn">
                                                                            <button type="button" class="btn p-25 btn-sm btn-outline-secondary addOverHeadSummaryBtn"><i data-feather="plus"></i> Overhead</button>
                                                                        </div>
                                                                        @endif
                                                                        </h6>
                                                                    </td>
                                                                </tr>
                                                                <tr class="voucher-tab-foot">
                                                                    <td class="text-primary"><strong>Item Total</strong></td>
                                                                    <td>
                                                                        <div class="justify-content-end text-end">
                                                                        <h5 id="footerTotalCost" amount="{{$bom->total_item_value  + $bom->item_overhead_amount + $bom->header_overhead_amount}}">{{number_format(($bom->total_item_value + $bom->item_overhead_amount + $bom->header_overhead_amount),2)}}</h5>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><strong>Header Overheads</strong></td>
                                                                    <td class="text-end" id="footerOverheadHeader">0.00</td>
                                                                </tr>
                                                                <tr class="voucher-tab-foot">
                                                                    <td class="text-primary"><strong>Grand Total</strong></td>
                                                                    <td>
                                                                        <div class="quottotal-bg justify-content-end">
                                                                        <h5 id="footerGrandTotal">0.00</h5>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            </td>
                                                            @endif
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                                </div>
                                        </div>
                                        <div class="tab-pane fade" id="instruction-items" role="tabpanel" aria-labelledby="product-details-tab">
                                            <div class="table-responsive pomrnheadtffotsticky">
                                            <table id="itemTable3" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                <thead>
                                                    <tr>
                                                        <th width="20px">
                                                            <div class="form-check form-check-primary custom-checkbox">
                                                                <input type="checkbox" class="form-check-input" id="Email">
                                                                <label class="form-check-label" for="Email"></label>
                                                            </div>
                                                        </th>
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
                                                    @include('billOfMaterial.partials.instruction-row-edit')
                                                </tbody>
                                            </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                                    <input type="file" name="attachment[]" class="form-control" onchange = "addFiles(this,'main_bom_preview')" max_file_count = "10" multiple>
                                                    <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                                                </div>
                                            </div>
                                            @include('partials.document-preview',['documents' => $bom->getDocuments(), 'document_status' => $bom->document_status,'elementKey' => 'main_bom_preview'])
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
                            <div id="approval-data">
                                <!-- Clone Approval Modal Form Data Here -->
                            </div>
                        </div>
                </div>
                <!-- Modal to add new record -->
            </section>
        </div>
    </div>
    </div>
    {{-- Overhead summary popup --}}
    @include('billOfMaterial.partials.overhead-modal')
    @include('procurement.po.partials.amendment-modal', ['id' => $bom->id])
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

{{-- Overhead row popup --}}
@include('billOfMaterial.partials.item-overhead-model')

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
           <button type="button" id="deleteCancel" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
           <button type="button" id="deleteConfirm" class="btn btn-primary" >Confirm</button>
         </div>
      </div>
   </div>
</div>

<div class="modal fade text-start alertbackdropdisabled" id="deleteProdComponentModal" tabindex="-1" aria-labelledby="myModalLabel2" aria-hidden="true" data-bs-backdrop="false">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header p-0 bg-transparent">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body alertmsg text-center warning">
           <i data-feather='alert-circle'></i>
           <h2>Are you sure?</h2>
           <p>Are you sure you want to delete selected <strong>production</strong>?</p>
           <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
           <button type="button" id="deleteProdConfirm" class="btn btn-primary" >Confirm</button>
         </div>
      </div>
   </div>
</div>

<div class="modal fade text-start alertbackdropdisabled" id="deleteInstrunctionComponentModal" tabindex="-1" aria-labelledby="myModalLabel2" aria-hidden="true" data-bs-backdrop="false">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header p-0 bg-transparent">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body alertmsg text-center warning">
           <i data-feather='alert-circle'></i>
           <h2>Are you sure?</h2>
           <p>Are you sure you want to delete selected <strong>instruction</strong>?</p>
           <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
           <button type="button" id="deleteInstructionConfirm" class="btn btn-primary" >Confirm</button>
         </div>
      </div>
   </div>
</div>

{{-- Approval Modal --}}
@include('billOfMaterial.partials.approve-modal', ['id' => $bom->id])

{{-- Reject Modal --}}
@include('billOfMaterial.partials.reject-modal', ['id' => $bom->id])

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
              <p>Are you sure you want to <strong>Amendment</strong> this <strong>BOM</strong>? After Amendment this action cannot be undone.</p>
              <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
              <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
          </div>
      </div>
  </div>
</div>

{{-- Delete overhead row modal --}}
<div class="modal fade text-start alertbackdropdisabled" id="deleteOverheadModal" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header p-0 bg-transparent">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body alertmsg text-center warning">
           <i data-feather='alert-circle'></i>
           <h2>Are you sure?</h2>
           <p>Are you sure you want to delete selected <strong>Overhead</strong>?</p>
           <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
           <button type="button" id="deleteConfirmOverhead" class="btn btn-primary" >Confirm</button>
         </div>
      </div>
   </div>
</div>

<div class="modal fade text-start alertbackdropdisabled" id="deleteItemOverheadModal" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header p-0 bg-transparent">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body alertmsg text-center warning">
           <i data-feather='alert-circle'></i>
           <h2>Are you sure?</h2>
           <p>Are you sure you want to delete selected <strong>Overhead</strong>?</p>
           <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
           <button type="button" id="deleteItemConfirmOverhead" class="btn btn-primary" >Confirm</button>
         </div>
      </div>
   </div>
</div>

@include('billOfMaterial.partials.consumption-modal')
@include('billOfMaterial.partials.q-bom-modal')
@endsection
@section('scripts')
<script>
    var canView = {{ $canView ? 'true' : 'false' }};
    var isEdit = {{ $isEdit ? 'true' : 'false' }};
</script>
<script type="text/javascript" src="{{asset('assets/js/modules/bom.js')}}"></script>
<script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
<script type="text/javascript">
/*Clear local storage*/
setTimeout(() => {
    localStorage.removeItem('deletedItemOverheadIds');
    localStorage.removeItem('deletedHeaderOverheadIds');
    localStorage.removeItem('deletedBomItemIds');
    localStorage.removeItem('deletedProdItemIds');
    localStorage.removeItem('deletedInstructionItemIds');
    localStorage.removeItem('itemIds');
    localStorage.removeItem('editItemIds');
},0);

@if($buttons['amend'] && intval(request('amendment') ?? 0) || $buttons['approve'])
    // $('.').prop('readonly', true);
@else
   @if($bom->document_status != 'draft' && $bom->document_status != 'rejected')
   $(':input').prop('readonly', true);
   $('select').not('.amendmentselect select').prop('disabled', true);
   $("#deleteBtn").remove();
   $("#addNewItemBtn").remove();
   $(document).on('show.bs.modal', function (e) {
       if(e.target.id != 'approveModal') {
           $(e.target).find('.modal-footer').remove();
           $('select').not('.amendmentselect select').prop('disabled', true);
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
    $('#th_bacth_inherit_requird').hide();

   /*Bind button value*/
   initializeAutocomplete2(".comp_item_code");
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
                    itemCodeChange(itemId);
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        // $('#itemId').val('');
                        $(this).attr('data-name', '');
                        $(this).attr('data-code', '');
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
    }

    function initializeAutocompleteCustomer(selector, type) {
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
                    $("#customer_id").val(customerId);
                    $("#customer_name").val(customerName);
                    let itemId = $("#head_item_id").val() || '';
                    if(itemId) {
                        itemCodeChange(itemId, customerId);
                    }
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#customer_id").val('');
                        $("#customer_name").val('');
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                    $("#customer_id").val('');
                    $("#customer_name").val('');
                }
            });
    }

    initializeAutocomplete1("#item_code");
    initializeAutocompleteCustomer("#customer");

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

    $('#itemTable').on('change', '.is_inherit_batch_item', function() {
        $(this).closest('tbody')
            .find('.is_inherit_batch_item')
            .prop('checked', false)
            .val('0');          // reset all to no
        $(this).prop('checked', true).val('1'); // set only this one to yes
    });

    // Set Approval Modal Data
    $(document).on('click','#approval-clone-btn',(e) => {

        // Reset approval-data
        $('#approval-data').empty();

        // Clone into approval-data
        $('#clone-approval').first().clone(true, true).appendTo('#approval-data');

        // Hide the div
        $('#approval-data').hide();

        // Hide the modal
        $('#approveModal').modal('hide');

        // Now submit the form
        $('#BomEditForm').submit();
    });

    function getDocNumberByBookId(bookId) {
      let document_date = $("[name='document_date']").val();
      let actionUrl = '{{route("book.get.doc_no_and_parameters")}}'+'?book_id='+bookId+'&document_date='+document_date;
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                if (data.status == 200) {
                  $("#book_code").val(data.data.book_code);
                  if(!data.data.doc.document_number) {
                     $("#document_number").val('');
                  }
                //   $("#document_number").val(data.data.doc.document_number);
                  if(data.data.doc.type == 'Manually') {
                     $("#document_number").attr('readonly', false);
                  } else {
                     $("#document_number").attr('readonly', true);
                  }
                  const parameters = data.data.parameters;
                  setServiceParameters(parameters);
                  setTableCalculation();
                }
                if(data.status == 404) {
                    $("#book_code").val('');
                    $("#document_number").val('');
                    const docDateInput = $("[name='document_date']");
                    docDateInput.removeAttr('min');
                    docDateInput.removeAttr('max');
                    docDateInput.val(new Date().toISOString().split('T')[0]);
                    alert(data.message);
                }
            });
        });
    }

   /*for trigger on edit cases*/
   setTimeout(() => {
       let bookId = $("#book_id").val();
       getDocNumberByBookId(bookId);
   },0);
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

        // Cunsumption Method
        if(parameters.consumption_method.includes('manual')) {
            $("#consumption_method").val('manual');
            $(".consumption_btn").addClass('d-none');
        }
        if(parameters.consumption_method.includes('norms')) {
            $("#consumption_method").val('norms');
            $(".consumption_btn").removeClass('d-none');
       }

       if (parameters.station_required && !parameters.station_required.includes('yes')) {
           let thIndex = $("#station_required").index();
           $('tfoot .totalsubheadpodetail:eq(0)').find(`td:last-child`).remove();
           $("#station_required").remove();
       }

       if (parameters.section_required && !parameters.section_required.includes('yes')) {
           let td = $('tfoot .totalsubheadpodetail:eq(0)').find("td[colspan]");
           if (td.length > 0) {
                let colspanValue = parseInt(td.attr("colspan"));
                let newColspanValue = colspanValue - 2;
                td.attr("colspan", newColspanValue);
            }
            if($("#section_required").length) {
                $("#section_required").remove();
            }
           if($("#section_required2").length) {
               $("#section_required2").remove();
           }
           if($("#sub_section_required").length) {
               $("#sub_section_required").remove();
           }
           if($("#sub_section_required2").length) {
               $("#sub_section_required2").remove();
           }
           let td2 = $("tfoot").find("tr[valign]").find('td[rowspan]');
           if (td2.length > 0) {
                let colspanValue = parseInt(td2.attr("colspan"));
                if(colspanValue > 6) {
                    let newColspanValue = colspanValue - 2;
                    td2.attr("colspan", newColspanValue);
                }
            }
       }

       if(parameters.section_required.includes('yes')) {
            if (parameters.sub_section_required && !parameters.sub_section_required.includes('yes')) {
            let td = $('tfoot .totalsubheadpodetail:eq(0)').find("td[colspan]");
            if (td.length > 0) {
                    let colspanValue = parseInt(td.attr("colspan"));
                    let newColspanValue = colspanValue - 1;
                    td.attr("colspan", newColspanValue);
                }
            if($("#sub_section_required").length) {
                $("#sub_section_required").remove();
            }
            if($("#sub_section_required2").length) {
                $("#sub_section_required2").remove();
            }

            let td2 = $("tfoot").find("tr[valign]").find('td[rowspan]');
            if (td2.length > 0) {
                    let colspanValue = parseInt(td2.attr("colspan"));
                    if(colspanValue > 6) {
                        let newColspanValue = colspanValue - 1;
                        td2.attr("colspan", newColspanValue);
                    }
                }
        }
       }

       if (parameters.component_overhead_required && !parameters.component_overhead_required.includes('yes')) {
           $("#component_overhead_required").remove();
           $("#totalOverheadAmountValue").remove();
           let td2 = $("tfoot").find("tr[valign]").find('td[rowspan]');
           if (td2.length > 0) {
                let colspanValue = parseInt(td2.attr("colspan"));
                if(colspanValue > 6) {
                    let newColspanValue = colspanValue - 1;
                    td2.attr("colspan", newColspanValue);
                }
            }

       }

       // Handle Batch Inheritance
        if (parameters.bacth_inherit_requird && parameters.bacth_inherit_requird.includes('yes')) {
            $('#th_bacth_inherit_requird').show();
        }
        let reference_from_service = parameters?.reference_from_service;
        if(reference_from_service?.length) {
            let c_bom = '{{ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS}}';
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
            //     // location.href = "{{url($routeAlias)}}";
            // },1500);
        }
   }

    function itemCodeChange(itemId,customerId = null){
        let customer_id = $("#customer_id").val() || '';
        let type = '{{ $servicesBooks['services'][0]?->alias }}';
        let actionUrl = '{{route("bill.of.material.item.code")}}'+'?item_id='+itemId+'&customer_id='+customer_id+'&type='+type;
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                if (data.status == 200) {
                    if(customerId) {
                        return false;
                    }
                  let item_name = data.data.item?.item_name || '';
                  let item_id = data.data.item?.id || '';
                  let uom_id = data.data.item?.uom_id || '';
                  let uom_name = data.data.item?.uom?.name || '';
                  $("#head_item_name").val(item_name);
                  $("#head_item_id").val(item_id);
                  $("#head_uom_id").val(uom_id);
                  $("#head_uom_name").val(uom_name);
                  $(".heaer_item").remove();
                  $('#vendor_section').find('input[name*="[attr_id]"]').remove();
                  if($(".customer_div").length) {
                      $(".customer_div").before(data.data.html);
                    } else {
                      $(".production_type_div").before(data.data.html);
                  }
                } else if(data.status == 422) {
                    $("#item_code").val('');
                    $("#head_item_name").val('');
                    $("#head_item_id").val('');
                    $("#head_uom_id").val('');
                    $("#head_uom_name").val('');
                    $(".heaer_item").remove();
                    $("input[name*='attributes[']").remove();
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                    });
                    if(customerId) {
                        $("#customer_id").val("");
                        $("#customer").val("");
                        $("#customer_name").val("");
                    }
                }
            });
        });
    }

   $(document).on('blur', '#item_code', (e) => {
       if(!e.target.value) {
           itemCodeChange(null)
       }
   });

});

/*Add New Row*/
// for component item code
function initializeAutocomplete2(selector, type) {
   $(selector).autocomplete({
       source: function(request, response) {
         let headItemId = $("#head_item_id").val();
         let selectedAllItemIds = [];
         if(Number(headItemId)) {
            selectedAllItemIds.push(headItemId);
         }
          $("#itemTable tbody [id*='row_']").each(function(index,item) {
            if(Number($(item).find('[name*="item_id"]').val())) {
               selectedAllItemIds.push(Number($(item).find('[name*="item_id"]').val()));
            }
          });
           $.ajax({
               url: '/search',
               method: 'GET',
               dataType: 'json',
               data: {
                   q: request.term,
                   type:'comp_item',
                   selectedAllItemIds : JSON.stringify(selectedAllItemIds)
               },
               success: function(data) {
                   response($.map(data, function(item) {
                       return {
                           id: item.id,
                           label: `${item.item_name} (${item.item_code})`,
                           code: item.item_code || '',
                           item_id: item.id,
                           uom_name:item.uom?.name,
                           uom_id:item.uom_id,
                           is_attr:item.item_attributes_count,
                           item_name:item.item_name,
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
           let itemName = ui.item.value;
           let itemN = ui.item.item_name;
           let itemId = ui.item.item_id;
           let uomId = ui.item.uom_id;
           let uomName = ui.item.uom_name;
           $input.attr('data-name', itemName);
           $input.attr('data-code', itemCode);
           $input.attr('data-id', itemId);
           $input.val(itemCode);
           $input.closest('tr').find('[name*=item_id]').val(itemId);
           $input.closest('tr').find('[name*=item_code]').val(itemCode);
           $input.closest('tr').find('[name*="[item_name]"]').val(itemN);
           let uomOption = `<option value=${uomId}>${uomName}</option>`;
           $input.closest('tr').find('[name*=uom_id]').empty().append(uomOption);
           $input.closest('tr').find('[name*=attr_group_id]').remove();
           setTimeout(() => {
                if(ui.item.is_attr) {
                    $input.closest('tr').find('.attributeBtn').trigger('click');
                } else {
                    $input.closest('tr').find('.attributeBtn').trigger('click');
                    if(!$("#consumption_method").val().includes('manual')) {
                    $input.closest('tr').find('.consumption_btn button').trigger('click');
                    } else {
                    $input.closest('tr').find('[name*="[qty]"]').val('').focus();
                    }
                }
            }, 100);
            getBomItemCost(itemId, itemAttributes = []);
           return false;
       },
       change: function(event, ui) {
           if (!ui.item) {
               $(this).val("");
               // $('#itemId').val('');
               $(this).attr('data-name', '');
               $(this).attr('data-code', '');
           }
       }
   }).focus(function() {
       if (this.value === "") {
           $(this).autocomplete("search", "");
       }
   });
}
$(document).on('click','#addNewItemBtn', (e) => {
    // let rowsLength = $("#itemTable > tbody > tr").length;
    let rowsLength = getUniqueRowCount();
    /*Check header attribute required*/
    let itemCode = $("#item_code").val();
    let selectedAttrRequired = false;
    let a = $("select[name*='[attr_name]']").filter(function () {
        return !$(this).val();
    });
    if(a.length) {
        selectedAttrRequired = true;
    }
    if(!$(".heaer_item").length) {
      selectedAttrRequired = true;
    }
    let head_item_id = $("#head_item_id").val();
    let itemObj = {
      item_code : itemCode,
      item_id : head_item_id,
      selectedAttrRequired : selectedAttrRequired
    };
    if($("[name*='attributes[1][attr_group_id]']").length == 0 && itemCode) {
      itemObj.selectedAttrRequired = false;
    }
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
       let item_id = lastRow.find("[name*='item_id']").val();
       if(lastRow.find("[name*='attr_name']").length) {
          var emptyElements = lastRow.find("[name*='attr_name']").filter(function() {
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
            const nameAttr = $(this).attr("name"); // Get the name attribute
            const value = $(this).val();
            const attributeIdMatch = nameAttr.match(/\[attr_group_id]\[(\d+)]/);
            const attributeId = attributeIdMatch ? attributeIdMatch[1] : null;
            componentAttr.push({
                'attr_name' : attributeId,
                'attr_value' : value
            });
        });
    }
    let customerId  = $("#customer_id").val() || '';
    let d_date = $("input[name='document_date']").val() || '';
    let book_id = $("#book_id").val() || '';

    let type = '{{ $servicesBooks['services'][0]?->alias }}';
    let actionUrl = '{{ route("bill.of.material.item.row") }}';
    const formData = new FormData();
    formData.append('count', rowsLength);
    formData.append('item', JSON.stringify(itemObj));
    formData.append('component_item', JSON.stringify(lastTrObj));
    // formData.append('header_attr', JSON.stringify(headerSelectedAttr));
    formData.append('comp_attr', JSON.stringify(componentAttr));
    formData.append('type', type);
    formData.append('customer_id', customerId);
    formData.append('d_date', d_date);
    formData.append('book_id', book_id);
    fetch(actionUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        body: formData
    })
    .then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
               // $("#submit-button").click();
                if (rowsLength) {
                    // $("#itemTable > tbody > tr:last").after(data.data.html);
                    if($("#itemTable > tbody > tr.trselected").length) {
                        $("#itemTable > tbody > tr.trselected").after(data.data.html);
                    }else {
                        $("#itemTable > tbody > tr:last").after(data.data.html);
                    }
                } else {
                    $("#itemTable > tbody").html(data.data.html);
                }
                initializeAutocomplete2(".comp_item_code");
                initializeStationAutocomplete();
                initializeVendorAutocomplete();
                initializeProductSectionAutocomplete();
                $(".prSelect").prop('disabled',true);
                feather.replace();
                focusAndScrollToLastRowInput();
            } else if(data.status == 422) {
               Swal.fire({
                    title: 'Error!',
                    text: data.message || 'An unexpected error occurred.',
                    icon: 'error',
                });
            } else {
               console.log("Someting went wrong!");
            }

            if(!$("#consumption_method").val().includes('manual')) {
                $("#itemTable > tbody > tr:last").find('.consumption_btn').removeClass('d-none');
            } else {
                $("#itemTable > tbody > tr:last").find('.consumption_btn').addClass('d-none');
            }
        });
    });
});

$(document).on('click', '#addNewInstructionBtn', (e) => {
    let rowsLength = $("#itemTable3 > tbody > tr").length;
    let type = '{{ $servicesBooks['services'][0]?->alias }}';
    let d_date = $("input[name='document_date']").val() || '';
    let book_id = $("#book_id").val() || '';
    let actionUrl = '{{route("bill.of.material.instruction.row")}}'+'?count='+rowsLength+'&type='+type+'&d_date='+d_date+'&book_id='+book_id;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
                if (rowsLength) {
                    $("#itemTable3 > tbody > tr:last").after(data.data.html);
                } else {
                    $("#itemTable3 > tbody").html(data.data.html);
                }
                initializeInstructionStationAutocomplete();
                initializeInstructionProductSectionAutocomplete();
                feather.replace();
                focusAndScrollToLastRowInput("input[name*='instruction_station']","#itemTable3");
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

/*Delete server side rows*/
$(document).on('click','#deleteConfirm', (e) => {
   let ids = e.target.getAttribute('data-ids');
   ids = JSON.parse(ids);
    localStorage.setItem('deletedBomItemIds', JSON.stringify(ids));
    $("#deleteComponentModal").modal('hide');

    if(ids.length) {
        ids.forEach((id,index) => {
            $(`#itemTable .form-check-input[data-id='${id}']`).closest('tr').remove();
        });
    }
    setTableCalculation();
    if(!$("#itemTable [id*=row_]").length) {
        $("#itemTable th .form-check-input").prop('checked',false);
        $(".prSelect").prop('disabled',false);
        $("#itemTable > thead .form-check-input").prop('checked',false);
    }
});
$(document).on('click', '#deleteCancel', (e) => {
    $('#itemTable > tbody .form-check-input:checked').each(function() {
        let selectedId = $(this).attr('data-id');

        if (selectedId) {
           
            let itemIds = JSON.parse(localStorage.getItem('itemIds')) || [];
            let editItemIds = JSON.parse(localStorage.getItem('editItemIds')) || [];

            itemIds = itemIds.filter(id => id != selectedId);
            editItemIds = editItemIds.filter(id => id != selectedId);

            localStorage.setItem('itemIds', JSON.stringify(itemIds));
            localStorage.setItem('editItemIds', JSON.stringify(editItemIds));
        }
    });
});


$(document).on('click','#deleteProdConfirm', (e) => {
   let ids = e.target.getAttribute('data-ids');
   ids = JSON.parse(ids);
    localStorage.setItem('deletedProdItemIds', JSON.stringify(ids));
    $("#deleteProdComponentModal").modal('hide');

    if(ids.length) {
        ids.forEach((id,index) => {
            $(`#itemTable2 .form-check-input[data-id='${id}']`).closest('tr').remove();
        });
    }
    if(!$("#itemTable2 [id*=row_]").length) {
        $("#itemTable2 th .form-check-input").prop('checked',false);
        $("#itemTable2 > thead .form-check-input").prop('checked',false);
    }
});

$(document).on('click','#deleteInstructionConfirm', (e) => {
   let ids = e.target.getAttribute('data-ids');
   ids = JSON.parse(ids);
    localStorage.setItem('deletedInstructionItemIds', JSON.stringify(ids));
    $("#deleteInstrunctionComponentModal").modal('hide');

    if(ids.length) {
        ids.forEach((id,index) => {
            $(`#itemTable3 .form-check-input[data-id='${id}']`).closest('tr').remove();
        });
    }
    if(!$("#itemTable3 [id*=row_]").length) {
        $("#itemTable3 th .form-check-input").prop('checked',false);
        $("#itemTable3 > thead .form-check-input").prop('checked',false);
    }
});

/*Check attrubute*/
$(document).on('click', '.attributeBtn', (e) => {
    let tr = e.target.closest('tr');
    let item_name = tr.querySelector('[name*=item_code]').value;
    let item_id = tr.querySelector('[name*=item_id]').value;
    let selectedAttr = [];
    const attrElements = tr.querySelectorAll('[name*=attr_name]');
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
    let currentTab = document.querySelector(".nav-link.active").getAttribute("data-bs-target").replace("#", "");
   let bom_detail_id = 0;
   if($(tr).find('[name*="bom_detail_id"]').length) {
      bom_detail_id = Number($(tr).find('[name*="bom_detail_id"]').val()) || 0;
   }
    let actionUrl = '{{route("bill.of.material.item.attr")}}'+'?item_id='+itemId+`&rowCount=${rowCount}&selectedAttr=${selectedAttr}&bom_detail_id=${bom_detail_id}&current_tab=${currentTab}`;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
                  $("#attribute tbody").empty();
                  $("#attribute table tbody").append(data.data.html);
                  $(tr).find('td:nth-child(4)').find("[name*='[attr_name]']").remove();
                  $(tr).find('td:nth-child(4)').append(data.data.hiddenHtml)
                  if (data.data.attr) {
                   $("#attribute").modal('show');
                   $(".select2").select2();
                  }
                  if(data.data.vendor_id) {
                    $(tr).find("[name='product_vendor']").val(data.data.vendor_name);
                    $(tr).find("[name*='vendor_id']").val(data.data.vendor_id);
                  }
                  qtyEnabledDisabled();
                  initAttributeAutocomplete();
            }
        });
    });
}

/*addOverHeadItemBtn*/
$(document).on('click', '.addOverHeadItemBtn', (e) => {
   e.preventDefault();
   let currentRow = $(e.target).closest('tr');
   let itemValue = Number(currentRow.find('[name*="item_value"]').val()) || 0;
   if(!itemValue) {
        Swal.fire({
            title: 'Error!',
            text: "Please input item cost first.",
            icon: 'error',
        });
        return;
   }
   let rowCount = $(e.target).closest('button').attr('data-row-count') || 0;
    let td = e.target.closest('td');
    let totalAmnt = 0;
    if ($(td).find('[name*=amnt]').length) {
        let tr = '';
        $(td).find('[name*=amnt]').each(function(index, item) {
         const indexCount = index + 1;
         const bomOverheadId = $(`tr[id="row_${rowCount}"] [name="components[${rowCount}][overhead][${indexCount}][id]"]`).val();
         const overheadId = $(`tr[id="row_${rowCount}"] [name="components[${rowCount}][overhead][${indexCount}][overhead_id]"]`).val();
         const description = $(`tr[id="row_${rowCount}"] [name="components[${rowCount}][overhead][${indexCount}][description]"]`).val();
         const perc = $(`tr[id="row_${rowCount}"] [name="components[${rowCount}][overhead][${indexCount}][perc]"]`).val();
         const amnt = Number(item.value).toFixed(2);
         const ledgerName = $(`tr[id="row_${rowCount}"] [name="components[${rowCount}][overhead][${indexCount}][ledger_name]"]`).val();
         const ledgerId = $(`tr[id="row_${rowCount}"] [name="components[${rowCount}][overhead][${indexCount}][ledger_id]"]`).val();
         const ledgerGroupName = $(`tr[id="row_${rowCount}"] [name="components[${rowCount}][overhead][${indexCount}][ledger_group_name]"]`).val();
         const ledgerGroupId = $(`tr[id="row_${rowCount}"] [name="components[${rowCount}][overhead][${indexCount}][ledger_group_id]"]`).val();
         totalAmnt+= Number(amnt);
         tr+= `<tr class="item_display_overhead_row">
            <td>${indexCount}</td>
            <td>
                <input type="text" id="item_overhead_input_${rowCount}_${indexCount}" placeholder="Select" name="components[${rowCount}][overhead][${indexCount}][description]" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="${description}">
                <input type="hidden" id="item_overhead_id_${rowCount}_${indexCount}" name="components[${rowCount}][overhead][${indexCount}][overhead_id]" value="${overheadId}">
                <input type="hidden" name="components[${rowCount}][overhead][${indexCount}][id]" value="${bomOverheadId}">
            </td>
            <td><input type="number" id="item_overhead_input_perc_${rowCount}_${indexCount}" class="form-control mw-100 percentage_input" name="components[${rowCount}][overhead][${indexCount}][perc]" step="any" value="${perc}"></td>
            <td><input type="number" class="form-control mw-100 ${perc ? 'disabled-input' : ''}" name="components[${rowCount}][overhead][${indexCount}][amnt]" step="any" value="${amnt}"></td>
            <td>
                <input type="text" readonly class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="" name="components[${rowCount}][overhead][${indexCount}][ledger_name]" value="${ledgerName}">
                <input type="hidden" name="components[${rowCount}][overhead][${indexCount}][ledger_id]" value="${ledgerId}">
            </td>
            <td>
                <input type="text" readonly class="form-control mw-100" name="components[${rowCount}][overhead][${indexCount}][ledger_group_name]" value="${ledgerGroupName}">
                <input type="hidden" name="components[${rowCount}][overhead][${indexCount}][ledger_group_id]" value="${ledgerGroupId}">
            </td>
            <td class="text-center">
                <a href="javascript:;" class="text-danger deleteOverheadItemRow" data-id="${bomOverheadId}">
                    <i data-feather="trash-2"></i>
                </a>
                <a href="javascript:;" class="text-primary addOverheadItemRow">
                    <i data-feather="plus-square"></i>
                </a>
            </td>
        </tr>
        `;
        });
        tr+=`<tr class="item_sub_total_row" id="item_sub_total_row_${rowCount}">
            <td colspan="2"></td>
            <td class="text-dark"><strong>Sub Total</strong></td>
            <td class="text-dark text-end"><strong id="total">0</strong></td>
            <td colspan="2"></td>
            <td class="text-center"></td>
        </tr>`;

        $("#itemOverheadTbl tbody").empty().append(tr);
        $(".item_display_overhead_row").find(".addOverheadItemRow").addClass('d-none');
        $(".item_display_overhead_row:last").find(".addOverheadItemRow").removeClass('d-none');
        $("#overheadItemPopup #itemLevelSubTotalPrice").text(itemValue.toFixed(2));
        $("#overheadItemPopup").modal('show');
        hideOverheadPopupData();
        feather.replace();
        itemOverheadeIntializeAutocomplete();
    } else {
        let indexCount = 0;
        let actionUrl = '{{route("bill.of.material.add.overhead.item.row")}}'+'?rowCount='+rowCount+'&indexCount='+indexCount;
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                if (data.status == 200) {
                    let indexCount = data.data.indexCount;
                    let rowCount = data.data.rowCount;
                    $("#itemOverheadTbl tbody").empty().append(data.data.html);
                    $("#overheadItemPopup #itemLevelSubTotalPrice").text(itemValue.toFixed(2));
                    $("#overheadItemPopup").modal('show');
                    hideOverheadPopupData();
                    feather.replace();
                    itemOverheadeIntializeAutocomplete();
                }
            });
        });
    }
});

// setTimeout(() => {
//    initLedger();
// },10);

/*Ledger Select*/
function initLedger()
{
   if($("[name='overhead_ledger']").length || $("[name='item_overhead_ledger']").length) {
      $("[name='overhead_ledger'], [name='item_overhead_ledger']").each(function(index,itemEle) {
      let appendToSelector = $(itemEle).closest("#overheadItemPopup").length ? "#overheadItemPopup" : "#overheadSummaryPopup";
      $(itemEle).autocomplete({
       source: function(request, response) {
           $.ajax({
               url: '/search',
               method: 'GET',
               dataType: 'json',
               data: {
                   q: request.term,
                   type:'ledger',
               },
               success: function(data) {
                   response($.map(data, function(item) {
                       return {
                           id: item.id,
                           label: item.name,
                           code: item.code,
                           name: item.name,
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
           let itemName = ui.item.label;
           $input.val(itemName);
       },
       appendTo: appendToSelector,
       change: function(event, ui) {
           if (!ui.item) {
               $(this).val("");
           }
       }
      }).focus(function() {
          if (this.value === "") {
              $(this).autocomplete("search", "");
          }
      });
      });
   }
}

/*Display item detail*/
$(document).on('input change focus', '#itemTable tr input', (e) => {
   let currentTr = e.target.closest('tr');
   let pName = $(currentTr).find("[name*='component_item_name']").val();
   let itemId = $(currentTr).find("[name*='item_id']").val();
   let bomDetailId = $(currentTr).find("[name*='bom_detail_id']").val();
   if (itemId) {
      let selectedAttr = [];
      $(currentTr).find("[name*='attr_name']").each(function(index, item) {
         if($(item).val()) {
            selectedAttr.push($(item).val());
         }
      });

      let sectionName = $(currentTr).find("[name*='[section_name]']").val() || '';
      let subSectionName = $(currentTr).find("[name*='[sub_section_name]']").val() || '';
      let stationName = $(currentTr).find("[name*='[station_name]']").val() || '';

      let remark = '';
      if($(currentTr).find("[name*='remark']")) {
       remark = $(currentTr).find("[name*='remark']").val() || '';
      }

    // Norms
    let qty_per_unit = $(currentTr).find("[name*='[qty_per_unit]']").val() || '';
    let total_qty = $(currentTr).find("[name*='[total_qty]']").val() || '';
    let std_qty = $(currentTr).find("[name*='[std_qty]']").val() || '';
      let actionUrl = '{{route("bill.of.material.get.itemdetail")}}'+'?item_id='+itemId+'&selectedAttr='+JSON.stringify(selectedAttr)+'&remark='+remark+'&section_name='+sectionName+'&sub_section_name='+subSectionName+'&station_name='+stationName+'&qty_per_unit='+qty_per_unit+'&total_qty='+total_qty+'&std_qty='+std_qty+'&bom_detail_id='+bomDetailId;
      fetch(actionUrl).then(response => {
         return response.json().then(data => {
            if(data.status == 200) {
               $(".item_detail_row").remove();
               if($("#itemDetailTable tbody tr").length > 2) {
                  $("#itemDetailTable tbody tr").slice(-2).remove();
               }
               $("#itemDetailTable tbody tr:first").after(data.data.html);
            }
         });
      });
   }
});

setTimeout(() => {
   initializeStationAutocomplete();
   initializeProductSectionAutocomplete();

   $("[name*='[section_id]']").each(function(index, item){
      subSection($(item).val(), item);
   });

},10);
// Function to initialize the product section autocomplete
function initializeProductSectionAutocomplete() {
    $("[name*='product_section']").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type: 'product_section'
                },
                success: function (data) {
                    const mappedData = $.map(data, function (item) {
                        return {
                            id: item.id,
                            label: item.name,
                        };
                    });
                    response(mappedData);

                },
                error: function (xhr) {
                    console.error('Error fetching data:', xhr.responseText);
                }
            });
        },
        minLength: 0,
        select: function (event, ui) {
            $(this).val(ui.item.label);
            $(this).closest('td').find("[name*='[section_id]']").val(ui.item.id);
            $(this).closest('td').find("[name*='[section_name]']").val(ui.item.label);
            subSection(ui.item.id, this);
            return false;
        },
        change: function (event, ui) {
            if (!ui.item) {
               $(this).val("");
               $(this).closest('td').find("[name*='[section_id]']").val("");
               $(this).closest('td').find("[name*='[section_name]']").val("");
               $(this).closest('tr').find("[name*='[sub_section_id]']").val('');
               $(this).closest('tr').find("[name*='[sub_section_name]']").val('');
            }
        },
        // appendTo: "#addSectionItemPopup"
    }).focus(function () {
        if (this.value === "") {
            $(this).val("");
            $(this).closest('td').find("[name*='[section_id]']").val("");
            $(this).closest('td').find("[name*='[section_name]']").val("");
            $(this).closest('tr').find("[name*='[sub_section_id]']").val('');
            $(this).closest('tr').find("[name*='[sub_section_name]']").val('');
            $(this).autocomplete("search", "");
        }
    });
}

// Function to initialize sub-section autocomplete
function subSection(id, thisObj) {
    $(thisObj).closest('tr').find("[name='product_sub_section']").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type: 'product_sub_section',
                    id: id
                },
                success: function (data) {
                  const mappedData = $.map(data, function (item) {
                        return {
                            id: item.id,
                            label: item.name,
                        };
                    });
                    response(mappedData);
                },
                error: function (xhr) {
                    console.error('Error fetching data:', xhr.responseText);
                }
            });
        },
        minLength: 0,
        select: function (event, ui) {
            $(this).val(ui.item.label);
            $(this).closest('td').find("[name*='[sub_section_id]']").val(ui.item.id);
            $(this).closest('td').find("[name*='[sub_section_name]']").val(ui.item.label);
            return false;
        },
        change: function (event, ui) {
            if (!ui.item) {
                $(this).val("");
                $(this).closest('td').find("[name*='[sub_section_id]']").val("");
                $(this).closest('td').find("[name*='[sub_section_name]']").val("");
            }
        },
        // appendTo: "#addSectionItemPopup"
    }).focus(function () {
        if (this.value === "") {
            $(this).val("");
            $(this).closest('td').find("[name*='[sub_section_id]']").val("");
            $(this).closest('td').find("[name*='[sub_section_name]']").val("");
            $(this).autocomplete("search", "");
        }
    });
}
subSection();
function subSectionInstruction(id, thisObj) {
    $(thisObj).closest('tr').find("[name='instruction_sub_section']").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type: 'product_sub_section',
                    id: id
                },
                success: function (data) {
                  const mappedData = $.map(data, function (item) {
                        return {
                            id: item.id,
                            label: item.name,
                        };
                    });
                    response(mappedData);
                },
                error: function (xhr) {
                    console.error('Error fetching data:', xhr.responseText);
                }
            });
        },
        minLength: 0,
        select: function (event, ui) {
            $(this).val(ui.item.label);
            $(this).closest('td').find("[name*='[sub_section_id]']").val(ui.item.id);
            $(this).closest('td').find("[name*='[sub_section_name]']").val(ui.item.label);
            return false;
        },
        change: function (event, ui) {
            if (!ui.item) {
                $(this).val("");
                $(this).closest('td').find("[name*='[sub_section_id]']").val("");
                $(this).closest('td').find("[name*='[sub_section_name]']").val("");
            }
        },
        // appendTo: "#addSectionItemPopup"
    }).focus(function () {
        if (this.value === "") {
            $(this).val("");
            $(this).closest('td').find("[name*='[sub_section_id]']").val("");
            $(this).closest('td').find("[name*='[sub_section_name]']").val("");
            $(this).autocomplete("search", "");
        }
    });
}
function initializeInstructionProductSectionAutocomplete() {
    $("[name*='instruction_section']").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type: 'product_section'
                },
                success: function (data) {
                    const mappedData = $.map(data, function (item) {
                        return {
                            id: item.id,
                            label: item.name,
                        };
                    });
                    response(mappedData);

                },
                error: function (xhr) {
                    console.error('Error fetching data:', xhr.responseText);
                }
            });
        },
        minLength: 0,
        select: function (event, ui) {
            $(this).val(ui.item.label);
            $(this).closest('td').find("[name*='[section_id]']").val(ui.item.id);
            $(this).closest('td').find("[name*='[section_name]']").val(ui.item.label);
            subSectionInstruction(ui.item.id, this);
            return false;
        },
        change: function (event, ui) {
            if (!ui.item) {
               $(this).val("");
               $(this).closest('td').find("[name*='[section_id]']").val("");
               $(this).closest('td').find("[name*='[section_name]']").val("");
               $(this).closest('tr').find("[name*='[sub_section_id]']").val('');
               $(this).closest('tr').find("[name*='[sub_section_name]']").val('');
            }
        },
        // appendTo: "#addSectionItemPopup"
    }).focus(function () {
        if (this.value === "") {
            $(this).val("");
            $(this).closest('td').find("[name*='[section_id]']").val("");
            $(this).closest('td').find("[name*='[section_name]']").val("");
            $(this).closest('tr').find("[name*='[sub_section_id]']").val('');
            $(this).closest('tr').find("[name*='[sub_section_name]']").val('');
            $(this).autocomplete("search", "");
        }
    });
}
initializeInstructionProductSectionAutocomplete();

// Function to initialize the product section autocomplete
function initializeStationAutocomplete() {
    $("[name*='product_station']").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type: 'station',
                    production_route_id: $("[name='production_route_id']").val() || '',
                },
                success: function (data) {
                    const mappedData = $.map(data, function (item) {
                        return {
                            id: item.id,
                            label: item.name,
                        };
                    });
                    response(mappedData);

                },
                error: function (xhr) {
                    console.error('Error fetching data:', xhr.responseText);
                }
            });
        },
        minLength: 0,
        select: function (event, ui) {
            $(this).val(ui.item.label);
            $(this).closest('td').find("[name*='[station_id]']").val(ui.item.id);
            $(this).closest('td').find("[name*='[station_name]']").val(ui.item.label);
            return false;
        },
        change: function (event, ui) {
            if (!ui.item) {
               $(this).val("");
               $(this).closest('td').find("[name*='[station_id]']").val("");
               $(this).closest('td').find("[name*='[station_name]']").val("");
            }
        },
        // appendTo: "#addSectionItemPopup"
    }).focus(function () {
        if (this.value === "") {
            $(this).closest('td').find("[name*='[station_id]']").val("");
            $(this).closest('td').find("[name*='[station_name]']").val("");
            $(this).autocomplete("search", "");
        }
    });
}
initializeStationAutocomplete();

setTimeout(() => {
    $("#itemTable3 tbody tr").each(function(index,item) {
        let sectionId = $(item).find("[name*='[section_id]']").val() || '';
        let subSection = $(item).find('[name*="instruction_sub_section"]');
        if (sectionId)  {
            subSectionInstruction(sectionId, subSection);
        }
    });
}, 100);

function initializeVendorAutocomplete() {
    $("[name*='product_vendor']").autocomplete({
        source: function (request, response) {
            const $input = this.element;
            const $row = $input.closest('tr');
            let itemId = $row.find("[name*='item_id']").val() || '';
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type: 'vendor_list',
                    item_id: itemId
                },
                success: function (data) {
                    const mappedData = $.map(data, function (item) {
                        return {
                            id: item.id,
                            label: item.company_name,
                        };
                    });
                    response(mappedData);

                },
                error: function (xhr) {
                    console.error('Error fetching data:', xhr.responseText);
                }
            });
        },
        minLength: 0,
        select: function (event, ui) {
            $(this).val(ui.item.label);
            $(this).closest('td').find("[name*='[vendor_id]']").val(ui.item.id);
            return false;
        },
        change: function (event, ui) {
            if (!ui.item) {
               $(this).val("");
               $(this).closest('td').find("[name*='[vendor_id]']").val("");
            }
        }
    }).focus(function () {
        if (this.value === "") {
            $(this).closest('td').find("[name*='[vendor_id]']").val("");
            $(this).autocomplete("search", "");
        }
    }).on("input", function () {
        if ($(this).val().trim() === "") {
            $(this).removeData("selected");
            $(this).closest('tr').find("input[name*='vendor_id']").val('');
        }
    });
}
initializeVendorAutocomplete();

function initializeInstructionStationAutocomplete() {
    $("#itemTable3 [name*='instruction_station']").autocomplete({
        source: function (request, response) {

            // let selectedIds = [];
            // let currentTab = document.querySelector(".nav-link.active").getAttribute("data-bs-target").replace("#", "");
            // if(currentTab === 'instruction-items') {
            //     if ($("#itemTable3 tbody").find('tr').length) {
            //         $("#itemTable3 tbody tr").each(function() {
            //             let stationInput = $(this).find("[name*='[station_id]']");
            //             let stationId = stationInput.val();
            //             if (stationId) {
            //                 selectedIds.push(stationId);
            //             }
            //         });
            //     }
            // }
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type: 'station',
                    production_route_id: $("[name='production_route_id']").val() || '',
                    // selectedIds : JSON.stringify(selectedIds)
                },
                success: function (data) {
                    const mappedData = $.map(data, function (item) {
                        return {
                            id: item.id,
                            label: item.name,
                        };
                    });
                    response(mappedData);
                },
                error: function (xhr) {
                    console.error('Error fetching data:', xhr.responseText);
                }
            });
        },
        minLength: 0,
        select: function (event, ui) {
            $(this).val(ui.item.label);
            $(this).closest('td').find("[name*='[station_id]']").val(ui.item.id);
            $(this).closest('td').find("[name*='[station_name]']").val(ui.item.label);
            return false;
        },
        change: function (event, ui) {
            if (!ui.item) {
               $(this).val("");
               $(this).closest('td').find("[name*='[station_id]']").val("");
               $(this).closest('td').find("[name*='[station_name]']").val("");
            }
        },
        // appendTo: "#addSectionItemPopup"
    }).focus(function () {
        if (this.value === "") {
            $(this).closest('td').find("[name*='[station_id]']").val("");
            $(this).closest('td').find("[name*='[station_name]']").val("");
            $(this).autocomplete("search", "");
        }
    });
}
initializeInstructionStationAutocomplete();
/*Delete server side rows*/
$(document).on('click', '#deleteItemConfirmOverhead', (e) => {
    let ids = JSON.parse(e.target.getAttribute('data-ids'));
    let rowCount = e.target.getAttribute('data-row-count') || 1;
    let itemValue = Number($(`tr[id="row_${rowCount}"]`).find('input[name*="[item_value]"]').val()) || 0;
    let storedIds = JSON.parse(localStorage.getItem('deletedItemOverheadIds')) || [];
    let mergedIds = [...new Set([...ids, ...storedIds])];
    localStorage.setItem('deletedItemOverheadIds', JSON.stringify(mergedIds));
    $("#deleteItemOverheadModal").modal('hide');
    if (ids.length) {
        ids.forEach((id) => {
            $(`#overheadItemPopup [value='${id}']`).closest('tr').remove();
        });
    }
    hideOverheadPopupData();
    $(".item_display_overhead_row").find(".addOverheadItemRow").addClass('d-none');
    $(".item_display_overhead_row:last").find(".addOverheadItemRow").removeClass('d-none');
    if(!$("#overheadItemPopup").find(".item_display_overhead_row").length) {
        let indexCount = 0;
        let actionUrl = '{{route("bill.of.material.add.overhead.item.row")}}'+'?rowCount='+rowCount+'&indexCount='+indexCount;
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                if (data.status == 200) {
                    let indexCount = data.data.indexCount;
                    let rowCount = data.data.rowCount;
                    $("#itemOverheadTbl tbody").empty().append(data.data.html);
                    $("#overheadItemPopup #itemLevelSubTotalPrice").text(itemValue.toFixed(2));
                    $("#overheadItemPopup").modal('show');
                    hideOverheadPopupData();
                    feather.replace();
                    itemOverheadeIntializeAutocomplete();
                    $(".item_display_overhead_row").find(".addOverheadItemRow").addClass('d-none');
                    $(".item_display_overhead_row:last").find(".addOverheadItemRow").removeClass('d-none');
                }
            });
        });
    }
});



/*Delete server side rows*/
$(document).on('click','#deleteConfirmOverhead', (e) => {
   let ids = e.target.getAttribute('data-ids');
   ids = JSON.parse(ids);

   let storedIds = JSON.parse(localStorage.getItem('deletedHeaderOverheadIds')) || [];
   let mergedIds = [...new Set([...ids, ...storedIds])];

    localStorage.setItem('deletedHeaderOverheadIds', JSON.stringify(mergedIds));
    $("#deleteOverheadModal").modal('hide');
    if(ids.length) {
        ids.forEach((id,index) => {
            let tr = $(`.deleteOverheadRow[data-id='${id}']`).closest('tr');
            if(tr.length) {
                let dataLevel = tr.attr('data-level');
                let dataLevelRows = $(`.display_overhead_row[data-level='${dataLevel}']`);
                if(dataLevelRows.length == 1) {
                    $("tr[id='sub_total_row_"+dataLevel+"']").remove();
                }
                tr.remove();
            }
        });
    }
    setTableCalculation();
});

/*Amendment modal open*/
$(document).on('click', '.amendmentBtn', (e) => {
   $("#amendmentconfirm").modal('show');
});

/*Open amendment popup*/
$(document).on('click', '#amendmentBtn', (e) => {
    $("#amendmentModal").modal('show');
});

$(document).on('click', '#amendmentSubmit', (e) => {
let url = new URL(window.location.href);
url.search = '';
url.searchParams.set('amendment', 1);
let amendmentUrl = url.toString();
window.location.replace(amendmentUrl);
});

/*Get Item Cost*/
function getBomItemCost(itemId,itemAttributes)
{
    let type = '{{$servicesBooks['services'][0]?->alias}}';
    let rowCount = $("tr[id*='row_'].trselected").attr('data-index');
    let uom_id = $(`select[name='components[${rowCount}][uom_id]']`).val();
    let actionUrl = '{{route("bill.of.material.get.item.cost")}}'+'?item_id='+itemId+'&itemAttributes='+JSON.stringify(itemAttributes)+'&uom_id='+uom_id+'&type='+type;
   fetch(actionUrl).then(response => {
      return response.json().then(data => {
         if (data.status == 200) {
            if(data.data.cost) {
               $("tr.trselected").find("[name*='[item_cost]']").val((data.data.cost).toFixed(2));
               if(data.data.route) {
                   $("tr.trselected .linkAppend").removeClass('d-none');
                   $("tr.trselected .linkAppend a").attr('href', data.data.route);
               }
            } else {
               $("tr.trselected .linkAppend").addClass('d-none');
               $("tr.trselected").find("[name*='[item_cost]']").val(Number(0).toFixed(2));
            }
         } else {
            $("tr.trselected .linkAppend").addClass('d-none');
            $("tr.trselected").find("[name*='[item_cost]']").val(Number(0).toFixed(2));
         }
      });
   });
}

$(document).on('click', '.submit_attribute', (e) => {
    $("#attribute").modal('hide');
});
// # Revision Number On Chage
$(document).on('change', '#revisionNumber', (e) => {
    let actionUrl = location.pathname + '?revisionNumber='+e.target.value;
    let revision_number = Number("{{$revision_number}}");
    let revisionNumber = Number(e.target.value);
    if(revision_number == revisionNumber) {
        location.href = actionUrl;
    } else {
        window.open(actionUrl, '_blank');
    }
});

/*Amendment btn submit*/
$(document).on('click', '#amendmentBtnSubmit', (e) => {
    let remark = $("#amendmentModal").find('[name="amend_remarks"]').val();
    if(!remark) {
        e.preventDefault();
        $("#amendRemarkError").removeClass("d-none");
        return false;
    } else {
        $("#amendmentModal").modal('hide');
        $("#amendRemarkError").addClass("d-none");
        e.preventDefault();
        $("#BomEditForm").submit();
    }
});

// Revoke Document
$(document).on('click', '#revokeButton', (e) => {
    let actionUrl = '{{ route("bill.of.material.revoke.document") }}'+ '?id='+'{{$bom->id}}';
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

/*Open Pr model*/
$(document).on('click', '.prSelect', (e) => {
    $("#prModal").modal('show');
    openBomRequest();
    getBoms();
});

/*searchPiBtn*/
$(document).on('click', '.searchPiBtn', (e) => {
    getBoms();
});

function openBomRequest()
{
    initializeAutocompleteQt("book_code_input_qt", "book_id_qt_val", "book_bom", "book_code", "");
    initializeAutocompleteQt("document_no_input_qt", "document_id_qt_val", "bom_document_qt", "document_number", "");
    initializeAutocompleteQt("item_name_input_qt", "item_id_qt_val", "header_item", "item_code", "item_name");
    initializeAutocompleteQt("department_po", "department_id_po", "department", "name", "");
    initializeAutocompleteQt("customer_po", "customer_id_po", "customer", "customer_code", "company_name");
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
        appendTo : '#prModal',
        minLength: 0,
        select: function(event, ui) {
            var $input = $(this);
            $input.val(ui.item.label);
            $("#" + selectorSibling).val(ui.item.id);
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
    });
}

function getBoms()
{
    let header_book_id = $("#book_id").val() || '';
    let series_id = $("#book_id_qt_val").val() || '';
    let document_number = $("#document_no_input_qt").val() || '';
    let item_id = $("#item_id_qt_val").val() || '';
    let department_id = $("#department_id_po").val() || '';
    let customerId = $("#customer_id_po").val() || '';
    let actionUrl = '{{ route("bill.of.material.get.quote.bom") }}';
    let fullUrl = `${actionUrl}?series_id=${encodeURIComponent(series_id)}&document_number=${encodeURIComponent(document_number)}&item_id=${encodeURIComponent(item_id)}&header_book_id=${encodeURIComponent(header_book_id)}&department_id=${encodeURIComponent(department_id)}&customer_id=${customerId}`;
    fetch(fullUrl).then(response => {
        return response.json().then(data => {
            $(".po-order-detail #prDataTable").empty().append(data.data.pis);
        });
    });
}

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

$(document).on('click', '.prProcess', (e) => {
    let ids = getSelectedPiIDS();
    if (!ids.length) {
        $("[name='quote_bom_id']").val('');
        $("#prModal").modal('hide');
        Swal.fire({
            title: 'Error!',
            text: 'Please select at least one one quotation',
            icon: 'error',
        });
        return false;
    }
    if (ids.length > 1) {
        $("[name='quote_bom_id']").val('');
        $("#prModal").modal('hide');
        Swal.fire({
            title: 'Error!',
            text: 'One time you can one process.',
            icon: 'error',
        });
        return false;
    }
    $("[name='quote_bom_id']").val(ids);

    ids = JSON.stringify(ids);
    let d_date = $("input[name='document_date']").val() || '';
    let book_id = $("#book_id").val() || '';
    let type = '{{ $servicesBooks['services'][0]?->alias }}';
    let actionUrl = '{{ route("bill.of.material.process.bom-item") }}'+'?ids=' + ids+'&type='+type+'&d_date='+d_date+'&book_id='+book_id;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if(data.status == 200) {
                $("#item_code").val(data.data.bom.item_code);
                $("#head_item_name").val(data.data.bom.item_name);
                $("#head_item_id").val(data.data.bom.item_id);
                $("#head_uom_id").val(data.data.bom.uom.id);
                $("#head_uom_name").val(data.data.bom.uom.name);
                $("#safety_buffer_perc").val(data?.data?.bom?.safety_buffer_perc);
                $(".heaer_item").remove();
                if($(".customer_div").length) {
                    $(".customer_div").before(data.data.headerAttrHtml);
                } else {
                    $(".production_type_div").before(data.data.headerAttrHtml);
                }
                $("#overheadSummaryFooter").remove();
                $("#headerOverheadTbl tbody").html(data.data.headerOverhead);
                $("#itemTable .mrntableselectexcel").empty().append(data.data.pos);
                $("#itemTable3 .mrntableselectexcel").empty().append(data.data.instructionHtml);
                $("#prModal").modal('hide');
                $(".prSelect").prop('disabled',true);
                $("table input[name*='superceeded_cost']").trigger('change');
                $('#itemTable > tbody .form-check-input').removeAttr('data-id');
                if(data?.data?.bom?.production_route_id) {
                    $("#production_route_id").val(data?.data?.bom?.production_route_id).trigger('change');
                }
                setTableCalculation();
                initializeAutocomplete2(".comp_item_code");
                overheadeIntializeAutocomplete();
                feather.replace();
                focusAndScrollToLastRowInput();
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

function initializeAutocompleteTED(selector, idSelector, nameSelector, type, percentageVal) {
    let context = '';
    if(!selector.includes('perc')) {
        if(selector.includes('overhead_input_')) {
            context = '#overheadSummaryPopup ';
        }
        if(selector.includes('item_overhead_input_')) {
            context = '#overheadItemPopup ';
        }

        const newSelector = context + '#' + selector;
        idSelector = context + '#' + idSelector;
        nameSelector = context + '#' + nameSelector;
        percentageVal = context + '#' + percentageVal;

        $(newSelector).autocomplete({
            source: function(request, response) {
                const ids = [];
                if(context.includes('overheadSummaryPopup')) {
                    $('.display_overhead_row[data-level]').each(function(index,item){
                        let tedId = $(item).find("input[id*='overhead_id_']").val() || '';
                        if(tedId) {
                            ids.push(tedId);
                        }
                    });
                }
                if(context.includes('overheadItemPopup')) {
                    let inputId = selector;
                    let match = inputId.match(/item_overhead_input_(\d+)_(\d+)/);
                    if (match) {
                        let rowCount = parseInt(match[1]);
                        $(`#overheadItemPopup input[name*="[overhead_id]"]`).each(function(index,item) {
                        let tedId = $(item).val() || '';
                        if(tedId) {
                            ids.push(tedId);
                        }
                    });
                    }
                }
                $.ajax({
                    url: '/search',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        q: request.term,
                        type:type,
                        ids: JSON.stringify(ids)
                    },
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                id: item.id,
                                label: `${item.name}`,
                                percentage: `${item.perc}`,
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
                var itemName = ui.item.label;
                var itemId = ui.item.id;
                var itemPercentage = ui.item.percentage;
                $input.val(itemName);
                $(idSelector).val(itemId);
                $(nameSelector).val(itemName);
                $(percentageVal).val(itemPercentage).trigger('keyup');
                if(context.includes('overheadItemPopup')) {
                    hideOverheadPopupData();
                }
                return false;
            },
            change: function(event, ui) {
                if (!ui.item) {
                    $(this).val("");
                    $(idSelector).val("");
                    $(nameSelector).val("");
                    if(newSelector.includes('overheadItemPopup')) {
                        let inputId = $(this).attr('id');
                        let match = inputId.match(/item_overhead_input_(\d+)_(\d+)/)
                        if(match) {
                            let rowCount = parseInt(match[1]);
                            let indexCount = parseInt(match[1]);
                            $(`tr[id="row_${rowCount}"] input[name='components[${rowCount}][overhead][${indexCount}][overhead_id]']`).val('');
                            $(`tr[id="row_${rowCount}"] input[name='components[${rowCount}][overhead][${indexCount}][description]']`).val('');
                        }
                        hideOverheadPopupData();
                    }
                }
            }
        }).focus(function() {
            if (this.value === "") {
                $(this).autocomplete("search", "");
            }
        }).on("input", function () {
            if ($(this).val().trim() === "") {
                $(this).removeData("selected");
                $(this).closest('tr').find("input[id*='overhead_input_perc_']").val('').trigger('keyup');
                $(this).closest('tr').find("input[id*='overhead_id']").val('');
                if(newSelector.includes('overheadItemPopup')) {
                    let inputId = $(this).attr('id');
                    let match = inputId.match(/item_overhead_input_(\d+)_(\d+)/)
                    if(match) {
                        let rowCount = parseInt(match[1]);
                        let indexCount = parseInt(match[1]);
                        $(`tr[id="row_${rowCount}"] input[name='components[${rowCount}][overhead][${indexCount}][overhead_id]']`).val('');
                        $(`tr[id="row_${rowCount}"] input[name='components[${rowCount}][overhead][${indexCount}][description]']`).val('');
                    }
                    hideOverheadPopupData();
                }
            }
        });
    }
}

// Hide item level overhead popup id
function hideOverheadPopupData() {
    let inputId = $("#overheadItemPopup").find("input[id*='item_overhead_input_']:first").attr('id');
    if(inputId) {
        let match = inputId.match(/item_overhead_input_(\d+)_(\d+)/);
        let rowCount = parseInt(match[1]);
        let hiddenRow = '';
        $(".item_display_overhead_row").each(function(index, item) {
            const indexCount = index + 1;
            const bomOverheadId = $(item).find("input[name*='[id]']").val() || '';
            const description = $(item).find("input[id*='item_overhead_input_']").val() || '';
            const overheadId = $(item).find("input[id*='item_overhead_id_']").val() || '';
            const overheadAmnt = $(item).find("input[name*='[amnt]']").val() || '';
            const overheadPerc = $(item).find("input[id*='item_overhead_input_perc_']").val() || '';
            const overheadLedgerName = $(item).find("input[name*='[ledger_name]']").val() || '';
            const overheadLedgerId = $(item).find("input[name*='[ledger_id]']").val() || '';
            const overheadLedgerGroupName = $(item).find("input[name*='[ledger_group_name]']").val() || '';
            const overheadLedgerGroupId = $(item).find("input[name*='[ledger_group_name]']").val() || '';
            hiddenRow += `
            <input type="hidden" name="components[${rowCount}][overhead][${indexCount}][id]" value="${bomOverheadId}">
            <input type="hidden" id="item_overhead_id_${rowCount}_${indexCount}" name="components[${rowCount}][overhead][${indexCount}][overhead_id]" value="${overheadId}">
            <input type="hidden" id="item_overhead_input_${rowCount}_${indexCount}" name="components[${rowCount}][overhead][${indexCount}][description]" value="${description}">
            <input type="hidden" name="components[${rowCount}][overhead][${indexCount}][perc]" value="${overheadPerc}">
            <input type="hidden" name="components[${rowCount}][overhead][${indexCount}][amnt]" value="${overheadAmnt}">
            <input type="hidden" name="components[${rowCount}][overhead][${indexCount}][ledger_name]" value="${overheadLedgerName}">
            <input type="hidden" name="components[${rowCount}][overhead][${indexCount}][ledger_id]" value="${overheadLedgerId}">
            <input type="hidden" name="components[${rowCount}][overhead][${indexCount}][ledger_group_name]" value="${overheadLedgerGroupName}">
            <input type="hidden" name="components[${rowCount}][overhead][${indexCount}][ledger_group_id]" value="${overheadLedgerGroupId}">
            `;
        });
        $("tr[id='row_"+rowCount+"']").find(`input[name*="[overhead]"]`).remove();
        $("tr[id='row_"+rowCount+"']").find(`input[name*="[overhead_amount]"]`).after(hiddenRow);
        setTableCalculation();
    }
}

function overheadeIntializeAutocomplete() {
    $("input[name*='][description]']").each(function(index, item){
        let inputId = $(item).attr('id');
        let match = inputId.match(/overhead_input_(\d+)_(\d+)/);
        if (match) {
            let levelCount = parseInt(match[1]);
            let rowCount = parseInt(match[2]);
            initializeAutocompleteTED(inputId, `overhead_id_${levelCount}_${rowCount}`, `overhead_name_${levelCount}_${rowCount}`, 'overhead_master', `overhead_input_perc_${levelCount}_${rowCount}`);
        }
    });
}
function itemOverheadeIntializeAutocomplete() {
    $("#overheadItemPopup input[name*='][description]']").each(function(index, item){
        let inputId = $(item).attr('id');
        let match = inputId.match(/item_overhead_input_(\d+)_(\d+)/);
        if (match) {
            let rowCount = parseInt(match[1]);
            let indexCount = parseInt(match[2]);
            initializeAutocompleteTED(inputId, `item_overhead_id_${rowCount}_${indexCount}`, `item_overhead_name__${rowCount}_${indexCount}`, 'overhead_master', `item_overhead_input_perc_${rowCount}_${indexCount}`);
        }
    });
}
$(document).on('click', '.addOverHeadSummaryBtn', (e) => {
    overheadeIntializeAutocomplete();
});

// Add overhead level header level
$(document).on('click', '.addOverheadLevel', (e) => {
    e.preventDefault();
    let currentRow = $(e.target).closest('tr').prev();
    let checkValidation = validateOverheadRow(currentRow);
    if(!checkValidation) {
        return false;
    }
    let ids = [];
    $('.display_overhead_row[data-level]').each(function(index,item){
        let tedId = $(item).find("input[id*='overhead_id_']").val() || '';
        if(tedId) {
            ids.push(tedId);
        }
    });
    let levelCount = $(".addOverheadLevel").length;
    let actionUrl = '{{ route("bill.of.material.add.overhead.level") }}'+`?levelCount=${levelCount}&ids=${JSON.stringify(ids)}`;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if(data.status == 200) {
                let level = data.data.levelCount;
                let index = data.data.rowCount;
                $(e.target).closest('tr').after(data.data.html);
                feather.replace();
                $(e.target).closest('a').addClass('d-none');
                initializeAutocompleteTED(`overhead_input_${level}_${index}`, `overhead_id_${level}_${index}`, `overhead_name_${level}_${index}`, 'overhead_master', `overhead_input_perc_${level}_${index}`);
            } else {
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

// Add overhead row
$(document).on('click', '.addOverheadRow', (e) => {
    e.preventDefault();
    let currentRow = $(e.target).closest('tr');
    let checkValidation = validateOverheadRow(currentRow);
    if(!checkValidation) {
        // Swal.fire({
        //     title: 'Error!',
        //     text: 'Please fill all the fields',
        //     icon: 'error',
        // });
        return false;
    }
    let rowCount = currentRow.attr('data-index');
    let levelCount = currentRow.attr('data-level');

    let ids = [];
    $('.display_overhead_row[data-level]').each(function(index,item){
        let tedId = $(item).find("input[id*='overhead_id_']").val() || '';
        if(tedId) {
            ids.push(tedId);
        }
    });

    let actionUrl = '{{ route("bill.of.material.add.overhead.row") }}'+`?levelCount=${levelCount}&rowCount=${rowCount}&ids=${JSON.stringify(ids)}`;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if(data.status == 200) {
                let level = data.data.levelCount;
                let index = data.data.rowCount;
                $(e.target).closest('tr').after(data.data.html);
                feather.replace();
                $(e.target).closest('a').addClass('d-none');
                initializeAutocompleteTED(`overhead_input_${level}_${index}`, `overhead_id_${level}_${index}`, `overhead_name_${level}_${index}`, 'overhead_master', `overhead_input_perc_${level}_${index}`);
            } else {
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

// Add overhead item level row
$(document).on('click', '.addOverheadItemRow', (e) => {
    e.preventDefault();
    let currentRow = $(e.target).closest('tr');
    let checkValidation = validateOverheadRow(currentRow);
    let itemValue = currentRow.find("[name*='[item_value]']").val() || 0;
    if(!checkValidation) {
        // Swal.fire({
        //     title: 'Error!',
        //     text: 'Please fill all the fields',
        //     icon: 'error',
        // });
        return false;
    }
    let inputId = currentRow.find('input[id*="item_overhead_input_"]').attr('id');

    let match = inputId.match(/item_overhead_input_(\d+)_(\d+)/);
    let rowCount = parseInt(match[1]);
    let ids = [];
    $('.item_display_overhead_row').each(function(index,item){
        let tedId = $(item).find("input[id*='overhead_id_']").val() || '';
        if(tedId) {
            ids.push(tedId);
        }
    });

    let indexCount = $("tr.item_display_overhead_row").length;
    let actionUrl = '{{route("bill.of.material.add.overhead.item.row")}}'+'?rowCount='+rowCount+'&indexCount='+indexCount;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
                let indexCount = data.data.indexCount;
                let rowCount = data.data.rowCount;

                let $tbody = $("#itemOverheadTbl tbody");
                if ($tbody.find("tr.item_display_overhead_row").length > 0) {
                    $tbody.find("tr.item_display_overhead_row:last").after(data.data.html);
                } else {
                    $tbody.append(data.data.html);
                }
                hideOverheadPopupData();
                feather.replace();
                $(e.target).closest('a').addClass('d-none');
                initializeAutocompleteTED(`item_overhead_input_${rowCount}_${indexCount}`, `item_overhead_id_${rowCount}_${indexCount}`, `item_overhead_name_${rowCount}_${indexCount}`, 'overhead_master', `item_overhead_input_perc_${rowCount}_${indexCount}`);
                $(".item_display_overhead_row").find(".addOverheadItemRow").addClass('d-none');
                $(".item_display_overhead_row:last").find(".addOverheadItemRow").removeClass('d-none');
            } else {
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

// Delete overhead row
$(document).on('click', '.deleteOverheadRow', (e) => {
    e.preventDefault();
    // edit case
    let dataId = Number($(e.target).closest('a').attr('data-id')) || '';
    if(dataId) {
        let editItemIds = [dataId];
        $("#deleteOverheadModal").find("#deleteConfirmOverhead").attr('data-ids',JSON.stringify(editItemIds));
        $("#deleteOverheadModal").modal('show');
        return false;
    }
    let currentTd = $(e.target).closest('td');
    let currentRow = $(e.target).closest('tr');
    if($("tr.display_overhead_row[data-level]").length === 1) {
        let firstTd = currentRow.find('td').first();
        firstTd.text(1);
        currentRow.attr('data-index', 1);
        currentRow.attr('data-level', 1);
        currentRow.find('input').val('');
    } else {
        let previousRow = currentRow.prev();
        let nextRow = currentRow.next();
        let rowCount = currentRow.attr('data-index');
        let levelCount = currentRow.attr('data-level');
        currentRow.remove();
        if (!currentTd.find('.addOverheadRow').hasClass('d-none'))  {
            let levelLength = $(`tr[data-level='${levelCount}']`).length || 0;
            if(!levelLength) {
                nextRow.remove();
                previousRow.find('.addOverheadLevel').removeClass('d-none');
            } else {
                if (levelLength === 1) {
                    $(`tr[data-level='${levelCount}']`).find('.addOverheadRow').removeClass('d-none');
                } else {
                    previousRow.find('.addOverheadRow').removeClass('d-none');
                }
            }
        }
    }
    updateOverheadRowCount();
});

// Delete overhead item level row
$(document).on('click', '.deleteOverheadItemRow', (e) => {
    e.preventDefault();
    let currentTd = $(e.target).closest('td');
    let currentRow = $(e.target).closest('tr');
    let rowCount = currentRow.attr('data-index');
    let previousRow = currentRow.prev();
    if(Number(e.target.closest('a').getAttribute('data-id'))) {
        let editItemIds = [Number(e.target.closest('a').getAttribute('data-id'))];
        $("#deleteItemOverheadModal").find("#deleteItemConfirmOverhead").attr('data-ids',JSON.stringify(editItemIds));
        $("#deleteItemOverheadModal").find("#deleteItemConfirmOverhead").attr('data-row-count',rowCount);
        $("#deleteItemOverheadModal").modal('show');
        return false;
    }
    if($("tr.item_display_overhead_row").length === 1) {
        let firstTd = currentRow.find('td').first();
        firstTd.text(1);
        currentRow.find('input').val('');
        initializeAutocompleteTED('item_overhead_input_1_1', `item_overhead_id_1_1`, `item_overhead_name_1_1`, 'overhead_master', `item_overhead_input_perc_1_1`);
    } else {
        let nextRow = currentRow.next();
        currentRow.remove();
        previousRow.find('.addOverheadLevel').removeClass('d-none');
    }

    if($("tr.item_display_overhead_row").length === 1) {
        previousRow.find('.addOverheadItemRow').removeClass('d-none');
    }
    itemOverheadeIntializeAutocomplete();
    hideOverheadPopupData();
    $(".item_display_overhead_row").find(".addOverheadItemRow").addClass('d-none');
    $(".item_display_overhead_row:last").find(".addOverheadItemRow").removeClass('d-none');
});

function updateOverheadRowCount() {
    let rowCount = 0;
    let newLevel = 1;
    let currentLevel = null;

    $("tr.display_overhead_row[data-level]").each(function(index, row) {
        let level = Number($(row).attr('data-level'));
        if (currentLevel === null) {
            currentLevel = level;
        }
        if (level !== currentLevel) {
            newLevel++;
            currentLevel = level;
            rowCount = 0;
        }

        rowCount++;
        $(row).attr('data-index', rowCount);
        $(row).attr('data-level', newLevel);
        $(row).find('td:first').text(rowCount);
        $(row).find('input').each(function() {
            let inputName = $(this).attr('name');
            if (inputName) {
                if(inputName.includes('description')) {
                    $(this).attr('name',`components[${newLevel}][overhead][${rowCount}][description]`);
                }
                if(inputName.includes('overhead_id')) {
                    $(this).attr('name',`components[${newLevel}][overhead][${rowCount}][overhead_id]`);
                }
                if(inputName.includes('perc')) {
                    $(this).attr('name',`components[${newLevel}][overhead][${rowCount}][perc]`);
                }
                if(inputName.includes('amnt')) {
                    $(this).attr('name',`components[${newLevel}][overhead][${rowCount}][amnt]`);
                }
                if(inputName.includes('ledger_name')) {
                    $(this).attr('name',`components[${newLevel}][overhead][${rowCount}][ledger_name]`);
                }
                if(inputName.includes('ledger_id')) {
                    $(this).attr('name',`components[${newLevel}][overhead][${rowCount}][ledger_id]`);
                }
                if(inputName.includes('ledger_group_name')) {
                    $(this).attr('name',`components[${newLevel}][overhead][${rowCount}][ledger_group_name]`);
                }
                if(inputName.includes('ledger_group_id')) {
                    $(this).attr('name',`components[${newLevel}][overhead][${rowCount}][ledger_group_id]`);
                }
            }
            let inputId = $(this).attr('id');
            if (inputId) {
                let newInputId = inputId.replace(/_(\d+)_/, `_${newLevel}_`);
                newInputId = newInputId.replace(/_(\d+)$/, `_${rowCount}`);
                $(this).attr('id', newInputId);
                initializeAutocompleteTED(newInputId, `overhead_id_${newLevel}_${rowCount}`, `overhead_name_${newLevel}_${rowCount}`, 'overhead_master', `overhead_input_perc_${newLevel}_${rowCount}`);
            }
        });
    });
    $('.sub_total_row').each(function(index,item) {
        index++;
        $(this).attr('id','sub_total_row_'+index);
    })
    setTableCalculation();
}

$(document).on('keyup', '.display_overhead_row input[name*="amnt"]', (e) => {
    setTableCalculation();
});

$(document).on('keyup', '.item_display_overhead_row input[name*="perc"]', (e) => {
    let perc = Number($(e.target).val()) || 0;
    let row = $(e.target).closest('tr');
    if(perc) {
        row.find('input[name*="amnt"]').addClass('disabled-input');
    } else {
        row.find('input[name*="amnt"]').val('').removeClass('disabled-input');;
    }
    hideOverheadPopupData();
});

$(document).on('keyup', '.item_display_overhead_row input[name*="amnt"]', function (e) {
    hideOverheadPopupData();
});

$(document).on('keyup', '.display_overhead_row input[name*="perc"]', (e) => {
    let perc = Number($(e.target).val()) || 0;
    if(perc) {
        $(e.target).closest('tr').find('input[name*="amnt"]').addClass('disabled-input');
    } else {
        $(e.target).closest('tr').find('input[name*="amnt"]').val('').removeClass('disabled-input');;
    }
    setTableCalculation();
});

function validateOverheadRow($row) {
    let isValid = true;
    const requiredSelectors = [
        'input[name*="[description]"]',
        'input[name*="[overhead_id]"]',
        'input[name*="[amnt]"]',
        // 'input[name*="[ledger_id]"]',
        // 'input[name*="[ledger_group_id]"]'
    ];
    requiredSelectors.forEach(selector => {
        const $input = $row.find(selector);
        if ($input.length && ($.trim($input.val()) === "" || $input.val() === null)) {
            isValid = false;
            $input.addClass('is-invalid');
        } else {
            $input.removeClass('is-invalid');
        }
    });

    // Special validation for [amnt] to check > 0
    const $amntInput = $row.find('input[name*="[amnt]"]');
    const amntVal = parseFloat($amntInput.val());
    if (isNaN(amntVal) || amntVal <= 0) {
        isValid = false;
        $amntInput.addClass('is-invalid');
    } else {
        $amntInput.removeClass('is-invalid');
    }

    return isValid;
}
</script>
@endsection
