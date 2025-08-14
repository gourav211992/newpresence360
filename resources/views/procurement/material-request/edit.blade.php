@extends('layouts.app')
@section('content')
    <form id="mrEditForm" class="ajax-input-form" method="POST" action="{{ route('material-request.update', $mr->id) }}" data-redirect="/material-requests" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="tax_required" id="tax_required" value="">
        <div class="app-content content ">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Material Request</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="/">Home</a>
                                            </li>
                                            <li class="breadcrumb-item active">Edit</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <input type="hidden" name="document_status" value="draft" id="document_status">
                                <button type="button" onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0">
                                    <i data-feather="arrow-left-circle"></i> Back
                                </button>
                                <a href="{{ route('material-request.generate-pdf', $mr->id) }}" target="_blank" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer">
                                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                        <rect x="6" y="14" width="12" height="8"></rect>
                                    </svg>
                                    Print
                                </a>
                                @if($buttons['draft'])
                                    <button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button" name="action" value="draft">
                                        <i data-feather='save'></i> Save as Draft
                                    </button>
                                @endif
                                @if($buttons['submit'])
                                    <button type="submit" class="btn btn-primary btn-sm submit-button" name="action" value="submitted">
                                        <i data-feather="check-circle"></i> Submit
                                    </button>
                                @endif
                                @if($buttons['approve'])
                                    <button type="button" class="btn btn-primary btn-sm" id="approved-button" name="action" value="approved"><i data-feather="check-circle"></i> Approve</button>
                                    <button type="button" id="reject-button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg> Reject</button>
                                @endif
                                @if($buttons['amend'] && intval(request('amendment') ?? 0))
                                    <button type="button" class="btn btn-primary btn-sm" id="amendmentBtn"><i data-feather="check-circle"></i> Submit</button>
                                @else
                                    @if($buttons['amend'])
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#amendmentconfirm" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i> Amendment</button>
                                    @endif
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
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                                                    <div>
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 text-sm-end">
                                                <span class="badge rounded-pill badge-light-secondary forminnerstatus">
                                                Status : <span class="{{$docStatusClass}}">{{ucfirst($mr->display_status)}}</span>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Series <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="hidden" name="book_id" class="form-control" id="book_id" value="{{$mr->book_id}}" readonly>
                                                        <input readonly type="text" class="form-control" value="{{$mr->book_code}}" id="book_code">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">mr No <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" readonly value="{{@$mr->document_number}}" id="document_number">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">mr Date <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" id="document_date" name="document_date" class="form-control" value="{{date('Y-m-d')}}" >
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Reference No </label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="reference_number" value="{{@$mr->reference_number}}" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Approval History Section --}}
                                            @include('partials.approval-history', ['document_status' => $mr->document_status, 'revision_number' => $revision_number])
                                        </div>
                                    </div>
                                </div>
                                <div class="card" id="item_section">
                                    <div class="card-body customernewsection-form">
                                        <div class="border-bottom mb-2 pb-25">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader ">
                                                        <h4 class="card-title text-theme">mr Item Wise Detail</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-sm-end">
                                                    <a href="javascript:;" id="deleteBtn" class="btn btn-sm btn-outline-danger me-50">
                                                    <i data-feather="x-circle"></i> Delete</a>
                                                    <a href="javascript:;" id="addNewItemBtn" class="btn btn-sm btn-outline-primary">
                                                    <i data-feather="plus"></i> Add New Item</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="table-responsive pomrheadtffotsticky">
                                                    <table id="itemTable" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrpad">
                                                        <thead>
                                                            <tr>
                                                                <th class="customernewsection-form">
                                                                    <div class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input" id="Email">
                                                                        <label class="form-check-label" for="Email"></label>
                                                                    </div>
                                                                </th>
                                                                <th width="200px">Item</th>
                                                                <th>Attributes</th>
                                                                <th>UOM</th>
                                                                <th>Qty</th>
                                                                <th class="text-end">Rate</th>
                                                                <th class="text-end">Value</th>
                                                                <th>Discount</th>
                                                                <th class="text-end">Total</th>
                                                                <th width="100px">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrtableselectexcel">
                                                            @include('procurement.material-request.partials.item-row-edit')
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="totalsubheadpodetail">
                                                                <td colspan="6"></td>
                                                                <td class="text-end" id="totalItemValue">
                                                                    {{@$mr->items->sum('basic_value')}}
                                                                </td>
                                                                <td class="text-end" id="totalItemDiscount">
                                                                    {{@$mr->items->sum('discount_amount')}}
                                                                </td>
                                                                <td class="text-end" id="TotalEachRowAmount">
                                                                    {{@$mr->items->sum('net_value')}}
                                                                </td>
                                                            </tr>
                                                            <tr valign="top">
                                                                <td colspan="7" rowspan="10">
                                                                    <table class="table border" id="itemDetailDisplay">
                                                                        <tr>
                                                                            <td class="p-0">
                                                                                <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Item Details</strong></h6>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="poprod-decpt">
                                                                                <span class="poitemtxt mw-100"><strong>Name</strong>:
                                                                                "test"
                                                                                </span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="poprod-decpt">
                                                                                <span class="badge rounded-pill badge-light-primary"><strong>HSN</strong>:</span>
                                                                                <span class="badge rounded-pill badge-light-primary"><strong>Color</strong>:</span>
                                                                                <span class="badge rounded-pill badge-light-primary"><strong>Size</strong>:</span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="poprod-decpt">
                                                                                <span class="badge rounded-pill badge-light-primary"><strong>Inv. UOM</strong>: </span>
                                                                                <span class="badge rounded-pill badge-light-primary"><strong>Qty.</strong>:</span>
                                                                                <span class="badge rounded-pill badge-light-primary"><strong>Exp. Date</strong>: </span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="poprod-decpt">
                                                                                <span class="badge rounded-pill badge-light-primary"><strong>Ava. Stock</strong>: </span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="poprod-decpt">
                                                                                <span class="badge rounded-pill badge-light-secondary"><strong>Remarks</strong>: </span>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                                <td colspan="4">
                                                                    <table class="table border mrsummarynewsty">
                                                                        <tr>
                                                                            <td colspan="2" class="p-0">
                                                                                <h6 class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between">
                                                                                    <strong>Mr Summary</strong>
                                                                                    <div class="addmendisexpbtn">
                                                                                        {{-- <button type="button" class="btn p-25 btn-sm btn-outline-secondary summaryTaxBtn"><i data-feather="plus"></i> Tax</button> --}}
                                                                                        <button class="btn p-25 btn-sm btn-outline-secondary summaryDisBtn"><i data-feather="plus"></i> Discount</button>
                                                                                        <button class="btn p-25 btn-sm btn-outline-secondary summaryExpBtn"><i data-feather="plus"></i> Expenses</button>
                                                                                    </div>
                                                                                </h6>
                                                                            </td>
                                                                        </tr>
                                                                        <tr class="totalsubheadpodetail">
                                                                            <td width="55%"><strong>Sub Total</strong></td>
                                                                            <td class="text-end" id="f_sub_total">
                                                                                <!-- {{ number_format(@$mr->total_item_amount, 2) }} -->
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Item Discount</strong></td>
                                                                            <td class="text-end" id="f_total_discount">
                                                                            </td>
                                                                        </tr>
                                                                        @if($mr->headerDiscount)
                                                                            <tr id="f_header_discount_hidden">
                                                                                <td><strong>Header Discount</strong></td>
                                                                                <td class="text-end" id="f_header_discount">
                                                                                </td>
                                                                            </tr>
                                                                        @else
                                                                            <tr class="d-none" id="f_header_discount_hidden">
                                                                                <td><strong>Header Discount</strong></td>
                                                                                <td class="text-end" id="f_header_discount">0.00</td>
                                                                            </tr>
                                                                        @endif
                                                                        <tr class="totalsubheadpodetail">
                                                                            <td><strong>Taxable Value</strong></td>
                                                                            <td class="text-end" id="f_taxable_value" amount="">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Tax</strong></td>
                                                                            <td class="text-end" id="f_tax">
                                                                            </td>
                                                                        </tr>
                                                                        <tr class="totalsubheadpodetail">
                                                                            <td><strong>Total After Tax</strong></td>
                                                                            <td class="text-end" id="f_total_after_tax">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Exp.</strong></td>
                                                                            <td class="text-end" id="f_exp">
                                                                            </td>
                                                                            <input type="hidden" name="expense_amount" class="text-end" id="expense_amount" value="{{$mr->expense_amount}}">
                                                                        </tr>
                                                                        <tr class="voucher-tab-foot">
                                                                            <td class="text-primary"><strong>Total After Exp.</strong></td>
                                                                            <td>
                                                                                <div class="quottotal-bg justify-content-end">
                                                                                    <h5 id="f_total_after_exp">
                                                                                    </h5>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-md-12">
                                                        <div class="col-md-4">
                                                            <div class="mb-1">
                                                                <label class="form-label">Upload Document</label>
                                                                <input type="file" name="attachment[]" class="form-control" onchange = "addFiles(this,'main_mr_preview')" multiple>
                                                                <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Final Remarks</label>
                                                            <textarea type="text" rows="4" name="remarks" class="form-control" placeholder="Enter Remarks here...">{!! $mr->final_remarks !!}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
        {{-- Discount summary modal --}}
        @include('procurement.material-request.partials.summary-disc-modal')
        {{-- Add expenses modal--}}
        @include('procurement.material-request.partials.summary-exp-modal')
        {{-- Edit Address --}}
        <div class="modal fade" id="edit-address" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
            <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
            </div>
        </div>
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
                    <button type="button" data-bs-dismiss="modal" class="btn btn-primary">Select</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Add each row discount popup --}}
    <div class="modal fade" id="itemRowDiscountModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Discount</h1>
                    <div class="text-end"></div>
                    <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                        <thead>
                            <tr>
                                <td>#</td>
                                <td>
                                    <input type="text" id="new_item_dis_name" class="form-control mw-100" />
                                </td>
                                <td>
                                    <input step="any" type="number" id="new_item_dis_perc" class="form-control mw-100" />
                                </td>
                                <td>
                                    <input step="any" type="number" id="new_item_dis_value" class="form-control mw-100" />
                                </td>
                                <td>
                                    <a href="javascript:;" id="add_new_item_dis" class="text-primary can_hide">
                                        <i data-feather="plus-square"></i>
                                    </a>
                                </td>
                            </tr>
                        </thead>
                    </table>
                    <div class="table-responsive-md customernewsection-form">
                        <table id="eachRowDiscountTable" class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th width="150px">Discount Name</th>
                                    <th>Discount %</th>
                                    <th>Discount Value</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="disItemFooter">
                                    <input type="hidden" name="row_count" id="row_count" value="1">
                                    <td colspan="2"></td>
                                    <td class="text-dark"><strong>Total</strong></td>
                                    <td class="text-dark text-end"><strong id="total">0.00</strong></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="button" class="btn btn-primary itemDiscountSubmit">Submit</button>
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
                    {{--
                    <p class="text-center">Enter the details below.</p>
                    --}}
                    <div class="row mt-2">
                        <div class="col-md-12 mb-1">
                            <label class="form-label">Remarks</label>
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

    {{-- Delete Item discount modal --}}
    <div class="modal fade text-start alertbackdropdisabled" id="deleteItemDiscModal" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
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
                    <button type="button" id="deleteItemDiscConfirm" class="btn btn-primary" >Confirm</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Header discount modal --}}
    <div class="modal fade text-start alertbackdropdisabled" id="deleteHeaderDiscModal" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
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
                    <button type="button" id="deleteHeaderDiscConfirm" class="btn btn-primary" >Confirm</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete header exp modal --}}
    <div class="modal fade text-start alertbackdropdisabled" id="deleteHeaderExpModal" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
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
                    <button type="button" id="deleteHeaderExpConfirm" class="btn btn-primary" >Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve/Reject Modal -->
    @include('procurement.material-request.partials.approve-modal', ['id' => $mr->id])

    {{-- Taxes --}}
    @include('procurement.material-request.partials.tax-detail-modal')

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
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>mr</strong>? After Amendment this action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')
    <script type="text/javascript" src="{{asset('assets/js/modules/mr.js')}}"></script>
    <script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
    <script>
        /*Clear local storage*/
        setTimeout(() => {
            localStorage.removeItem('deletedItemDiscTedIds');
            localStorage.removeItem('deletedHeaderDiscTedIds');
            localStorage.removeItem('deletedHeaderExpTedIds');
            localStorage.removeItem('deletedmrItemIds');
        },0);
        @if($buttons['amend'] && intval(request('amendment') ?? 0))

        @else
            @if(($mr->document_status != 'draft') && ($mr->document_status != 'rejected'))
                $(':input').prop('readonly', true);
                $('select').not('.amendmentselect select').prop('disabled', true);
                $("#deleteBtn").remove();
                $("#addNewItemBtn").remove();
                $(".editAddressBtn").remove();
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

        // Change BookId
        $(document).on('change','#book_id', (e) => {
            let bookId = e.target.value;
            if (bookId) {
                getDocNumberByBookId(bookId);
            } else {
                $("#document_number").val('');
                $("#book_id").val('');
                $("#document_number").attr('readonly', false);
            }
        });

        function getDocNumberByBookId(bookId) {
            let document_date = $("[name='document_date']").val();
            let actionUrl = '{{route("book.get.doc_no_and_parameters")}}'+'?book_id='+bookId+'&document_date='+document_date;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    // console.log(data.data);
                    if (data.status == 200) {
                        $("#book_code").val(data.data.book_code);
                        if(!data.data.doc.document_number) {
                            $("#document_number").val('');
                        }
                        $("#document_number").val(data.data.doc.document_number);
                        if(data.data.doc.type == 'Manually') {
                            $("#document_number").attr('readonly', false);
                        } else {
                            $("#document_number").attr('readonly', true);
                        }
                        const parameters = data.data.parameters;
                        console.log('parameters', parameters);
                        setServiceParameters(parameters);

                        if(parameters?.tax_required.some(val => val.toLowerCase() === 'yes')) {
                            $("#tax_required").val(parameters?.tax_required[0]);
                        } else {
                            $("#tax_required").val("");
                        }
                    }
                    if(data.status == 404) {
                        $("#book_code").val('');
                        $("#document_number").val('');
                        $("#tax_required").val("");
                        const docDateInput = $("[name='document_date']");
                        docDateInput.removeAttr('min');
                        docDateInput.removeAttr('max');
                        docDateInput.val(new Date().toISOString().split('T')[0]);
                    }
                });
            });
        }

        /*for trigger on edit cases*/
        setTimeout(() => {
            let bookId = $("#book_id").val();
            getDocNumberByBookId(bookId);
        },1000);

        /*Set Service Parameter*/
        function setServiceParameters(parameters) {
            /*Date Validation*/
            const docDateInput = $("[name='document_date']");
            let isFeature = false;
            let isPast = false;
            if (parameters.future_date_allowed && parameters.future_date_allowed.includes('yes')) {
                let futureDate = new Date();
                futureDate.setDate(futureDate.getDate() /*+ (parameters.future_date_days || 1)*/);
                docDateInput.val(futureDate.toISOString().split('T')[0]);
                docDateInput.attr("min", new Date().toISOString().split('T')[0]);
                isFeature = true;
            } else {
                isFeature = false;
                docDateInput.attr("max", new Date().toISOString().split('T')[0]);
            }
            if (parameters.back_date_allowed && parameters.back_date_allowed.includes('yes')) {
                let backDate = new Date();
                backDate.setDate(backDate.getDate() /*- (parameters.back_date_days || 1)*/);
                docDateInput.val(backDate.toISOString().split('T')[0]);
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


            setTimeout(() => {
                if($("tr[id*='row_']").length) {
                    setTableCalculation();
                }
            },100);
        }
        /*Add New Row*/
        // for component item code
        function initializeAutocomplete2(selector, type) {
            $(selector).autocomplete({
                source: function(request, response) {
                    let selectedAllItemIds = [];
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
                            type:'goods_item_list',
                            selectedAllItemIds : JSON.stringify(selectedAllItemIds)
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: `${item.item_name} (${item.item_code})`,
                                    code: item.item_code || '',
                                    item_id: item.id,
                                    item_name:item.item_name,
                                    uom_name:item.uom?.name,
                                    uom_id:item.uom_id,
                                    hsn_id:item.hsn?.id,
                                    hsn_code:item.hsn?.code,
                                    alternate_u_o_ms:item.alternate_u_o_ms,
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
                    let itemName = ui.item.value;
                    let itemN = ui.item.item_name;
                    let itemId = ui.item.item_id;
                    let uomId = ui.item.uom_id;
                    let uomName = ui.item.uom_name;
                    let hsnId = ui.item.hsn_id;
                    let hsnCode = ui.item.hsn_code;
                    $input.attr('data-name', itemName);
                    $input.attr('data-code', itemCode);
                    $input.attr('data-id', itemId);
                    $input.val(itemCode);
                    let closestTr = $input.closest('tr');
                    closestTr.find('[name*=item_id]').val(itemId);
                    closestTr.find('[name*=item_code]').val(itemCode);
                    closestTr.find('[name*=item_name]').val(itemN);
                    closestTr.find('[name*=hsn_id]').val(hsnId);
                    closestTr.find('[name*=hsn_code]').val(hsnCode);
                    let uomOption = `<option value=${uomId}>${uomName}</option>`;
                    if(ui.item?.alternate_u_o_ms) {
                        for(let alterItem of ui.item.alternate_u_o_ms) {
                        uomOption += `<option value="${alterItem.uom_id}" ${alterItem.is_purchasing ? 'selected' : ''}>${alterItem.uom?.name}</option>`;
                        }
                    }
                    closestTr.find('[name*=uom_id]').append(uomOption);
                    closestTr.find('.attributeBtn').trigger('click');
                    let price = 0;
                    let transactionType = 'collection';

                    let rowCount = Number(closestTr.attr('data-index'));
                    let queryParams = new URLSearchParams({
                        price: price,
                        item_id: itemId,
                        transaction_type: transactionType,
                        rowCount : rowCount
                    }).toString();
                    getItemDetail(closestTr);
                    // taxHidden(queryParams);
                    setTimeout(() => {
                        if(ui.item.is_attr) {
                            $input.closest('tr').find('.attributeBtn').trigger('click');
                        } else {
                            $input.closest('tr').find('[name*="[quantity]"]').focus();
                        }
                    }, 100);
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
        initializeAutocomplete2(".comp_item_code");
        $(document).on('click','#addNewItemBtn', (e) => {
            // for component item code
            var supplierName = $('#vendor_name').val();
            if(!supplierName){
                Swal.fire(
                    "Warning!",
                    "Please select vendor first!",
                    "warning"
                );
                return false;
            }
            let rowsLength = $("#itemTable > tbody > tr").length;
            /*Check last tr data shoud be required*/
            let lastRow = $('#itemTable .mrtableselectexcel tr:last');
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

            let actionUrl = '{{route("material-request.item.row")}}'+'?count='+rowsLength+'&component_item='+JSON.stringify(lastTrObj);
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                            // $("#submit-button").click();
                        if (rowsLength) {
                            $("#itemTable > tbody > tr:last").after(data.data.html);
                        } else {
                            $("#itemTable > tbody").html(data.data.html);
                        }
                        initializeAutocomplete2(".comp_item_code");
                    } else if(data.status == 422) {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'An unexpected error occurred.',
                            icon: 'error',
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Someting went wrong!',
                            icon: 'error',
                        });
                    }
                });
            });
        });

        /*Delete Row*/
        $(document).on('click','#deleteBtn', (e) => {
            let itemIds = [];
            let editItemIds = [];
            let poItemIds = [];
            $('.form-check-input:checked').each(function(index, item) {
                let tr = $(item).closest('tr');
                let trIndex = tr.index();
                let po_detail_id = Number($(tr).find('[name*="[po_detail_id]"]').val()) || 0;
                let mr_detail_id = Number($(tr).find('[name*="[mr_detail_id]"]').val()) || 0;
                if (po_detail_id > 0 && mr_detail_id > 0) {
                    poItemIds.push({ index: trIndex + 1, po_detail_id: po_detail_id });
                }
            });
            if (poItemIds.length) {
                e.preventDefault();
                let rowNumbers = poItemIds.map(item => item.index).join(", ");
                Swal.fire({
                    title: 'Error!',
                    text: `You cannot delete mr item(s) at row(s): ${rowNumbers}`,
                    icon: 'error',
                });
                return false;
            }

            $('#itemTable > tbody .form-check-input').each(function() {
                if ($(this).is(":checked")) {
                    if($(this).attr('data-id')) {
                        editItemIds.push($(this).attr('data-id'));
                    } else {
                        itemIds.push($(this).val());
                    }
                }
            });
            if (itemIds.length) {
                itemIds.forEach(function(item,index) {
                    $(`#row_${item}`).remove();
                });
            }
            if(editItemIds.length == 0 && itemIds.length == 0) {
                Swal.fire({
                    title: 'Error!',
                    text: "Please first add & select row item.",
                    icon: 'error',
                });
                return false;
            }
            if (editItemIds.length) {
                $("#deleteComponentModal").find("#deleteConfirm").attr('data-ids',JSON.stringify(editItemIds));
                $("#deleteComponentModal").modal('show');
            }
            if(!$("tr[id*='row_']").length) {
                $("#itemTable > thead .form-check-input").prop('checked',false);
                // $(".prSelect").prop('disabled',false);
            }
            setTableCalculation();
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
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select first item name.',
                    icon: 'error',
                });
                return false;
            }
        });

        /*For comp attr*/
        function getItemAttribute(itemId, rowCount, selectedAttr, tr){
            let actionUrl = '{{route("material-request.item.attr")}}'+'?item_id='+itemId+`&rowCount=${rowCount}&selectedAttr=${selectedAttr}`;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        $("#attribute tbody").empty();
                        $("#attribute table tbody").append(data.data.html)
                        $(tr).find('td:nth-child(2)').find("[name*=attr_name]").remove();
                        $(tr).find('td:nth-child(2)').append(data.data.hiddenHtml);
                        if (data.data.attr) {
                            $("#attribute").modal('show');
                            $(".select2").select2();
                        }
                    }
                });
            });
        }

        /*Display item detail*/
        $(document).on('change focus', '#itemTable tr input ', function(e){
            let currentTr = e.target.closest('tr');
            getItemDetail(currentTr);
        });

        function getItemDetail(currentTr) {
            let pName = $(currentTr).find("[name*='component_item_name']").val();
            let itemId = $(currentTr).find("[name*='item_id']").val();
            let poHeaderId = $(currentTr).find("[name*='purchase_order_id']").val();
            let poDetailId = $(currentTr).find("[name*='po_detail_id']").val();
            let remark = '';
            if($(currentTr).find("[name*='remark']")) {
                remark = $(currentTr).find("[name*='remark']").val() || '';
            }

            if (itemId) {
                let selectedAttr = [];
                $(currentTr).find("[name*='attr_name']").each(function(index, item) {
                    if($(item).val()) {
                        selectedAttr.push($(item).val());
                    }
                });
                let uomId = $(currentTr).find("[name*='[uom_id]']").val() || '';
                let qtyElement = $(currentTr).find("[name*='[quantity]']");  // Get the jQuery object, not the value
                let qty = qtyElement.val() || '';  // Get the value of the quantity input

                let itemStoreData = JSON.parse($(currentTr).find("[id*='components_stores_data']").val() || "[]");
                let headerId = $(currentTr).find("[name*='mr_header_id']").val() ?? '';
                let detailId = $(currentTr).find("[name*='mr_detail_id']").val() ?? '';

                let actionUrl = '{{route("material-request.get.itemdetail")}}' +
                    '?item_id=' + itemId +
                    '&purchase_order_id=' + poHeaderId +
                    '&po_detail_id=' + poDetailId +
                    '&selectedAttr=' + JSON.stringify(selectedAttr) +
                    '&itemStoreData=' + JSON.stringify(itemStoreData) +
                    '&remark=' + remark +
                    '&uom_id=' + uomId +
                    '&qty=' + qty +
                    '&headerId=' + headerId +
                    '&detailId=' + detailId;

                fetch(actionUrl).then(response => {
                    return response.json().then(data => {
                        if (data.status == 200) {
                            $("#itemDetailDisplay").html(data.data.html);
                            var approvedStockLedger = data.data.checkApprovedQuantity;
                            if ((approvedStockLedger['code'] == 200) && (approvedStockLedger['status'] == 'error')) {
                                let approved_stock = approvedStockLedger['approvedStock'];
                                let receipt_qty = $(currentTr).find("[name*='[order_qty]']").val() || '';
                                let rejQtyElement = $(currentTr).find("[name*='[rejected_qty]']");  // Get the jQuery object, not the value
                                let rejQty = rejQtyElement.val() || '';  // Get the value of the rejected_qty input
                                if (qty < approved_stock) {
                                    if (qtyElement.length > 0) {  // Ensure the element was found
                                        qtyElement.val(receipt_qty);  // Set the value of qtyElement (jQuery object)
                                        rejQtyElement.val(0.00);  // Set the value of qtyElement (jQuery object)
                                    } else {
                                        Swal.fire({
                                            title: 'Error!',
                                            text: "Accepted quantity input not found",
                                            icon: 'error',
                                        });
                                    }
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: "Accepted quantity is higher than the approved stock.",
                                        icon: 'error',
                                    });
                                }
                            }
                        }
                    });
                });
            }
        }

        // Event listener for Edit Address button click
        $(document).on('click', '.editAddressBtn', (e) => {
            let addressType = $(e.target).closest('a').attr('data-type');
            let vendorId = $("#vendor_id").val();
            let onChange = 0;
            let addressId = addressType === 'shipping' ? $("#shipping_id").val() : $("#billing_id").val();
            let actionUrl = `{{route("material-request.edit.address")}}?type=${addressType}&vendor_id=${vendorId}&address_id=${addressId}&onChange=${onChange}`;
            fetch(actionUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200) {
                        $("#edit-address .modal-dialog").html(data.data.html);
                        $("#address_type").val(addressType);
                        $("#edit-address").modal('show');
                        initializeFormComponents(data.data.selectedAddress);
                    } else {
                        console.error('Failed to fetch address data:', data.message);
                    }
                })
                .catch(error => console.error('Error fetching address data:', error));
        });

        $(document).on('change', "[name='address_id']", (e) => {
            let vendorId = $("#vendor_id").val();
            let addressType = $("#address_type").val();
            let addressId = e.target.value;
            if (!addressId) {
                $("#country_id").val('').trigger('change');
                $("#state_id").val('').trigger('change');
                $("#city_id").val('').trigger('change');
                $("#pincode").val('');
                $("#address").val('');
                return false;
            }
            let onChange = 1;
            let actionUrl = `{{route("material-request.edit.address")}}?type=${addressType}&vendor_id=${vendorId}&address_id=${addressId}&onChange=${onChange}`;
            fetch(actionUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200) {
                        initializeFormComponents(data.data.selectedAddress);
                    } else {
                        console.error('Failed to fetch address data:', data.message);
                    }
                })
                .catch(error => console.error('Error fetching address data:', error));
        });

        function initializeFormComponents(selectedAddress) {
            const countrySelect = $('#country');
            fetch('/countries')
                .then(response => response.json())
                .then(data => {
                    countrySelect.empty();
                    countrySelect.append('<option value="">Select Country</option>');
                    data.data.countries.forEach(country => {
                        const isSelected = country.value === selectedAddress.country.id;
                        countrySelect.append(new Option(country.label, country.value, isSelected, isSelected));
                    });
                    if (selectedAddress.country.value) {
                        countrySelect.trigger('change');
                    }
                })
                .catch(error => console.error('Error fetching countries:', error));

            countrySelect.on('change', function () {
                let countryValue = $(this).val();
                let stateSelect = $('#state_id');
                stateSelect.empty().append('<option value="">Select State</option>'); // Reset state dropdown

                if (countryValue) {
                    fetch(`/states/${countryValue}`)
                        .then(response => response.json())
                        .then(data => {
                            data.data.states.forEach(state => {
                                const isSelected = state.value === selectedAddress.state.id;
                                stateSelect.append(new Option(state.label, state.value, isSelected, isSelected));
                            });
                            if (selectedAddress.state.value) {
                                stateSelect.trigger('change');
                            }
                        })
                        .catch(error => console.error('Error fetching states:', error));
                }
            });
            $('#state_id').on('change', function () {
                let stateValue = $(this).val();
                let citySelect = $('#city');
                citySelect.empty().append('<option value="">Select City</option>');
                if (stateValue) {
                    fetch(`/cities/${stateValue}`)
                        .then(response => response.json())
                        .then(data => {
                            data.data.cities.forEach(city => {
                                const isSelected = city.value === selectedAddress.city.id;
                                citySelect.append(new Option(city.label, city.value, isSelected, isSelected));
                            });
                        })
                        .catch(error => console.error('Error fetching cities:', error));
                }
            });
            $("#pincode").val(selectedAddress.pincode);
            $("#address").val(selectedAddress.address);
        }

        /* Address Submit */
        $(document).on('click', '.submitAddress', function (e) {
            $('.ajax-validation-error-span').remove();
            e.preventDefault();
            var innerFormData = new FormData();
            $('#edit-address').find('input,textarea,select').each(function () {
                innerFormData.append($(this).attr('name'), $(this).val());
            });
            var method = "POST" ;
            var url = '{{route("material-request.address.save")}}';
            fetch(url, {
                method: method,
                body: innerFormData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                return response.json();
            })
            .then(data => {
                if(data.status == 200) {
                    let addressType = $("#address_type").val();
                    if(addressType == 'shipping') {
                        $("#shipping_id").val(data.data.new_address.id);
                        $(".shipping_detail").text(data.data.new_address.display_address);
                    } else {
                        $("#billing_id").val(data.data.new_address.id);
                        $(".billing_detail").text(data.data.new_address.display_address);
                    }
                    $("#edit-address").modal('hide');
                } else {
                    let formObj = $("#edit-address");
                    let errors = data.errors;
                    for (const [key, errorMessages] of Object.entries(errors)) {
                        var name = key.replace(/\./g, "][").replace(/\]$/, "");
                        formObj.find(`[name="${name}"]`).parent().append(
                            `<span class="ajax-validation-error-span form-label text-danger" style="font-size:12px">${errorMessages[0]}</span>`
                        );
                    }
                }
            })
            .catch(error => {
                console.error('Form submission error:', error);
            });

        });

        /*Delete server side rows*/
        $(document).on('click','#deleteConfirm', (e) => {
            let ids = e.target.getAttribute('data-ids');
            ids = JSON.parse(ids);
            localStorage.setItem('deletedmrItemIds', JSON.stringify(ids));
            $("#deleteComponentModal").modal('hide');

            if(ids.length) {
                ids.forEach((id,index) => {
                    $(`.form-check-input[data-id='${id}']`).closest('tr').remove();
                });
            }
            setTableCalculation();
            if(!$("#itemTable [id*=row_]").length) {
                $("th .form-check-input").prop('checked',false);
                $('#vendor_name').prop('readonly',false);
                $("#editBillingAddressBtn").show();
                $("#editShippingAddressBtn").show();
            }
        });

        /*Remove item level discount*/
        $(document).on("click", ".deleteItemDiscountRow", (e) => {
            let rowCount = e.target.closest('a').getAttribute('data-row-count') || 0;
            let rowIndex = $(e.target).closest('tr').index();
            let id = e.target.closest('a').getAttribute('data-id') || 0;
            if(Number(id)) {
                $("#deleteItemDiscModal").find("#deleteItemDiscConfirm").attr('data-id', id);
                $("#deleteItemDiscModal").find("#deleteItemDiscConfirm").attr('data-row-index', rowIndex);
                $("#deleteItemDiscModal").find("#deleteItemDiscConfirm").attr('data-row-count', rowCount);
                $("#deleteItemDiscModal").modal('show');
            }
        });

        /*Delete server side rows*/
        $(document).on('click','#deleteItemDiscConfirm', (e) => {
            let rowCount = e.target.getAttribute('data-row-count') || $("#disItemFooter #row_count").val();
            let id = e.target.getAttribute('data-id');
            let dataRowId = Number(e.target.getAttribute('data-row-index'));
            $("#deleteItemDiscModal").modal('hide');
            $(`.display_discount_row`).find(`[value="${id}"]`).closest('tr').remove();
            let ids = JSON.parse(localStorage.getItem('deletedItemDiscTedIds')) || [];
            if (!ids.includes(id)) {
                ids.push(id);
            }
            localStorage.setItem('deletedItemDiscTedIds', JSON.stringify(ids));

            let total_head_dis = 0;
            $("[name*='[item_d_amnt]']").each(function(index,item) {
                total_head_dis+=Number($(item).val());
            });
            $("#disItemFooter #total").text(total_head_dis.toFixed(2));
            $(`[id*='row_${rowCount}']`).find("[name*='[discounts]'").remove();

            let hiddenDis = '';
            let totalAmnt = 0;
            $(".display_discount_row").find("[name*='[item_d_amnt]']").each(function(index,item) {
                let key = index + 1;
                let id = $(item).closest('tr').find(`[name*="[item_d_id]"]`).val();
                let name = $(item).closest('tr').find(`[name*="[item_d_name]"]`).val();
                let perc = $(item).closest('tr').find(`[name*="[item_d_perc]"]`).val();
                let amnt = $(item).val();
                totalAmnt+=Number(amnt);
                hiddenDis+= `<input type="hidden" value="${id}" name="components[${rowCount}][discounts][${index+1}][id]">
                <input type="hidden" value="${name}" name="components[${rowCount}][discounts][${index+1}][dis_name]">
                <input type="hidden" value="${perc}" name="components[${rowCount}][discounts][${index+1}][dis_perc]">
                <input type="hidden" value="${amnt}" name="components[${rowCount}][discounts][${index+1}][dis_amount]">`;
            });
            $(`[name*="components[${rowCount}][discount_amount]"]`).val(totalAmnt);
            $(`[name*="components[${rowCount}][discount_amount]"]`).after(hiddenDis);
            setTableCalculation();
        });

        /*Remove item level discount*/
        $(document).on("click", ".deleteSummaryDiscountRow", (e) => {
            let id = $(e.target.closest('tr')).find('[name*="[d_id]"]').val();
            const rowIndex = $(e.target).closest('tr').index();
            if(id) {
                $("#deleteHeaderDiscModal").find("#deleteHeaderDiscConfirm").attr('data-id', id);
                $("#deleteHeaderDiscModal").find("#deleteHeaderDiscConfirm").attr('data-row-index', rowIndex);
                $("#deleteHeaderDiscModal").modal('show');
            }
        });

        /*Delete server side rows*/
        $(document).on('click','#deleteHeaderDiscConfirm', (e) => {
            let id = e.target.getAttribute('data-id');
            let dataRowId = e.target.getAttribute('data-row-index');
            $("#deleteHeaderDiscModal").modal('hide');
            $(`.display_summary_discount_row:eq(${dataRowId})`).remove();
            let ids = JSON.parse(localStorage.getItem('deletedHeaderDiscTedIds')) || [];
            if (!ids.includes(id)) {
                ids.push(id);
            }
            localStorage.setItem('deletedHeaderDiscTedIds', JSON.stringify(ids));
            let itemValue = Number($("#TotalEachRowAmount").attr('amount'));
            $('.display_summary_discount_row [name*="[d_perc]"]').each(function(index, item) {
                let perc = Number($(item).val());
                if(perc) {
                    let disAmount = itemValue * Number(perc) / 100;
                    $(item).closest('tr').find("[name*='[d_amnt]']").prop('readonly', true).val(disAmount.toFixed(2));
                } else {
                    $(item).closest('tr').find("[name*='[d_amnt]']").prop('readonly', false).val('');
                }
            });
            summaryDisTotal();
            setTableCalculation();
        });


        /*Remove header level exp*/
        $(document).on("click", ".deleteExpRow", (e) => {
            let id = $(e.target.closest('tr')).find('[name*="[e_id]"]').val();
            const rowIndex = $(e.target).closest('tr').index();
            if(id) {
                $("#deleteHeaderExpModal").find("#deleteHeaderExpConfirm").attr('data-id', id);
                $("#deleteHeaderExpModal").find("#deleteHeaderExpConfirm").attr('data-row-index', rowIndex);
                $("#deleteHeaderExpModal").modal('show');
            }
        });

        /*Delete server side rows*/
        $(document).on('click','#deleteHeaderExpConfirm', (e) => {
            let id = e.target.getAttribute('data-id');
            let dataRowId = e.target.getAttribute('data-row-index');
            $("#deleteHeaderExpModal").modal('hide');
            $(`.display_summary_exp_row:eq(${dataRowId})`).remove();

            let ids = JSON.parse(localStorage.getItem('deletedHeaderExpTedIds')) || [];
            if (!ids.includes(id)) {
                ids.push(id);
            }
            localStorage.setItem('deletedHeaderExpTedIds', JSON.stringify(ids));

            let totalPerc = 100;
            let itemValue = Number($("#f_total_after_tax").attr('amount'));
            $('.display_summary_exp_row [name*="[e_perc]"]').each(function(index, item) {
                let perc = Number($(item).val());
                if(perc) {
                    let total = itemValue * perc / 100;
                    $(item).closest('tr').find('[name*="e_amnt"]').prop('readonly',true).val(total.toFixed(2));
                } else {
                    $(item).closest('tr').find('[name*="e_amnt"]').prop('readonly',false).val('');
                }
            });

            summaryExpTotal();
            setTableCalculation();
        });

        /*Amendment modal open*/
        $(document).on('click', '.amendmentBtn', (e) => {
            $("#amendmentconfirm").modal('show');
        });

        $(document).on('click', '#amendmentSubmit', (e) => {
            let actionUrl = "{{ route('material-request.amendment.submit', $mr->id) }}";
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

        /*Open amendment popup*/
        $(document).on('click', '#amendmentBtn', (e) => {
            $("#amendmentModal").modal('show');
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
                $("#mrEditForm").submit();
            }
        });
    </script>
@endsection
