@extends('layouts.app')
@use(App\Helpers\ConstantHelper)
@section('styles')
<style>
#rescdule .table-responsive {
    overflow-y: auto;
    max-height: 350px; /* Set the height of the scrollable body */
    position: relative;
}

#rescdule .po-order-detail {
    width: 100%;
    border-collapse: collapse;
}

#rescdule .po-order-detail thead {
    position: sticky;
    top: 0; /* Stick the header to the top of the table container */
    background-color: white; /* Optional: Make sure header has a background */
    z-index: 1; /* Ensure the header stays above the body content */
}
#rescdule .po-order-detail th {
    background-color: #f8f9fa; /* Optional: Background for the header */
    text-align: left;
    padding: 8px;
}

#rescdule .po-order-detail td {
    padding: 8px;
}
/* .nav-tabs .nav-link.tab-error-highlight {
    border-bottom: 3px solid red !important;
    color: red !important;
} */

</style>
@endsection
@section('content')

<!-- BEGIN: Content-->
<form method="POST" data-module="pslip" data-completionFunction = "disableHeader" class="ajax-input-form sales_module_form production_slip" action = "{{route('production.slip.store')}}" data-redirect="{{ $redirect_url }}" id = "sale_invoice_form" enctype='multipart/form-data'>
    <input type="hidden" name="station_wise_consumption" value="" id="station_wise_consumption">
    <input type="hidden" name="inspection_required_key" value="" id="inspection_required_key">
    @if(isset($slip))
        <input type="hidden" name="id" value="{{ $slip->id ?? '' }}">
    @endif
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
				<div class="row">
                    @include('layouts.partials.breadcrumb-add-edit', [
                        'title' => 'Production Slip',
                        'menu' => 'Home',
                        'menu_url' => url('home'),
                        'sub_menu' => 'Add New'
                    ])
                    <input type = "hidden" value = "draft" name = "document_status" id = "document_status" />
					<div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
						<div class="form-group breadcrumb-right" id = "buttonsDiv">
                        @if(!isset(request() -> revisionNumber))
                        <button type = "button" onclick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
                            @if (isset($slip) && !empty($slip))
                                <a href="{{ route('production.slip.generate-pdf', $slip->id) }}" target="_blank" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer"><polyline points="6 9 6 2 18 2 18 9"></polyline>
                                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                    <rect x="6" y="14" width="12" height="8"></rect></svg> Print
                                </a>
                                @if($buttons['draft'])
                                    <button type="button" onclick = "submitForm('draft');" name="action" value="draft" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0" id="save-draft-button" name="action" value="draft"><i data-feather='save'></i> Save as Draft</button>
                                @endif
                                @if($buttons['submit'])
                                    <button type="button" onclick = "submitForm('submitted');" name="action" value="submitted" class="btn btn-primary btn-sm" id="submit-button" name="action" value="submitted">
                                        <i data-feather="check-circle"></i> Submit
                                    </button>
                                @endif

                                @if($buttons['approve'])
                                    <button type="button" id="reject-button" data-bs-toggle="modal" data-bs-target="#approveModal" onclick = "setReject('reject');" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                                        <i data-feather="x-circle"></i>
                                        Reject
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal" onclick = "setApproval('approve');" >
                                        <i data-feather="check-circle"></i> Approve
                                    </button>
                                @endif
                                @if($buttons['amend'])
                                    <button id = "amendShowButton" type="button" onclick = "openModal('amendmentconfirm')" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i> Amendment</button>
                                @endif
                                @if($buttons['post'])
                                    <button id = "postButton" onclick = "onPostVoucherOpen();" type = "button" class="btn btn-warning btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                                        <i data-feather='check-circle'></i> Post
                                    </button>
                                @endif
                                @if($buttons['voucher'])
                                    <button type = "button" onclick = "onPostVoucherOpen('posted');" class="btn btn-primary btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                                        <i data-feather='file-text'></i>
                                        Voucher
                                    </button>
                                @endif
                                @if($buttons['revoke'])
                                    <button id = "revokeButton" type="button" onclick = "revokeDocument();" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='rotate-ccw'></i> Revoke</button>
                                @endif
                                @if($buttons['delete'])
                                    <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                        data-url="{{ route('production.slip.destroy', [$slip->id, $buttons['amend'] ? $buttons['amend'] : 0]) }}"
                                        data-redirect="{{ $redirect_url }}"
                                        data-message="Are you sure you want to delete this record?">
                                        <i data-feather="trash-2" class="me-50"></i> Delete
                                    </button>
                                @endif
                            @else
                                <button type = "button" name="action" value="draft" id = "save-draft-button" onclick = "submitForm('draft');" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0"><i data-feather='save'></i> Save as Draft</button>
                                <button type = "button" name="action" value="submitted" id = "submit-button" onclick = "submitForm('submitted');" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Submit</button>
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
								 <div class="card-body customernewsection-form" id ="main_so_form">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                                                    <div>
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                            </div>
                                            @if (isset($slip) && isset($docStatusClass))
                                            <div class="col-md-6 text-sm-end">
                                                <span class="badge rounded-pill badge-light-{{$slip->display_status === 'Posted' ? 'info' : 'secondary'}} forminnerstatus">
                                                    <span class = "text-dark" >Status</span> : <span class="{{$docStatusClass}}">{{$slip->display_status}}</span>
                                                </span>
                                            </div>

                                            @endif
                                            <div class="col-md-8">
                                                <input type = "hidden" name = "type" id = "type_hidden_input"></input>
                                            @if (isset($slip))
                                                <input type = "hidden" value = "{{$slip -> id}}" name = "pslip_id"></input>
                                            @endif

                                                    <div class="row align-items-center mb-1" style = "display:none;">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Document Type <span class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <select class="form-select disable_on_edit" id = "service_id_input" {{isset($slip) ? 'disabled' : ''}} onchange = "onSeriesChange(this);">
                                                                @foreach ($services as $currentService)
                                                                    <option value = "{{$currentService -> alias}}" {{isset($selectedService) ? ($selectedService == $currentService -> alias ? 'selected' : '') : ''}}>{{$currentService -> name}}</option>
                                                                @endforeach
                                                            </select>
                                                            <input type = "hidden" value = "yes" id = "invoice_to_follow_input" />
                                                        </div>

                                                    </div>


                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Series <span class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <select class="form-select disable_on_edit" onChange = "getDocNumberByBookId(this);" name = "book_id" id = "series_id_input">
                                                                @foreach ($series as $currentSeries)
                                                                    <option value = "{{$currentSeries -> id}}" {{isset($slip) ? ($slip -> book_id == $currentSeries -> id ? 'selected' : '') : ''}}>{{$currentSeries -> book_code}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <input type = "hidden" name = "book_code" id = "book_code_input" value = "{{isset($slip) ? $slip -> book_code : ''}}"></input>
                                                     </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Document No <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <input type="text" value = "{{isset($slip) ? $slip -> document_number : ''}}" class="form-control disable_on_edit" readonly id = "order_no_input" name = "document_no">
                                                        </div>
                                                     </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Document Date <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <input type="date" value = "{{isset($slip) ? $slip -> document_date : Carbon\Carbon::now() -> format('Y-m-d')}}" class="form-control" name = "document_date" id = "order_date_input" oninput = "onDocDateChange();">
                                                        </div>
                                                     </div>



                                                    <div class="row align-items-center mb-1 lease-hidden">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Location<span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <select class="form-select disable_on_edit" name = "store_id" id = "store_id_input">
                                                                @foreach ($stores as $store)
                                                                    <option value = "{{$store -> id}}" {{isset($slip) ? ($slip -> store_id == $store -> id ? 'selected' : '') : ''}}>{{$store -> store_name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1 lease-hidden" id="sub_store_div">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Sub Location<span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <select class="form-select disable_on_edit" id="sub_store_id" name="sub_store_id">
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1 lease-hidden">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Shift<span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <select class="form-select disable_on_edit" id="shift_id" name="shift_id">
                                                                @foreach ($shifts as $shift)
                                                                    <option value="{{$shift->id}}" {{isset($slip) ? ($slip->shift_id == $shift->id ? 'selected' : '') : ''}}>{{$shift->label}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1" id = "selection_section" style = "display:none;">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Reference From</label>
                                                        </div>
                                                            <div class="col-md-2 action-button" id = "pwo_selection">
                                                                <button onclick = "openHeaderPullModal();" disabled type = "button" id = "select_pwo_button" data-bs-toggle="modal" data-bs-target="#rescdule" class="btn btn-outline-primary btn-sm mb-0"><i data-feather="plus-square"></i>
                                                                MO
                                                            </button>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    @if(isset($slip) && ($slip -> document_status !== "draft"))
                            @if((isset($approvalHistory) && count($approvalHistory) > 0) || isset($revision_number))
                           <div class="col-md-4">
                               <div class="step-custhomapp bg-light p-1 customerapptimelines customerapptimelinesapprovalpo">
                                   <h5 class="mb-2 text-dark border-bottom pb-50 d-flex align-items-center justify-content-between">
                                       <strong><i data-feather="arrow-right-circle"></i> Approval History</strong>
                                       @if(!isset(request() -> revisionNumber) && $slip -> document_status !== 'draft')
                                       <strong class="badge rounded-pill badge-light-secondary amendmentselect">Rev. No.
                                           <select class="form-select" id="revisionNumber">
                                            @for($i=$revision_number; $i >= 0; $i--)
                                               <option value="{{$i}}" {{request('revisionNumber',$slip->revision_number) == $i ? 'selected' : ''}}>{{$i}}</option>
                                            @endfor
                                           </select>
                                       </strong>
                                       @else
                                       @if ($slip -> document_status !== 'draft')
                                       <strong class="badge rounded-pill badge-light-secondary amendmentselect">
                                        Rev. No.{{request() -> revisionNumber}}
                                        </strong>
                                       @endif

                                       @endif
                                   </h5>
                                   <ul class="timeline ms-50 newdashtimline ">
                                        @foreach($approvalHistory as $approvalHist)
                                        <li class="timeline-item">
                                           <span class="timeline-point timeline-point-indicator"></span>
                                           <div class="timeline-event">
                                               <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                   <h6>{{ucfirst($approvalHist->name ?? $approvalHist?->user?->name ?? 'NA')}}</h6>
                                                   @if($approvalHist->approval_type == 'approve')
                                                   <span class="badge rounded-pill badge-light-success">{{ucfirst($approvalHist->approval_type)}}</span>
                                                   @elseif($approvalHist->approval_type == 'submit')
                                                   <span class="badge rounded-pill badge-light-primary">{{ucfirst($approvalHist->approval_type)}}</span>
                                                   @elseif($approvalHist->approval_type == 'reject')
                                                   <span class="badge rounded-pill badge-light-danger">{{ucfirst($approvalHist->approval_type)}}</span>
                                                   @elseif($approvalHist->approval_type == 'posted')
                                                   <span class="badge rounded-pill badge-light-info">{{ucfirst($approvalHist->approval_type)}}</span>
                                                   @else
                                                   <span class="badge rounded-pill badge-light-danger">{{ucfirst($approvalHist->approval_type)}}</span>
                                                   @endif
                                               </div>
                                               @if($approvalHist->created_at)
                                                    <h6>
                                                        {{ \Carbon\Carbon::parse($approvalHist->created_at)->format('d/m/Y') }} || {{ \Carbon\Carbon::parse($approvalHist->created_at)->format('h.iA') }}
                                                    </h6>
                                                @endif
                                                @if($approvalHist->remarks)
                                                <p>{!! $approvalHist->remarks !!}</p>
                                                @endif
                                                @if ($approvalHist -> media && count($approvalHist -> media) > 0)
                                                    @foreach ($approvalHist -> media as $mediaFile)
                                                        <p><a href="{{$mediaFile -> file_url}}" target = "_blank"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg></a></p>
                                                    @endforeach
                                                @endif
                                           </div>
                                        </li>
                                       @endforeach

                                   </ul>
                               </div>
                           </div>
                           @endif
                           @endif
                                        </div>
                                </div>
                            </div>

                            <div class="col-md-12" id="vendor_section">
                                <div class="card quation-card">
                                    <div class="card-header newheader">
                                        <div>
                                            <h4 class="card-title">General Information</h4>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col">
                                                <div class="mb-1">
                                                    <label class="form-label">MO No. </label>
                                                    <input type="text" @if(isset($slip)) value="{{$slip?->mo?->book_code}} - {{$slip?->mo?->document_number}}" @endif placeholder="Select" class="form-control mw-100 ledgerselecct disabled-input" id="mo_no" name="mo_no" />
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="mb-1">
                                                    <label class="form-label">Date </label>
                                                    <input type="text" @if(isset($slip)) value="{{$slip?->mo?->getFormattedDate('document_date')}}" @endif placeholder="Select" class="form-control mw-100 ledgerselecct disabled-input" id="mo_date" name="mo_date"  />
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="mb-1">
                                                    <label class="form-label">Product Name </label>
                                                    <input type="text" @if(isset($slip)) value="{{$slip?->mo?->item?->item_name}}" @endif id="mo_product_name" placeholder="Select" class="form-control mw-100 ledgerselecct disabled-input" name="mo_product_name" />
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="mb-1">
                                                    <label class="form-label">Type </label>
                                                    <input type="text" @if(isset($slip)) value="{{$slip->is_last_station == true ? 'Final' : 'WIP'}}" @endif id="mo_type_name" placeholder="Select" class="form-control mw-100 ledgerselecct disabled-input" name="mo_type_name" />
                                                </div>
                                            </div>
                                            <div class="col {{ isset($slip) && optional($slip?->mo?->station)->name ? '' : 'd-none' }}">
                                                <div class="mb-1">
                                                    <label class="form-label">Station </label>
                                                    <input type="text" @if(isset($slip)) value="{{$slip?->mo?->station?->name}}" @endif placeholder="Select" class="form-control mw-100 ledgerselecct disabled-input" id="station_name" name="station_name"  />
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="mb-1">
                                                    <label class="form-label"><span id="fg_label">Finished Goods Store</span> <span class="text-danger">*</span></label>
                                                    <select class="form-select {{@$slip?->items?->count() ? '' : ''}}" id="fg_sub_store_id" name="fg_sub_store_id">
                                                        @if(isset($slip) && $slip?->fg_sub_store_id)
                                                            <option value="{{$slip?->fg_sub_store_id}}">{{$slip?->fg_sub_store?->store_name}}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="mb-1">
                                                    <label class="form-label">Rejected Goods Store </label>
                                                    <select class="form-select {{@$slip?->items?->count() ? '' : ''}}" id="rg_sub_store_id" name="rg_sub_store_id">
                                                        @if(isset($slip) && $slip?->rg_sub_store_id)
                                                            <option value="{{$slip?->rg_sub_store_id}}">{{$slip?->rg_sub_store?->store_name}}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>


                                            <input type="hidden" id="mo_id" name="mo_id" @if(isset($slip)) value="{{$slip?->mo_id}}" @endif>
                                            <input type="hidden" id="mo_bom_id" name="bom_id" @if(isset($slip)) value="{{$slip?->bom_id}}" @endif>
                                            <input type="hidden" id="mo_product_id" name="mo_product_id" @if(isset($slip)) value="{{$slip?->mo?->item_id}}" @endif>
                                            <input type="hidden" id="is_last_station" name="is_last_station" @if(isset($slip)) value="{{$slip?->is_last_station}}" @endif>
                                            <input type="hidden" id="is_batch_no" name="is_batch_no" @if(isset($slip)) value="{{$slip?->is_batch_no}}" @endif>
                                            <input type="hidden" id="mo_station_id" name="mo_station_id" @if(isset($slip)) value="{{$slip?->station_id}}" @endif>
                                        </div>
                                        <div class="row">
                                             <div class="col">
                                                <div class="mb-1">
                                                    <label class="form-label">Lot No. <span class="text-danger show_required_field_for_batch" style="display: none;">*</span></label>
                                                    <input type="text" @if(isset($slip)) value="{{ $slip?->lot_number }}" @endif placeholder="Enter lot number" class="form-control mw-100" id="lot_number" name="lot_number" />
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="mb-1">
                                                    <label class="form-label">Mfg. Year <span class="text-danger show_required_field_for_batch" style="display: none;">*</span></label>
                                                    <input type="number" @if(isset($slip)) value="{{ $slip?->manufacturing_year }}" @else value="{{ date('Y') }}" @endif pattern="\d{4}"  placeholder="YYYY" class="form-control mw-100" id="manufacturing_year" name="manufacturing_year" />
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="mb-1">
                                                    <label class="form-label">Expiry Date <span class="text-danger show_required_field_for_batch" style="display: none;">*</span> </label>
                                                    <input type="date" @if(isset($slip)) value="{{$slip?->expiry_date}}" @endif placeholder="Select" class="form-control mw-100" id="expiry_date" name="expiry_date" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
								 <div class="card-body customernewsection-form">
                                                <div class="border-bottom mb-2 pb-25">
                                                     <div class="row">

                                                        <div class="col-md-6">
                                                            <div class="newheader ">
                                                                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                                                                    <li class="nav-item " role="presentation">
                                                                        <button class="nav-link active fs-5" id="production-items-tab" data-bs-toggle="tab" data-bs-target="#production-items" type="button" role="tab" aria-controls="production-items" aria-selected="true">
                                                                            Production
                                                                        </button>
                                                                    </li>
                                                                    <li class="nav-item" role="presentation">
                                                                        <button class="nav-link fs-5" id="raw-materials-tab" data-bs-toggle="tab" data-bs-target="#raw-materials" type="button" role="tab" aria-controls="raw-materials" aria-selected="false">
                                                                            Consumption
                                                                        </button>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 text-sm-end" id = "add_delete_item_section">
                                                            <a href="#" onclick = "deleteItemRows();" class="btn btn-sm btn-outline-danger me-50 tab-action d-none" data-tab="production-items">
                                                                <i data-feather="x-circle"></i> Delete</a>
                                                            <a href="#" id = "add_item_section" class="btn btn-sm btn-outline-primary tab-action d-none" data-tab="production-items">
                                                                <i data-feather="plus"></i> Add Product
                                                            </a>
                                                            @if(!isset($slip->document_status) || $slip->document_status == ConstantHelper::DRAFT)
                                                                <a href="#" onclick="deleteItemRows();" class="btn btn-sm btn-outline-danger me-50 tab-action d-none" data-tab="raw-materials">
                                                                    <i data-feather="x-circle"></i> Delete
                                                                </a>

                                                                <a href="#" id="co_add_material" onclick="addConsumtionAlternateItems();" class="btn btn-sm btn-outline-primary tab-action d-none" disabled="true" data-tab="raw-materials">
                                                                    <i data-feather="plus"></i> Add Alternate
                                                                </a>
                                                            @endif

                                                         </div>
                                                    </div>
                                                </div>

                                             <div class="tab-content mt-1" id="productTabsContent">
                                                <div class="tab-pane fade show active" id="production-items" role="tabpanel" aria-labelledby="product-details-tab">
                                                    <div class="table-responsive pomrnheadtffotsticky">
                                                        <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"
                                                        data-json-key="components_json"
                                                        data-row-selector="tr[id^='item_row_0']"
                                                        >
                                                           <thead>
                                                                <tr>
                                                                   <th class="customernewsection-form">
                                                                       <div class="form-check form-check-primary custom-checkbox">
                                                                           <input type="checkbox" class="form-check-input" id="select_all_items_checkbox" oninput = "checkOrRecheckAllItems(this);">
                                                                           <label class="form-check-label" for="select_all_items_checkbox" ></label>
                                                                       </div>
                                                                   </th>
                                                                   <th>SO No.</th>
                                                                   <th width="150px">Customer</th>
                                                                   <th width="150px">Product Code</th>
                                                                   <th width="240px">Product Name</th>
                                                                   <th max-width="180px">Attributes</th>
                                                                   <th>UOM</th>
                                                                   <th class="text-end">SO Qty</th>
                                                                   <th class="text-end">Produced Qty</th>
                                                                   <th class="text-end">Accepted (A)</th>
                                                                   <th class="text-end" id="subprime_qty_col">Substandard (B)</th>
                                                                   <th class="text-end">Rejected (C)</th>
                                                                   {{-- @if($isWipQty) --}}
                                                                    <th class="{{ $isWipQty ? 'text-end' : 'd-none text-end'}}" id="wip_qty_col">WIP Qty</th>
                                                                    <th class="{{ $isWipQty ? 'text-end' : 'd-none text-end'}}" id="total_qty_col">Total Qty</th>
                                                                   {{-- @endif --}}
                                                                   {{-- @if(in_array($slip->document_status ?? [], ConstantHelper::DOCUMENT_STATUS_APPROVED))
                                                                    <th class="text-end">Rate</th>
                                                                    <th class="text-end">Value</th>
                                                                   @endif
                                                                   <th class="{{$machines->isNotEmpty() ? '' : 'd-none'}}" id="machineName">Machine</th>
                                                                   <th class="{{$machines->isNotEmpty() ? '' : 'd-none'}}" id="cycleCount">Cycle Count</th>
                                                                   <th class="{{$stationLines->count() ? '' : 'd-none'}}" id="prodLine">Line</th>
                                                                   <th class="{{$stationLines->count() ? '' : 'd-none'}}" id="prodSupervisor">Supervisor</th>
                                                                   @endif --}}
                                                                   <th class="{{$machines->isNotEmpty() ? '' : 'd-none'}}" id="machineName">Machine</th>
                                                                   <th class="{{$machines->isNotEmpty() ? '' : 'd-none'}}" id="cycleCount">Cycle Count</th>
                                                                   <th class="{{$stationLines->count() ? '' : 'd-none'}}" id="prodLine">Line</th>
                                                                   <th class="{{$stationLines->count() ? '' : 'd-none'}}" id="prodSupervisor">Supervisor</th>
                                                                   <th width="240px">Action</th>
                                                                 </tr>
                                                               </thead>
                                                               <tbody class="mrntableselectexcel" id="item_header">
                                                                   @include('productionSlip.partials.item-row-edit')
                                                               </tbody>

                                                            <tfoot>
                                                                <tr valign="top">
                                                                   @if (isset($slip))
                                                                       <td id = "item_details_td" colspan="{{$isWipQty ? '17' : '15'}}" rowspan="10">
                                                                    @else
                                                                       <td id = "item_details_td" colspan="{{$isWipQty ? '15' : '13'}}" rowspan="10">
                                                                   @endif
                                                                       <table class="table border">
                                                                           <tr>
                                                                               <td class="p-0">
                                                                                   <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Product Details</strong></h6>
                                                                               </td>
                                                                           </tr>
                                                                           <tr>
                                                                               <td class="poprod-decpt">
                                                                                   <div id ="current_item_cat_hsn">

                                                                                   </div>
                                                                               </td>
                                                                           </tr>
                                                                           <tr id = "current_item_specs_row">
                                                                               <td class="poprod-decpt">
                                                                                   <div id ="current_item_specs">

                                                                                   </div>
                                                                               </td>
                                                                           </tr>
                                                                           <tr id = "current_item_attribute_row">
                                                                               <td class="poprod-decpt">
                                                                                   <div id ="current_item_attributes">

                                                                                   </div>
                                                                               </td>
                                                                           </tr>
                                                                           <tr id = "current_item_stocks_row">
                                                                               <td class="poprod-decpt">
                                                                                   <div id ="current_item_stocks">

                                                                                   </div>
                                                                               </td>
                                                                           </tr>
                                                                           <tr id = "current_item_inventory">
                                                                               <td class="poprod-decpt">
                                                                                   <div id ="current_item_inventory_details">

                                                                                   </div>
                                                                               </td>
                                                                           </tr>
                                                                           <tr id = "current_item_qt_no_row">
                                                                               <td class="poprod-decpt">
                                                                                   <div id ="current_item_qt_no">

                                                                                   </div>
                                                                               </td>
                                                                           </tr>
                                                                           <tr id = "current_item_store_location_row">
                                                                               <td class="poprod-decpt">
                                                                                   <div id ="current_item_store_location">

                                                                                   </div>
                                                                               </td>
                                                                           </tr>
                                                                           <tr id = "current_item_description_row">
                                                                               <td class="poprod-decpt">
                                                                                   <span class="badge rounded-pill badge-light-secondary"><strong>Remarks</strong>: <span style = "text-wrap:auto;" id = "current_item_description"></span></span>
                                                                                </td>
                                                                           </tr>
                                                                           <tr id = "current_item_land_lease_agreement_row">
                                                                               <td class="poprod-decpt">
                                                                                   <div id ="current_item_land_lease_agreement">

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
                                                <div class="tab-pane fade" id="raw-materials" role="tabpanel" aria-labelledby="raw-materials-tab">
                                                    <div class="table-responsive pomrnheadtffotsticky">
                                                        <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                            <thead>
                                                                <tr>
                                                                @if(!isset($slip->document_status) || $slip->document_status == ConstantHelper::DRAFT)
                                                                    <th></th>
                                                                @endif
                                                                    <th>SO No.</th>
                                                                    <th>Item Code</th>
                                                                    <th>Item Name</th>
                                                                    <th>Item Type</th>
                                                                    <th max-width="180px">Attributes</th>
                                                                    <th>UOM</th>
                                                                    <th class="text-end">Required Qty</th>
                                                                    <th class="text-end">Consumed Qty</th>
                                                                    @if(in_array($slip->document_status ?? [], ConstantHelper::DOCUMENT_STATUS_APPROVED))
                                                                        {{-- <th class="text-end">Rate</th>
                                                                        <th class="text-end">Value</th> --}}
                                                                    @else
                                                                        <th class="text-end">Avl Stock</th>
                                                                    @endif
                                                                </tr>
                                                            </thead>
                                                               <tbody class="mrntableselectexcel" id="item_header">
                                                                   @include('productionSlip.partials.item-row-consumption')
                                                               </tbody>
                                                            <tfoot>
                                                                <tr valign="top">
                                                                   @if (isset($slip))
                                                                       <td id = "item_details_td" colspan="12" rowspan="10">
                                                                       @else
                                                                       <td id = "item_details_td" colspan="10" rowspan="10">
                                                                   @endif
                                                                       <table class="table border">
                                                                           <tr>
                                                                               <td class="p-0">
                                                                                   <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Item Details</strong></h6>
                                                                               </td>
                                                                           </tr>
                                                                           <tr>
                                                                               <td class="poprod-decpt">
                                                                                   <div id ="current_item_cat_hsn">

                                                                                   </div>
                                                                               </td>
                                                                           </tr>
                                                                           <tr id = "current_item_specs_row">
                                                                               <td class="poprod-decpt">
                                                                                   <div id ="current_item_specs">

                                                                                   </div>
                                                                               </td>
                                                                           </tr>
                                                                           <tr id = "current_item_attribute_row">
                                                                               <td class="poprod-decpt">
                                                                                   <div id ="current_item_attributes">

                                                                                   </div>
                                                                               </td>
                                                                           </tr>
                                                                           <tr id = "current_item_stocks_row">
                                                                               <td class="poprod-decpt">
                                                                                   <div id ="current_item_stocks">

                                                                                   </div>
                                                                               </td>
                                                                           </tr>
                                                                           <tr id = "current_item_inventory">
                                                                               <td class="poprod-decpt">
                                                                                   <div id ="current_item_inventory_details">

                                                                                   </div>
                                                                               </td>
                                                                           </tr>
                                                                           <tr id = "current_item_qt_no_row">
                                                                               <td class="poprod-decpt">
                                                                                   <div id ="current_item_qt_no">

                                                                                   </div>
                                                                               </td>
                                                                           </tr>
                                                                           <tr id = "current_item_store_location_row">
                                                                               <td class="poprod-decpt">
                                                                                   <div id ="current_item_store_location">

                                                                                   </div>
                                                                               </td>
                                                                           </tr>
                                                                           <tr id = "current_item_description_row">
                                                                               <td class="poprod-decpt">
                                                                                   <span class="badge rounded-pill badge-light-secondary"><strong>Remarks</strong>: <span style = "text-wrap:auto;" id = "current_item_description"></span></span>
                                                                                </td>
                                                                           </tr>
                                                                           <tr id = "current_item_land_lease_agreement_row">
                                                                               <td class="poprod-decpt">
                                                                                   <div id ="current_item_land_lease_agreement">

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
                            </div>

                            <div class="card">
                                <div class="card-body customernewsection-form">
                                    <div class="border-bottom mb-2 pb-25" id="componentSection">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row mt-2">
                                                    <div class="col-md-12">
                                                        <div class = "row">
                                                         <div class="col-md-4">
                                                            <div class="mb-1">
                                                                <label class="form-label">Upload Document</label>
                                                                <input type="file" class="form-control" name = "attachments[]" onchange = "addFiles(this,'main_order_file_preview')" max_file_count = "{{isset($maxFileCount) ? $maxFileCount : 10}}" multiple >
                                                                <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                                                            </div>
                                                        </div>
                                                        <div class = "col-md-6" style = "margin-top:19px;">
                                                            <div class = "row" id = "main_order_file_preview">
                                                            </div>
                                                        </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Final Remarks</label>
                                                            <textarea type="text" rows="4" class="form-control" placeholder="Enter Remarks here..." name = "final_remarks">{{isset($slip) ? $slip -> remarks : '' }}</textarea>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <div id="approval-reject-data-div" style="display: none;"></div>

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

    <div class="modal fade text-start" id="rescdule" tabindex="-1" aria-labelledby="header_pull_label" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1250px">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="header_pull_label">Select Document</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					 <div class="row">

                        <div class="col">
                            <div class="mb-1">
                            <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" id="customer_code_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                <input type = "hidden" id = "customer_id_qt_val"></input>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" id="item_name_input_pwo" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                <input type = "hidden" id = "item_id_pwo_val"></input>
                            </div>
                        </div>

                         <div class="col">
                            <div class="mb-1">
                                <label class="form-label">So No. <span class="text-danger">*</span></label>
                                <input type="text" id="document_so_no_input" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                            </div>
                        </div>

                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">MO No. <span class="text-danger">*</span></label>
                                <input type="text" id="document_mo_no_input" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                            </div>
                        </div>

                         <div class="col  mb-1">
                              <label class="form-label">&nbsp;</label><br/>
                              <button type="button"  onclick = "getOrders();"  class="btn btn-warning btn-sm clearPiFilter"><i data-feather="x-circle"></i> Clear</button>
                             {{-- <button onclick = "getOrders();" type = "button" class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button> --}}
                         </div>

						 <div class="col-md-12">
							<div class="table-responsive">
								<table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
									<thead>
										 <tr>
											<th>
                                                <div class="form-check form-check-inline me-0">
                                                    <input class="form-check-input" type="checkbox" name="podetail" id="inlineCheckbox1">
                                                </div>
                                            </th>
                                            <th>Customer </th>
											<th>MO No.</th>
											<th>MO Date</th>
											<th>Station</th>
                                            <th>SO No.</th>
                                            <th>SO Date</th>
											<th>Product Code</th>
											<th>Product Name</th>
											<th>Attributes</th>
											<th>UOM</th>
											<th>Quantity</th>
											<th>Balance Qty</th>
										  </tr>
										</thead>
										<tbody id = "qts_data_table">

									   </tbody>


								</table>
							</div>
						</div>


					 </div>
				</div>
				<div class="modal-footer text-end">
					<button type = "button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
					<button type = "button" class="btn btn-primary btn-sm" onclick = "processOrder();" data-bs-dismiss="modal"><i data-feather="check-circle"></i> Process</button>
				</div>
			</div>
		</div>
	</div>

    <div class="modal fade text-start" id="rescdule2" tabindex="-1" aria-labelledby="header_pull_label" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="header_pull_label">Select Document</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					 <div class="row">

                     <div class="col">
                            <div class="mb-1">
                            <label class="form-label">Customer <span class="text-danger">*</span></label>
                                <input type="text" id="customer_code_input_qt_land" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                <input type = "hidden" id = "customer_id_qt_val_land"></input>
                            </div>
                        </div>

                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Series <span class="text-danger">*</span></label>
                                <input type="text" id="book_code_input_qt_land" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                <input type = "hidden" id = "book_id_qt_val_land"></input>
                            </div>
                        </div>


                         <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Document No. <span class="text-danger">*</span></label>
                                <input type="text" id="document_no_input_qt_land" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                <input type = "hidden" id = "document_id_qt_val_land"></input>
                            </div>
                        </div>

                         <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Land Parcel <span class="text-danger">*</span></label>
                                <input type="text" id="land_parcel_input_qt_land" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                <input type = "hidden" id = "land_parcel_id_qt_val_land"></input>
                            </div>
                        </div>

                         <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Land Plots <span class="text-danger">*</span></label>
                                <input type="text" id="land_plot_input_qt_land" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                <input type = "hidden" id = "land_plot_id_qt_val_land"></input>
                            </div>
                        </div>

                         <div class="col  mb-1">
                              <label class="form-label">&nbsp;</label><br/>
                             <button onclick = "getOrders('land-lease');" type = "button" class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button>
                         </div>

						 <div class="col-md-12">
							<div class="table-responsive">
								<table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
									<thead>
										 <tr>
											<th>
											</th>
											<th>Series</th>
											<th>Document No.</th>
											<th>Document Date</th>
                                            <th>Customer</th>
											<th>Land Parcel</th>
											<th>Plots</th>
                                            <th>Service Type</th>
											<th>Amount</th>
											<th>Due Date</th>
										  </tr>
										</thead>
										<tbody id = "qts_data_table_land">

									   </tbody>


								</table>
							</div>
						</div>


					 </div>
				</div>
				<div class="modal-footer text-end">
					<button type = "button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
					<button type = "button" class="btn btn-primary btn-sm" onclick = "processOrder('land-lease');" data-bs-dismiss="modal"><i data-feather="check-circle"></i> Process</button>
				</div>
			</div>
		</div>
	</div>


    <div class="modal fade" id="Remarks" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog  modal-dialog-centered" >
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body px-sm-2 mx-50 pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">Add/Edit Remarks</h1>
					<p class="text-center">Enter the details below.</p>


                     <div class="row mt-2">


						<div class="col-md-12 mb-1">
							<label class="form-label">Remarks</label>
							<textarea class="form-control" current-item = "item_remarks_0" onchange = "changeItemRemarks(this);" id ="current_item_remarks_input" placeholder="Enter Remarks"></textarea>
						</div>

                    </div>



				</div>

				<div class="modal-footer justify-content-center">
						<button type="button" class="btn btn-outline-secondary me-1" onclick="closeModal('Remarks');">Cancel</button>
					<button type="button" class="btn btn-primary" onclick="closeModal('Remarks');">Submit</button>
				</div>
			</div>
		</div>
	</div>


    <div class="modal fade" id="FromLocation" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog  modal-dialog-centered" style="max-width: 900px">
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body px-sm-2 mx-50 pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">From Location</h1>
					<div class="table-responsive-md customernewsection-form">
								<table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
									<thead>
										 <tr>
                                            <th width="80px">S.No</th>
											<th>Rack</th>
											<th>Shelf</th>
											<th>Bin</th>
                                            <th width="50px">Qty</th>
										  </tr>
										</thead>
										<tbody id = "item_from_location_table" current-item-index = '0'>
									   </tbody>
								</table>
							</div>
				    </div>

				<div class="modal-footer justify-content-center">
						<button type="button" class="btn btn-outline-secondary me-1" onclick="closeModal('FromLocation');">Cancel</button>
					<button type="button" class="btn btn-primary" onclick="closeModal('FromLocation');">Submit</button>
				</div>
			</div>
		</div>
	</div>

    <div class="modal fade" id="ToLocation" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog  modal-dialog-centered" style="max-width: 900px">
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body px-sm-2 mx-50 pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">To Location</h1>

                    <a href="#" class="text-primary add-contactpeontxt mt-50 text-end" onclick = "addToLocationRow();">
                            <i data-feather='plus'></i> Add Location
                        </a>

					<div class="table-responsive-md customernewsection-form">
								<table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
									<thead>
										 <tr>
                                            <th width="50px">S.No</th>
											<th>Rack</th>
											<th>Shelf</th>
											<th>Bin</th>
                                            <th width="80px">Qty</th>
										  </tr>
										</thead>
										<tbody id = "item_to_location_table" current-item-index = '0'>


									   </tbody>


								</table>
							</div>

				</div>

				<div class="modal-footer justify-content-center">
						<button type="button" class="btn btn-outline-secondary me-1" onclick="closeModal('ToLocation');">Cancel</button>
					<button type="button" class="btn btn-primary" onclick="closeModal('ToLocation');">Submit</button>
				</div>
			</div>
		</div>
	</div>
    <div class="modal fade" id="amendConfirmPopup" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Amend
                    Invoice
                    </h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <input type="hidden" name="action_type" id="action_type_main">

                </div>
                <div class="modal-body pb-2">
                    <div class="row mt-1">
                    <div class="col-md-12">
                        <div class="mb-1">
                            <label class="form-label">Remarks</label>
                            <textarea name="amend_remarks" class="form-control cannot_disable"></textarea>
                        </div>
                        <div class = "row">
                            <div class = "col-md-8">
                                <div class="mb-1">
                                    <label class="form-label">Upload Document</label>
                                    <input name = "amend_attachments[]" onchange = "addFiles(this, 'amend_files_preview')" type="file" class="form-control cannot_disable" max_file_count = "2" multiple/>
                                </div>
                            </div>
                            <div class = "col-md-4" style = "margin-top:19px;">
                                <div class="row" id = "amend_files_preview">
                                </div>
                            </div>
                        </div>
                        <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                    </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary me-1" onclick = "closeModal('amendConfirmPopup');">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick = "submitAmend();">Submit</button>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Approve & Reject Modal --}}
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        {{-- <form class="ajax-submit-2" method="POST" action="{{ route('document.approval.materialIssue') }}" data-redirect="{{ $redirect_url }}" enctype='multipart/form-data'> --}}
          {{-- @csrf --}}

         <div class="modal-header">
            <div>
               <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="approve_reject_heading_label">
               </h4>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body pb-2">
            <div class="row mt-1">
               <div class="col-md-12">
                <div id="approve-and-reject-div">
                    <input type="hidden" name="approve_reject_action_type" id="approve_reject_action_type">
                    <input type="hidden" name="pslip_id" value="{{isset($slip) ? $slip -> id : ''}}">
                    <div class="mb-1">
                       <label class="form-label">Remarks</label>
                       <textarea name="approver_reject_remarks" class="form-control cannot_disable"></textarea>
                    </div>
                    <div class="row">
                      <div class = "col-md-8">
                          <div class="mb-1">
                              <label class="form-label">Upload Document</label>
                              <input type="file" name = "approver_reject_attachments[]" multiple class="form-control cannot_disable" onchange = "addFiles(this, 'approval_files_preview');" max_file_count = "2"/>
                          </div>
                      </div>
                      <div class = "col-md-4" style = "margin-top:19px;">
                          <div class = "row" id = "approval_files_preview">

                          </div>
                      </div>
                    </div>
                </div>
                  <span class = "text-primary small">{{__("message.attachment_caption")}}</span>

               </div>
            </div>
         </div>
         <div class="modal-footer justify-content-center">
            <button type="reset" class="btn btn-outline-secondary me-1" onclick = "closeModal('approveModal');">Cancel</button>
            <button type="button" id="approve-and-reject-btn" class="btn btn-primary">Submit</button>
         </div>
       {{-- </form> --}}
      </div>
   </div>
</div>

<div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
  <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header p-0 bg-transparent">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body alertmsg text-center warning">
              <i data-feather='alert-circle'></i>
              <h2>Are you sure?</h2>
              <p>Are you sure you want to <strong>Amend</strong> this <strong>Material Issue</strong>?</p>
              <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
              <button type="button" data-bs-dismiss="modal" onclick = "amendConfirm();" class="btn btn-primary">Confirm</button>
          </div>
      </div>
  </div>
</div>

<!-- Inspection Checklist Modal  -->
@include('procurement.inspection.partials.inspection-checklist-modal')

<!-- Post Voucher Modal  -->
<div class="modal fade text-start show" id="postvoucher" tabindex="-1" aria-labelledby="postVoucherModal" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="postVoucherModal"> Voucher Details</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                    <div class="row">

                        <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label">Series <span class="text-danger">*</span></label>
                            <input id = "voucher_book_code" class="form-control" disabled="" >
                        </div>
                    </div>

                        <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label">Voucher No <span class="text-danger">*</span></label>
                            <input id = "voucher_doc_no" class="form-control" disabled="" value="">
                        </div>
                    </div>
                        <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label">Voucher Date <span class="text-danger">*</span></label>
                            <input id = "voucher_date" class="form-control" disabled="" value="">
                        </div>
                    </div>
                        <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                            <input id = "voucher_currency" class="form-control" disabled="" value="">
                        </div>
                    </div>

                        <div class="col-md-12">


                        <div class="table-responsive">
                            <table class="mt-1 table table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                <thead>
                                        <tr>
                                        <th>Type</th>
                                        <th>Group</th>
                                        <th>Leadger Code</th>
                                        <th>Leadger Name</th>
                                        <th class="text-end">Debit</th>
                                        <th class="text-end">Credit</th>
                                        </tr>
                                    </thead>
                                    <tbody id = "posting-table">


                                    </tbody>


                            </table>
                        </div>
                    </div>


                    </div>
            </div>
            <div class="modal-footer text-end">
                <button onclick = "postVoucher(this);" id = "posting_button" type = "button" class="btn btn-primary btn-sm waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Submit</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="bundleInfo" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
                <h1 class="text-center mb-1" id="shareProjectTitle">Packing Info</h1>

                <div class="table-responsive-md customernewsection-form"  style="max-height: 250px; overflow-y: auto;">
                            <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                                <thead>
                                        <tr>
                                        <th width="50px">S.No</th>
                                        <th>Package No</th>
                                        <th>Quantity</th>
                                        <th width="50px">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id = "bundle_schedule_table" current-item-index = '0'>


                                    </tbody>


                            </table>
                </div>

            </div>

            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="closeModal('bundleInfo');">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="closeModal('bundleInfo');">Submit</button>
                <a href="#" class="text-primary add-contactpeontxt mt-50 text-end" onclick = "addBundleQty();">
                        <i data-feather='plus'></i> Add Package
                </a>
            </div>
        </div>
    </div>
</div>


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
                <button type="button" class="btn btn-primary submitAttributeBtn">Select</button>
            </div>
        </div>
    </div>
</div>


@section('scripts')
<script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/modules/inspection-checklist.js')}}"></script>
<script src="{{ asset('app-assets/js/scripts/sweetalert.js') }}"></script>

<script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
            // Hide Inspection
            $('.inspectionChecklistBtn').hide();
        })

        $('#issues').on('change', function() {
            var issue_id = $(this).val();
            var seriesSelect = $('#series');

            seriesSelect.empty(); // Clear any existing options
            seriesSelect.append('<option value="">Select</option>');

            if (issue_id) {
                $.ajax({
                    url: "{{ url('get-series') }}/" + issue_id,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $.each(data, function(key, value) {
                            seriesSelect.append('<option value="' + key + '">' + value + '</option>');
                        });
                    }
                });
            }
        });

        $(document).on('click','#approve-and-reject-btn',(e) => {

            // Reset approval and reject data
            $('#approval-reject-data-div').empty();

            // Clone into approval-data
            $('#approve-and-reject-div').first().clone(true, true).appendTo('#approval-reject-data-div');

            // Hide the div
            $('#approval-reject-data-div').hide();

            // Hide the modal
            $('#approveModal').modal('hide');

            // Now submit the form
            $('#document_status').val('approved');
            $('#sale_invoice_form').submit();
        });


        $('#series').on('change', function() {
            var book_id = $(this).val();
            var request = $('#requestno');

            request.val(''); // Clear any existing options

            if (book_id) {
                $.ajax({
                    url: "{{ url('get-request') }}/" + book_id,
                    type: "GET",
                    dataType: "json",
                    success: function(data)
                        {
                            if (data.requestno) {
                            request.val(data.requestno);
                        }
                    }
                });
            }
        });

    function resetStoreFields()
    {
        $("#new_store_id_input").val("")
        $("#new_store_code_input").val("")

        $("#new_rack_id_input").val("")
        $("#new_rack_code_input").val("")

        $("#new_shelf_id_input").val("")
        $("#new_shelf_code_input").val("")

        $("#new_bin_id_input").val("")
        $("#new_bin_code_input").val("")

        $("#new_location_qty").val("")
    }


        function onChangeSeries(element)
        {
            document.getElementById("order_no_input").value = 12345;
        }

        function changeDropdownOptions(mainDropdownElement, dependentDropdownIds, dataKeyNames, routeUrl, resetDropdowns = null, resetDropdownIdsArray = [])
        {
            const mainDropdown = mainDropdownElement;
            const secondDropdowns = [];
            const dataKeysForApi = [];
            if (Array.isArray(dependentDropdownIds)) {
                dependentDropdownIds.forEach(elementId => {
                    if (elementId.type && elementId.type == "class") {
                        const multipleUiDropDowns = document.getElementsByClassName(elementId.value);
                        const secondDropdownInternal = [];
                        for (let idx = 0; idx < multipleUiDropDowns.length; idx++) {
                            secondDropdownInternal.push(document.getElementById(multipleUiDropDowns[idx].id));
                        }
                        secondDropdowns.push(secondDropdownInternal);
                    } else {
                        secondDropdowns.push(document.getElementById(elementId));
                    }
                });
            } else {
                secondDropdowns.push(document.getElementById(dependentDropdownIds))
            }

            if (Array.isArray(dataKeyNames)) {
                dataKeyNames.forEach(key => {
                    dataKeysForApi.push(key);
                })
            } else {
                dataKeysForApi.push(dataKeyNames);
            }

            if (dataKeysForApi.length !== secondDropdowns.length) {
                // console.log("Dropdown function error");
                return;
            }

            if (resetDropdowns) {
                const resetDropdownsElement = document.getElementsByClassName(resetDropdowns);
                for (let index = 0; index < resetDropdownsElement.length; index++) {
                    resetDropdownsElement[index].innerHTML = `<option value = '0'>Select</option>`;
                }
            }

            if (resetDropdownIdsArray) {
                if (Array.isArray(resetDropdownIdsArray)) {
                    resetDropdownIdsArray.forEach(elementId => {
                        let currentResetElement = document.getElementById(elementId);
                        if (currentResetElement) {
                            currentResetElement.innerHTML = `<option value = '0'>Select</option>`;
                        }
                    });
                } else {
                    const singleResetElement = document.getElementById(resetDropdownIdsArray);
                    if (singleResetElement) {
                        singleResetElement.innerHTML = `<option value = '0'>Select</option>`;
                    }
                }
            }

            const apiRequestValue = mainDropdown?.value;
            const apiUrl = routeUrl + apiRequestValue;
            fetch(apiUrl, {
                method : "GET",
                headers : {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            }).then(response => response.json()).then(data => {
                if (mainDropdownElement.id == "customer_id_input") {
                    if (data?.data?.currency_exchange?.status == false || data?.data?.error_message) {
                        Swal.fire({
                            title: 'Error!',
                            text: data?.data?.currency_exchange?.message ? data?.data?.currency_exchange?.message : data?.data?.error_message,
                            icon: 'error',
                        });
                        mainDropdownElement.value = "";
                        document.getElementById('currency_dropdown').innerHTML = "";
                        document.getElementById('currency_dropdown').value = "";
                        document.getElementById('payment_terms_dropdown').innerHTML = "";
                        document.getElementById('payment_terms_dropdown').value = "";
                        document.getElementById('current_billing_address_id').value = "";
                        document.getElementById('current_shipping_address_id').value = "";
                        document.getElementById('current_billing_address').textContent = "";
                        document.getElementById('current_shipping_address').textContent = "";
                        document.getElementById('customer_id_input').value = "";
                        document.getElementById('customer_code_input').value = "";
                        return;
                    }

                }
                // console.clear();
                // console.log(data);
                // return false;
                secondDropdowns.forEach((currentElement, idx) => {
                    if (Array.isArray(currentElement)) {
                        currentElement.forEach(currentElementInternal => {
                            currentElementInternal.innerHTML = `<option value = '0'>Select</option>`;
                            const response = data.data;
                            response?.[dataKeysForApi[idx]]?.forEach(item => {
                                const option = document.createElement('option');
                                option.value = item.value;
                                option.textContent = item.label;
                                currentElementInternal.appendChild(option);
                            })
                        });
                    } else {

                        currentElement.innerHTML = `<option value = '0'>Select</option>`;
                        const response = data.data;
                        response?.[dataKeysForApi[idx]]?.forEach((item, idxx) => {
                            if (idxx == 0) {
                                if (currentElement.id == "billing_address_dropdown") {
                                    document.getElementById('current_billing_address').textContent = item.label;
                                    document.getElementById('current_billing_address_id').value = item.id;
                                    // $('#billing_country_id_input').val(item.country_id).trigger('change');
                                    // changeDropdownOptions(document.getElementById('billing_country_id_input'), ['billing_state_id_input'], ['states'], '/states/', null, ['billing_city_id_input']);
                                }
                                if (currentElement.id == "shipping_address_dropdown") {
                                    document.getElementById('current_shipping_address').textContent = item.label;
                                    document.getElementById('current_shipping_address_id').value = item.id;
                                    document.getElementById('current_shipping_country_id').value = item.country_id;
                                    document.getElementById('current_shipping_state_id').value = item.state_id;
                                    // $('#shipping_country_id_input').val(item.country_id).trigger('change');
                                    // changeDropdownOptions(document.getElementById('shipping_country_id_input'), ['shipping_state_id_input'], ['states'], '/states/', null, ['shipping_city_id_input']);
                                }
                                // if (currentElement.id == "billing_state_id_input") {
                                //     changeDropdownOptions(document.getElementById('billing_state_id_input'), ['billing_city_id_input'], ['cities'], '/cities/', null, []);
                                //     $('#billing_state_id_input').val(item.state_id).trigger('change');
                                //     console.log("STATEID", item);

                                // }
                                // if (currentElement.id == "shipping_state_id_input") {
                                //     changeDropdownOptions(document.getElementById('shipping_state_id_input'), ['shipping_city_id_input'], ['cities'], '/cities/', null, []);
                                //     $('#shipping_state_id_input').val(item.state_id).trigger('change');
                                //     console.log("STATEID", item);

                                // }
                            }
                            const option = document.createElement('option');
                            option.value = item.value;
                            option.textContent = item.label;
                            if (idxx == 0 && (currentElement.id == "billing_address_dropdown" || currentElement.id == "shipping_address_dropdown")) {
                                option.selected = true;
                            }
                            currentElement.appendChild(option);
                        })
                    }
                });
            }).catch(error => {
                mainDropdownElement.value = "";
                document.getElementById('currency_dropdown').innerHTML = "";
                document.getElementById('currency_dropdown').value = "";
                document.getElementById('payment_terms_dropdown').innerHTML = "";
                document.getElementById('payment_terms_dropdown').value = "";
                document.getElementById('current_billing_address_id').value = "";
                document.getElementById('current_shipping_address_id').value = "";
                document.getElementById('current_billing_address').textContent = "";
                document.getElementById('current_shipping_address').textContent = "";
                document.getElementById('customer_id_input').value = "";
                document.getElementById('customer_code_input').value = "";
                // console.log("Error : ", error);
                return;
            })
        }

        function deleteItemRows() {
        Swal.fire({
                title: 'Are you sure?',
                text: "Do you really want to delete this?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                
            if (result.isConfirmed) {
                        
            let deletedItemIds = JSON.parse(localStorage.getItem('deletedSiItemIds')) || [];
            let deletedConsItemIds = JSON.parse(localStorage.getItem('deletedConsItemIds')) || [];
            const allRowsCheck = document.querySelectorAll('#production-items .item_row_checks');
            const allConsCheck = document.querySelectorAll('#raw-materials .consumption_row_checks');

            let deleteableElementsId = [];

            for (let index = allRowsCheck.length - 1; index >= 0; index--) {

                if (allRowsCheck[index]) {
                    const currentRowIndex = allRowsCheck[index].getAttribute('del-index');
                    var currentRowcheckbox = document.getElementById('item_row_check_' + currentRowIndex);
                    if(!currentRowcheckbox){
                        var currentRowcheckbox = document.getElementById('item_checkbox_' + currentRowIndex);
                    }
                    if(currentRowcheckbox.checked){
                        const inputElement = document.getElementById('item_row_' + currentRowIndex);
                        if (inputElement) {
                            const dataId = inputElement.getAttribute('data-id');
                            if (dataId) {
                                deletedItemIds.push(dataId);
                            }
                            deleteableElementsId.push('item_row_' + currentRowIndex);
                        }
                    }
                }
            }

            for (let index2 = 0; index2 < deleteableElementsId.length; index2++) {
                let row = document.getElementById(deleteableElementsId[index2]);
                if (row) {
                    let moProductId = $(row).find("input[name*='mo_product_id']").val();
                    let consumptionItems = $(`#raw-materials tbody input[data-mo-product-id="${moProductId}"]`);
                    if (consumptionItems.length) {
                        consumptionItems.each(function () {
                            $(this).closest('tr').remove();
                        });
                    }
                    row.remove();
                }
            }

            // write code for consumption
            for (let Consindex = allConsCheck.length - 1; Consindex >= 0; Consindex--) {
                if (allConsCheck[Consindex]) {
                    const parentRow = allConsCheck[Consindex].closest('tr');
                    const currentRowIndex = parentRow.getAttribute('data-index');
                    const currentRow = document.querySelector('#raw-materials #item_row_' + currentRowIndex);
                    const currentConcheckbox = document.getElementById('item_co_row_check_' + currentRowIndex);

                    if (currentConcheckbox && currentConcheckbox.checked) {

                        if (currentRow) {
                            const dataId = currentRow.getAttribute('data-id');
                            const alternateId = currentRow.getAttribute('data-altr-id');

                            if(!alternateId) {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Only alternate item can be deleted.',
                                    icon: 'error',
                                });

                                return false;
                            }

                            if (dataId) {
                                deletedConsItemIds.push(dataId);
                            }
                            currentRow.remove();
                        }
                    }
                }
            }

            localStorage.setItem('deletedSiItemIds', JSON.stringify(deletedItemIds));
            let itemsIds=JSON.parse(localStorage.getItem('deletedSiItemIds')) || [];
            const uniqueIds = [...new Set(deletedConsItemIds)];
            if (itemsIds.length === 0) {
                localStorage.setItem('deletedConsItemIds', JSON.stringify(uniqueIds));
            } else {
                localStorage.setItem('deletedConsItemIds', JSON.stringify([]));
            }
            console.log(JSON.parse(localStorage.getItem('deletedSiItemIds')) || [])
            console.log(JSON.parse(localStorage.getItem('deletedConsItemIds')) || [])
            // const allRowsNew = document.querySelectorAll('#production-items .item_row_checks');

            // if (allRowsNew.length > 0) {
            //     disableHeader();
            // } else {
            //     enableHeader();
            // }
                }
            });
        }

        function setItemRemarks(elementId) {
            const currentRemarksValue = document.getElementById(elementId).value;
            const modalInput = document.getElementById('current_item_remarks_input');
            modalInput.value = currentRemarksValue;
            modalInput.setAttribute('current-item', elementId);
        }

        function changeItemRemarks(element)
        {
            var newVal = element.value;
            newVal = newVal.substring(0,255);
            element.value = newVal;
            const elementToBeChanged = document.getElementById(element.getAttribute('current-item'));
            if (elementToBeChanged) {
                elementToBeChanged.value = newVal;
            }
        }

        function changeItemQty(element, index)
        {
            const docType = $("#service_id_input").val();
            const invoiceToFollow = $("#invoice_to_follow_input").val() == "yes";

            var inputNumValue = parseFloat(element.value ? element.value  : 0);
            if (element.hasAttribute('max'))
            {
                var maxInputVal = parseFloat(element.getAttribute('max'));
                if (inputNumValue > maxInputVal) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Quantity cannot be greater than ' + maxInputVal,
                        icon: 'error',
                    });
                    element.value = (parseFloat(maxInputVal ? maxInputVal  : 0)).toFixed(2)
                    // return;
                }
            }
            // if (element.hasAttribute('max-stock'))
            // {
            //     var maxInputVal = parseFloat(element.getAttribute('max-stock'));
            //     if (inputNumValue > maxInputVal) {
            //         Swal.fire({
            //             title: 'Error!',
            //             text: 'Qty cannot be greater than confirmed stock',
            //             icon: 'error',
            //         });
            //         element.value = (parseFloat(maxInputVal ? maxInputVal  : 0)).toFixed(2)
            //         // return;
            //     }
            // }

            assignDefaultBundleInfoArray(index);
            updateQty(element,index);
        }

        function updateQty(element, index) {
            const totalProduced = parseFloat($("#item_qty_" + index).val()) || 0;
            // console.log(element, totalProduced);
            $("#consumption_qty_" + index).val(parseFloat($("#consumption_item_qty_" + index).val()) || 0);


            let acceptedQty = parseFloat($("#item_accepted_qty_" + index).val()) || 0;
            let subPrimedQty = parseFloat($("#item_sub_prime_qty_" + index).val()) || 0;
            const changedType = element.name.includes('accepted')
                ? 'accepted'
                : element.name.includes('sub_prime')
                ? 'sub_prime'
                : element.name.includes('item_qty')
                ? 'qty'
                : '';

            if(changedType == 'qty') {
                $("#item_accepted_qty_" + index).val(totalProduced);
                acceptedQty = totalProduced;
            }
            // Validation: Accepted Qty must be ≤ Produced Qty
            if (acceptedQty > totalProduced) {
                alert("Accepted Qty cannot be greater than Produced Qty.");
                $("#item_accepted_qty_" + index).val('');
                acceptedQty = 0;
            }
            // Validation: Sub-Prime Qty must be ≤ (Produced - Accepted)
            if (subPrimedQty > (totalProduced - acceptedQty)) {
                alert("Sub-Prime Qty cannot be greater than (Produced - Accepted).");
                $("#item_sub_prime_qty_" + index).val('');
                subPrimedQty = 0;
            }
            // Auto-calculate Rejected Qty
            let rejectedQty = totalProduced - acceptedQty - subPrimedQty;
            $("#item_rejected_qty_" + index).val(Math.max(rejectedQty, 0));
        }

        function addHiddenInput(id, val, name, classname, docId, dataId = null)
        {
            const newHiddenInput = document.createElement("input");
            newHiddenInput.setAttribute("type", "hidden");
            newHiddenInput.setAttribute("name", name);
            newHiddenInput.setAttribute("id", id);
            newHiddenInput.setAttribute("value", val);
            newHiddenInput.setAttribute("class", classname);
            newHiddenInput.setAttribute('data-id', dataId ? dataId : '');
            document.getElementById(docId).appendChild(newHiddenInput);
        }

        function renderIcons()
        {
            feather.replace()
        }

        function onItemClick(itemRowId)
        {
            const docType = $("#service_id_input").val();
            const invoiceToFollowParam = $("invoice_to_follow_input").val() == "yes";

            const hsn_code = document.getElementById('items_dropdown_'+ itemRowId).getAttribute('hsn_code');
            const item_name = document.getElementById('items_dropdown_'+ itemRowId).getAttribute('item-name');
            const attributes = JSON.parse(document.getElementById('items_dropdown_'+ itemRowId).getAttribute('attribute-array'));
            const specs = JSON.parse(document.getElementById('items_dropdown_'+ itemRowId).getAttribute('specs'));
            // const locations = JSON.parse(decodeURIComponent(document.getElementById('data_stores_'+ itemRowId).getAttribute('data-stores')));

            const qtDetailsRow = document.getElementById('current_item_qt_no_row');
            const qtDetails = document.getElementById('current_item_qt_no');

            //Reference From
            const referenceFromLabels = document.getElementsByClassName("reference_from_label_" + itemRowId);
            if (referenceFromLabels && referenceFromLabels.length > 0)
            {
                qtDetailsRow.style.display = "table-row";
                referenceFromLabelsHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Reference From</strong>`;
                for (let index = 0; index < referenceFromLabels.length; index++) {
                    referenceFromLabelsHTML += `<span class="badge rounded-pill badge-light-primary">${referenceFromLabels[index].value}</span>`
                }
                qtDetails.innerHTML = referenceFromLabelsHTML;
            }
            else
            {
                qtDetailsRow.style.display = "none";
                qtDetails.innerHTML = ``;
            }

            const leaseAgreementDetailsRow = document.getElementById('current_item_land_lease_agreement_row');
            const leaseAgreementDetails = document.getElementById('current_item_land_lease_agreement');
            //assign agreement details
            let agreementNo = document.getElementById('land_lease_agreement_no_' + itemRowId)?.value;
            let leaseEndDate = document.getElementById('land_lease_end_date_' + itemRowId)?.value;
            let leaseDueDate = document.getElementById('land_lease_due_date_' + itemRowId)?.value;
            let repaymentPeriodType = document.getElementById('land_lease_repayment_period_' + itemRowId)?.value;

            if (agreementNo && leaseEndDate && leaseDueDate && repaymentPeriodType) {
                leaseAgreementDetails.style.display = "table-row";
                leaseAgreementDetails.innerHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Agreement Details</strong>:<span class="badge rounded-pill badge-light-primary"><strong>Agreement No</strong>: ${agreementNo}</span><span class="badge rounded-pill badge-light-primary"><strong>Lease End Date</strong>: ${leaseEndDate}</span><span class="badge rounded-pill badge-light-primary"><strong>Repayment Schedule</strong>: ${repaymentPeriodType}</span><span class="badge rounded-pill badge-light-primary"><strong>Due Date</strong>: ${leaseDueDate}</span>`;
            } else {
                leaseAgreementDetails.style.display = "none";
                leaseAgreementDetails.innerHTML = "";
            }
            //assign land plot details
            let parcelName = document.getElementById('land_lease_land_parcel_' + itemRowId)?.value;
            let plotsName = document.getElementById('land_lease_land_plots_' + itemRowId)?.value;

            let qtDocumentNo = document.getElementById('qt_document_no_'+ itemRowId);
            let qtBookCode = document.getElementById('qt_book_code_'+ itemRowId);
            let qtDocumentDate = document.getElementById('qt_document_date_'+ itemRowId);

            qtDocumentNo = qtDocumentNo?.value ? qtDocumentNo.value : '';
            qtBookCode = qtBookCode?.value ? qtBookCode.value : '';
            qtDocumentDate = qtDocumentDate?.value ? qtDocumentDate.value : '';

            // if (qtDocumentNo && qtBookCode && qtDocumentDate) {
            //     qtDetailsRow.style.display = "table-row";
            //     qtDetails.innerHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Reference From</strong>:<span class="badge rounded-pill badge-light-primary"><strong>Document No: </strong>: ${qtBookCode + "-" + qtDocumentNo}</span><span class="badge rounded-pill badge-light-primary"><strong>Document Date: </strong>: ${qtDocumentDate}</span>`;

            //     if (parcelName && plotsName) {
            //         qtDetails.innerHTML =  qtDetails.innerHTML + `<span class="badge rounded-pill badge-light-primary"><strong>Land Parcel</strong>: ${parcelName}</span><span class="badge rounded-pill badge-light-primary"><strong>Plots</strong>: ${plotsName}</span>`;
            //     }
            // } else {
            //     qtDetailsRow.style.display = "none";
            //     qtDetails.innerHTML = ``;
            // }
            // document.getElementById('current_item_hsn_code').innerText = hsn_code;
            var innerHTMLAttributes = ``;
            attributes.forEach(element => {
                var currentOption = '';
                element.values_data.forEach(subElement => {
                    if (subElement.selected) {
                        currentOption = subElement.value;
                    }
                });
                innerHTMLAttributes +=  `<span class="badge rounded-pill badge-light-primary"><strong>${element.group_name}</strong>: ${currentOption}</span>`;
            });
            var specsInnerHTML = ``;
            specs.forEach(spec => {
                specsInnerHTML +=  `<span class="badge rounded-pill badge-light-primary "><strong>${spec.specification_name}</strong>: ${spec.value}</span>`;
            });

            document.getElementById('current_item_attributes').innerHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Attributes</strong>:` + innerHTMLAttributes;
            if (innerHTMLAttributes) {
                document.getElementById('current_item_attribute_row').style.display = "table-row";
            } else {
                document.getElementById('current_item_attribute_row').style.display = "none";
            }
            document.getElementById('current_item_specs').innerHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Specifications</strong>:` + specsInnerHTML;
            if (specsInnerHTML) {
                document.getElementById('current_item_specs_row').style.display = "table-row";
            } else {
                document.getElementById('current_item_specs_row').style.display = "none";
            }
            const remarks = document.getElementById('item_remarks_' + itemRowId).value;
            if (specsInnerHTML) {
                document.getElementById('current_item_specs_row').style.display = "table-row";
            } else {
                document.getElementById('current_item_specs_row').style.display = "none";
            }
            document.getElementById('current_item_description').textContent = remarks;
            if (remarks) {
                document.getElementById('current_item_description_row').style.display = "table-row";
            } else {
                document.getElementById('current_item_description_row').style.display = "none";
            }
            let itemAttributes = JSON.parse(document.getElementById(`items_dropdown_${itemRowId}`).getAttribute('attribute-array'));
                    let selectedItemAttr = [];
                    if (itemAttributes && itemAttributes.length > 0) {
                        itemAttributes.forEach(element => {
                        element.values_data.forEach(subElement => {
                            if (subElement.selected) {
                                selectedItemAttr.push(subElement.id);
                            }
                        });
                    });
                    }
                        $.ajax({
                            url: "{{route('get_item_inventory_details')}}",
                            method: 'GET',
                            dataType: 'json',
                            data: {
                                quantity: document.getElementById('item_qty_' + itemRowId).value,
                                item_id: document.getElementById('items_dropdown_'+ itemRowId + '_value').value,
                                uom_id : document.getElementById('uom_dropdown_' + itemRowId).value,
                                selectedAttr : selectedItemAttr,
                                store_id: $("#item_store_to_" + itemRowId).val()
                            },
                            success: function(data) {
                                if (data.inv_qty && data.inv_uom) {
                                    document.getElementById('current_item_inventory').style.display = 'table-row';
                                    document.getElementById('current_item_inventory_details').innerHTML = `
                                    <span class="badge rounded-pill badge-light-primary"><strong>Inv. UOM</strong>: ${data.inv_uom}</span>
                                    <span class="badge rounded-pill badge-light-primary"><strong>Qty in ${data.inv_uom}</strong>: ${data.inv_qty}</span>
                                    `;
                                } else {
                                    document.getElementById('current_item_inventory').style.display = 'none';
                                    document.getElementById('current_item_inventory_details').innerHTML = ``;
                                }

                                if (data?.item && data?.item?.category && data?.item?.sub_category) {
                                    document.getElementById('current_item_cat_hsn').innerHTML = `
                                    <span class="badge rounded-pill badge-light-primary"><strong>Category</strong>: <span id = "item_category">${ data?.item?.category?.name}</span></span>
                                    <span class="badge rounded-pill badge-light-primary"><strong>Sub Category</strong>: <span id = "item_sub_category">${ data?.item?.sub_category?.name}</span></span>
                                    <span class="badge rounded-pill badge-light-primary"><strong>HSN</strong>: <span id = "current_item_hsn_code">${hsn_code}</span></span>
                                    `;
                                }
                                //Stocks
                                    if (data?.stocks) {
                                    document.getElementById('current_item_stocks_row').style.display = "table-row";
                                    document.getElementById('current_item_stocks').innerHTML = `
                                    <span class="badge rounded-pill badge-light-primary"><strong>Confirmed Stocks</strong>: <span id = "item_sub_category">${data?.stocks?.confirmedStockAltUom}</span></span>
                                    <span class="badge rounded-pill badge-light-primary"><strong>Pending Stocks</strong>: <span id = "item_category">${data?.stocks?.pendingStockAltUom}</span></span>
                                    `;
                                    var inputQtyBox = document.getElementById('item_qty_' + itemRowId);
                                    inputQtyBox.setAttribute('max-stock',data.stocks.confirmedStockAltUom);
                                    }
                                 else {
                                        document.getElementById('current_item_stocks_row').style.display = "none";
                                    }
                            },
                            error: function(xhr) {
                                console.error('Error fetching customer data:', xhr.responseText);
                            }
                        });

        }

        function checkStockData(itemRowId)
        {
            let itemAttributes = JSON.parse(document.getElementById(`items_dropdown_${itemRowId}`).getAttribute('attribute-array'));
                    let selectedItemAttr = [];
                    if (itemAttributes && itemAttributes.length > 0) {
                        itemAttributes.forEach(element => {
                        element.values_data.forEach(subElement => {
                            if (subElement.selected) {
                                selectedItemAttr.push(subElement.id);
                            }
                        });
                    });
                    }
            $.ajax({
                url: "{{route('get_item_inventory_details')}}",
                method: 'GET',
                dataType: 'json',
                data: {
                    quantity: document.getElementById('item_qty_' + itemRowId).value,
                    item_id: document.getElementById('items_dropdown_'+ itemRowId + '_value').value,
                    uom_id : document.getElementById('uom_dropdown_' + itemRowId).value,
                    selectedAttr : selectedItemAttr,
                    store_id: $("#item_store_" + itemRowId).val()
                },
                success: function(data) {

                        var inputQtyBox = document.getElementById('item_qty_' + itemRowId);
                        var actualQty = inputQtyBox.value;
                        inputQtyBox.setAttribute('max-stock',data.stocks.confirmedStockAltUom);
                        if (inputQtyBox.getAttribute('max-stock')) {
                            var maxStock = parseFloat(inputQtyBox.getAttribute('max-stock') ? inputQtyBox.getAttribute('max-stock') : 0);
                            if (maxStock <= 0) {
                                inputQtyBox.value = 0;
                                inputQtyBox.readOnly = true;
                            } else {
                                if (actualQty > maxStock) {
                                    inputQtyBox.value = maxStock;
                                    inputQtyBox.readOnly  = false;
                                } else {
                                    inputQtyBox.readOnly  = false;
                                }
                            }
                        }

                },
                error: function(xhr) {
                    console.error('Error fetching customer data:', xhr.responseText);
                }
                });
        }

        function getStoresData(itemRowId, qty = null, callOnClick = true)
        {
            const qtyElement = document.getElementById('item_qty_' + itemRowId);
            if (qtyElement && qtyElement.value > 0) {
            const itemDetailId = document.getElementById('item_row_' + itemRowId).getAttribute('data-detail-id');
            const itemId = document.getElementById('items_dropdown_'+ itemRowId).getAttribute('data-id');
            let itemAttributes = JSON.parse(document.getElementById(`items_dropdown_${itemRowId}`).getAttribute('attribute-array'));
                    let selectedItemAttr = [];
                    if (itemAttributes && itemAttributes.length > 0) {
                        itemAttributes.forEach(element => {
                        element.values_data.forEach(subElement => {
                            if (subElement.selected) {
                                selectedItemAttr.push(subElement.id);
                            }
                        });
                    });
                    }
                        const storeElement = document.getElementById('data_stores_' + itemRowId);

                        const rateInput = document.getElementById('item_rate_' + itemRowId);
                        const valueInput = document.getElementById('item_value_' + itemRowId);

                        $.ajax({
                        url: "{{route('get_item_store_details')}}",
                            method: 'GET',
                            dataType: 'json',
                            data : {
                                item_id : itemId,
                                uom_id : $("#uom_dropdown_" + itemRowId).val(),
                                selectedAttr : selectedItemAttr,
                                quantity : qty ? qty : document.getElementById('item_qty_' + itemRowId).value,
                                is_edit : "{{isset($slip) ? 1 : 0}}",
                                header_id : "{{isset($slip) ? $slip -> id : null}}",
                                detail_id : itemDetailId,
                                store_id: $("#item_store_" + itemRowId).val()
                            },
                            success: function(data) {
                                if (data?.stores && data?.stores?.records && data?.stores?.records?.length > 0 && data.stores.code == 200) {
                                    var storesArray = [];
                                    var dataRecords = data?.stores?.records;
                                    var totalValue = 0;
                                    var totalRate = 0;
                                    dataRecords.forEach(storeData => {
                                        storesArray.push({
                                            store_id : storeData.store_id,
                                            store_code : storeData.store,
                                            rack_id : storeData.rack_id,
                                            rack_code : storeData.rack ? storeData.rack : '',
                                            shelf_id : storeData.shelf_id,
                                            shelf_code : storeData.shelf ? storeData.shelf : '',
                                            bin_id : storeData.bin_id,
                                            bin_code : storeData.bin ? storeData.bin : '',
                                            qty : parseFloat(storeData.allocated_quantity_alt_uom).toFixed(2),
                                            inventory_uom_qty : parseFloat(storeData.allocated_quantity).toFixed(2)
                                        })
                                        totalValue+= parseFloat(storeData.cost_per_unit) * parseFloat(storeData.allocated_quantity_alt_uom);
                                    });
                                    var actualQty = qtyElement.value;
                                    if (actualQty > 0) {
                                        valueInput.value = totalValue.toFixed(2);
                                        totalRate = parseFloat(totalValue) / parseFloat(qty ? qty : qtyElement.value);
                                        rateInput.value = parseFloat(totalRate).toFixed(2);
                                    } else {
                                        rateInput.value = 0.00;
                                        valueInput.value = 0.00;
                                    }
                                    storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify(storesArray)));
                                    if (callOnClick) {
                                        onItemClick(itemRowId, callOnClick);
                                    }
                                } else if (data?.stores?.code == 202) {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: data?.stores?.message,
                                        icon: 'error',
                                    });
                                    storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify([])));
                                    document.getElementById('item_qty_' + itemRowId).value = 0.00;
                                    if (callOnClick) {
                                        onItemClick(itemRowId, callOnClick);
                                    }
                                    rateInput.value = 0.00;
                                    valueInput.value = 0.00;
                                } else {
                                    storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify([])));
                                    if (callOnClick) {
                                        onItemClick(itemRowId, callOnClick);
                                    }
                                    rateInput.value = 0.00;
                                    valueInput.value = 0.00;
                                }
                            },
                            error: function(xhr) {
                                console.error('Error fetching customer data:', xhr.responseText);
                                storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify([])));
                                rateInput.value = 0.00;
                                valueInput.value = 0.00;

                            }
                        });
            }
        }

        function openModal(id)
        {
            $('#' + id).modal('show');
        }

        function closeModal(id)
        {
            $('#' + id).modal('hide');
        }

        function submitForm(status) {
            enableHeader();
        }

        function disableHeader()
        {
            const disabledFields = document.getElementsByClassName('disable_on_edit');
            for (let disabledIndex = 0; disabledIndex < disabledFields.length; disabledIndex++) {
                disabledFields[disabledIndex].disabled = true;
            }
        }

    function enableHeader()
    {
        const disabledFields = document.getElementsByClassName('disable_on_edit');
            for (let disabledIndex = 0; disabledIndex < disabledFields.length; disabledIndex++) {
                disabledFields[disabledIndex].disabled = false;
            }
    }

    //Function to set values for edit form
    function editScript()
    {
        localStorage.setItem('deletedItemDiscTedIds', JSON.stringify([]));
        localStorage.setItem('deletedHeaderDiscTedIds', JSON.stringify([]));
        localStorage.setItem('deletedHeaderExpTedIds', JSON.stringify([]));
        localStorage.setItem('deletedSiItemIds', JSON.stringify([]));
        localStorage.setItem('deletedConsItemIds', JSON.stringify([]));
        localStorage.setItem('deletedAttachmentIds', JSON.stringify([]));
        const order = @json(isset($slip) ? $slip : null);
        if (order) {
            disableHeader();
            order.items.forEach((item, itemIndex) => {
                var itemLocations = [];
                var itemLocationsTo = [];
                let racksHTML = `<option disabled>Select</option>`;
                let binsHTML = `<option disabled>Select</option>`;
                if (item.to_item_locations && item.to_item_locations.length > 0) {
                    let racksPromise = $.ajax({
                        url: "{{ route('store.racksAndBins') }}",
                        type: "GET",
                        dataType: "json",
                        data: {
                            store_id: item.to_item_locations[0].store_id
                        }
                    });

                    racksPromise.then(data => {
                        let racksHTML = `<option value = "" disabled >Select</option>`;
                        let binsHTML = `<option value = "" disabled >Select</option>`;

                        if (data.data.racks) {
                            data.data.racks.forEach(rack => {
                                racksHTML += `<option value='${rack.id}'>${rack.rack_code}</option>`;
                            });
                        }
                        if (data.data.bins) {
                            data.data.bins.forEach(bin => {
                                binsHTML += `<option value='${bin.id}'>${bin.bin_code}</option>`;
                            });
                        }

                        let shelfPromises = item.to_item_locations.map(itemLoc => {
                            let shelfsHTML = `<option value="" disabled>Select</option>`;

                            if (itemLoc.rack_id) {
                                return $.ajax({
                                    url: "{{ route('store.rack.shelfs') }}",
                                    type: "GET",
                                    dataType: "json",
                                    data: {
                                        rack_id: itemLoc.rack_id
                                    }
                                }).then(shelfData => {
                                    if (shelfData.data.shelfs) {
                                        shelfData.data.shelfs.forEach(shelf => {
                                            shelfsHTML += `<option value='${shelf.id}'>${shelf.shelf_code}</option>`;
                                        });
                                    }

                                    itemLocationsTo.push({
                                        store_id: itemLoc.store_id,
                                        store_code: itemLoc.store_code,
                                        rack_id: itemLoc.rack_id,
                                        rack_code: itemLoc.rack_code,
                                        rack_html: racksHTML,
                                        shelf_id: itemLoc.shelf_id,
                                        shelf_code: itemLoc.shelf_code,
                                        shelf_html: shelfsHTML,
                                        bin_id: itemLoc.bin_id,
                                        bin_code: itemLoc.bin_code,
                                        bin_html: binsHTML,
                                        qty: itemLoc.quantity
                                    });
                                });
                            } else {
                                itemLocationsTo.push({
                                    store_id: itemLoc.store_id,
                                    store_code: itemLoc.store_code,
                                    rack_id: itemLoc.rack_id,
                                    rack_code: itemLoc.rack_code,
                                    rack_html: racksHTML,
                                    shelf_id: itemLoc.shelf_id,
                                    shelf_code: itemLoc.shelf_code,
                                    shelf_html: shelfsHTML,
                                    bin_id: itemLoc.bin_id,
                                    bin_code: itemLoc.bin_code,
                                    bin_html: binsHTML,
                                    qty: itemLoc.quantity
                                });
                                return Promise.resolve(); // Resolve immediately if no AJAX call is needed
                            }
                        });
                        return Promise.all(shelfPromises);
                    }).then(() => {
                        // console.log("All AJAX calls completed. Now executing final task.");
                        document.getElementById('data_stores_to_' + itemIndex)?.setAttribute('data-stores', encodeURIComponent(JSON.stringify(itemLocationsTo)))
                    }).catch(error => {
                        console.error("An error occurred:", error);
                        document.getElementById('data_stores_to_' + itemIndex)?.setAttribute('data-stores', encodeURIComponent(JSON.stringify(itemLocationsTo)))
                    });
                }
                let itemBundlesArray = [];

                item.bundles.forEach((itemBundle) => {
                    itemBundlesArray.push({
                        id : itemBundle.id,
                        bundle_no : itemBundle.bundle_no,
                        qty : itemBundle.qty
                    });
                });

                const bundleElement = document.getElementById('item_bundles_' + itemIndex);
                bundleElement.setAttribute('data-bundles', encodeURIComponent(JSON.stringify(itemBundlesArray)));

                itemUomsHTML = ``;
                if (item.item.uom && item.item.uom.id) {
                    itemUomsHTML += `<option value = '${item.item.uom.id}' ${item.item.uom.id == item.uom_id ? "selected" : ""}>${item.item.uom.alias}</option>`;
                }
                item.item.alternate_uoms.forEach(singleUom => {
                    if (singleUom.is_selling) {
                        itemUomsHTML += `<option value = '${singleUom.uom.id}' ${singleUom.uom.id == item.uom_id ? "selected" : ""} >${singleUom.uom?.alias}</option>`;
                    }
                });
                document.getElementById('uom_dropdown_' + itemIndex).innerHTML = itemUomsHTML;
                if (itemIndex==0){
                    onItemClick(itemIndex);
                }
            });
            //Disable header fields which cannot be changed
            disableHeader();
            //Set all documents
            order.media_files.forEach((mediaFile, mediaIndex) => {
                appendFilePreviews(mediaFile.file_url, 'main_order_file_preview', mediaIndex, mediaFile.id, order.document_status == 'draft' ? false : true);
            });
        }
        renderIcons();
        let finalAmendSubmitButton = document.getElementById("amend-submit-button");
        viewModeScript(finalAmendSubmitButton ? false : true);

    }

    document.addEventListener('DOMContentLoaded', function() {
        const order = @json(isset($slip) ? $slip : null);
        onSeriesChange(document.getElementById('service_id_input'), order ? false : true);
    });

    function resetParametersDependentElements(reset = true)
    {
        var selectionSection = document.getElementById('selection_section');
        if (selectionSection) {
            selectionSection.style.display = "none";
        }
        document.getElementById('add_item_section').style.display = "none";
        $("#order_date_input").attr('max', "<?php echo date('Y-m-d'); ?>");
        $("#order_date_input").attr('min', "<?php echo date('Y-m-d'); ?>");
        $("#order_date_input").off('input');
        if (reset) {
            $("#order_date_input").val(moment().format("YYYY-MM-DD"));
        }
        $('#order_date_input').on('input', function() {
            restrictBothFutureAndPastDates(this);
        });
    }

    function getDocNumberByBookId(element, reset = true)
    {
        resetParametersDependentElements(reset);
        let bookId = element.value;
        let actionUrl = '{{route("book.get.doc_no_and_parameters")}}'+'?book_id='+bookId + "&document_date=" + $("#order_date_input").val();
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                if (data.status == 200) {
                  $("#book_code_input").val(data.data.book_code);
                  if(!data.data.doc.document_number) {
                    if (reset) {
                        $("#order_no_input").val('');
                    }
                  }
                  if (reset) {
                    $("#order_no_input").val(data.data.doc.document_number);
                    $("#lot_number").val(data.data.lot_number);
                  }
                  if(data.data.doc.type == 'Manually') {
                     $("#order_no_input").attr('readonly', false);
                  } else {
                     $("#order_no_input").attr('readonly', true);
                  }
                  enableDisableQtButton();
                  if (data.data.parameters)
                  {
                    implementBookParameters(data.data.parameters);
                  }
                }
                if(data.status == 404) {
                    if (reset) {
                        $("#book_code_input").val("");
                        // alert(data.message);
                    }
                    enableDisableQtButton();
                }
                if(data.status == 500) {
                    if (reset) {
                        $("#book_code_input").val("");
                        $("#series_id_input").val("");
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                        });
                    }
                    enableDisableQtButton();
                }
                if (reset == false) {
                    viewModeScript();
                }
            });
        });
    }

    function onDocDateChange()
    {
        let bookId = $("#series_id_input").val();
        let actionUrl = '{{route("book.get.doc_no_and_parameters")}}'+'?book_id='+bookId + "&document_date=" + $("#order_date_input").val();
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                if (data.status == 200) {
                  $("#book_code_input").val(data.data.book_code);
                  if(!data.data.doc.document_number) {
                     $("#order_no_input").val('');
                  }
                  $("#order_no_input").val(data.data.doc.document_number);
                  if(data.data.doc.type == 'Manually') {
                     $("#order_no_input").attr('readonly', false);
                  } else {
                     $("#order_no_input").attr('readonly', true);
                  }
                }
                if(data.status == 404) {
                    $("#book_code_input").val("");
                    alert(data.message);
                }
            });
        });
    }

    function implementBookParameters(paramData)
    {
        var selectedRefFromServiceOption = paramData.reference_from_service;
        var selectedBackDateOption = paramData.back_date_allowed;
        var selectedFutureDateOption = paramData.future_date_allowed;
        // var inspectionRequired	 = paramData.inspection_required;
        var inspectionRequired = paramData?.inspection_required ?? [];

        // console.log('inspectionRequired', inspectionRequired);

        $('#inspection_required_key').val(inspectionRequired);

        // Handle Inspection Show/Hide
        if (inspectionRequired.includes("yes") || inspectionRequired =='yes') {
            $('.inspectionChecklistBtn').show();
        }

        var invoiceToFollowParam = paramData?.invoice_to_follow;
        var issueTypeParameters = paramData?.issue_type;
        if (selectedRefFromServiceOption) {
            var selectVal = selectedRefFromServiceOption;
            if (selectVal && selectVal.length > 0) {
                selectVal.forEach(selectSingleVal => {
                    if (selectSingleVal == 'mo') {
                        var selectionSectionElement = document.getElementById('selection_section');
                        if (selectionSectionElement) {
                            selectionSectionElement.style.display = "";
                        }
                        var selectionPopupElement = document.getElementById('pwo_selection');
                        if (selectionPopupElement)
                        {
                            selectionPopupElement.style.display = ""
                        }
                    }
                    if (selectSingleVal == 'd') {
                        document.getElementById('add_item_section').style.display = "";
                    }
                });
            }
        }

        var backDateAllow = false;
        var futureDateAllow = false;

        //Back Date Allow
        if (selectedBackDateOption) {
            var selectVal = selectedBackDateOption;
            if (selectVal && selectVal.length > 0) {
                if (selectVal[0] == "yes") {
                    backDateAllow = true;
                } else {
                    backDateAllow = false;
                }
            }
        }

        //Future Date Allow
        if (selectedFutureDateOption) {
            var selectVal = selectedFutureDateOption;
            if (selectVal && selectVal.length > 0) {
                if (selectVal[0] == "yes") {
                    futureDateAllow = true;
                } else {
                    futureDateAllow = false;
                }
            }
        }

        if (backDateAllow && futureDateAllow) { // Allow both ways (future and past)
            $("#order_date_input").removeAttr('max');
            $("#order_date_input").removeAttr('min');
            $("#order_date_input").off('input');
        }
        if (backDateAllow && !futureDateAllow) { // Allow only back date
            $("#order_date_input").removeAttr('min');
            $("#order_date_input").attr('max', "<?php echo date('Y-m-d'); ?>");
            $("#order_date_input").off('input');
            $('#order_date_input').on('input', function() {
                restrictFutureDates(this);
            });
        }
        if (!backDateAllow && futureDateAllow) { // Allow only future date
            $("#order_date_input").removeAttr('max');
            $("#order_date_input").attr('min', "<?php echo date('Y-m-d'); ?>");
            $("#order_date_input").off('input');
            $('#order_date_input').on('input', function() {
                restrictPastDates(this);
            });
        }

        //Issue Type
        if (issueTypeParameters && issueTypeParameters.length > 0) {
            const issueTypeInput = document.getElementById('issue_type_input');
            if (issueTypeInput) {
                var issueTypeHtml = ``;
                var firstIssueType = null;
                issueTypeParameters.forEach((issueType, issueTypeIndex) => {
                    if (issueTypeIndex == 0) {
                        firstIssueType = issueType;
                    }
                    issueTypeHtml += `<option value = '${issueType}'> ${issueType} </option>`
                });
                if ("{{isset($slip)}}") {
                    firstIssueType = "{{isset($slip) ? $slip -> issue_type : ''}}";
                }
                issueTypeInput.innerHTML = issueTypeHtml;
                $("#issue_type_input").val(firstIssueType).trigger('input');
            }
        }
    }

    function enableDisableQtButton()
    {
        const bookId = document.getElementById('series_id_input').value;
        const bookCode = document.getElementById('book_code_input').value;
        const documentDate = document.getElementById('order_date_input').value;

        if (bookId && bookCode && documentDate) {
        //     let siButton = document.getElementById('select_si_button');
        //     if (siButton) {
        //         siButton.disabled = false;
        //     }
        //     let dnButton = document.getElementById('select_dn_button');
        //     if (dnButton) {
        //         dnButton.disabled = false;
        //     }
        //     let leaseButton = document.getElementById('select_lease_button');
        //     if (leaseButton) {
        //         leaseButton.disabled = false;
        //     }
            let orderButton = document.getElementById('select_pwo_button');
            if (orderButton) {
                orderButton.disabled = false;
            }
        } else {
        //     let siButton = document.getElementById('select_si_button');
        //     if (siButton) {
        //         siButton.disabled = true;
        //     }
        //     let dnButton = document.getElementById('select_dn_button');
        //     if (dnButton) {
        //         dnButton.disabled = true;
        //     }
        //     let leaseButton = document.getElementById('select_lease_button');
        //     if (leaseButton) {
        //         leaseButton.disabled = true;
        //     }
            let orderButton = document.getElementById('select_pwo_button');
            if (orderButton) {
                orderButton.disabled = true;
            }
        }
    }

    editScript();


    function checkItemAddValidation()
    {
        let addRow = $('#series_id_input').val &&  $('#order_no_input').val && $('#order_date_input').val;
        return addRow;
    }

    function setApproval(action)
    {
        document.getElementById('approve_reject_action_type').value = action;
        document.getElementById('approve_reject_heading_label').textContent = "Approve " + "Invoice";

    }

    function setReject(action)
    {
        document.getElementById('approve_reject_action_type').value = 'rejected';
        document.getElementById('approve_reject_heading_label').textContent = "Reject " + "Invoice";
    }

    function setFormattedNumericValue(element)
    {
        element.value = (parseFloat(element.value ? element.value  : 0)).toFixed(2)
    }

    function initializeAutocompleteQt(selector, selectorSibling, typeVal, labelKey1, labelKey2 = "") {
            $("#" + selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type: typeVal,
                            customer_id : $("#customer_id_qt_val").val(),
                            header_book_id : $("#series_id_input").val()
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: `${item[labelKey1]} (${item[labelKey2] ? item[labelKey2] : ''})`,
                                    code: item[labelKey1] || '',
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
                    $input.val(ui.item.label);
                    $("#" + selectorSibling).val(ui.item.id);
                    getOrders();
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

    //Disable form submit on enter button
    document.querySelector("form").addEventListener("keydown", function(event) {
        if (event.key === "Enter") {
            event.preventDefault();  // Prevent form submission
        }
    });
    $("input[type='text']").on("keydown", function(event) {
        if (event.key === "Enter") {
            event.preventDefault();  // Prevent form submission
        }
    });

    $(document).ready(function() {
        // Event delegation to handle dynamically added input fields
        $(document).on('input', '.decimal-input', function() {
            // Allow only numbers and a single decimal point
            this.value = this.value.replace(/[^0-9.]/g, ''); // Remove non-numeric characters

            // Prevent more than one decimal point
            if ((this.value.match(/\./g) || []).length > 1) {
                this.value = this.value.substring(0, this.value.length - 1);
            }

            // Optional: limit decimal places to 2
            if (this.value.indexOf('.') !== -1) {
                this.value = this.value.substring(0, this.value.indexOf('.') + 3);
            }
        });
    });


$(document).on('click', '#amendmentSubmit', (e) => {
   let actionUrl = "{{ route('sale.order.amend', isset($slip) ? $slip -> id : 0) }}";
   fetch(actionUrl).then(response => {
      return response.json().then(data => {
         if (data.status == 200) {
            Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success'
            });
            location.reload();
         } else {
            Swal.fire({
                title: 'Error!',
                text: data.message,
                icon: 'error'
            });
        }
      });
   });
});

