@extends('layouts.app')
@section('content')
    <form class="ajax-input-form scrap_module_form" data-module="scrap" method="POST" action="{{ isset($scrap->id) ? route('scrap.update', $scrap->id) : route('scrap.store') }}" data-redirect="{{ route('scrap.index') }}" enctype="multipart/form-data">
        @php
            if (isset($scrap->reference_type) && $scrap->reference_type) {
                $scrap->applyReference($scrap->reference_type);
            }
            $createEdit = $buttons['draft'] && $buttons['submit'] ? true : false;
            $createEditReadonly = $createEdit ? '' : 'readonly';
            $createEditDisabled = $createEdit ? '' : 'disabled';
        @endphp
        <input type="hidden" name="id" value="{{ $scrap->id ?? '' }}">
        <input type="hidden" name="pslip_ids" id="pslip_ids" value="{{ $scrap->pslip_ids ?? '' }}">
        <input type="hidden" name="ps_item_ids" id="ps_item_ids" value="{{ $scrap->pslip_item_ids ?? '' }}">
        <input type="hidden" name="ps_pull_item_ids" id="ps_pull_item_ids" value="">

        <input type="hidden" name="ro_ids" id="ro_ids" value="{{ $scrap->ro_ids ?? '' }}">
        <input type="hidden" name="ro_item_ids" id="ro_item_ids" value="{{ $scrap->ro_item_ids ?? '' }}">
        <input type="hidden" name="ro_item_pull_ids" id="ro_item_pull_ids" value="">

        <input type="hidden" name="item_ids" id="item_ids" value="{{ $scrap->item_ids ?? '' }}">
        <input type="hidden" name="reference_type" id="reference_type" value="{{ $scrap->reference_type ?? '' }}">
        <input type="hidden" name="document_status" id="document_status" value="{{ $scrap->document_status ?? '' }}">

        <input type="hidden" name="deleted_ro_item_ids" id="deleted_ro_item_ids" value="">
        <input type="hidden" name="deleted_ps_item_ids" id="deleted_ps_item_ids" value="">
        <input type="hidden" name="deleted_attachment_ids" id="deleted_attachment_ids" value="">
        <input type="hidden" name="deleted_scrap_item_ids" id="deleted_scrap_item_ids" value="">

        <div class="app-content content">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Scrap</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="index.html">Home</a>
                                            </li>
                                            <li class="breadcrumb-item active">
                                                {{ isset($scrap) ? (isset($view) ? '' : 'Edit') : 'Create' }} Scrap </li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right" id = "buttonsDiv">
                                @if (!isset(request()->revisionNumber))
                                    <button type = "button" onclick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
                                    @if (isset($scrap->id))
                                        {{-- <a href="{{ route('scrap.generate-pdf', $scrap->id) }}" target="_blank" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer">
                                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2">
                                                </path>
                                                <rect x="6" y="14" width="12" height="8"></rect>
                                            </svg> Print
                                        </a> --}}
                                        @if ($buttons['draft'])
                                            <button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button" name="action" value="draft"><i data-feather='save'></i> Save as Draft</button>
                                            <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn" data-url="{{ route('scrap.destroy', ['id' => $scrap->id, 'isAmedment' => $buttons['amend'] ? $buttons['amend'] : 0]) }}"
                                                    data-redirect="{{ route('scrap.index') }}" data-message="Are you sure you want to delete this record?">
                                                <i data-feather="trash-2" class="me-50"></i> Delete
                                            </button>
                                        @endif
                                        @if ($buttons['submit'])
                                            <button type="submit" class="btn btn-primary btn-sm submit-button" name="action" value="submitted"><i data-feather="check-circle"></i> Submit</button>
                                        @endif
                                        @if ($buttons['approve'])
                                            <button type="button" id="reject-button" data-bs-toggle="modal" data-bs-target="#approveModal" type="submit" class="btn btn-primary btn-sm mb-50 mb-sm-0 submit-button" name="action" value="reject"><svg xmlns="http://www.w3.org/2000/svg"
                                                     width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <line x1="15" y1="9" x2="9" y2="15">
                                                    </line>
                                                    <line x1="9" y1="9" x2="15" y2="15">
                                                    </line>
                                                </svg> Reject</button>
                                            <button type="button" data-bs-toggle="modal" data-bs-target="#approveModal" type="submit" class="btn btn-primary btn-sm mb-50 mb-sm-0 submit-button" name="action" value="approve"><i data-feather="check-circle"></i> Approve</button>
                                        @endif
                                        @if ($buttons['amend'])
                                            <button id = "amendShowButton" type="submit" class="btn btn-primary btn-sm mb-50 mb-sm-0 submit-button" name="action" value="amend"><i data-feather='edit'></i> Amendment</button>
                                        @endif
                                        @if ($buttons['revoke'])
                                            <button id = "revokeButton" type="submit" class="btn btn-primary btn-sm mb-50 mb-sm-0 submit-button" name="action" value="revoke"><i data-feather='rotate-ccw'></i> Revoke</button>
                                        @endif
                                    @else
                                        <button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button" name="action" value="draft"><i data-feather='save'></i> Save as Draft</button>
                                        <button type="submit" class="btn btn-primary btn-sm submit-button" name="action" value="submitted"><i data-feather="check-circle"></i> Submit</button>
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
                                <div class="card" id="basic_section">
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
                                                        <select class="form-select" {{ $createEditDisabled }} id="book_id" name="book_id">
                                                            @foreach ($books as $book)
                                                                <option value="{{ $book->id }}" {{ isset($scrap->book_id) && $scrap->book_id == $book->id ? 'selected' : '' }}>
                                                                    {{ ucfirst($book->book_code) }}</option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" name="book_code" id="book_code">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Scrap No <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="document_number" class="form-control" id="document_number" {{ $createEditReadonly }} value="{{ isset($scrap->id) ? $scrap->document_number : '' }}">
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Scrap Date <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" class="form-control" {{ $createEditReadonly }} value="{{ isset($scrap->id) ? $scrap->document_date : \Carbon\Carbon::now()->format('Y-m-d') }}" name="document_date" min="{{ $current_financial_year['start_date'] }}"
                                                               max="{{ $current_financial_year['end_date'] }}">
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Location <spanp class="text-danger">
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2" {{ $createEditDisabled }} name="store_id" id="store_id" onchange="getSubStores(this.value)">
                                                            <option value="">Select Location</option>
                                                            @foreach ($locations as $location)
                                                                <option value="{{ $location->id }}" {{ isset($scrap->store_id) && $scrap->store_id == $location->id ? 'selected' : '' }}>
                                                                    {{ $location->store_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1 sub-store-row {{ isset($scrap->id) ? '' : 'd-none' }}">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Sub Store <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2 sub_store" {{ $createEditDisabled }} id="sub_store_id" name="sub_store_id"></select>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1 {{ isset($scrap->id) ? '' : 'd-none' }}" id="reference_type_div">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Reference from</label>
                                                    </div>
                                                    <div class="col-md-5 action-button">
                                                        @if (isset($scrap->reference_type) && $scprocurement_type_paramrap->reference_type)
                                                            @if ($scrap->reference_type == 'pslip')
                                                                <button {{ $createEditDisabled }} type="button" class="btn btn-outline-primary btn-sm mb-0 psSelect" @if (!$buttons['draft'] || !$buttons['submit']) disabled @endif>
                                                                    <i data-feather="plus-square"></i>
                                                                    Production Slip
                                                                </button>
                                                            @elseif ($scrap->reference_type == 'repairOrder')
                                                                <button {{ $createEditDisabled }} type="button" class="btn btn-outline-primary btn-sm mb-0 roSelect" @if (!$buttons['draft'] || !$buttons['submit']) disabled @endif>
                                                                    <i data-feather="plus-square"></i>
                                                                    Repair Order
                                                                </button>
                                                            @endif
                                                        @else
                                                            <button type="button" class="btn btn-outline-primary btn-sm mb-0 psSelect d-none" disabled> <i data-feather="plus-square"></i>Production Slip </button>
                                                            <button type="button" class="btn btn-outline-primary btn-sm mb-0 roSelect d-none" disabled><i data-feather="plus-square"></i> Repair Order</button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            @if (isset($scrap) && $scrap->document_status !== 'draft')
                                                @if ((isset($approvalHistory) && count($approvalHistory) > 0) || isset($revision_number))
                                                    <div class="col-md-4">
                                                        <div class="step-custhomapp bg-light p-1 customerapptimelines customerapptimelinesapprovalpo">
                                                            <h5 class="mb-2 text-dark border-bottom pb-50 d-flex align-items-center justify-content-between">
                                                                <strong><i data-feather="arrow-right-circle"></i> Approval History</strong>
                                                                @if (!isset(request()->revisionNumber) && $scrap->document_status !== 'draft')
                                                                    <strong class="badge rounded-pill badge-light-secondary amendmentselect">Rev. No.
                                                                        <select class="form-select" id="revisionNumber">
                                                                            @for ($i = $revision_number; $i >= 0; $i--)
                                                                                <option value="{{ $i }}" {{ request('revisionNumber', $scrap->revision_number) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                                            @endfor
                                                                        </select>
                                                                    </strong>
                                                                @else
                                                                    @if ($scrap->document_status !== 'draft')
                                                                        <strong class="badge rounded-pill badge-light-secondary amendmentselect">
                                                                            Rev. No.{{ request()->revisionNumber }}
                                                                        </strong>
                                                                    @endif
                                                                @endif
                                                            </h5>
                                                            <ul class="timeline ms-50 newdashtimline ">
                                                                @foreach ($approvalHistory as $approvalHist)
                                                                    <li class="timeline-item">
                                                                        <span class="timeline-point timeline-point-indicator"></span>
                                                                        <div class="timeline-event">
                                                                            <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                                <h6>{{ ucfirst($approvalHist->name ?? ($approvalHist?->user?->name ?? 'NA')) }}</h6>
                                                                                @if ($approvalHist->approval_type == 'approve')
                                                                                    <span class="badge rounded-pill badge-light-success">{{ ucfirst($approvalHist->approval_type) }}</span>
                                                                                @elseif($approvalHist->approval_type == 'submit')
                                                                                    <span class="badge rounded-pill badge-light-primary">{{ ucfirst($approvalHist->approval_type) }}</span>
                                                                                @elseif($approvalHist->approval_type == 'reject')
                                                                                    <span class="badge rounded-pill badge-light-danger">{{ ucfirst($approvalHist->approval_type) }}</span>
                                                                                @else
                                                                                    <span class="badge rounded-pill badge-light-danger">{{ ucfirst($approvalHist->approval_type) }}</span>
                                                                                @endif
                                                                            </div>
                                                                            @if ($approvalHist->created_at)
                                                                                <h6>
                                                                                    {{ \Carbon\Carbon::parse($approvalHist->created_at)->timezone('Asia/Kolkata')->format('d/m/Y | h.iA') }}
                                                                                </h6>
                                                                            @endif
                                                                            @if ($approvalHist->remarks)
                                                                                <p>{!! $approvalHist->remarks !!}</p>
                                                                            @endif
                                                                            @if ($approvalHist->media && count($approvalHist->media) > 0)
                                                                                @foreach ($approvalHist->media as $mediaFile)
                                                                                    <p>
                                                                                        <a href="{{ $mediaFile->file_url }}" target = "_blank">
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                                                                 class="feather feather-download">
                                                                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                                                                <polyline points="7 10 12 15 17 10"></polyline>
                                                                                                <line x1="12" y1="15" x2="12" y2="3"></line>
                                                                                            </svg>
                                                                                        </a>
                                                                                    </p>
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

                                <div class="card">
                                    <div class="card-body customernewsection-form">
                                        <div class="border-bottom mb-2 pb-25">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader">
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="step-custhomapp bg-light">
                                            <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" data-bs-toggle="tab" href="#scavengingItems">
                                                        Scrap Items
                                                    </a>
                                                </li>
                                                @if (isset($scrap->reference_type) && $scrap->reference_type)
                                                    @if ($scrap->reference_type == 'pslip')
                                                        @if (isset($scrap->pslip_ids) && $scrap->pslip_ids)
                                                            <li class="nav-item">
                                                                <a class="nav-link" data-bs-toggle="tab" href="#productionSlip">
                                                                    Production Slips
                                                                </a>
                                                            </li>
                                                        @endif
                                                    @endif
                                                    @if ($scrap->reference_type == 'repairOrder')
                                                        @if (isset($scrap->ro_ids) && $scrap->ro_ids)
                                                            <li class="nav-item">
                                                                <a class="nav-link" data-bs-toggle="tab" href="#repairOrder">
                                                                    Repair Orders
                                                                </a>
                                                            </li>
                                                        @endif
                                                    @endif
                                                @else
                                                    <li class="nav-item psLink d-none">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#productionSlip">
                                                            Production Slips
                                                        </a>
                                                    </li>
                                                    <li class="nav-item roLink d-none">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#repairOrder">
                                                            Repair Orders
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>

                                        <div class="tab-content pb-1">
                                            <div class="tab-pane active" id="scavengingItems">
                                                <div class="text-end mb-50">
                                                    @if ($createEdit)
                                                        <a href="javascript:;" id="deleteBtn" class="btn btn-sm btn-outline-danger me-50">
                                                            <i data-feather="x-circle"></i>
                                                            Delete
                                                        </a>
                                                        <a href="javascript:;" id="addNewItemBtn" class="btn btn-sm btn-outline-primary">
                                                            <i data-feather="plus"></i>
                                                            Add Item
                                                        </a>
                                                    @endif
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="table-responsive pomrnheadtffotsticky">
                                                            <table id="scavengingItemsTable" class="ItemsTable table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad" data-json-key="components_json" data-row-selector="tr[id^='row_']">
                                                                <thead id="scavengingItemsThead">
                                                                    <tr>
                                                                        <th width="62" class="customernewsection-form">
                                                                            <div class="form-check form-check-primary custom-checkbox">
                                                                                <input type="checkbox" class="form-check-input" id="Email"{{ $createEditDisabled }} />
                                                                                <label class="form-check-label" for="Email"></label>
                                                                            </div>
                                                                        </th>
                                                                        <th width="285">Item Code</th>
                                                                        <th width="208">Item Name</th>
                                                                        <th>Attributes</th>
                                                                        <th>UOM</th>
                                                                        <th>Qty</th>
                                                                        <th>Rate</th>
                                                                        <th>Total Cost</th>
                                                                        <th>Cost Center</th>
                                                                        <th>Remark</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="mrntableselectexcel" id="scavengingItemsTbody">
                                                                    @if (isset($scrap) && $scrap->items)
                                                                        @foreach ($scrap->items as $key => $item)
                                                                            @include('remanufacturing.scrap.partials.item-row', [
                                                                                'rowCount' => $key,
                                                                                'item' => $item,
                                                                                'createEdit' => $createEdit,
                                                                                'createEditReadonly' => $createEditReadonly,
                                                                                'createEditDisabled' => $createEditDisabled,
                                                                            ])
                                                                        @endforeach
                                                                    @endif
                                                                </tbody>
                                                                <tfoot id="scavengingItemsTfoot">
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="tab-pane" id="productionSlip">
                                                <div class="text-end mb-50">
                                                    <a href="javascript:;" id="deleteBtnPullItem" class="btn btn-sm btn-outline-danger me-50">
                                                        <i data-feather="x-circle"></i> Delete</a>
                                                </div>
                                                <div class="table-responsive pomrnheadtffotsticky">
                                                    <table id="productionSlipsTable" class="ItemsTable table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                        <thead>
                                                            <tr>
                                                                <th width="62" class="customernewsection-form">
                                                                    <div class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input" id="Email" />
                                                                        <label class="form-check-label" for="Email"></label>
                                                                    </div>
                                                                </th>
                                                                <th>Slip Date</th>
                                                                <th>Slip No.</th>
                                                                <th>Item Code</th>
                                                                <th>Item Name</th>
                                                                <th>Attributes</th>
                                                                <th>UOM</th>
                                                                <th>Qty</th>
                                                                {{-- <th id="uidTh">UID</th> --}}
                                                                <th>Remark</th>
                                                            </tr>
                                                        </thead>
                                                        @php
                                                            $reference_type = isset($scrap) ? $scrap->reference_type : null;
                                                            $roItems = isset($scrap) && isset($reference_type) && $reference_type == 'repairOrder' && isset($scrap->roItems) ? $scrap->roItems : [];
                                                            $pslipItems = isset($scrap) && isset($reference_type) && $reference_type == 'pslip' && isset($scrap->pslipItems) ? $scrap->pslipItems : [];
                                                        @endphp
                                                        <tbody class="mrntableselectexcel">
                                                            @if ($reference_type == 'pslip' && !empty($pslipItems))
                                                                @foreach ($pslipItems ?? [] as $key => $item)
                                                                    @include('remanufacturing.scrap.partials.pull-items', ['rowCount' => $key, 'item' => $item, 'type' => 'pslip'])
                                                                @endforeach
                                                            @endif
                                                            @if ($reference_type == 'repairOrder' && !empty($roItems))
                                                                @foreach ($roItems as $key => $item)
                                                                    @include('remanufacturing.scrap.partials.ro-items', ['rowCount' => $key, 'item' => $item])
                                                                @endforeach
                                                            @endif
                                                        </tbody>
                                                        <tfoot id="productionSlipsTfoot">
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-2">
                                            <div class="col-md-12">
                                                <div class="col-md-4">
                                                    <div class="mb-1">
                                                        <label class="form-label">Upload Document</label>
                                                        <input type="file" class="form-control" name="attachment" {{ $createEditDisabled }} id="document-upload" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">Final Remarks</label>
                                                    <textarea type="text" rows="4" class="form-control" name="document_remarks" {{ $createEditReadonly }} id="document_remarks" value="{{ $scrap->remarks ?? '' }}" placeholder="Enter Remarks here..."></textarea>
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
                        <table class="mt-1 table myrequesttablecbox table-striped ps-order-detail custnewpo-detail">
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
                    <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary cancelAttributeBtn me-1">Cancel</button>
                    <button type="button" {{-- data-bs-dismiss="modal" --}} class="btn btn-primary submitAttributeBtn">Select</button>
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
                    <button type="button" id="deleteConfirm" class="btn btn-primary">Confirm</button>
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
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>Scrap</strong>? After Amendment this action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Item Remark Modal --}}
    <div class="modal fade" id="itemRemarkModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered">
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
                            <textarea maxlength="250" class="form-control" placeholder="Enter Remarks"></textarea>
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

    {{-- Referencing From Item Modals --}}
    {{-- PI Items --}}
    @include('remanufacturing.scrap.partials.ps-modal')
    {{-- PI Items --}}

    {{-- RO Items --}}
    {{-- @include('remanufacturing.scrap.partials.ro-modal') --}}
    {{-- RO Items --}}
@endsection
@section('scripts')
    <script type="text/javascript" src="{{ asset('assets/js/modules/common-datatable.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/modules/scrap-attr-ui.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/modules/scrap.js') }}"></script>
    <script type="text/javascript" src="{{ asset('app-assets/js/file-uploader.js') }}"></script>
    <script>
        let psTable;
        const scrap = @json($scrap ?? []);
        const type = '{{ request()->route('type') }}';
        const getPsRoute = '{{ route('scrap.get.ps') }}';
        const scrapIndexRoute = '{{ route('scrap.index') }}';
        const scrapItemRowRoute = '{{ route('scrap.item.row') }}';
        const scrapItemAttrRoute = '{{ route('scrap.item.attr') }}';
        const getDocNumberByBookIdUrl = '{{ route('book.doc_no') }}';
        const scrapItemDetailsRoute = '{{ route('scrap.get.itemdetail') }}';
        const PRODUCTION_SLIP_SERVICE_ALIAS = "{{ \App\Helpers\ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS }}";
    </script>
    <script>
        @if (isset($scrap))
            getSubStores(scrap.store_id, scrap.sub_store_id);
        @endif

        $(document).on("change", "#sub_store_id", function(e) {
            const value = $(this).val();
            console.log("Selected:", value);

            if (!value || value === "0") {
                $("#reference_type_div").addClass("d-none");
            } else {
                $("#reference_type_div").removeClass("d-none");
            }
        });


        $(document).on('change', '#book_id', (e) => {
            let bookId = e.target.value;
            if (bookId) {
                getDocNumberByBookId(bookId, scrap?.document_number ?? '');
            } else {
                $("#book_id").val('');
                $("#document_number").val('');
                $("#document_number").attr('readonly', false);
            }
        });

        $(document).on('click', '#backBtn', (e) => {
            $("#psModal").modal('hide');
            setTimeout(() => {
                $("#psModal").modal('show');
            }, 0);
        });

        $(document).on("click", ".psSelect", function() {
            logger();
            const selector = "#psModal .ps-order-detail";
            $("#psModal").modal("show");

            if ($.fn.DataTable.isDataTable(selector)) {
                psTable = psTable || $(selector).DataTable();
                psTable.ajax.reload(null, false);
            } else {
                getPslipItems();
            }

            $(".select2").each(function() {
                const $el = $(this);
                if ($el.data("select2")) $el.select2("destroy");
                $el.select2({
                    dropdownParent: $("#psModal")
                });
            });
        });

        $(document).on("click", ".psProcess", function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            $("#psModal th .form-check-input").prop("checked", false);

            const ids = getSelectedItemIDs();

            if (!ids.length) {
                updateHiddenInput("ps_item_ids", []);
                updateHiddenInput("ps_pull_item_ids", []);
                updateHiddenInput("item_ids", []);
                $("#psModal").modal("hide");
                Swal.fire("Error!", "Please select at least one SO item.", "error");
                return false;
            }

            updateHiddenInput("ps_item_ids", ids, true);
            updateHiddenInput("ps_pull_item_ids", ids, true);

            const itemIds = [];
            const selectedItems = [];
            $("#psModal .ps_item_checkbox:checked").each(function() {
                const itemId = Number($(this).data("item-id"));
                itemIds.push(itemId);
                selectedItems.push({
                    item_id: itemId
                });
            });

            updateHiddenInput("item_ids", itemIds, true);
            $('#reference_type').val("pslip");

            const params = {
                type: "pslip",
                ids: JSON.stringify(ids),
                selected_items: JSON.stringify(selectedItems),
                store_id: $("#store_id").val() || "",
                sub_store_id: $("#sub_store_id").val() || "",
                current_row_count: $("#productionSlipsTable .mrntableselectexcel tr").length,
            };

            const query = new URLSearchParams(params).toString();
            const actionUrl = `{{ route('scrap.process.item') }}?${query}`;

            fetch(actionUrl)
                .then((r) => r.json())
                .then((data) => {
                    if (data.status === 200) {
                        $("#uidTh").hide();
                        $(".psLink").removeClass("d-none");
                        $("#psModal").modal("hide");
                        $("#productionSlipsTable .mrntableselectexcel").append(data.data.pos);
                        reInitAttributes("#productionSlipsTable > tbody");

                        $('a[href="#productionSlip"]').tab("show");

                        $("#productionSlipsTable .mrntableselectexcel tr").last().attr("tabindex", "-1").focus();
                    } else {
                        clearPsInputs();
                        Swal.fire("Error!", data.message, "error");
                    }
                })
                .catch(() => {
                    clearPsInputs();
                    Swal.fire("Error!", "Something went wrong while processing the request.", "error");
                });
        });

        $("#psModal").on("shown.bs.modal", () => {
            $.fn.dataTable.tables({
                visible: true,
                api: true
            }).columns.adjust();
        });

        $(document).on('click', '.clearPiFilter', (e) => {
            $("#item_name_search").val('');
            $("#item_name_input_qt").val('');
            $("#item_id_qt_val").val('');
            $("#department_po").val('');
            $("#department_id_po").val('');
            $("#customer_code_input_qt").val('');
            $("#customer_id_qt_val").val('');
            $("#book_code_input_qt").val('');
            $("#book_id_qt_val").val('');
            $("#document_no_input_qt").val('');
            $("#document_id_qt_val").val('');
            getPslipItems();
        });

        $(document).on("click", ".clearPiFilter", () => {
            $("#item_name_search, #item_name_input_qt, #item_id_qt_val, #department_po, #department_id_po, #customer_code_input_qt, #customer_id_qt_val, #book_code_input_qt, #book_id_qt_val, #document_no_input_qt, #document_id_qt_val").val("");
            getPslipItems();
        });

        $(document).on("keyup", "#item_name_search", getPslipItems);

        $(document).on("change", "#psModal .ps-order-detail > thead .form-check-input", function() {
            $("#psModal .ps-order-detail > tbody .form-check-input").prop("checked", this.checked);
        });

        $(document).on("change", "#psModal .ps-order-detail > tbody .form-check-input", () => {
            const allChecked = !$("#psModal .ps-order-detail > tbody .form-check-input:not(:checked)").length;
            $("#psModal .ps-order-detail > thead .form-check-input").prop("checked", allChecked);
        });

        initializeAutocomplete2(".comp_item_code");
        initializeAutocompleteQt(
            ".comp_item_code_cost_centers",
            "cost_center_id",
            "cost_center",
            "name",
            "code"
        );

        reInitAttributes("#scavengingItemsTable > tbody");
        reInitAttributes("#productionSlipsTable > tbody");

        function logger() {
            console.log("ps_item_ids ==========>>   ", $("#ps_item_ids").val());
            console.log("ps_pull_item_ids ==========>>   ", $("#ps_pull_item_ids").val());
            console.log("deleted_ps_item_ids ==========>>   ", $("#deleted_ps_item_ids").val());
            console.log('item attr', $(`[name^="components[${0}][attr_group_id]"][name$="[attr_name]"]`).val());

        }
    </script>
@endsection