var currentRevNo = $("#revisionNumber").val();

// # Revision Number On Change
$(document).on('change', '#revisionNumber', (e) => {
    e.preventDefault();
    let actionUrl = location.pathname + '?type=' + "{{request() -> type ?? 'si'}}" + '&revisionNumber=' + e.target.value;
    $("#revisionNumber").val(currentRevNo);
    window.open(actionUrl, '_blank'); // Opens in a new tab
    // console.log(actionUrl);
});

$(document).on('submit', '.ajax-submit-2', function (e) {
    e.preventDefault();
     var submitButton = (e.originalEvent && e.originalEvent.submitter)
                        || $(this).find(':submit');
    var submitButtonHtml = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
    submitButton.disabled = true;
    var method = $(this).attr('method');
    var url = $(this).attr('action');
    var redirectUrl = $(this).data('redirect');
    var data = new FormData($(this)[0]);

    var formObj = $(this);

    $.ajax({
        url,
        type: method,
        data,
        contentType: false,
        processData: false,
        success: function (res) {
            submitButton.disabled = false;
            submitButton.innerHTML = submitButtonHtml;
            $('.ajax-validation-error-span').remove();
            $(".is-invalid").removeClass("is-invalid");
            $(".help-block").remove();
            $(".waves-ripple").remove();
            Swal.fire({
                title: 'Success!',
                text: res.message,
                icon: 'success',
            });
            setTimeout(() => {
                if (res.store_id) {
                    location.href = `/stores/${res.store_id}/edit`;
                } else if (redirectUrl) {
                    location.href = redirectUrl;
                } else {
                    location.reload();
                }
            }, 1500);

        },
        error: function (error) {
            submitButton.disabled = false;
            submitButton.innerHTML = submitButtonHtml;
            $('.ajax-validation-error-span').remove();
            $(".is-invalid").removeClass("is-invalid");
            $(".help-block").remove();
            $(".waves-ripple").remove();
            let res = error.responseJSON || {};
            if (error.status === 422 && res.errors) {
                if (
                    Object.size(res) > 0 &&
                    Object.size(res.errors) > 0
                ) {
                    show_validation_error(res.errors);
                }
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: res.message || 'An unexpected error occurred.',
                    icon: 'error',
                });
            }
        }
    });
});



function viewModeScript(disable = true)
{
    const currentOrder = @json(isset($slip) ? $slip : null);
    const editOrder = "{{( isset($buttons) && ($buttons['draft'] || $buttons['submit'])) ? false : true}}";
    const isApprover = "{{ isset($buttons['approve']) ? true : 0 }}";
    const revNoQuery = "{{ isset(request() -> revisionNumber) ? true : false }}";
    // console.log('viewModeScript', revNoQuery, isApprover, editOrder, currentOrder);

    if ((editOrder || revNoQuery) && currentOrder) {
        document.querySelectorAll('input, textarea, select').forEach(element => {
            if (
                element.id !== 'revisionNumber' &&
                element.type !== 'hidden' &&
                !element.classList.contains('cannot_disable')
            ) {
                // check if it's approver-can-edit field
                if (element.classList.contains('approver-can-edit') && isApprover) {
                    // ✅ keep editable for approver
                    element.style.pointerEvents = "auto";
                    element.removeAttribute('readonly');
                } else {
                    // normal logic
                    element.style.pointerEvents = disable ? "none" : "auto";
                    if (disable) {
                        element.setAttribute('readonly', true);
                    } else {
                        element.removeAttribute('readonly');
                    }
                }
            }
        });

        // document.querySelectorAll('input, textarea, select').forEach(element => {
        //     if (element.id !== 'revisionNumber' && element.type !== 'hidden' && !element.classList.contains('cannot_disable')) {
        //         // element.disabled = disable;
        //         element.style.pointerEvents = disable ? "none" : "auto";
        //         if (disable) {
        //             element.setAttribute('readonly', true);
        //         } else {
        //             element.removeAttribute('readonly');
        //         }
        //     }
        // });

        //Disable all submit and cancel buttons
        document.querySelectorAll('.can_hide').forEach(element => {
            element.style.display = disable ? "none" : "";
        });
        //Remove add delete button
        document.getElementById('add_delete_item_section').style.display = disable ? "none" : "";
    } else {
        return;
    }
}

function amendConfirm()
{
    viewModeScript(false);
    disableHeader();
    const amendButton = document.getElementById('amendShowButton');
    if (amendButton) {
        amendButton.style.display = "none";
    }
    //disable other buttons
    var printButton = document.getElementById('dropdownMenuButton');
    if (printButton) {
        printButton.style.display = "none";
    }
    var postButton = document.getElementById('postButton');
    if (postButton) {
        postButton.style.display = "none";
    }
    const buttonParentDiv = document.getElementById('buttonsDiv');
    const newSubmitButton = document.createElement('button');
    newSubmitButton.type = "button";
    newSubmitButton.id = "amend-submit-button";
    newSubmitButton.className = "btn btn-primary btn-sm mb-50 mb-sm-0";
    newSubmitButton.innerHTML = `<i data-feather="check-circle"></i> Submit`;
    newSubmitButton.onclick = function() {
        openAmendConfirmModal();
    };

    if (buttonParentDiv) {
        buttonParentDiv.appendChild(newSubmitButton);
    }

    if (feather) {
        feather.replace({
            width: 14,
            height: 14
        });
    }

    reCheckEditScript();
}

function reCheckEditScript()
    {
        const currentOrder = @json(isset($slip) ? $slip : null);
        if (currentOrder) {
            currentOrder.items.forEach((item, index) => {
                document.getElementById('item_checkbox_' + index).disabled = item?.is_editable ? false : true;
                document.getElementById('items_dropdown_' + index).readonly = item?.is_editable ? false : true;
                document.getElementById('attribute_button_' + index).disabled = item?.is_editable ? false : true;
            });
        }
    }

function openAmendConfirmModal()
{
    $("#amendConfirmPopup").modal("show");
}

function submitAmend()
{
    enableHeader();
    let remark = $("#amendConfirmPopup").find('[name="amend_remarks"]').val();
    $("#action_type_main").val("amendment");
    $("#amendConfirmPopup").modal('hide');
    $("#sale_invoice_form").submit();
}

let isProgrammaticChange = false; // Flag to prevent recursion

document.addEventListener('input', function (e) {
    if (e.target.classList.contains('text-end')) {
        if (isProgrammaticChange) {
            return; // Prevent recursion
        }
        let value = e.target.value;

        // Remove invalid characters (anything other than digits and a single decimal)
        value = value.replace(/[^0-9.]/g, '');

        // Prevent more than one decimal point
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts[1];
        }

        // Prevent starting with a decimal (e.g., ".5" -> "0.5")
        if (value.startsWith('.')) {
            value = '0' + value;
        }

        // Limit to 2 decimal places
        if (parts[1]?.length > 2) {
            value = parts[0] + '.' + parts[1].substring(0, 2);
        }

        // Prevent exceeding the max limit
        const maxNumericLimit = 9999999; // Define your max limit here
        if (value && Number(value) > maxNumericLimit) {
            value = maxNumericLimit.toString();
        }
        isProgrammaticChange = true; // Set flag before making a programmatic change
        // Update the input's value
        e.target.value = value;

        // Manually trigger the change event
        const event = new Event('input', { bubbles: true });
        e.target.dispatchEvent(event);
        const event2 = new Event('change', { bubbles: true });
        e.target.dispatchEvent(event2);
        isProgrammaticChange = false; // Reset flag after programmatic change
    }
});

    document.addEventListener('keydown', function (e) {
        if (e.target.classList.contains('text-end')) {
            if ( e.key === 'Tab' ||
                ['Backspace', 'ArrowLeft', 'ArrowRight', 'Delete', '.'].includes(e.key) ||
                /^[0-9]$/.test(e.key)
            ) {
                // Allow numbers, navigation keys, and a single decimal point
                return;
            }
            e.preventDefault(); // Block everything else
        }
    });

    function checkOrRecheckAllItems(element)
    {
        const allRowsCheck = document.getElementsByClassName('item_row_checks');
        const checkedStatus = element.checked;
        for (let index = 0; index < allRowsCheck.length; index++) {
            allRowsCheck[index].checked = checkedStatus;
        }
    }

    function resetSeries()
    {
        document.getElementById('series_id_input').innerHTML = '';
    }

    function onSeriesChange(element, reset = true)
    {
        resetSeries();
        $.ajax({
            url: "{{route('book.service-series.get')}}",
            method: 'GET',
            dataType: 'json',
            data: {
                menu_alias: "{{request() -> segments()[0]}}",
                service_alias: element.value,
                book_id : reset ? null : "{{isset($slip) ? $slip -> book_id : null}}"
            },
            success: function(data) {
                if (data.status == 'success') {
                    let newSeriesHTML = ``;
                    data.data.forEach((book, bookIndex) => {
                        newSeriesHTML += `<option value = "${book.id}" ${bookIndex == 0 ? 'selected' : ''} >${book.book_code}</option>`;
                    });
                    document.getElementById('series_id_input').innerHTML = newSeriesHTML;
                    getDocNumberByBookId(document.getElementById('series_id_input'), reset);
                } else {
                    document.getElementById('series_id_input').innerHTML = '';
                }
            },
            error: function(xhr) {
                console.error('Error fetching customer data:', xhr.responseText);
                document.getElementById('series_id_input').innerHTML = '';
            }
        });
    }

    function revokeDocument()
    {
        const orderId = "{{isset($slip) ? $slip -> id : null}}";
        if (orderId) {
            $.ajax({
            url: "{{route('production.slip.revoke')}}",
            method: 'POST',
            dataType: 'json',
            data: {
                id : orderId
            },
            success: function(data) {
                if (data.status == 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                    });
                    location.reload();
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                    });
                    window.location.href = "{{$redirect_url}}";
                }
            },
            error: function(xhr) {
                console.error('Error fetching customer data:', xhr.responseText);
                Swal.fire({
                    title: 'Error!',
                    text: 'Some internal error occured',
                    icon: 'error',
                });
            }
        });
        }
    }

    function onHeaderStoreChange(element, type)
    {
        const currentVal = element.value;
        var otherVal = null;
        if (type === "from") {
            otherVal = $("#store_to_id_input").val();
        } else {
            otherVal = $("#store_from_id_input").val();
        }
        if (currentVal == otherVal) {
            Swal.fire({
                title: 'Error!',
                text: 'From and To Location cannot be same',
                icon: 'error',
            });
            element.value = "";
            return;
        }
    }
    function onItemStoreChange(element, type, index)
    {
        const currentVal = element.value;
        var otherVal = null;
        if (type === "from") {
            otherVal = $("#item_store_to_" + index).val();
        } else {
            otherVal = $("#item_store_from_" + index).val();
        }
        if (currentVal == otherVal) {
            Swal.fire({
                title: 'Error!',
                text: 'From and To Location cannot be same',
                icon: 'error',
            });
            element.value = "";
            return;
        }
    }

    function onFromLocationRackChange(element, index, itemRowIndex)
    {
        const storeElement = document.getElementById('data_stores_to_' + itemRowIndex);
        var existingStoreArray = [];
        if (storeElement.getAttribute('data-stores')) {
            existingStoreArray = JSON.parse(decodeURIComponent(storeElement.getAttribute('data-stores')));
        }

        modifyHTMLArrayForToLocation(element, itemRowIndex, index, 'rack_id');


        const rackId = element.value;
        let shelfsHTML = `<option value = "" disabled selected>Select</option>`;
        const relativeShelfDropdownElement = document.getElementById('to_location_shelf_input_' + itemRowIndex + "_" + index);
        if (rackId && relativeShelfDropdownElement) {
            $.ajax({
                url: "{{ route('store.rack.shelfs') }}",
                type: "GET",
                dataType: "json",
                data: {
                    rack_id : rackId
                },
                success: function(data) {
                    if (data.data.shelfs) { // RACKS DATA IS PRESENT
                        data.data.shelfs.forEach(shelf => {
                            shelfsHTML+= `<option value = '${shelf.id}'>${shelf.shelf_code}</option>`;
                        });
                    }
                    relativeShelfDropdownElement.innerHTML = shelfsHTML;
                    if (existingStoreArray[index]) {
                        existingStoreArray[index].shelf_html = shelfsHTML;
                        storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify(existingStoreArray)));
                    }
                },
                error : function(xhr){
                    relativeShelfDropdownElement.innerHTML = shelfsHTML;
                    existingStoreArray[index].shelf_html = shelfsHTML;
                    storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify(existingStoreArray)));
                }
            });
        }
        //Also update the array
    }

    function addToLocationRow()
    {
        const tableInput = document.getElementById('item_to_location_table');
        const itemIndex = tableInput ? tableInput.getAttribute('current-item-index') : 0;
        const qtyInput = document.getElementById('item_qty_' + itemIndex);


        const itemQtysInput = document.getElementsByClassName('to_location_qty_input_' + itemIndex);
        var existingQty = 0;
        for (let index = 0; index < itemQtysInput.length; index++) {
            existingQty += parseFloat(itemQtysInput[index].value);
        }

        if (existingQty >= parseFloat(qtyInput ? qtyInput.value : 0)) {
            Swal.fire({
                title: 'Warning!',
                text: 'Cannot exceed quantity',
                icon: 'warning',
            });
            return;
        }

        const newQty = parseFloat(qtyInput ? qtyInput.value : 0) - existingQty;

        const storeElement = document.getElementById('data_stores_to_' + itemIndex);
        var existingStoreArray = [];
        if (storeElement.getAttribute('data-stores')) {
            existingStoreArray = JSON.parse(decodeURIComponent(storeElement.getAttribute('data-stores')));
        }
        const defaultStore = document.getElementById('item_store_to_' + itemIndex);
        const defaultStoreId = defaultStore.value;
        const defaultStoreCode = defaultStore.options[defaultStore.selectedIndex].text;
        let racksHTML = `<option value = "" disabled selected>Select</option>`;
        let binsHTML = `<option value = "" disabled selected>Select</option>`;
        let shelfsHTML = `<option value = "" disabled selected>Select</option>`;

        if (qtyInput && qtyInput.value > 0) { //Only add if qty is greater than 0
            $.ajax({
                url: "{{ route('store.racksAndBins') }}",
                type: "GET",
                dataType: "json",
                data: {
                    store_id : defaultStoreId
                },
                success: function(data) {
                    if (data.data.racks) { // RACKS DATA IS PRESENT
                        data.data.racks.forEach(rack => {
                            racksHTML+= `<option value = '${rack.id}'>${rack.rack_code}</option>`;
                        });
                    }
                    if (data.data.bins) { //BINS DATA IS PRESENT
                        data.data.bins.forEach(bin => {
                            binsHTML+= `<option value = '${bin.id}'>${bin.bin_code}</option>`;
                        });
                    }
                    existingStoreArray.push({
                        store_id : defaultStoreId,
                        store_code : defaultStoreCode,
                        rack_id : null,
                        rack_code : '',
                        rack_html : racksHTML,
                        shelf_id : null,
                        shelf_code : '',
                        shelf_html : shelfsHTML,
                        bin_id : null,
                        bin_code : '',
                        bin_html : binsHTML,
                        qty : newQty
                    });
                    storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify(existingStoreArray)));
                },
                error : function(xhr){
                    console.error('Error fetching customer data:', xhr.responseText);
                    storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify(existingStoreArray)));
                }
            });
        }
    }

    function toLocationQtyChange(element, itemIndex, index)
    {
        const qtyInput = document.getElementById('item_qty_' + itemIndex);
        const itemQtysInput = document.getElementsByClassName('to_location_qty_input_' + itemIndex);

        var existingQty = 0;
        for (let storeIndex = 0; storeIndex < itemQtysInput.length; storeIndex++) {
            existingQty += parseFloat(itemQtysInput[storeIndex].value);
        }

        if (existingQty > parseFloat(qtyInput ? qtyInput.value : 0)) {
            Swal.fire({
                title: 'Warning!',
                text: 'Cannot exceed quantity',
                icon: 'warning',
            });
            element.value = 0;
            return;
        }
        modifyHTMLArrayForToLocation(element, itemIndex, index, 'qty');
        updateToLocationsTotalQty(itemIndex);
    }

    function openToLocationModal(index) {
        const tableInput = document.getElementById('item_to_location_table');
        if (tableInput) {
            tableInput.setAttribute('item_to_location_table', index);
        }
    }

    function modifyHTMLArrayForToLocation(element, itemIndex, index, key)
    {
        const storeElement = document.getElementById('data_stores_to_' + itemIndex);
        var existingStoreArray = [];
        if (storeElement.getAttribute('data-stores')) {
            existingStoreArray = JSON.parse(decodeURIComponent(storeElement.getAttribute('data-stores')));
        }
        if (existingStoreArray[index]) {
            existingStoreArray[index][key] = element.value;
        }
        storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify(existingStoreArray)));
    }

    function updateToLocationsTotalQty(itemIndex)
    {
        const toLocationTotalQtyDiv = document.getElementById('to_location_total_qty');
        const itemQtysInput = document.getElementsByClassName('to_location_qty_input_' + itemIndex);
        var existingQty = 0;
        for (let storeIndex = 0; storeIndex < itemQtysInput.length; storeIndex++) {
            existingQty += parseFloat(itemQtysInput[storeIndex].value);
        }
        if (toLocationTotalQtyDiv) {
            toLocationTotalQtyDiv.textContent = existingQty;
        }
    }

    function onIssueTypeChange(element)
    {
        const selectedType = element.value;
        if (selectedType == 'Location Transfer') {
            implementIssueTypeChange('location_transfer','.sub_contracting');
        } else if (selectedType == 'Sub Contracting') {
            implementIssueTypeChange('sub_contracting','.location_transfer');
        }
    }

    function implementIssueTypeChange(targetClass, querySelectorOtherClasses)
    {
        var targetElements = document.getElementsByClassName(targetClass);
        for (let index = 0; index < targetElements.length; index++) {
            targetElements[index].style.removeProperty("display");
        }
        var otherElements = document.querySelectorAll(querySelectorOtherClasses);
        for (let index = 0; index < otherElements.length; index++) {
            otherElements[index].style.display = "none";
        }
        $("#vendor_id_input").trigger('input');
    }

    function openBundleSchedulePopup(index)
    {
        const bundleTable = document.getElementById('bundle_schedule_table');
        if (bundleTable) {
            bundleTable.setAttribute('current-item-index', index);
        }
        openModal("bundleInfo");
    }

    function renderBundleDetails(itemIndex, openModalFlag = false)
    {
        const bundleElement = document.getElementById('item_bundles_' + itemIndex);
        var bundlesArray = [];
        if (bundleElement.getAttribute('data-bundles')) {
            bundlesArray = JSON.parse(decodeURIComponent(bundleElement.getAttribute('data-bundles')));
        } else {
            Swal.fire({
                title: 'Warning!',
                text: 'Please enter quantity first',
                icon: 'warning',
            });
            return;
        }
        if (openModalFlag) {
            openBundleSchedulePopup(itemIndex);
        }
        if (bundlesArray.length > 0) {
            const bundleTable = document.getElementById('bundle_schedule_table');
            var bundleScheduleHTML = ``;
            var totalQty = 0;
            bundlesArray.forEach((bundleDetail, bundleDetailIndex) => {
                if (!bundleDetail.deleted) {
                    var currentBundleNo = parseInt("{{$startingBundleNo}}") + bundleDetailIndex;
                    var curreentActualBundleNo = parseInt(currentBundleNo?.bundle_no);
                    currentBundleNo = curreentActualBundleNo ? curreentActualBundleNo : currentBundleNo;
                    bundleDetail.bundle_no = currentBundleNo;
                    bundleScheduleHTML+= `
                    <tr id = "bundle_item_row_${itemIndex}_${bundleDetailIndex}">
                    <td>${bundleDetailIndex+1}</td>
                    <td>
                    <input type = "text" class = "form-control mw-100" readonly id = "item_bundle_no_${bundleDetailIndex}_${itemIndex}" value = "${currentBundleNo}" />
                    </td>
                    <td>
                    <input type = "text" class = "form-control mw-100 text-end bundle_qties_${itemIndex}" id = "item_bundle_qty_${bundleDetailIndex}_${itemIndex}" value = "${bundleDetail.qty}" oninput = "onBundleQtyChange(this, ${itemIndex}, ${bundleDetailIndex}, 'qty')" />
                    </td>
                    <td>
                    <a href="#" class="text-danger" onclick = "deleteBundleRow(${itemIndex}, ${bundleDetailIndex});"><i data-feather="trash-2"></i></a>
                    </td>
                    </tr>
                    `;
                    totalQty += parseFloat(bundleDetail.qty);
                }
            });
            bundleElement.setAttribute('data-bundles', encodeURIComponent(JSON.stringify(bundlesArray)));
            bundleTable.innerHTML = bundleScheduleHTML + `
            <tr>
                <td class="text-dark text-end" colspan = "3"><strong>Total</strong></td>
                <td class="text-dark text-end"><strong id = "bundle_total_qty">${totalQty}</strong></td>
			</tr>
            `;
        }
        updateBundleQtyTotal(itemIndex);
        renderIcons();
    }

    function getTotalQty(itemIndex)
    {
        let a = Number(document.getElementById('item_accepted_qty_' + itemIndex)?.value) || 0;
        let b = Number(document.getElementById('item_sub_prime_qty_' + itemIndex)?.value) || 0;
        let c = a+b;
        return c;
    }
    function assignDefaultBundleInfoArray(itemIndex, openModalFlag = false)
    {
        const bundleElement = document.getElementById('item_bundles_' + itemIndex);
        var bundleScheduleArray = [];
        if (bundleElement.getAttribute('data-bundles')) {
            bundleScheduleArray = JSON.parse(decodeURIComponent(bundleElement.getAttribute('data-bundles')));
        }
        const qtyInput = document.getElementById('item_qty_' + itemIndex);
        const totalQty = getTotalQty(itemIndex);
        //Create
        if (!bundleScheduleArray.length) {
            if (qtyInput && totalQty > 0) { //Only add if qty is greater than 0
                bundleScheduleArray.push({
                    bundle_no : "{{$startingBundleNo}}",
                    editable : "{{$editableBundle}}",
                    bundle_type : 'Bundle',
                    qty : totalQty
                });
                bundleElement.setAttribute('data-bundles', encodeURIComponent(JSON.stringify(bundleScheduleArray)));
            }
        }
        renderBundleDetails(itemIndex, openModalFlag);
    }

    function onBundleQtyChange(element, itemIndex, index, key)
    {
        const bundleElement = document.getElementById('item_bundles_' + itemIndex);
        var bundlesArray = [];
        var message = "";
        if (bundleElement.getAttribute('data-bundles')) {
            bundlesArray = JSON.parse(decodeURIComponent(bundleElement.getAttribute('data-bundles')));
        }
        if (bundlesArray[index]) {
            bundlesArray[index][key] = element.value;
            if (key == 'qty') {
                //Check Qty
                // let maxQty = document.getElementById('item_qty_' + itemIndex).value;
                let maxQty = getTotalQty(itemIndex);
                let existingQty = 0;
                bundlesArray.forEach((bundle) => {
                    existingQty += parseFloat(bundle.qty);
                });
                if (existingQty > maxQty) {
                    bundlesArray[index][key] = 0;
                    element.value = 0;
                    message = "Cannot exceed Quantity";
                }
            }
        }
        bundleElement.setAttribute('data-bundles', encodeURIComponent(JSON.stringify(bundlesArray)));
        if (message) {
            Swal.fire({
                title: 'Warning!',
                text: message,
                icon: 'warning',
            });
        }
    }

    function addBundleQty()
    {
        const tableInput = document.getElementById('bundle_schedule_table');
        const itemIndex = tableInput ? tableInput.getAttribute('current-item-index') : 0;
        const qtyInput = document.getElementById('item_qty_' + itemIndex);
        const totalQty = getTotalQty(itemIndex);

        const itemQtysInput = document.getElementsByClassName('bundle_qties_' + itemIndex);
        var existingQty = 0;
        for (let index = 0; index < itemQtysInput.length; index++) {
            existingQty += parseFloat(itemQtysInput[index].value);
        }

        if (existingQty >= parseFloat(qtyInput ? totalQty : 0)) {
            Swal.fire({
                title: 'Warning!',
                text: 'Cannot exceed quantity',
                icon: 'warning',
            });
            return;
        }

        const newQty = parseFloat(qtyInput ? totalQty : 0) - existingQty;

        const bundleElement = document.getElementById('item_bundles_' + itemIndex);
        var bundlesArray = [];
        if (bundleElement.getAttribute('data-bundles')) {
            bundlesArray = JSON.parse(decodeURIComponent(bundleElement.getAttribute('data-bundles')));
        }
        if (qtyInput && totalQty > 0) { //Only add if qty is greater than 0
            bundlesArray.push({
                bundle_type : 'Bundle',
                bundle_no: bundlesArray.length + 1,
                qty : newQty,
                editable : false
            });
            bundleElement.setAttribute('data-bundles', encodeURIComponent(JSON.stringify(bundlesArray)));
            renderBundleDetails(itemIndex);
        }
    }

    function deleteBundleRow(itemIndex, index)
    {
        const bundleElement = document.getElementById('item_bundles_' + itemIndex);
        var bundlesArray = [];
        if (bundleElement.getAttribute('data-bundles')) {
            bundlesArray = JSON.parse(decodeURIComponent(bundleElement.getAttribute('data-bundles')));
        }
        if (bundlesArray[index]) {
            if (bundlesArray[index]['id']) {
                bundlesArray[index]['deleted'] = true;
            } else {
                bundlesArray.splice(index, 1);
            }
            bundleElement.setAttribute('data-bundles', encodeURIComponent(JSON.stringify(bundlesArray)));
            renderBundleDetails(itemIndex);
        }
    }

    function updateBundleQtyTotal(itemIndex)
    {
        const totalBundleQtyDiv = document.getElementById('bundle_total_qty');
        const itemQtysInput = document.getElementsByClassName('bundle_qties_' + itemIndex);
        var existingQty = 0;
        for (let bundleIndex = 0; bundleIndex < itemQtysInput.length; bundleIndex++) {
            existingQty += parseFloat(itemQtysInput[bundleIndex].value);
        }
        if (totalBundleQtyDiv) {
            totalBundleQtyDiv.textContent = existingQty;
        }
        }

    function onPostVoucherOpen(type = "not_posted")
    {
        console.log('onPostVoucherOpen');

        resetPostVoucher();
        const apiURL = "{{route('production.slip.get.posting.details')}}";
        $.ajax({
            url: apiURL + "?book_id=" + $("#series_id_input").val() + "&document_id=" + "{{isset($slip) ? $slip -> id : ''}}" + "&type=" + (type == "not_posted" ? 'get' : 'view'),
            type: "GET",
            dataType: "json",
            success: function(data) {
                if (!data.data.status) {
                    Swal.fire({
                        title: 'Error!',
                        text: data.data.message,
                        icon: 'error',
                    });
                    return;
                }
                const voucherEntries = data.data.data;
                // console.log(voucherEntries)
                var voucherEntriesHTML = ``;
                Object.keys(voucherEntries.ledgers).forEach((voucher) => {
                    voucherEntries.ledgers[voucher].forEach((voucherDetail, index) => {
                        voucherEntriesHTML += `
                        <tr>
                        <td>${voucher}</td>
                        <td class="fw-bolder text-dark">${voucherDetail.ledger_group_code ? voucherDetail.ledger_group_code : ''}</td>
                        <td>${voucherDetail.ledger_code ? voucherDetail.ledger_code : ''}</td>
                        <td>${voucherDetail.ledger_name ? voucherDetail.ledger_name : ''}</td>
                        <td class="text-end">${voucherDetail.debit_amount > 0 ? parseFloat(voucherDetail.debit_amount).toFixed(2) : ''}</td>
                        <td class="text-end">${voucherDetail.credit_amount > 0 ? parseFloat(voucherDetail.credit_amount).toFixed(2) : ''}</td>
                        </tr>
                        `
                    });
                });
                voucherEntriesHTML+= `
                <tr>
                    <td colspan="4" class="fw-bolder text-dark text-end">Total</td>
                    <td class="fw-bolder text-dark text-end">${voucherEntries.total_debit.toFixed(2)}</td>
                    <td class="fw-bolder text-dark text-end">${voucherEntries.total_credit.toFixed(2)}</td>
                </tr>
                `;
                document.getElementById('posting-table').innerHTML = voucherEntriesHTML;
                document.getElementById('voucher_doc_no').value = voucherEntries.document_number;
                document.getElementById('voucher_date').value = moment(voucherEntries.document_date).format('D/M/Y');
                document.getElementById('voucher_book_code').value = voucherEntries.book_code;
                document.getElementById('voucher_currency').value = voucherEntries.currency_code;
                if (type === "posted") {
                    document.getElementById('posting_button').style.display = 'none';
                } else {
                    document.getElementById('posting_button').style.removeProperty('display');
                }
                $('#postvoucher').modal('show');
            }
        });

    }

    function postVoucher(element)
    {
        const bookId = "{{isset($slip) ? $slip -> book_id : ''}}";
        const documentId = "{{isset($slip) ? $slip -> id : ''}}";
        const postingApiUrl = "{{route('production.slip.post.voucher')}}"
        if (bookId && documentId) {
            $.ajax({
                url: postingApiUrl,
                type: "POST",
                dataType: "json",
                contentType: "application/json", // Specifies the request payload type
                data: JSON.stringify({
                    // Your JSON request data here
                    book_id: bookId,
                    document_id: documentId,
                }),
                success: function(data) {
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
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Some internal error occured',
                        icon: 'error',
                    });
                }
            });

        }
    }

function resetPostVoucher()
{
    document.getElementById('voucher_doc_no').value = '';
    document.getElementById('voucher_date').value = '';
    document.getElementById('voucher_book_code').value = '';
    document.getElementById('voucher_currency').value = '';
    document.getElementById('posting-table').innerHTML = '';
    document.getElementById('posting_button').style.display = 'none';
}

function openHeaderPullModal(type = null)
    {
        document.getElementById('qts_data_table').innerHTML = '';
        initializeAutocompleteQt("book_code_input_pwo", "book_id_pwo_val", "book_pwo", "book_code", "book_name");
        initializeAutocompleteQt("document_no_input_pwo", "document_id_pwo_val", "pwo_document", "document_number", "document_number");
        initializeAutocompleteQt("customer_code_input_qt", "customer_id_qt_val", "customer", "customer_code", "company_name");
        initializeAutocompleteQt("item_name_input_pwo", "item_id_pwo_val", "material_issue_items", "item_code", "item_name");
        getOrders();
    }

    function getOrders()
    {
        var qtsHTML = ``;
        const targetTable = document.getElementById('qts_data_table');
        const item_id = $("#item_id_pwo_val").val() || '';
        const customer_id = $("#customer_id_qt_val").val() || '';
        const book_id = $("#book_id_pwo_val").val() || '';
        const soDoc = $("#document_so_no_input").val() || '';
        const moDoc = $("#document_mo_no_input").val() || '';
        const apiUrl = "{{route('production.slip.pull.items')}}";
        var selectedIds = [];
        document.querySelectorAll("#production-items .item_header_rows").forEach(row => {
        let input = row.querySelector("input[id*='mo_product_id_']");
        if (input) {
            selectedIds.push(input.value);
        }
        });
        $.ajax({
            url: apiUrl,
            method: 'GET',
            dataType: 'json',
            data : {
                customer_id : customer_id,
                book_id : book_id,
                so_doc_number : soDoc,
                mo_doc_number : moDoc,
                item_id : item_id,
                doc_type : "mo",
                header_book_id : $("#series_id_input").val() || '',
                store_id: $("#store_id_input").val() || '',
                sub_store_id: $("#sub_store_id").val() || '',
                selected_ids: selectedIds
            },
            success: function(data) {
                if(data.status == 200) {
                    qtsHTML = data.data.html;
                }
                targetTable.innerHTML = qtsHTML;
            },
            error: function(xhr) {
                console.error('Error fetching customer data:', xhr.responseText);
                targetTable.innerHTML = '';
            }
        });

    }

    $(document).on('input change', '#item_name_input', function() {
        getOrders();
    });
    $(document).on('input change', '#document_so_no_input', function() {
        getOrders();
    });
    $(document).on('input change', '#document_mo_no_input', function() {
        getOrders();
    });


    $(document).on('click', '.clearPiFilter', (e) => {
        $("#customer_code_input_qt").val('');
        $("#customer_id_qt_val").val('');
        $("#item_name_input_pwo").val('');
        $("#item_id_pwo_val").val('');
        $("#document_so_no_input").val('');
        $("#document_mo_no_input").val('');
        getOrders();
    });

    let current_doc_id = 0;

    function checkQuotation(element, message = '')
    {
        if (element.getAttribute('can-check-message')) {
            Swal.fire({
                title: 'Error!',
                text: element.getAttribute('can-check-message'),
                icon: 'error',
            });
            element.checked = false;
            return;
        }
        const docId = element.getAttribute('doc-id');
        if (current_doc_id != 0) {
            if (element.checked == true) {
                if (current_doc_id != docId) {
                    element.checked = false;
                }
            } else {
                const otherElementsSameDoc = document.getElementsByClassName('po_checkbox');
                let resetFlag = true;
                for (let index = 0; index < otherElementsSameDoc.length; index++) {
                    if (otherElementsSameDoc[index].getAttribute('doc-id') == current_doc_id && otherElementsSameDoc[index].checked) {
                        resetFlag = false;
                        break;
                    }
                }
                if (resetFlag) {
                    current_doc_id = 0;
                }
            }
        } else {
            current_doc_id = element.getAttribute('doc-id');
        }

    }

    function processOrder()
    {
        // Handle Inspection Show/Hide
        let inspectionRequiredP = $('#inspection_required_key').val();
        // console.log('inspectionRequired2', inspectionRequired);

        if (inspectionRequiredP.includes("yes") || inspectionRequiredP =='yes') {
            $('.inspectionChecklistBtn').show();
        }

        const stationWise = getStationWiseConsBySubStoreId();
        const allCheckBoxes = document.getElementsByClassName('po_checkbox');
        const docType = $("#service_id_input").val();
        const apiUrl = "{{route('production.slip.process.items')}}";
        let docId = [];
        let documentDetails = [];
        for (let index = 0; index < allCheckBoxes.length; index++) {
            if (allCheckBoxes[index].checked) {
                if(allCheckBoxes[index].getAttribute('document-id')) {
                    docId.push(allCheckBoxes[index].getAttribute('document-id'));
                }
            }
        }
        if (docId && docId.length > 0) {
            $.ajax({
                url: apiUrl,
                method: 'GET',
                dataType: 'json',
                data: {
                    station_wise_consumption : stationWise,
                    docIds: JSON.stringify(docId),
                    doc_type: 'mo',
                    store_id : $("#store_id_input").val(),
                    inspection_required : inspectionRequiredP,
                },
                success: function(data) {
                    // Mo detail fill
                    const currentOrders = data.data;
                    $("#mo_no").val(currentOrders.mo.mo_no);
                    $("#mo_date").val(currentOrders.mo.mo_date);
                    $("#mo_product_name").val(currentOrders.mo.mo_product_name);
                    $("#mo_type_name").val(currentOrders.mo.mo_type);
                    $("#station_name").val(currentOrders.mo.mo_station_name);
                    $("#mo_product_id").val(currentOrders.mo.mo_product_id);
                    $("#mo_id").val(currentOrders.mo.mo_id);
                    $("#mo_bom_id").val(currentOrders.mo.mo_bom_id);
                    $("#is_last_station").val(currentOrders.mo.is_last_station);
                    $("#is_batch_no").val(currentOrders.mo.is_batch_no);

                    if(currentOrders.mo.is_batch_no)
                    {
                        $('.show_required_field_for_batch').show();
                    }else{
                        $('.show_required_field_for_batch').hide();
                    }
                    $("#mo_station_id").val(currentOrders.mo.mo_station_id);
                    // const mainTableItem = document.getElementById('item_header');
                    // let currentOrderIndexVal = document.getElementsByClassName('item_header_rows').length;
                    // mainTableItem.innerHTML = currentOrders.html;
                    $("#production-items tbody:first").html(currentOrders.html);
                    $("#production-items tbody:first .item_header_rows").each(function(itemIndex,item){
                        setAttributesUI(itemIndex,"#production-items tbody");
                        assignDefaultBundleInfoArray(itemIndex);
                    });
                    if(currentOrders?.is_machine) {
                        $("#machineName").removeClass('d-none');
                        $("#cycleCount").removeClass('d-none');
                        $("#item_details_td").attr('colspan','17');
                    } else {
                        $("#item_details_td").attr('colspan','15');
                        $("#machineName").addClass('d-none');
                        $("#cycleCount").addClass('d-none');
                    }
                    $("#raw-materials tbody:first").html(currentOrders.consHtml);
                    $("#raw-materials tbody:first .item_header_rows").each(function(itemIndex,item){
                        setAttributesUI(itemIndex,"#raw-materials tbody");
                    });

                    if(currentOrders?.stationLines?.length) {
                       let col = Number($("#item_details_td").attr('colspan'));
                       $("#item_details_td").attr('colspan',col+2);
                       $("#prodLine").removeClass('d-none');
                       $("#prodSupervisor").removeClass('d-none');
                    } else {
                        $("#prodLine").addClass('d-none');
                        $("#prodSupervisor").addClass('d-none');
                    }

                    if (feather) {
                        feather.replace({
                            width: 14,
                            height: 14
                        });
                    }
                    if($("#station_name").val()) {
                        $('#station_name').closest('.mb-1').closest('.col').removeClass('d-none');
                    } else {
                        $('#station_name').closest('.mb-1').closest('.col').addClass('d-none');

                    }
                    if(currentOrders.mo.is_last_station) {
                        $("#fg_label").text("Finished Goods Store");
                    } else {
                        $("#fg_label").text("WIP Store");
                    }
                    // if(!currentOrders.mo.is_last_station) {
                    //     $("#subprime_qty_col").addClass('d-none');
                    // } else {
                    //     $("#subprime_qty_col").removeClass('d-none');
                    // }
                    $(".select2").select2();
                },
                error: function(xhr) {
                    $("#mo_no").val("");
                    $("#mo_date").val("");
                    $("#mo_product_name").val("");
                    $("#mo_type_name").val("");
                    $("#station_name").val("");
                    $("#mo_product_id").val("");
                    $("#mo_id").val("");
                    $("#is_last_station").val("");
                    $("#mo_station_id").val("");
                    if($("#station_name").val()) {
                        $('#station_name').closest('.mb-1').closest('.col').removeClass('d-none');
                    } else {
                        $('#station_name').closest('.mb-1').closest('.col').addClass('d-none');

                    }
                    // console.error('Error fetching customer data:', xhr.responseText);
                }
            });
        } else {
            $("#mo_no").val("");
            $("#mo_date").val("");
            $("#mo_product_name").val("");
            $("#mo_type_name").val("");
            $("#station_name").val("");
            $("#mo_product_id").val("");
            $("#mo_id").val("");
            $("#is_last_station").val("");
            $("#mo_station_id").val("");
            if($("#station_name").val()) {
                $('#station_name').closest('.mb-1').closest('.col').removeClass('d-none');
            } else {
                $('#station_name').closest('.mb-1').closest('.col').addClass('d-none');

            }
            Swal.fire({
                title: 'Error!',
                text: 'Please select at least one one document',
                icon: 'error',
            });
        }
    }

    function initSelect2() {
        $('.select2').select2();
    }
    $(document).ready(function () {
        initSelect2();
    });

$(document).on('change',"#store_id_input", (e) => {
    let storeId = e.target.value || '';
    locationOnChange(storeId);
});

setTimeout(() => {
    let storeId = $("#store_id_input").val() || '';
    locationOnChange(storeId);
    $(".select2").select2();
}, 0);
    // Sub Store
function locationOnChange(storeId = '') {
    let actionUrl = '{{route("production.slip.substore")}}'+'?store_id='+storeId;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
                let subStore = ``;
                let selId = @json($slip->sub_store_id ?? '');
                if(data?.data?.sub_store?.length) {
                    data?.data?.sub_store?.forEach(element => {
                        let selected = element.id == selId ? 'selected' : '';
                        subStore += `<option value="${element.id}" ${selected} data-station-wise-consumption="${element.station_wise_consumption}">${element.name}</option>`;
                    });
                    const stationWise = getStationWiseConsBySubStoreId();
                    if(stationWise.includes('yes')) {
                        $("#station_column").removeClass('d-none');
                    } else {
                        $("#station_column").addClass('d-none');
                    }
                }
                $("#sub_store_id").empty().append(subStore);

                let subFgStore = `<option value="">Select</option>`;
                let selectedId1 = @json($slip->fg_sub_store_id ?? '');
                if(data?.data?.fg_sub_store?.length) {
                    data?.data?.fg_sub_store?.forEach(element => {
                        let selected = element.id == selectedId1 ? 'selected' : '';
                        subFgStore += `<option value="${element.id}" ${selected}>${element.name}</option>`;
                    });
                }
                $("#fg_sub_store_id").empty().append(subFgStore);

                let subRgStore = `<option value="">Select</option>`;
                let selectedId2 = @json($slip->rg_sub_store_id ?? '');
                if(data?.data?.rg_sub_store?.length) {
                    data?.data?.rg_sub_store?.forEach(element => {
                        let selected = element.id == selectedId2 ? 'selected' : '';
                        subRgStore += `<option value="${element.id}" ${selected}>${element.name}</option>`;
                    });
                }
                $("#rg_sub_store_id").empty().append(subRgStore);
            }
        });
    });
}

function getStationWiseConsBySubStoreId()
{
    const swc = $('#sub_store_id').find('option:selected').attr('data-station-wise-consumption') || 'no';
    $("#station_wise_consumption").val(swc);
    return swc;
}


//  Display attribute default
setTimeout(()=>{
    // $("#production-items tbody:first .item_header_rows").each(function(itemIndex,item){
    //     setAttributesUI(itemIndex,"#production-items tbody");
    // });
    // $("#raw-materials tbody:first .item_header_rows").each(function(itemIndex,item){
    //     setAttributesUI(itemIndex,"#raw-materials tbody");
    // });
}, 500);
var currentSelectedItemIndex = null ;
item_header
function setAttributesUI(paramIndex = null, selectorPrifix = ''){
    let currentItemIndex = null;
    if (paramIndex != null || paramIndex != undefined) {
        currentItemIndex = paramIndex;
    } else {
        currentItemIndex = currentSelectedItemIndex;
    }
    //Attribute modal is closed
    const containerOne = document.querySelector(selectorPrifix) || document.querySelector("#production-items tbody");
    let itemIdDoc = containerOne.querySelector('#items_dropdown_' + currentItemIndex);
    if (!itemIdDoc) {
        return;
    }
    //Item Doc is found
    let attributesArray = itemIdDoc.getAttribute('attribute-array');
    if (!attributesArray) {
        return;
    }
    attributesArray = JSON.parse(attributesArray);
    if (attributesArray.length == 0) {
        return;
    }
    let attributeUI = `<div style = "white-space:nowrap; cursor:pointer;">`;
    let maxCharLimit = 15;
    let attrTotalChar = 0;
    let total_selected = 0;
    let total_atts = 0;
    let addMore = true;
    attributesArray.forEach(attrArr => {
        if (!addMore) {
            return;
        }
        let short = false;
        total_atts += 1;
        if(attrArr?.short_name)
        {
            short = true;
        }
        //Retrieve character length of attribute name
        let currentStringLength = short ? Number(attrArr.short_name.length) : Number(attrArr.group_name.length);
        let currentSelectedValue = '';
        attrArr.values_data.forEach((attrVal) => {
            if (attrVal.selected === true) {
                total_selected += 1;
                // Add character length with selected value
                currentStringLength += Number(attrVal.value.length);
                currentSelectedValue = attrVal.value;
            }
        });
        //Add the attribute in UI only if it falls within the range
        if ((attrTotalChar + Number(currentStringLength)) <= 15) {
            attributeUI += `
            <span class="badge rounded-pill badge-light-primary"><strong>${short ? attrArr.short_name : attrArr.group_name}</strong>: ${currentSelectedValue ? currentSelectedValue :''}</span>
            `;
        } else {
            //Get the remaining length
            let remainingLength =  15 - attrTotalChar;
            //Only show the data if remaining length is greater than 3
            if (remainingLength >= 3) {
                attributeUI += `<span class="badge rounded-pill badge-light-primary"><strong>${attrArr.group_name.substring(0, remainingLength - 1)}..</strong></span>`
            }
            else {
                addMore = false;

                attributeUI += `<i class="ml-2 fa-solid fa-ellipsis-vertical"></i>`;
            }
        }
        attrTotalChar += Number(currentStringLength);
    });
    const container = document.querySelector(selectorPrifix) || document.querySelector("#production-items tbody");
    let attributeSection = container.querySelector(`[id="attribute_section_${currentItemIndex}"]`);
    if (attributeSection) {
        attributeSection.innerHTML = attributeUI + '</div>';
    }
    if(total_selected == 0){
        attributeSection.innerHTML = `
            <button id = "attribute_button_${currentItemIndex}"
                ${attributesArray.length > 0 ? '' : 'disabled'}
                type = "button"
                class="btn p-25 btn-sm btn-outline-secondary"
                style="font-size: 10px">Attributes</button>
            <input type = "hidden" name = "attribute_value_${currentItemIndex}" />
        `;
    }
}

/*Check box check and uncheck*/
$(document).on('change','#rescdule thead .form-check-input',(e) => {
    const isChecked = e.target.checked;
    if(isChecked) {
        let firstCheckedItem = $('#rescdule tbody .form-check-input:checked').first();
        if(!firstCheckedItem.length) {
            firstCheckedItem = $('#rescdule tbody .form-check-input').first();
        }
        let moId = firstCheckedItem.attr('document-mo-id');
        let itemNeedChecked = $(`.form-check-input.po_checkbox[document-mo-id="${moId}"]`);
        itemNeedChecked.prop('checked',isChecked);
    } else {
        $('#rescdule tbody .form-check-input').prop('checked', isChecked);
    }
});

$(document).on('change','#rescdule tbody .form-check-input',(e) => {
    let currentChechedMoId = $(e.target).attr('document-mo-id');
    if(e.target.checked) {
        e.target.checked = false;
    } else {
        e.target.checked = false;
        return false;
    }
    let moIds = $('#rescdule tbody .form-check-input:checked')
    .map(function () {
        return $(this).attr('document-mo-id');
    }).get();
    if(moIds.length) {
        if(moIds.includes(currentChechedMoId)) {
            e.target.checked = true;
        } else {
            e.target.checked = false;
        }
    } else {
        e.target.checked = true;
    }
});

$(document).on("keyup","#production-items tbody input[name*='item_qty[']", (e) => {
    let moProductQty = Number(e.target.value);
    let moProductId = $(e.target).closest('tr').find("input[name*='mo_product_id']").val();
    let consumptionItems = $(`#raw-materials tbody input[data-mo-product-id="${moProductId}"]`);
    if(consumptionItems.length) {
        consumptionItems.each(function(inputQty,index) {
            let bomQty = Number($(this).attr('data-bom-qty')) || 1;
            let calQty = moProductQty * bomQty;
            $(this).val(calQty);
        });
    }
});

document.addEventListener("DOMContentLoaded", function () {
   function updateButtonVisibility() {
       let activeTab = document.querySelector(".nav-link.active").getAttribute("data-bs-target").replace("#", "");
       document.querySelectorAll(".tab-action").forEach(button => {
           if (button.getAttribute("data-tab") === activeTab) {
               button.classList.remove("d-none");
           } else {
               button.classList.add("d-none");
           }
       });
   }
   updateButtonVisibility();
   document.querySelectorAll(".nav-link").forEach(tab => {
       tab.addEventListener("click", function () {
           setTimeout(updateButtonVisibility, 100);
       });
   });
});

setTimeout(()=> {
    let tr = $("#raw-materials #item_row_0");
    fetchItemDetailsFromRow(tr);
},500);
function fetchItemDetailsFromRow(rowSelector) {
    let $row = $(rowSelector);
    let attributeArray = $row.find("input[name*='item_code[']").attr('attribute-array') || '[]';
    attributeArray = JSON.parse(attributeArray);
    const selectedAttributeIds = attributeArray.flatMap(group =>
        group.values_data
            .filter(value => value.selected)
            .map(value => value.id)
    );
    let pslipBomConsId = $row.find("input[name*='[pslip_bom_cons_id]']").val() || '';
    let moBomConsId = $row.find("input[name*='[mo_bom_cons_id]']").val() || '';
    let queryParams = new URLSearchParams({
        pslip_bom_cons_id: pslipBomConsId,
        mo_bom_cons_id: moBomConsId,
        selected_attribute_ids: selectedAttributeIds.join(',')
    });
    let actionUrl = `{{route("production.slip.item.detail")}}?${queryParams.toString()}`;
    fetch(actionUrl)
        .then(response => response.json())
        .then(data => {
            $("#raw-materials #item_details_td tbody").empty().append(data.data.html);
        });
}

$(document).on("click", "#raw-materials .item_header_rows", (e) => {
    let $row = $(e.target).closest('tr');
      $('.consumption_row_checks').on('change', function () {
        if ($(this).is(':checked')) {
            $('.consumption_row_checks').not(this).prop('checked', false);

            if (!$(this).data('checked-once')) {
                $(this).data('checked-once', true);

            }
        }
    });
    fetchItemDetailsFromRow($row);
});


 function addConsumtionAlternateItems(){

        let checkedBox = $('.consumption_row_checks:checked').first();
        // let item_id = checkedBox.data('conId');
        // let item_id = checkedBox.data('conId');
        let closestRow = checkedBox.closest('tr');
        let item_id = closestRow.find('input[name*="[item_id]"]').val();
        let soDoc = closestRow.find('input[name*="[so_doc]"]').val();
        let item_type = closestRow.find('input[name*="[item_type]"]').val();
        let item_code = closestRow.find('input[data-code]').attr('data-code');

        let item_qty = closestRow.find('input[name*="[item_qty]"]').val();
        let consumption_qty = closestRow.find('input[name*="[consumption_qty]"]').val();
        let rowlastIndex = closestRow.data('lastindex');
        let mo_bom_cons_id = closestRow.find('input[name*="[mo_bom_cons_id]"]').val();
        // Select all hidden inputs with name ending in [alternate_id]
        let alternateInputs = document.querySelectorAll("input[name$='[alternate_id]']");

        let alternateIds = Array.from(alternateInputs).map(input => input.value);

        alternateIds = alternateIds.filter(id => id !== "" && id !== null);
        if(alternateIds.includes(mo_bom_cons_id)){
            Swal.fire({
                title: 'Error!',
                text: 'Alternate item already exists for item code: '+item_code,
                icon: 'error',
            });
            return true;

        }

        if(item_qty-consumption_qty<=0){
            Swal.fire({
                title: 'Error!',
                text: 'Consumed quantity cannot exceed required quantity.',
                icon: 'error',
            });
            return true;
        }

        var newRow = ``;
        if (item_id) {
                $.ajax({
                    url: "{{ route('production.slip.clone_alterItems') }}",
                    type: "GET",
                    data: {
                        item_id: item_id,
                        soDoc: soDoc,
                        itemType: item_type,
                        item_qty: item_qty,
                        mo_bom_cons_id: mo_bom_cons_id,
                        rowlastIndex:rowlastIndex
                    },
                    dataType: "json",
                    success: function(data) {
                        closestRow.after(data.data);
                        var item=data.item;
                        initializeAutocomplete2(".comp_item_code",item);
                    },
                    error:function(error){
                    Swal.fire({
                            title: 'Error!',
                            text: error.responseJSON.message,
                            icon: 'error',
                        });
                    }
                });
            }
    }
  function initializeAutocomplete2(selector, data) {
    $(selector).autocomplete({
        source: $.map(data, function(item) {

            return {
                id: item.id,
                label: `${item.item_name} (${item.item_code})`,
                value: item.item_code || '',
                code: item.item_code,
                item_id: item.alt_item_id,
                item_name: item.item_name,
                uom_id: item.item.uom_id,
                uomName: item.item.uom.name,
                is_attr: 1,

            };
        }),
        minLength: 0,
        select: function(event, ui) {

            let $input = $(this);
            const itemId = ui.item.item_id;
            const itemCode = ui.item.code;
            const itemName = ui.item.item_name;
            const itemN = ui.item.item_name;
            const uomId = ui.item.uom_id;
            const uomName = ui.item.uomName;

            $input.attr('data-name', itemName);
            $input.attr('data-code', itemCode);
            $input.attr('data-id', itemId);
            $input.val(itemCode);

            const $row = $input.closest('tr');

            $row.find('[name*=item_id]').val(itemId);
            $row.find('[name*=item_code]').val(itemCode);
            $row.find('[name*="[item_name]"]').val(itemN);

            const uomOption = `<option value="${uomId}">${uomName}</option>`;
            $row.find('[name*=uom_id]').empty().append(uomOption);

            setTimeout(() => {
                if (ui.item.is_attr) {
                    $row.find('.attributeBtn').trigger('click');
                } else {
                    $row.find('.attributeBtn').trigger('click');
                    if (!$("#consumption_method").val().includes('manual')) {
                        $row.find('.consumption_btn button').trigger('click');
                    } else {
                        $row.find('[name*="[qty]"]').val('').focus();
                    }
                }
            }, 50);


            return false;
        },
        change: function(event, ui) {
            if (!ui.item) {
                $(this).val("");
                $(this).attr('data-name', '');
                $(this).attr('data-code', '');
                $(this).attr('data-id', '');
            }
        }
    }).focus(function () {
        if (this.value === "") {
            $(this).autocomplete("search", "");
        }
    });
}

/*Check attrubute*/
$(document).on('click', '.attributeBtn', (e) => {

    let tr = e.target.closest('tr');

    let item_name = tr.querySelector('[name*=item_code]').value;
    let item_id = tr.querySelector('[name*="[item_id]"]').value;

   let attributeInput = tr.querySelector("input[name*='item_code[']");
    let attributeArrayRaw = attributeInput?.getAttribute('attribute-array') || '[]';
    let attributeArray = [];

    try {
        attributeArray = JSON.parse(attributeArrayRaw);
    } catch (e) {
        console.error("Invalid JSON in attribute-array:", attributeArrayRaw);
    }

    // ✅ Extract selected IDs
    let selectedIds = attributeArray.flatMap(group =>
        (group?.values_data || [])
            .filter(value => value.selected)
            .map(value => value.id)
    );

    console.log("Selected Attribute IDs:", selectedIds);  // Example output: [29]


    if (item_name && item_id) {

        let rowCount = tr.getAttribute('data-index');
        getItemAttribute(item_id, rowCount, selectedIds, tr);
    } else {
        alert("Please select first item name.");
    }
});

/*For comp attr*/
function getItemAttribute(itemId, rowCount, selectedAttr, tr){
    let isSo = $(tr).find('[name*="so_item_id"]').length ? 1 : 0;
    if(!isSo) {
        isSo = $(tr).find('[name*="so_pi_mapping_item_id"]').length ? 1 : 0;
    }
    if(!isSo) {
        if($(tr).find('td[id*="attribute_section_"]').data('disabled')) {
            isSo = 1;
        }
    }
    let actionUrl = '{{route("production.slip.getattributes")}}'+'?item_id='+itemId+`&rowCount=${rowCount}&selectedAttr=${selectedAttr}&isSo=${isSo}`;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
                $("#attribute tbody").empty();
                $("#attribute table tbody").append(data.data.html)
                $(tr).find('td:nth-child(2)').find("[name*='[attr_name]']").remove();
                $(tr).find('td:nth-child(2)').append(data.data.hiddenHtml);

                $(tr)
                    .find("input[id^='items_dropdown_']")
                    .attr('attribute-array', JSON.stringify(data.data.itemAttributeArray));
                if (data.data.attr) {
                    $("#attribute").modal('show');
                    $(".select2").select2();
                }else{
                    avlStock(rowCount)
                }
            }
        });
    });
}
function avlStock(indexId){
    let tr = $(`#raw-materials #item_row_${indexId}`);

    if (!tr.length) {
        console.error(`Row with index ${indexId} not found.`);
        return;
    }

    // Get and parse attribute-array safely
    let attributeArrayRaw = tr.find("input[name*='item_code[']").attr('attribute-array') || '[]';
    let attributeArray = [];

    try {
        attributeArray = JSON.parse(attributeArrayRaw);
    } catch (e) {
        console.error("Invalid JSON in attribute-array:", attributeArrayRaw);
    }

    // Extract selected attribute IDs
    const selectedAttributeIds = attributeArray.flatMap(group =>
        (group?.values_data || [])
            .filter(value => value.selected)
            .map(value => value.id)
    );

    let value = $('#so_doc_'+indexId).val();
    let match = value.match(/(\d+)(?!.*\d)/);


    // let lastNumber = match[0];
    let lastNumber = (match && match[0]) ? match[0] : null;

    const attributes=selectedAttributeIds;
    const store_id=$("#store_id_input").val();
    const sub_store_id=$("#sub_store_id").val();
    const station_id=$("#mo_station_id").val();
    const rm_type=$("#items_type_"+indexId).val();
    const so_item_id=lastNumber;
    const item_id=tr.find('[name*="[item_id]"]').val();
    const uom_id=$("#uom_dropdown_"+indexId).val();
    const mo_bom_mapping_co_id=tr.attr('data-id');

        $.ajax({
            url: "{{ route('production.slip.avlStock') }}",
            type: "GET",
            data: {
                item_id: item_id,
                so_item_id: so_item_id,
                station_id: station_id,
                uom_id: uom_id,
                store_id: store_id,
                attributes:attributes,
                mo_bom_mapping_id:mo_bom_mapping_co_id,
                sub_store_id:sub_store_id,
                rm_type:rm_type,
            },
            dataType: "json",
            success: function(data) {
             document.getElementById('item_avl_qty_'+indexId).value = data;


            },
            error:function(error){
            Swal.fire({
                    title: 'Error!',
                    text: error.responseJSON.message,
                    icon: 'error',
                });
            }
        });
}

$(document).on('change', 'select.select2', function () {
    const selectedValue = $(this).val();
    const attrName = $(this).data('attr-name');
    const attrGroupId = $(this).data('attr-group-id');
    const selectedText = $(this).find('option:selected').text();

    $('input[name^="row_count["]').each(function () {
        const rowIndex = $(this).val();
        const $hiddenInput = $(`input[name="item_code[${rowIndex}]"]`);
         const $hiddenInputattr = $(`input[name="cons[${rowIndex}][attribute_value]"]`);
        const attrArrayRaw = $hiddenInput.attr('attribute-array');

        if (!attrArrayRaw) return;

        try {
            const attrArray = JSON.parse(attrArrayRaw);
            let selectedItems = [];

            attrArray.forEach(group => {
                if (group.attribute_group_id == attrGroupId) {
                    group.values_data.forEach(item => {
                        item.selected = (String(item.id) === String(selectedValue));
                        if (item.selected) {
                            let newItem = {
                                attribute_id: item.value ,
                                attribute_name: attrGroupId,
                                attribute_value: item.id
                            };
                            selectedItems.push(newItem);
                        }
                    });
                }
            });

            $hiddenInputattr.val(JSON.stringify(selectedItems));
            $hiddenInput.attr('attribute-array', JSON.stringify(attrArray));

            console.log(`Updated attribute-array for row ${rowIndex}:`, attrArray);

            const $row = $(`#raw-materials #item_row_${rowIndex}`);
            fetchItemDetailsFromRow($row);
            avlStock(rowIndex);
            $(`#attribute_section_${rowIndex}`).html(
                ` <div style="white-space:nowrap; cursor:pointer;">
                <span class="badge rounded-pill badge-light-primary"><strong>${attrName}</strong>: ${selectedText}</span>
            </div>`
            );

        } catch (e) {
            console.error(`Error parsing attribute-array for row ${rowIndex}:`, e);
        }
    });
});


$(document).on('click', '.submitAttributeBtn', function (e) {
    e.preventDefault();

    let closestRow = $(this).closest('tr');

    let rowCount = closestRow.data('index');
    $(`[name="cons[${rowCount}][item_qty]"]`).focus();

    $("#attribute").modal('hide');
});



$(document).on("keyup",
 "#production-items input[name*='item_accepted_qty'] , #production-items input[name*='item_sub_prime_qty']",
  (e) => {
    // console.log(e.target.name);
    let qty = Number(e.target.value) || 0;
    let $tr = $(e.target).closest('tr');
    let trId = $tr.attr('id') || '';
    let itemIndex = trId.split('_').pop();
    const bundleElement = document.getElementById('item_bundles_' + itemIndex);
    var bundleScheduleArray = [];
    // const qtyInput = document.getElementById('item_qty_' + itemIndex);
    const totalQty = getTotalQty(itemIndex);
    bundleScheduleArray.push({
                bundle_no : "{{$startingBundleNo}}",
                editable : "{{$editableBundle}}",
                bundle_type : 'Bundle',
                qty : totalQty
            });
    bundleElement.setAttribute('data-bundles', encodeURIComponent(JSON.stringify(bundleScheduleArray)));
    renderBundleDetails(itemIndex, false);
});

$(document).on('change', 'select[name^="station_line_id"]', function () {
    const supervisorName = $(this).find('option:selected').data('name');
    const indexMatch = $(this).attr('name').match(/\[(\d+)\]/);
    if (indexMatch) {
        const index = indexMatch[1];
        $('#supervisor_name_' + index).val(supervisorName || '');
    }
});

// When WIP quantity changes
$(document).on("input", "#production-items input[name*='item_wip_qty']", function (e) {
    validateWipAgainstQty($(e.target));
});

// When Item quantity changes
$(document).on("input", "#production-items input[name*='item_qty']", function (e) {
    let $tr = $(e.target).closest('tr');
    let $wipInput = $tr.find("input[name*='item_wip_qty']");
    validateWipAgainstQty($wipInput);
});

function validateWipAgainstQty($wipInput) {
    let wipQty = Number($wipInput.val()) || 0;
    let $tr = $wipInput.closest('tr');
    let itemQty = Number($tr.find("input[name*='item_qty']").val()) || 0;
    $tr.find("input[name*='item_total_qty']").val(wipQty + itemQty);
    // let itemSoQty = Number($tr.find("input[name*='item_so_qty']").val()) || 0;
    // if (wipQty > (itemSoQty - itemQty)) {
    //     $wipInput.val(itemSoQty - itemQty);
    // }
}
</script>

@endsection
@endsection
