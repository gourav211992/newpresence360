@extends('layouts.app')

@section('content')
    <style>
        .middleinputerror {
        padding-bottom: 30px;
        }
        .middleinputerror span.text-danger {
            font-size: 12px;
            position: absolute;
            top: 38px;
        }
        .itemactive { position: absolute; left: 6px; font-size: 11px; top: 6px; color: #fff } 
        .iteminactive {  left: 24px; color: #999 } 
        .customernewsection-form .statusactiinactive .form-check-input { width: 80px; cursor: pointer}
        .customernewsection-form .statusactiinactive .form-check-input:checked + .itemactive { display: inline-block}
        .customernewsection-form .statusactiinactive .form-check-input:checked ~ .iteminactive { display: none }
        
        .customernewsection-form .statusactiinactive .form-check-input:not(:checked) + .itemactive { display: none}
        .customernewsection-form .statusactiinactive .form-check-input:not(:checked) ~ .iteminactive { display: inline-block }
    </style>
    <!-- BEGIN: Content-->
    <form class="ajax-input-form" method="POST" action="{{ route('item.update', $item->id) }}" id="item_form" data-redirect="{{ route('item.index') }}">
    <input type="hidden" name="item_id" value="{{ $item->id ?? '' }}">
    <input type="hidden" name="item_code_type" value="{{ $itemCodeType }}">
    <input type="hidden" id="documentStatus" name="document_status" value="{{ $item->documentStatus ?? '' }}">
    @csrf
    @method('PUT')
    @php
        $isEditable = isset($item) && $item->status === 'draft';
        $statusValue = isset($item) && (strtolower($item->status) === 'active') && ($item->document_status == 'approval_not_required' || $item->document_status == 'approved') ? 'active' : 'inactive';
        $isChecked = $statusValue === 'active' ? 'checked' : '';
        $tables=$tablesToCheck;
    @endphp

    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 col-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Item Master</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                                        <li class="breadcrumb-item active">Edit</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                        <div class="form-group breadcrumb-right" id="buttonsDiv">
                                <a href="{{ route('item.index') }}" class="btn btn-secondary btn-sm">
                                 <i data-feather="arrow-left-circle"></i> Back
                                </a>
                              
                                @if(!isset(request()->revisionNumber))
                                    @if (isset($item))
                                       @if($buttons['delete'])
                                            <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                                data-url="{{ route('item.destroy', $item->id) }}" 
                                                data-redirect="{{ route('item.index') }}"
                                                data-message="Are you sure you want to delete this record?">
                                                <i data-feather="trash-2" class="me-50"></i> Delete
                                            </button>
                                        @endif
                                        @if($buttons['amendDelete'])
                                        <button type="button" style="display:none;" id="btnAmendDelete" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                                data-url="{{ route('item.destroy', $item->id) }}" 
                                                data-redirect="{{ route('item.index') }}"
                                                data-message="Are you sure you want to delete this record?">
                                                <i data-feather="trash-2" class="me-50"></i> Delete
                                        </button>
                                        @endif
                                        @if($buttons['draft'])
                                            <button type="submit" value="draft" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button">
                                                <i data-feather='save'></i> Save as Draft
                                            </button>
                                        @endif
                                        @if($buttons['submit'])
                                            <button type="submit" value="submitted" class="btn btn-primary btn-sm submit-button">
                                                <i data-feather="check-circle"></i> Submit
                                            </button>
                                        @endif
                                        @if($buttons['approve'])
                                            <button type="button" id="reject-button" data-bs-toggle="modal" data-bs-target="#approveModal" onclick="setReject();" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                                </svg>
                                                Reject
                                            </button>
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal" onclick="setApproval();">
                                                <i data-feather="check-circle"></i> Approve
                                            </button>
                                        @endif
                                        @if($buttons['amend'])
                                            <button type="button" id="amendShowButton" onclick="openModal('amendmentconfirm')" class="btn btn-primary btn-sm mb-50 mb-sm-0">
                                                <i data-feather='edit'></i> Amendment
                                            </button>
                                        @endif
                                        @if($buttons['revoke'])
                                            <button type="button" id="revokeButton" onclick="revokeDocument();" class="btn btn-primary btn-sm mb-50 mb-sm-0">
                                                <i data-feather='rotate-ccw'></i> Revoke
                                            </button>
                                        @endif
                                    @else
                                        <button type="submit" value="draft" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button"></i> Save as Draft</button>  
                                        <button type="submit" value="submitted" class="btn btn-primary btn-sm submit-button"></i> Submit</button> 
                                    @endif
                            @endif
                            <input type="hidden" value="draft" name="current_status" id="document_status"/>
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
                                               
                                                <div>
                                                    <div class="d-flex align-items-center"> 
                                                        <a href="{{route('bill.of.material.index')}}"  target="_blank" class="text-primary add-contactpeontxt mt-25 me-1"><i data-feather='file-text'></i> Bill of Material</a>
                                                        <div class="form-check form-check-primary form-switch statusactiinactive me-1">
                                                            <input type="hidden" name="status" id="status_hidden_input" value="">
                                                            <input type="checkbox" class="form-check-input" id="customSwitch3" {{ $isChecked }} {{ $isItemReferenced ? 'disabled' : '' }} >
                                                            <span class="itemactive">Active</span>
                                                            <span class="itemactive iteminactive">Inactive</span>
                                                        </div>
                                                    </div>    
                                                </div>
                                            </div>
                                        </div> 
                                        
                                        <div class="col-md-9">
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Type<span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="demo-inline-spacing">
                                                    @foreach ($types as $type)
                                                        <div class="form-check form-check-primary mt-25">
                                                        <input type="radio" 
                                                                id="{{ $type }}" 
                                                                name="type" 
                                                                value="{{ $type }}" 
                                                                class="form-check-input" 
                                                                {{ $item->type === $type ? 'checked' : '' }}
                                                                {{ $isItemReferenced  ? 'disabled' : '' }} 
                                                            >
                                                            <label class="form-check-label fw-bolder" for="{{ $type }}">
                                                                {{ ucfirst($type) }}
                                                            </label>
                                                            @if($isItemReferenced  && $item->type === $type)
                                                                <input type="hidden" name="type" value="{{ $type }}">
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Group Mapping<span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-3 pe-sm-0 mb-1 mb-sm-0">
                                                    <input type="text" name="category_name" class="form-control category-autocomplete" placeholder="Type to search group" value="{{ $item->subCategory->name ?? '' }}">
                                                    <input type="hidden" name="subcategory_id" class="category-id" value="{{ $item->subcategory_id?? '' }}">
                                                    <input type="hidden" name="category_type" class="category-type" value="Product">
                                                    <input type="hidden" name="cat_initials" class="cat_initials-id" value="{{ $item->subCategory->cat_initials ?? ($item->subCategory->sub_cat_initials ?? '') }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <a href="{{route('categories.index')}}" target="_blank" class="voucehrinvocetxt mt-0">Add Group</a>
                                                </div>
                                            </div>

                                            <div class="row mb-1"> 
                                                <div class="col-md-3"> 
                                                    <label class="form-label">Sub Type <span class="text-danger">*</span></label>  
                                                </div> 
                                                <div class="col-md-9"> 
                                                    <div class="demo-inline-spacing">
                                                        @foreach ($subTypes as $subType)
                                                                <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                
                                                                <input type="checkbox" 
                                                                    class="form-check-input subTypeCheckbox" 
                                                                    id="subType{{ $subType->id }}"
                                                                    name="sub_types[]" 
                                                                    value="{{ $subType->id ??'' }}"{{ $isItemReferenced  ? 'disabled' : '' }}
                                                                    {{ isset($item) && $item->subTypes->contains('sub_type_id', $subType->id) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="subType{{ $subType->id }}">{{ $subType->name }}</label>
                                                            </div>
                                                        @endforeach

                                                          {{-- Scrap Checkbox --}} 
                                                            <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                <input type="hidden" name="is_scrap" value="0">
                                                                <input type="checkbox" class="form-check-input subTypeCheckbox" id="scrapCheckbox" name="is_scrap" value="1" 
                                                                    {{ isset($item) && $item->is_scrap ? 'checked' : '' }} 
                                                                    {{ $isItemReferenced ? 'disabled' : '' }}>
                                                                <label class="form-check-label" for="scrapCheckbox">Scrap</label>
                                                            </div>
                                                           {{-- Traded Item Checkbox --}}
                                                           
                                                            <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                <input type="hidden" name="is_traded_item" value="0">
                                                                <input type="checkbox" class="form-check-input subTypeCheckbox" id="tradedItemCheckbox" name="is_traded_item"value="1" {{ isset($item) && $item->is_traded_item ? 'checked' : '' }} >
                                                                <label class="form-check-label" for="tradedItemCheckbox">Traded Item</label>
                                                            </div>

                                                            {{-- Asset Checkbox --}}
                                                            <div class="form-check form-check-primary mt-25 custom-checkbox me-0">
                                                                <input type="hidden" name="is_asset" value="0">
                                                                <input type="checkbox" class="form-check-input subTypeCheckbox" id="assetCheckbox" name="is_asset" value="1" {{ isset($item) && $item->is_asset ? 'checked' : '' }} {{ $isItemReferenced  ? 'disabled' : '' }}>
                                                                <label class="form-check-label" for="assetCheckbox">Asset</label>
                                                            </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="hsn">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">
                                                            <span id="item_name_label">Item Name</span><span class="text-danger">*</span>
                                                        </label>
                                                    </div>
                                                    <div class="col-md-5 mb-1 mb-sm-0">
                                                        <input type="text" name="item_name" class="form-control item-name-autocomplete" value="{{ old('item_name', $item->item_name ?? '') }}" {{ $isItemReferenced ? 'readonly' : '' }}/>
                                                    </div>
                                                    <div class="col-md-2" >
                                                        <label class="form-label">
                                                            <span id="item_initial_label">Item Initial</span><span class="text-danger">*</span>
                                                        </label>
                                                    </div>
                                                    <div class="col-md-2 mb-1 mb-sm-0">
                                                        <input type="text" name="item_initial" class="form-control" value="{{ old('item_initial', $item->item_initial ?? '') }}" {{ $isItemReferenced ? 'readonly' : '' }} />
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label"  id="item_code_label"><span id="item_code_label">Item Code</span><span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text"  name="item_code" class="form-control" value="{{ old('item_code', $item->item_code ??'') }}" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">HSN/SAC<span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5 mb-1 mb-sm-0">
                                                        <input type="text" name="hsn_name" id="hsn-autocomplete_1" class="form-control hsn-autocomplete" data-id="1" placeholder="Select HSN/SAC" autocomplete="off" value="{{ $item->hsn ? $item->hsn->code : '' }}" {{ $isItemReferenced ? 'readonly' : '' }}/>
                                                        <input type="hidden" class="hsn-id" name="hsn_id" value="{{ $item->hsn_id ?? '' }}"/>
                                                    </div>

                                                    <div class="col-md-2" >
                                                       <label class="form-label">Inventory UOM <span class="text-danger">*</span></label>  
                                                    </div>
                                                    <div class="col-md-2 mb-1 mb-sm-0">
                                                        <select name="uom_id" class="form-select select2" {{$isItemReferenced ? 'disabled' : '' }}>
                                                            @foreach ($units as $unit)
                                                                <option value="{{ $unit->id }}" {{ $item->uom_id == $unit->id ? 'selected' : '' }}>
                                                                    {{ $unit->name ??'' }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @if($isItemReferenced && $item->uom_id)
                                                            <input type="hidden" name="uom_id" value="{{ $item->uom_id }}">
                                                        @endif
                                                    </div>
                                                </div>
                                            
                                                <div class="row align-items-center mb-1">
                                                     <div class="col-md-3"> 
                                                        <label class="form-label">Cost Price</label>  
                                                    </div>
                                                    <div class="col-md-3 mb-1 mb-sm-0 middleinputerror">
                                                        <div class="input-group">
                                                            <input type="text" name="cost_price" class="form-control cost-price-input" value="{{ number_format($item->cost_price, 2) }}" placeholder="Enter Cost Price">
                                                            <select class="form-select select2" id="currencySelect" name="cost_price_currency_id">
                                                                @foreach($currencies as $currency)
                                                                <option value="{{ $currency->id }}" data-short-name="{{ $currency->short_name ?? '' }}"
                                                                    {{ (isset($item) && $item->cost_price_currency_id == $currency->id) || 
                                                                    (isset($item) && !isset($item->cost_price_currency_id) && isset($organization) && $organization->currency_id == $currency->id) ? 'selected' : '' }}>
                                                                     {{ $currency->short_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 text-sm-end mb-1 mb-sm-0">  
                                                        <label class="form-label fw-bold">Selling Price</label>  
                                                    </div>
                                                      <div class="col-md-3 middleinputerror">
                                                        <div class="input-group">
                                                            <input type="text" name="sell_price" class="form-control sell-price-input" value="{{ number_format($item->sell_price, 2) }}" placeholder="Enter Sell Price">
                                                              <select class="form-select select2" id="currencySelect" name="sell_price_currency_id">
                                                                @foreach($currencies as $currency)
                                                                <option value="{{ $currency->id }}" data-short-name="{{ $currency->short_name ?? '' }}"
                                                                    {{ (isset($item) && $item->sell_price_currency_id == $currency->id) || 
                                                                    (isset($item) && !isset($item->sell_price_currency_id) && isset($organization) && $organization->currency_id == $currency->id) ? 'selected' : '' }}>
                                                                      {{ $currency->short_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3"> 
                                                        <label class="form-label">Item Remarks</label>  
                                                    </div>  
                                                    <div class="col-md-9">
                                                        <textarea name="item_remark" id="item_remark" class="form-control" rows="1">{{ old('item_remark', $item->item_remark ?? '') }}</textarea>
                                                    </div>
                                                </div>
   
                                            </div>
                                        </div>
                                        <div class="col-md-3 border-start">
                                            @if(isset($item) && ($item->document_status !== "draft"))
                                                @if((isset($approvalHistory) && count($approvalHistory) > 0) || isset($item->revision_number))
                                                        <div class="step-custhomapp bg-light p-1 customerapptimelines customerapptimelinesapprovalpo">
                                                            <h5 class="mb-2 text-dark border-bottom pb-50 d-flex align-items-center justify-content-between">
                                                                <strong><i data-feather="arrow-right-circle"></i> Approval History</strong>
                                                                @if(!isset(request()->revisionNumber) && $item->document_status !== 'draft')
                                                                    <strong class="badge rounded-pill badge-light-secondary amendmentselect">Rev. No.
                                                                        <select class="form-select cannot_disable" id="revisionNumber">
                                                                            @for($i=$item->revision_number; $i >= 0; $i--)
                                                                                <option value="{{$i}}" {{request('revisionNumber', $item->revision_number) == $i ? 'selected' : ''}}>{{$i}}</option>
                                                                            @endfor
                                                                        </select>
                                                                    </strong>
                                                                @else
                                                                    @if ($item->document_status !== 'draft')
                                                                        <strong class="badge rounded-pill badge-light-secondary amendmentselect cannot_disable">
                                                                            Rev. No. {{ request()->revisionNumber }}
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
                                                                                <h6>{{ ucfirst($approvalHist->name ?? $approvalHist?->user?->name ?? 'NA') }}</h6>
                                                                                @if($approvalHist->approval_type == 'approve')
                                                                                    <span class="badge rounded-pill badge-light-success">{{ ucfirst($approvalHist->approval_type) }}</span>
                                                                                @elseif($approvalHist->approval_type == 'submit')
                                                                                    <span class="badge rounded-pill badge-light-primary">{{ ucfirst($approvalHist->approval_type) }}</span>
                                                                                @elseif($approvalHist->approval_type == 'reject')
                                                                                    <span class="badge rounded-pill badge-light-danger">{{ ucfirst($approvalHist->approval_type) }}</span>
                                                                                @else
                                                                                    <span class="badge rounded-pill badge-light-danger">{{ ucfirst($approvalHist->approval_type) }}</span>
                                                                                @endif
                                                                            </div>
                                                                            @if($approvalHist->approval_date)
                                                                                <h6>
                                                                                    {{ \Carbon\Carbon::parse($approvalHist->approval_date)->format('d-m-Y') }}
                                                                                </h6>
                                                                            @endif
                                                                            @if($approvalHist->remarks)
                                                                                <p>{!! $approvalHist->remarks !!}</p>
                                                                            @endif
                                                                            @if ($approvalHist->media && count($approvalHist->media) > 0)
                                                                                @foreach ($approvalHist->media as $mediaFile)
                                                                                    <p><a href="{{ $mediaFile->file_url }}" target="_blank">
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download">
                                                                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                                                                <polyline points="7 10 12 15 17 10"></polyline>
                                                                                                <line x1="12" y1="15" x2="12" y2="3"></line>
                                                                                            </svg>
                                                                                        </a></p>
                                                                                @endforeach
                                                                            @endif
                                                                        </div>
                                                                    </li>
                                                                @endforeach

                                                            </ul>
                                                        </div>
                                                @endif
                                            @endif
                                            {{-- Approval History Section --}}
                                        </div>
                                       </div>
                                            <div class="mt-2">
                                                <div class="step-custhomapp bg-light">
                                                    <ul class="nav nav-tabs my-25 custapploannav" role="tablist"> 
                                                        <li class="nav-item">
                                                           <a class="nav-link active" data-bs-toggle="tab" href="#Specification">Product Specification</a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#Attributes">Attributes</a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#UOM">Alt. UOM</a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#Alternative">Alternative Items</a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#Details">Inventory Details</a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#Customer">Approved Customers</a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#Vendors">Approved Vendors</a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" id="assetTabLink" data-bs-toggle="tab" href="#Assets" style="display: none;">Asset Details</a>
                                                        </li>
                                                        <!-- <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#Compliances">Compliances</a>
                                                        </li> -->
                                                    </ul> 
                                                </div>

												 <div class="tab-content pb-1 px-1">
                                                        <div class="tab-pane active" id="Specification">
                                                            <div class="row align-items-center mb-3">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Product Specification Group</label>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <select id="groupSelect" class="form-select mw-100 select2 specificationId">
                                                                        <option value="">Select Group</option>
                                                                        @foreach ($specificationGroups as $group)
                                                                            <option value="{{ $group->id }}" 
                                                                                @if(isset($item->specifications) && $item->specifications->isNotEmpty() && $item->specifications->first()->group_id == $group->id) 
                                                                                    selected
                                                                                @endif>
                                                                                {{ $group->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div id="specificationContainer" class="mt-2">
                                                                @if(isset($item->specifications))
                                                                @foreach($item->specifications as $index => $specification)
                                                                <input type="hidden" name="item_specifications[{{ $index }}][id]" value="{{ $specification->id }}">
                                                                    <div class="row mb-3" data-specification-id="{{ $specification->id }}">
                                                                        <div class="col-md-2">
                                                                            <input type="hidden" name="item_specifications[{{ $index }}][group_id]" value="{{ $specification->group_id }}">
                                                                            <input type="hidden" name="item_specifications[{{ $index }}][specification_id]" value="{{ $specification->specification_id}}">
                                                                            <input type="hidden" name="item_specifications[{{ $index }}][specification_name]" value="{{ $specification->specification_name }}">
                                                                            <label class="form-label">{{ $specification->specification_name }}</label>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <input type="text" class="form-control" name="item_specifications[{{ $index }}][value]" value="{{ $specification->value }}" placeholder="Enter value">
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                                @endif
                                                            </div>
                                                            <div id="specificationContainer" class="mt-2">
                                                                <input type="hidden" id="hiddenGroupId" name="item_specifications[group_id]" value="">
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane" id="Attributes">
                                                            <div class="table-responsive-md">
                                                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border" id="attributesTable">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>S.NO</th>
                                                                            <th>Attribute Name</th>
                                                                            <th>Attribute Value</th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @if (!$item->itemAttributes->isEmpty())
                                                                            @foreach ($item->itemAttributes as $index => $attribute)
                                                                            @php
                                                                                $usageResults = $attribute->checkAttributeUsage($attribute->id, $attribute->attribute_group_id, $attribute->attribute_id, $tables);
                                                                                $isGroupUsed = $usageResults['group_match']; 
                                                                            @endphp
                                                                            <tr data-index="{{ $index }}">
                                                                            <input type="hidden" name="attributes[{{ $index }}][id]" value="{{ $attribute->id }}">
                                                                                <td>{{ $index + 1 }}</td>
                                                                                <td>
                                                                                    <select name="attributes[{{ $index }}][attribute_group_id]" class="form-select mw-100 select2 attribute-group" {{ $isGroupUsed ? 'disabled' : '' }}>
                                                                                        @foreach ($attributeGroups as $group)
                                                                                            <option value="{{ $group->id }}" {{ $group->id == $attribute->attribute_group_id ? 'selected' : '' }}>
                                                                                                {{ $group->name }}
                                                                                            </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </td>
                                                                                <td>
                                                                                    <div class="align-items-center row">
                                                                                        <div class="col-md-10">
                                                                                            {{-- Visible Select --}}
                                                                                            <div class="attribute-value">
                                                                                            <select name="attributes[{{ $index }}][attribute_id][]" 
                                                                                                    class="form-select mw-100 select2 attribute-values" 
                                                                                                    multiple>
                                                                                                @php
                                                                                                    $selectedAttributes = isset($attribute->attribute_id) ? (array) $attribute->attribute_id : [];
                                                                                                @endphp
                                                                                                @if (isset($attribute->attributeGroup->attributes) && count($attribute->attributeGroup->attributes) > 0)
                                                                                                    @foreach ($attribute->attributeGroup->attributes as $value)
                                                                                                        <option value="{{ $value->id }}" {{ in_array($value->id, $selectedAttributes) ? 'selected' : '' }} >
                                                                                                            {{ $value->value }}
                                                                                                        </option>
                                                                                                    @endforeach
                                                                                                @else
                                                                                                    <option disabled>No attributes available</option>
                                                                                                @endif
                                                                                            </select>
                                                                                            </div>

                                                                                            {{-- Hidden Select: always hidden, only enabled on 'All' check --}}
                                                                                            <div class="attribute-values-hidden">
                                                                                                <select name="attributesss[{{ $index }}][attribute_id][]" 
                                                                                                        class="form-select mw-100  attribute-values" 
                                                                                                        multiple hidden disabled style="display:none" >
                                                                                                   <option></option>
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-md-2">
                                                                                            <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                                                <input type="checkbox" class="form-check-input all-checked" 
                                                                                                    name="attributes[{{ $index }}][all_checked]" 
                                                                                                    value="{{ isset($attribute->all_checked) ? $attribute->all_checked : '' }}" 
                                                                                                    id="allChecked-{{ $index }}"
                                                                                                    {{ isset($attribute->all_checked) && $attribute->all_checked ? 'checked disabled' : '' }}>
                                                                                                <label class="form-check-label" for="allChecked-{{ $index }}">All</label>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </td>
                                                                                <td>
                                                                                    @if ($index == 0)
                                                                                        <a href="#" class="text-danger remove-row"><i data-feather='trash-2'></i></a>
                                                                                        <a href="#" class="text-primary add-row"><i data-feather='plus-square'></i></a>
                                                                                    @endif
                                                                                    <a href="#" class="text-danger remove-row" style="{{ $index == 0 ? 'display: none;' : '' }}"><i data-feather='trash-2'></i></a>
                                                                                </td>
                                                                            </tr>
                                                                            @endforeach
                                                                        
                                                                        @endif
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane" id="UOM">
                                                            <div class="table-responsive-md">
                                                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border" id="alternateUOMTable">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>S.NO</th>
                                                                            <th width="300px">UOM</th>
                                                                            <th>Conversion to Inventory</th>
                                                                            <th>Cost Price</th>
                                                                            <th>Sell Price</th>
                                                                            <th>Default</th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    @forelse ($item->alternateUOMs ?? [] as $index => $uom)
                                                                            <tr id="row-{{ $index }}" data-predefined-cost-price="{{ number_format($uom->cost_price, 2) }}">
                                                                                <td>{{ $index + 1 }}</td>
                                                                                <td>
                                                                                    <select name="alternate_uoms[{{ $index }}][uom_id]" class="form-select mw-100">
                                                                                    <option value="">Select</option>
                                                                                        @foreach ($units as $unit)
                                                                                            <option value="{{ $unit->id ??'' }}" {{ $unit->id == $uom->uom_id ? 'selected' : '' }}>{{ $unit->name ??'' }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                    <input type="hidden" name="alternate_uoms[{{ $index }}][id]" value="{{ $uom->id }}">
                                                                                </td>
                                                                                <td><input type="text" name="alternate_uoms[{{ $index }}][conversion_to_inventory]" class="form-control mw-100" value="{{ $uom->conversion_to_inventory ??'' }}"></td>
                                                                                <td><input type="text" name="alternate_uoms[{{ $index }}][cost_price]" class="form-control cost-price-alternate  mw-100" value="{{number_format($uom->cost_price,2 ??'') }}"></td>
                                                                                <td><input type="text" name="alternate_uoms[{{ $index }}][sell_price]" class="form-control sell-price-alternate  mw-100" value="{{number_format($uom->sell_price,2 ??'') }}"></td>
                                                                                <td>
                                                                                    <div class="demo-inline-spacing">
                                                                                        <div class="form-check form-check-primary mt-25">
                                                                                            <input type="radio" id="is_purchasing_{{ $index }}_1" name="alternate_uoms[{{ $index }}][is_purchasing]" value="1" class="form-check-input" {{ $uom->is_purchasing ? 'checked' : '' }}>
                                                                                            <label class="form-check-label fw-bolder" for="is_purchasing_{{ $index }}_1">Purchase</label>
                                                                                        </div>
                                                                                        <div class="form-check form-check-primary mt-25">
                                                                                            <input type="radio" id="is_selling_{{ $index }}_1" name="alternate_uoms[{{ $index }}][is_selling]" value="1" class="form-check-input" {{ $uom->is_selling ? 'checked' : '' }}>
                                                                                            <label class="form-check-label fw-bolder" for="is_selling_{{ $index }}_1">Selling</label>
                                                                                        </div>
                                                                                    </div>
                                                                                </td>
                                                                                <td>
                                                                                    <a href="#" class="text-primary add-address"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                    <a href="#" class="text-danger delete-address"><i data-feather="trash-2" class="me-50"></i></a>
                                                                                </td>
                                                                            </tr>
                                                                        @empty
                                                                            <tr id="row-0">
                                                                                <td>1</td>
                                                                                <td>
                                                                                    <select name="alternate_uoms[0][uom_id]" class="form-select mw-100">
                                                                                    <option value="">Select</option>
                                                                                        @foreach ($units as $unit)
                                                                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </td>
                                                                                <td><input type="text" name="alternate_uoms[0][conversion_to_inventory]" class="form-control mw-100"></td>
                                                                                <td> <input type="text" name="alternate_uoms[0][cost_price]" class="form-control cost-price-alternate  mw-100"></td>
                                                                                <td> <input type="text" name="alternate_uoms[0][sell_price]" class="form-control sell-price-alternate mw-100"></td>
                                                                                <td>
                                                                                    <div class="demo-inline-spacing">
                                                                                        <div class="form-check form-check-primary mt-25">
                                                                                            <input type="radio" id="is_purchasing_0_1" name="alternate_uoms[0][is_purchasing]" value="1" class="form-check-input">
                                                                                            <label class="form-check-label fw-bolder" for="is_purchasing_0_1">Purchase</label>
                                                                                        </div>
                                                                                        <div class="form-check form-check-primary mt-25">
                                                                                            <input type="radio" id="is_selling_0_1" name="alternate_uoms[0][is_selling]" value="1" class="form-check-input">
                                                                                            <label class="form-check-label fw-bolder" for="is_selling_0_1">Selling</label>
                                                                                        </div>
                                                                                    </div>
                                                                                </td>
                                                                                <td>
                                                                                    <a href="#" class="text-primary add-address"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                    <a href="#" class="text-danger delete-address"><i data-feather="trash-2" class="me-50"></i></a>
                                                                                </td>
                                                                            </tr>
                                                                        @endforelse
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>

                                                        <div class="tab-pane" id="Alternative">
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2"> 
                                                                    <label class="form-label">Alternative Item</label>  
                                                                </div>  
                                                                <div class="col-md-3">  
                                                                    <input class="form-control item-autocomplete" data-name="" data-code="" placeholder="Search Item" autocomplete="off">
                                                                    <input type="hidden" id="itemId" name="item_id">
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <a href="#" id="addNewItem" class="text-primary add-contactpeontxt mt-1 mt-sm-0"><i data-feather='plus'></i> Add New</a>
                                                                </div>
                                                            </div> 
                                                            <div class="table-responsive-md"> 
                                                                <table id="itemTable" class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border"> 
                                                                    <thead>
                                                                        <tr>
                                                                            <th width="100px">S.NO</th> 
                                                                            <th width="200px">Item Code</th>
                                                                            <th width="400px">Item Name</th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    @if(count($item->alternateItems) > 0)
                                                                  
                                                                        @foreach($item->alternateItems as $index => $alternateItem)
                                                                            <tr data-id="{{ $alternateItem->id }}">
                                                                                <input type="hidden" name="alternateItems[{{ $index }}][id]" value="{{ $alternateItem->id }}">
                                                                                <input type="hidden" name="alternateItems[{{ $index }}][alt_item_id]" value="{{ $alternateItem->alt_item_id ?? '' }}">
                                                                                <td>{{ $index + 1 }}</td>
                                                                                <td>
                                                                                    <input type="hidden" name="alternateItems[{{ $index }}][item_code]" value="{{ $alternateItem->item_code ??'' }}" />
                                                                                    {{ $alternateItem->item_code }}
                                                                                </td>
                                                                                <td>
                                                                                    <input type="hidden" name="alternateItems[{{ $index }}][item_name]" value="{{ $alternateItem->item_name ??'' }}" />
                                                                                    {{ $alternateItem->item_name }}
                                                                                </td>
                                                                                <td>
                                                                                    <a href="#" class="text-danger remove-item"><i data-feather="trash-2" class="me-50"></i></a>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    @endif
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                   
                                                        <div class="tab-pane" id="Details">
                                                            <div class="row mt-2">
                                                                <div class="col-md-12">
                                                                    <div class="newheader border-bottom pb-50 mb-1">
                                                                        <h4 class="card-title text-theme">Replenishment</h4>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">Min Stocking Level</label>
                                                                    <input type="text" class="form-control numberonly" name="min_stocking_level" value="{{ isset($item->min_stocking_level) ? (int) $item->min_stocking_level : '' }}" />
                                                                </div>
                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">Max Stocking Level</label>
                                                                    <input type="text" class="form-control numberonly" name="max_stocking_level"value="{{ isset($item->max_stocking_level) ? (int) $item->max_stocking_level : '' }}" />
                                                                </div>
                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">Reorder Level</label>
                                                                    <input type="text" class="form-control numberonly" name="reorder_level" value="{{ isset($item->reorder_level) ? (int) $item->reorder_level : '' }}" />
                                                                </div>
                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">Minimum Order Qty</label>
                                                                    <input type="text" class="form-control numberonly" name="minimum_order_qty" value="{{ isset($item->minimum_order_qty) ? (int) $item->minimum_order_qty : '' }}" />
                                                                </div>
                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">Lead Days</label>
                                                                    <input type="text" class="form-control numberonly" name="lead_days" value="{{ $item->lead_days ?? '' }}" />
                                                                </div>
                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">Safety Days</label>
                                                                    <input type="text" class="form-control numberonly" name="safety_days" value="{{ $item->safety_days ?? '' }}" />
                                                                </div>
                                                            </div>

                                                            <div class="row mt-1">
                                                                <div class="col-md-12">
                                                                    <div class="newheader border-bottom pb-50 mb-1">
                                                                        <h4 class="card-title text-theme">Tolerance</h4>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">PO Positive Tolerance</label>
                                                                    <input type="number" class="form-control" step="any" name="po_positive_tolerance" value="{{ $item->po_positive_tolerance ?? '' }}" />
                                                                </div>
                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">PO Negative Tolerance</label>
                                                                    <input type="number" class="form-control" step="any" name="po_negative_tolerance" value="{{ $item->po_negative_tolerance ?? '' }}" />
                                                                </div>
                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">SO Positive Tolerance</label>
                                                                    <input type="number" class="form-control" step="any" name="so_positive_tolerance" value="{{ $item->so_positive_tolerance ?? '' }}" />
                                                                </div>
                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">SO Negative Tolerance</label>
                                                                    <input type="number" class="form-control" step="any" name="so_negative_tolerance" value="{{ $item->so_negative_tolerance ?? '' }}" />
                                                                </div>
                                                            </div>

                                                            <div class="row mt-1">
                                                                <div class="col-md-12">
                                                                    <div class="newheader border-bottom pb-50 mb-1">
                                                                        <h4 class="card-title text-theme">Storage</h4>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">Storage UOM</label>
                                                                    <select name="storage_uom_id" class="form-select select2">
                                                                        <option value="">Select Storage Uom</option>
                                                                        @foreach ($units as $unit)
                                                                            <option value="{{ $unit->id }}" {{ (isset($item) && $item->storage_uom_id == $unit->id) ? 'selected' : '' }}>
                                                                                {{ $unit->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                               <div class="col-md-3 mb-1">
                                                                    <label class="form-label">Conversion</label>
                                                                    <input type="text" name="storage_uom_conversion" class="form-control" 
                                                                        placeholder="Enter Conversion"
                                                                        value="{{ isset($item->storage_uom_conversion) ? number_format($item->storage_uom_conversion, 2, '.', '') : '' }}"
                                                                        {{ (isset($item) && $item->uom_id == $item->storage_uom_id) ? 'readonly' : '' }}>
                                                                </div>

                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">No of Pack</label>
                                                                    <input type="number" name="storage_uom_count" class="form-control" 
                                                                        placeholder="Enter No of Pack"
                                                                        value="{{ isset($item->storage_uom_count) ? number_format($item->storage_uom_count, 2, '.', '') : '' }}">
                                                                </div>

                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">Weight (kg)</label>
                                                                    <input type="number" step="0.01" name="storage_weight" class="form-control" 
                                                                        placeholder="Enter Storage Weight in KG"
                                                                        value="{{ isset($item->storage_weight) ? number_format($item->storage_weight, 2, '.', '') : '' }}">
                                                                </div>

                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">Volume (cft)</label>
                                                                    <input type="number" step="0.01" name="storage_volume" class="form-control" 
                                                                        placeholder="Enter Storage Volume in CFT"
                                                                        value="{{ isset($item->storage_volume) ? number_format($item->storage_volume, 2, '.', '') : '' }}">
                                                                </div>
                                                            </div>

                                                            <div class="row mt-1">
                                                                <div class="col-md-12">
                                                                    <div class="newheader border-bottom pb-50 mb-1">
                                                                        <h4 class="card-title text-theme">Inspection</h4>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">Inspection Required</label>
                                                                    <select name="is_inspection" id="is_inspection" class="form-select select2">
                                                                        <option value="1" {{ $item->is_inspection ? 'selected' : '' }}>Yes</option>
                                                                        <option value="0" {{ !$item->is_inspection ? 'selected' : '' }}>No</option>
                                                                    </select>
                                                                </div>

                                                                <div class="col-md-6" id="inspectionCheckContainer">
                                                                    <div class="row align-items-center mb-1">
                                                                      <label class="form-label">Inspection Checklist</label>
                                                                        <div class="col-md-8">
                                                                            <input type="text" name="inspection_checklist_name" class="form-control inspection-autocomplete" placeholder="Search Inspection Checklist" value="{{ $item->inspectionChecklist ? $item->inspectionChecklist->name : '' }}" />
                                                                            <input type="hidden" name="inspection_checklist_id" class="inspection_checklist_id" value="{{ $item->inspection_checklist_id ?? '' }}" />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row mt-1">
                                                                <div class="col-md-12">
                                                                    <div class="newheader border-bottom pb-50 mb-1">
                                                                        <h4 class="card-title text-theme">Tracking Type</h4>
                                                                    </div>
                                                                </div>
                                                            </div>



                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Serial No</label>
                                                                    <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                        <input type="hidden" name="is_serial_no" value="0">
                                                                        <input type="checkbox" class="form-check-input" id="Serial" name="is_serial_no" value="1"
                                                                            @if(isset($item) && $item->is_serial_no) checked @endif>
                                                                        <label class="form-check-label" for="Serial">Yes/No</label>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-2">
                                                                    <label class="form-label">Batch No</label>
                                                                    <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                        <input type="hidden" name="is_batch_no" value="0">
                                                                        <input type="checkbox" class="form-check-input" id="Batch" name="is_batch_no" value="1"
                                                                            @if(isset($item) && $item->is_batch_no) checked @endif>
                                                                        <label class="form-check-label" for="Batch">Yes/No</label>
                                                                    </div>
                                                                </div>



                                                                   <div class="col-md-2">
                                                                        <label class="form-label">Expiry</label>
                                                                        <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                            <input type="hidden" name="is_expiry" value="0">
                                                                            <input type="checkbox" class="form-check-input" id="ExpiryCheck" name="is_expiry" value="1"
                                                                                @if(isset($item) && $item->is_expiry) checked @endif>
                                                                            <label class="form-check-label" for="ExpiryCheck">Yes/No</label>
                                                                        </div>
                                                                   </div>

                                                                   <div class="col-md-3" id="shelfLifeContainer" style="display: none;">
                                                                        <label class="form-label">Shelf Life in Days</label>
                                                                        <input type="text" class="form-control numberonly" name="shelf_life_days" @if(isset($item)) value="{{ $item->shelf_life_days }}" @endif />
                                                                   </div>
                                                            </div>

                                                            {{-- Uncomment if you need storage type --}}
                                                            {{-- 
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Storage Type</label>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <select name="storage_type" class="form-select mw-100">
                                                                                <option value="">Select</option>
                                                                                @foreach ($storageTypes as $type)
                                                                                    <option value="{{ $type }}" {{ (isset($item) && $item->storage_type == $type) ? 'selected' : '' }}>
                                                                                        {{ ucfirst($type) }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            --}}
                                                        </div>
                                                       
                                                        <?php
                                                            $alternateUOMIds = [];
                                                            foreach ($item->alternateUOMs as $alternateItem) {
                                                                $alternateUOMIds[] = $alternateItem->uom_id;
                                                            }
                                                            $filteredUnits = [];
                                                            $uomIdsToAdd = array_merge([$item->uom_id], $alternateUOMIds);
                                                            foreach ($units as $unit) {
                                                                if (in_array($unit->id, $uomIdsToAdd)) {
                                                                    $filteredUnits[] = $unit;
                                                                }
                                                            }
                                                        ?>
                                                        <div class="tab-pane" id="Customer">
                                                            <div class="table-responsive-md">
                                                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border" id="customerTable">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>S.NO</th>
                                                                            <th width="300px">Customer Name</th>
                                                                            <th>Customer Code</th>
                                                                            <th>Customer Item Code</th>
                                                                            <th>Customer Item Name</th>
                                                                            <th>Customer Item Details</th>
                                                                            <th id="sell-price-header">Sell Price</th>
                                                                            <th>Purchase Uom</th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="customerTableBody">
                                                                       @forelse ($item->approvedCustomers ?? [] as $index => $customer)
                                                                            <tr id="row-{{ $index }}">
                                                                            <input type="hidden" name="approved_customer[{{ $index }}][id]" value="{{ $customer->id }}">
                                                                                <td>{{ $index + 1 }}</td>
                                                                                <td>
                                                                                    <input type="text" name="approved_customer[{{ $index }}][customer_name]" class="form-control mw-100 customer-autocomplete" data-id="{{ $index }}" value="{{ $customer->customer->company_name ??'' }}" placeholder="Search Customer" autocomplete="off">
                                                                                    <input type="hidden" id="customer-id_{{ $index }}" name="approved_customer[{{ $index }}][customer_id]" class="customer-id" value="{{ $customer->customer_id ?? '' }}">
                                                                                </td>
                                                                                <td><input type="text" name="approved_customer[{{ $index }}][customer_code]" id="customer-code_0" class="form-control mw-100" readonly value="{{ $customer->customer_code ??'' }}"></td>
                                                                                <td><input type="text" name="approved_customer[{{ $index }}][item_code]" class="form-control mw-100"  value="{{ $customer->item_code ??'' }}"></td>
                                                                                <td><input type="text" name="approved_customer[{{ $index }}][item_name]" class="form-control mw-100" value="{{ $customer->item_name??'' }}"></td>
                                                                                <td><input type="text" name="approved_customer[{{ $index }}][item_details]" class="form-control mw-100" value="{{ $customer->item_details ??'' }}"></td>
                                                                                <td><input type="text" name="approved_customer[{{ $index }}][sell_price]"  class="form-control sell-price-approved-customer mw-100"  id="sell-price_{{ $index }}" value="{{ number_format($customer->sell_price, 2) }}"></td>
                                                                                <td>
                                                                                <select name="approved_customer[{{ $index }}][uom_id]" id="uom_{{ $index }}" class="form-select mw-100">
                                                                                    <option value="">Select</option>
                                                                                    <?php foreach ($filteredUnits as $unit): ?>
                                                                                        <option value="{{ $unit->id }}" 
                                                                                            {{ $unit->id == $customer->uom_id ? 'selected' : '' }}>
                                                                                            {{ $unit->name }}
                                                                                        </option>
                                                                                    <?php endforeach; ?>
                                                                                </select>
                                                                                </td>
                                                                                <td>
                                                                                    <a href="#" class="text-primary add-row"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                    <a href="#" class="text-danger remove-row"><i data-feather="trash-2" class="me-50"></i></a>
                                                                                </td>
                                                                            </tr>
                                                                        @empty
                                                                            <tr id="row-0">
                                                                                <td>1</td>
                                                                                <td>
                                                                                    <input type="text" name="approved_customer[0][customer_name]" class="form-control mw-100 customer-autocomplete" data-id="0" placeholder="Search Customer" autocomplete="off">
                                                                                    <input type="hidden" id="customer-id_0" name="approved_customer[0][customer_id]" class="customer-id">
                                                                                </td>
                                                                                <td><input type="text" name="approved_customer[0][customer_code]" id="customer-code_0" class="form-control mw-100" readonly></td>
                                                                                <td><input type="text" name="approved_customer[0][item_code]" class="form-control mw-100"></td>
                                                                                <td><input type="text" name="approved_customer[0][item_name]" class="form-control mw-100"></td>
                                                                                <td><input type="text" name="approved_customer[0][item_details]" class="form-control mw-100"></td>
                                                                                <td><input type="text" name="approved_customer[0][sell_price]" id="sell-price_0" class="form-control sell-price-approved-customer mw-100"></td>
                                                                                <td>
                                                                                    <select name="approved_customer[0][uom_id]" id="uom_0" class="form-select mw-100" disabled>
                                                                                        <option value="">Select</option>
                                                                                        <?php foreach ($filteredUnits as $unit): ?>
                                                                                            <option value="{{ $unit->id }}" >
                                                                                                {{ $unit->name }}
                                                                                            </option>
                                                                                        <?php endforeach; ?>
                                                                                    </select>
                                                                                </td>
                                                                                <td>
                                                                                    <a href="#" class="text-primary add-row"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                    <a href="#" class="text-danger remove-row"><i data-feather="trash-2" class="me-50"></i></a>
                                                                                </td>
                                                                            </tr>
                                                                            
                                                                        @endforelse
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                         <div class="tab-pane" id="Vendors">
                                                            <div class="table-responsive-md">
                                                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border" id="vendorTable">
                                                                <thead>
                                                                        <tr>
                                                                            <th>S.NO</th>
                                                                            <th width="300px">Vendor Name</th>
                                                                            <th>Vendor Code</th>
                                                                            <th id="cost-price-header">Cost Price</th>
                                                                            <th>Purchase Uom</th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="vendorTableBody">
                                                                  
                                                                    @forelse ($item->approvedVendors ?? [] as $index => $vendor)
                                                                        <tr id="row-{{ $index }}" data-vendor-predefined-cost-price="{{ number_format($vendor->cost_price, 2) }}">
                                                                            <input type="hidden" name="approved_vendor[{{ $index }}][id]" value="{{ $vendor->id }}">
                                                                            <td>{{ $index + 1 }}</td>
                                                                            <td>
                                                                                <input type="text" name="approved_vendor[{{ $index }}][item_name]" class="form-control mw-100 vendor-autocomplete" data-id="{{ $index }}" value="{{$vendor->vendor->company_name ??''}}" placeholder="Search Vendor" autocomplete="off">
                                                                                <input type="hidden" id="vendor-id_{{ $index }}" name="approved_vendor[{{ $index }}][vendor_id]" class="vendor-id" value="{{ $vendor->vendor_id ?? '' }}">
                                                                            </td>
                                                                            <td><input type="text" name="approved_vendor[{{ $index }}][vendor_code]" class="form-control mw-100" readonly id="item-code_{{ $index }}" value="{{ $vendor->vendor_code ??'' }}" ></td>
                                                                            <td><input type="text" name="approved_vendor[{{ $index }}][cost_price]"  class="form-control cost-price-approved-vendor mw-100"  id="cost-price_{{ $index }}" value="{{ number_format($vendor->cost_price, 2) }}"></td>
                                                                            <td>
                                                                            <select name="approved_vendor[{{ $index }}][uom_id]" id="uom_{{ $index }}" class="form-select mw-100">
                                                                                <option value="">Select</option>
                                                                                <?php foreach ($filteredUnits as $unit): ?>
                                                                                    <option value="{{ $unit->id }}" 
                                                                                        {{ $unit->id == $vendor->uom_id ? 'selected' : '' }}>
                                                                                        {{ $unit->name }}
                                                                                    </option>
                                                                                <?php endforeach; ?>
                                                                            </select>
                                                                            </td>
                                                                            <td>
                                                                                <a href="#" class="text-primary add-row"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                <a href="#" class="text-danger remove-row"><i data-feather="trash-2" class="me-50"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                        @empty
                                                                        <tr id="row-0">
                                                                            <td>1</td>
                                                                            <td>
                                                                                <input type="text" name="approved_vendor[0][item_name]" class="form-control mw-100 vendor-autocomplete" data-id="0" placeholder="Search Vendor" autocomplete="off">
                                                                                <input type="hidden" id="vendor-id_0" name="approved_vendor[0][vendor_id]" class="vendor-id">
                                                                            </td>
                                                                            <td><input type="text" name="approved_vendor[0][vendor_code]" class="form-control mw-100" id="item-code_0" readonly></td>
                                                                            <td><input type="text" name="approved_vendor[0][cost_price]" id="cost-price_0" class="form-control cost-price-approved-vendor mw-100"></td>
                                                                            <td>
                                                                                <select name="approved_vendor[0][uom_id]" id="uom_0" class="form-select mw-100" disabled>
                                                                                      <option value="">Select</option>
                                                                                        <?php foreach ($filteredUnits as $unit): ?>
                                                                                            <option value="{{ $unit->id }}" >
                                                                                                {{ $unit->name }}
                                                                                            </option>
                                                                                        <?php endforeach; ?>
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <a href="#" class="text-primary add-row"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                <a href="#" class="text-danger remove-row"><i data-feather="trash-2" class="me-50"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                        @endforelse

                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                         </div>
                                                       <!-- Asset Category -->
                                                       <div class="tab-pane" id="Assets">
                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-2">
                                                                <label for="asset_category" class="form-label">Category<span class="text-danger">*</span></label>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <select id="asset_category" name="asset_category_id" class="form-select mw-100 select2">
                                                                    <option value="">Select</option>
                                                                        @foreach($fixedAssetCategories as $fixedAssetCategorie)
                                                                            <option value="{{ $fixedAssetCategorie->asset_category_id }}"
                                                                                {{ (isset($item) && $item->asset_category_id == $fixedAssetCategorie->asset_category_id) ? 'selected' : '' }}>
                                                                                {{ $fixedAssetCategorie->assetCategory->name ?? 'N/A' }}
                                                                            </option>
                                                                        @endforeach
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <!-- Expected Life -->
                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-2">
                                                                <label for="expected_life" class="form-label">Est.Useful Life (yrs) <span class="text-danger">*</span></label>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <input type="number" id="expected_life" name="expected_life" class="form-control"
                                                                    value="{{ old('expected_life', $item->expected_life ?? '') }}">
                                                            </div>
                                                        </div>

                                                        <!-- Maintenance Schedule -->
                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-2">
                                                                <label for="maintenance_schedule" class="form-label">Maint.Schedule<span class="text-danger">*</span></label>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <select class="form-select mw-100 select2" name="maintenance_schedule">
                                                                    <option value="">Select</option>
                                                                    @foreach(['weekly', 'monthly', 'quarterly', 'semi-annually', 'annually'] as $schedule)
                                                                        <option value="{{ $schedule }}"
                                                                            {{ (isset($item) && $item->maintenance_schedule == $schedule) ? 'selected' : '' }}>
                                                                            {{ ucfirst($schedule) }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <!-- Brand Name -->
                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-2">
                                                                <label for="brand_name" class="form-label">Brand Name<span class="text-danger">*</span></label>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <input type="text" id="brand_name" name="brand_name" class="form-control"
                                                                    value="{{ $item->brand_name ?? '' }}" maxlength="255">
                                                            </div>
                                                        </div>

                                                        <!-- Model No. -->
                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-2">
                                                                <label for="model_no" class="form-label">Model No.<span class="text-danger">*</span></label>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <input type="text" id="model_no" name="model_no" class="form-control"
                                                                    value="{{ $item->model_no ?? '' }}" maxlength="255">
                                                            </div>
                                                        </div>

                                                        </div>
                                                         <div class="tab-pane" id="Compliances" style="display:none">
                                                             <div class="table-responsive-md"> 
                                                                    <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border"> 
                                                                        <thead>
                                                                             <tr>
                                                                                <th>#</th> 
                                                                                <th>Tax Type</th>
                                                                                <th>Tax</th>
                                                                                <th>Action</th>
                                                                              </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                 <tr>
                                                                                    <td>#</td> 
                                                                                    <td>
                                                                                        <select class="form-select mw-100">
                                                                                            <option>Select</option>
                                                                                            <option selected>TDS</option> 
                                                                                        </select> 
                                                                                    </td>
                                                                                     <td>
                                                                                        <select class="form-select mw-100">
                                                                                            <option>Select</option>
                                                                                            <option selected>Tax on Professional</option>
                                                                                        </select> 
                                                                                    </td>
                                                                                     <td><a href="#" class="text-primary"><i data-feather="plus-square" class="me-50"></i></a></td>
                                                                                  </tr>

                                                                                <tr>
                                                                                    <td>1</td> 
                                                                                    <td>TDS</td> 
                                                                                    <td>Tax on Professional</td> 
                                                                                    <td><a href="#" class="text-danger"><i data-feather="trash-2" class="me-50"></i></a></td>
                                                                                  </tr>


                                                                           </tbody>


                                                                    </table>
                                                                </div>

                                                                <a href="#" class="text-primary add-contactpeontxt"><i data-feather='plus'></i> Add New</a>
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
    <div class="modal fade" id="amendConfirmPopup" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Amend
                    {{request() -> type === 'item' ? 'Item' : 'Item'}}
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
                <button type="button" class="btn btn-outline-secondary me-1" onclick="closeModal('amendConfirmPopup')">Cancel</button> 
                <button type="button" class="btn btn-primary" onclick = "submitAmend();">Submit</button>
            </div>
        </div>
    </div>
    </div>
</form>
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form class="ajax-submit-2" method="POST" action="{{ route('document.approval.item') }}" data-redirect="{{ route('item.index') }}" enctype='multipart/form-data'>
          @csrf
          <input type="hidden" class = "cannot_disable" name="action_type" id="action_type">
          <input type="hidden" class = "cannot_disable" name="id" value="{{isset($item) ? $item -> id : ''}}">
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
                  <div class="mb-1">
                     <label class="form-label">Remarks</label>
                     <textarea name="remarks" class="form-control cannot_disable"></textarea>
                  </div>
                  <div class="row">
                    <div class = "col-md-8">
                        <div class="mb-1">
                            <label class="form-label">Upload Document</label>
                            <input type="file" name = "attachment[]" multiple class="form-control cannot_disable" onchange = "addFiles(this, 'approval_files_preview');" max_file_count = "2"/>
                        </div>
                    </div>
                    <div class = "col-md-4" style = "margin-top:19px;">
                        <div class = "row" id = "approval_files_preview">

                        </div>
                    </div>
                  </div>
                  <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                  
               </div>
            </div>
         </div>
         <div class="modal-footer justify-content-center">  
            <button type="reset" class="btn btn-outline-secondary me-1" onclick="closeModal('approveModal')">Cancel</button> 
            <button type="submit" class="btn btn-primary">Submit</button>
         </div>
       </form>
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
              <p>Are you sure you want to <strong>Amend</strong> this <strong>{{request() -> type == "item" ? "Item" : "Item"}}</strong>?</p>
              <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
              <button type="button" data-bs-dismiss="modal" onclick = "amendConfirm();" class="btn btn-primary">Confirm</button>
          </div> 
      </div>
  </div>
</div>
<!-- END: Content-->
@endsection
@section('scripts')
<script>
    $(document).ready(function () {
        var units = @json($units);
        var purchasingUOMIds = []; 
        var selectedUOMIds = [];  
        var initialUOM = $("select[name='uom_id']").find(":selected").text().trim();
        var selectedUOMId = $("select[name='uom_id']").find(":selected").val();
        function applyCapsLock() {
            $('input[type="text"], input[type="number"]').each(function() {
                $(this).val($(this).val().toUpperCase());
            });
            $('input[type="text"], input[type="number"]').on('input', function() {
                var value = $(this).val().toUpperCase();  
                $(this).val(value); 
            });
        }
        $("select[name='uom_id']").on('change', function () {
            initialUOM = $(this).find(":selected").text().trim();
            selectedUOMId = $(this).find(":selected").val();
            updateSelectedTypes(initialUOM, selectedUOMId);
            disableSelectedUOMOptions();
        });

        var initialCostPrice = parseFloat($('input.cost-price-input').val()) || 0;
        $('input.cost-price-input').on('input', function () {
            initialCostPrice = parseFloat($(this).val()) || 0;
        });

        var initialSellPrice = parseFloat($('input.sell-price-input').val()) || 0;
        $('input.sell-price-input').on('input', function () {
            initialSellPrice = parseFloat($(this).val()) || 0;
        });

        $('#alternateUOMTable').on('input', 'input[name*="[conversion_to_inventory]"]', function () {
            var $row = $(this).closest('tr');
            var conversionFactor = parseFloat($row.find('input[name*="[conversion_to_inventory]"]').val()) || 1; 
            var updatedCostPrice = initialCostPrice * conversionFactor; 
            var updatedSellPrice = initialSellPrice * conversionFactor; 
            $row.find('input[name*="[cost_price]"]').val(updatedCostPrice.toFixed(2));
            $row.find('input[name*="[sell_price]"]').val(updatedSellPrice.toFixed(2));
        });

        function populateDropdown(selectElement) {
            var options = '<option value="">Select</option>';
            $.each(units, function (index, unit) {
                options += `<option value="${unit.id}">${unit.name}</option>`;
            });
            selectElement.html(options);
            disableSelectedUOMOptions();
        }

        function disableSelectedUOMOptions() {
            $('#alternateUOMTable tbody tr').each(function() {
                var $select = $(this).find('select[name*="[uom_id]"]');
                var selectedValue = $select.val();
                var $options = $select.find('option');
                $options.each(function() {
                    var optionValue = $(this).val();
                    if (selectedUOMIds.includes(optionValue) && optionValue !== selectedValue) {
                        $(this).prop('disabled', true);
                    } else {
                        $(this).prop('disabled', false);
                    }
                });
            });
        }
        function initializeSelectedUOMIds() {
            $('#alternateUOMTable tbody tr').each(function() {
                var uomId = $(this).find('select[name*="[uom_id]"]').val();
                if (uomId) {
                    selectedUOMIds.push(uomId);  
                }
            });
            if (selectedUOMId && !selectedUOMIds.includes(selectedUOMId)) {
                selectedUOMIds.push(selectedUOMId);
            }
            disableSelectedUOMOptions(); 
        }
        function updateRowIndices() {
            var $rows = $('#alternateUOMTable tbody tr');
            $('#alternateUOMTable tbody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);

                $(this).find('input, select').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                    }
                    var id = $(this).attr('id');
                    if (id) {
                        $(this).attr('id', id.replace(/\d+$/, index));
                    }
                });

                $(this).attr('id', 'row-' + index);
                if ($rows.length === 1) {
                    $(this).find('.delete-address').hide(); 
                    $(this).find('.add-address').show(); 
                } else {
                    $(this).find('.delete-address').show(); 
                    $(this).find('.add-address').toggle(index === 0); 
                }  
            });
        }

        function handleRadioSelection() {
            $('#alternateUOMTable').on('change', 'input[type="radio"][name*="[is_purchasing]"]', function () {
                $('#alternateUOMTable input[type="radio"][name*="[is_purchasing]"]').not(this).prop('checked', false);
                $(this).val('1');
                updateSelectedTypes(initialUOM, selectedUOMId);
            });

            $('#alternateUOMTable').on('change', 'input[type="radio"][name*="[is_selling]"]', function () {
                $('#alternateUOMTable input[type="radio"][name*="[is_selling]"]').not(this).prop('checked', false);
                $(this).val('1');
                updateSelectedTypes(initialUOM, selectedUOMId);
            });
        }

        $('#alternateUOMTable').on('change', 'select[name*="[uom_id]"], input[type="radio"][name*="[is_purchasing]"], input[type="radio"][name*="[is_selling]"]', function () {
            updateSelectedTypes(initialUOM, selectedUOMId);
            disableSelectedUOMOptions();
        });

        function updateSelectedTypes(initialUOM, selectedUOMId) {
            var selectedUOMIds = [];
            var selectedUOMTypes = [];
            var selectedValue = selectedUOMId;

            $('#alternateUOMTable tbody tr').each(function () {
                var $row = $(this);
                var uomId = $row.find('select[name*="[uom_id]"]').val();
                var uomName = $row.find('select[name*="[uom_id]"] option:selected').text();

                if (uomId && !selectedUOMIds.includes(uomId)) {
                    selectedUOMIds.push(uomId);
                }

                if (uomName && !selectedUOMTypes.some(type => type.unite === uomName)) {
                    if ($row.find('input[name*="[is_purchasing]"]:checked').val() === '1') {
                        selectedValue = uomId;
                    }
                    if ($row.find('input[name*="[is_selling]"]:checked').val() === '1') {
                        selectedValue = uomId;
                    }

                    selectedUOMTypes.push({
                        id: uomId || '',
                        unite: uomName,
                        purchasing: $row.find('input[name*="[is_purchasing]"]:checked').val() === '1' ? 'Purchasing' : '',
                        selling: $row.find('input[name*="[is_selling]"]:checked').val() === '1' ? 'Selling' : '',
                        initialUOM: initialUOM,
                        selectedUOMId: selectedUOMId,
                        selectedValue: selectedValue,
                    });
                }
            });

            if (selectedUOMId && !selectedUOMIds.includes(selectedUOMId)) {
                selectedUOMIds.push(selectedUOMId);
            }

            if (initialUOM && !selectedUOMTypes.some(type => type.unite === initialUOM)) {
                selectedUOMTypes.push({
                    id: selectedUOMId || '',
                    unite: initialUOM,
                    purchasing: '',
                    selling: ''
                });
            }

            $.ajax({
                url: "{{ route('send.uom') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    selectedUOMIds: selectedUOMIds,
                    selectedUOMTypes: selectedUOMTypes
                },
                success: function (response) {
                    $('#vendorTable tbody tr').each(function (index) {
                        var $row = $(this);
                        var $selectVendor = $row.find('select[name^="approved_vendor["]');
                        if ($selectVendor.length > 0) {
                            $selectVendor.empty();
                            $selectVendor.append('<option value="">Select UOM</option>');

                            selectedUOMTypes.forEach(function (uom) {
                                if (!uom.id) return;

                                var option = $('<option></option>')
                                    .val(uom.id)
                                    .text(uom.unite);

                                if (uom.purchasing === 'Purchasing') {
                                    option.prop('selected', true);
                                }

                                $selectVendor.append(option);
                            });
                        }
                    });

                    $('#customerTable tbody tr').each(function (index) {
                        var $row = $(this);
                        var $selectCustomer = $row.find('select[name^="approved_customer["]');
                        if ($selectCustomer.length > 0) {
                            $selectCustomer.empty();
                            $selectCustomer.append('<option value="">Select Customer</option>');

                            selectedUOMTypes.forEach(function (uom) {
                                if (!uom.id) return;

                                var option = $('<option></option>')
                                    .val(uom.id)
                                    .text(uom.unite);

                                if (uom.selling === 'Selling') {
                                    option.prop('selected', true); 
                                }

                                $selectCustomer.append(option);
                            });
                        }
                    });
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching UOM types:', xhr.responseText);
                }
            });
        }

        $('#alternateUOMTable').on('click', '.add-address', function (e) {
            e.preventDefault();
            var newRow = $('#alternateUOMTable tbody tr:first').clone();
            var rowCount = $('#alternateUOMTable tbody tr').length;

            newRow.find('td:first').text(rowCount + 1);
            newRow.attr('id', `row-${rowCount}`);
            newRow.find('input').val('');
            newRow.find('select').html('<option value="">Select</option>');
            newRow.find('input[type="radio"]').prop('checked', false);
            $('#alternateUOMTable tbody').append(newRow);
            populateDropdown(newRow.find('select'), '');
            updateRowIndices();
            handleRadioSelection();
            disableSelectedUOMOptions();
            feather.replace();
        });
        $('#alternateUOMTable').on('change', 'select[name*="[uom_id]"]', function () {
            var selectedValue = $(this).val();
            var uomId = $(this).attr('name').match(/\[\d+\]/)[0];
            selectedUOMIds = [];
            $('#alternateUOMTable tbody tr').each(function () {
                var groupId = $(this).find('select[name*="[uom_id]"]').val();
                if (groupId) {
                    selectedUOMIds.push(groupId);
                }
            });

            if (selectedUOMId && !selectedUOMIds.includes(selectedUOMId)) {
                selectedUOMIds.push(selectedUOMId);
            }
            disableSelectedUOMOptions();
        });
        $('#alternateUOMTable').on('click', '.delete-address', function(e) {
            e.preventDefault();
            var row = $(this).closest('tr');
            var rowId = row.find('input[name="alternate_uoms[' + row.index() + '][id]"]').val();
            if (rowId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Are you sure delete the record.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                        $.ajax({
                            url: '/items/alternate-uom/delete/' + rowId,
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                row.remove();
                                updateRowIndices();
                                Swal.fire('Deleted!', 'The record has been deleted.', 'success');
                                location.reload();
                            },
                            error: function() {
                                Swal.fire('Failed!', response.message, 'error');
                            }
                        });
                }
            });
        } else {
                row.remove();
                updateRowIndices();
                disableSelectedUOMOptions(); 
            }
        });
        updateRowIndices();
        handleRadioSelection();
        initializeSelectedUOMIds();

        var selectedVendorIds = @json($item->approvedVendors->pluck('vendor_id')->toArray());
        
        $('#vendorTableBody, #customerTableBody').on('input', '.cost-price-approved-vendor, .sell-price-approved-customer', function () {
            var rowId = $(this).closest('tr').attr('id').split('-')[1];
            var costPrice = $('#cost-price_' + rowId).val();
            var sellPrice = $('#sell-price_' + rowId).val();
            var uomField = $(this).closest('tr').find('select[name*="[uom_id]"]');
            
            if ((costPrice && !isNaN(costPrice)) || (sellPrice && !isNaN(sellPrice))) {
                uomField.prop('disabled', false); 
            } else {
                uomField.prop('disabled', true); 
            }
        });
        function initializeVendorAutocomplete(selector) {
            $(selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: "{{ url('/vendors/search') }}",
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: item.company_name + " (" + item.vendor_code + ")",
                                    vendor_code: item.vendor_code,  
                                    company_name: item.company_name,  
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching vendor data:', xhr.responseText);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    $(this).val(ui.item.company_name); 
                    var rowId = $(this).data('id');
                    $('#vendor-id_' + rowId).val(ui.item.id);
                    $('#item-code_' + rowId).val(ui.item.vendor_code);
                    return false;
                },
               }).on('input', function() {
                    var rowId = $(this).data('id');
                    if ($(this).val() === "") {
                        $('#vendor-id_' + rowId).val('');
                        $('#item-code_' + rowId).val('');
                    }
                }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
        }

        function updateVendorRowIndices() {
            var $rows = $('#vendorTable tbody tr'); 
            $('#vendorTable tbody tr').each(function (index) {
                $(this).find('td:first').text(index + 1);

                $(this).find('input, select').each(function () {
                    var name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/\[\d+\]/, `[${index}]`));
                    }

                    var id = $(this).attr('id');
                    if (id) {
                        $(this).attr('id', id.replace(/\d+$/, index));
                    }

                    var dataId = $(this).data('id');
                    if (dataId !== undefined) {
                        $(this).data('id', index);
                    }
                });
                $(this).attr('id', `row-${index}`);
                if ($rows.length === 1) {
                    $(this).find('.remove-row').hide(); 
                    $(this).find('.add-row').show(); 
                } else {
                    $(this).find('.remove-row').show(); 
                    $(this).find('.add-row').toggle(index === 0); 
                } 
            });
            initializeVendorAutocomplete(".vendor-autocomplete");
        }

        $('#vendorTable').on('click', '.add-row', function (e) {
            e.preventDefault();
            var newRow = $('#vendorTableBody tr:last').clone();
            var rowCount = $('#vendorTableBody tr').length;

            newRow.find('td:first').text(rowCount + 1); 
            newRow.attr('id', `row-${rowCount}`);
            newRow.find('input').val('');
            newRow.find('input, select').each(function () {
                var id = $(this).attr('id');
                if (id) {
                    $(this).attr('id', id.replace(/\d+$/, rowCount)); 
                }
                var dataId = $(this).data('id');
                if (dataId !== undefined) {
                    $(this).data('id', rowCount); 
                }
            });
            newRow.find('select').each(function() {
                var selectId = $(this).attr('id');
                if (selectId) {
                    $(this).attr('id', selectId.replace(/\d+$/, rowCount));
                }
                $(this).val(''); 
                $(this).prop('disabled', true);
            });

            $('#vendorTableBody').append(newRow);
            updateVendorRowIndices();
            feather.replace();
            applyCapsLock();
        });

        $('#vendorTable').on('click', '.remove-row', function(e) {
            e.preventDefault();
            var row = $(this).closest('tr');
            var rowId = row.find('input[name^="approved_vendor["][name$="[id]"]').val(); 
            var vendorRowId = $(this).closest('tr').find('input[data-id]').data('id');
            var vendorIdToRemove = $('#vendor-id_' + vendorRowId).val();
            if (vendorIdToRemove && selectedVendorIds.includes(parseInt(vendorIdToRemove))) {
                selectedVendorIds.splice(selectedVendorIds.indexOf(parseInt(vendorIdToRemove)), 1);
            }
         if (rowId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Are you sure delete the record?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                        $.ajax({
                            url: '/items/approved-vendor/delete/' + rowId, 
                            method: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(response) {
                                row.remove();
                                updateVendorRowIndices();
                                Swal.fire('Deleted!', response.message, 'success');
                                location.reload();
                            },
                            error: function() {
                                Swal.fire('Failed!', response.message, 'error');
                            }
                        });
                }
            });
        } else {
                row.remove();
                updateVendorRowIndices();
            }
        });

        $('#addVendor').on('click', function(e) {
            e.preventDefault();
            $('#vendorTable').find('.add-row').first().trigger('click');
        });

        var selectedCustomerIds = @json($item->approvedCustomers->pluck('customer_id')->toArray());

        function initializeCustomerAutocomplete(selector) {
            $(selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: "{{ url('/customers/search') }}",
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: item.company_name + " (" + item.customer_code + ")",
                                    customer_code: item.customer_code,
                                    company_name: item.company_name,
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
                    var rowId = $(this).data('id');
                    var currentCustomerId = $('#customer-id_' + rowId).val();
                    if (currentCustomerId) {
                        $('#customer-id_' + rowId).val('');
                        $('#customer-code_' + rowId).val('');
                    }
                    $(this).val(ui.item.company_name);
                    $('#customer-id_' + rowId).val(ui.item.id);
                    $('#customer-code_' + rowId).val(ui.item.customer_code);
                },
            }).on('input', function() {
                    var rowId = $(this).data('id');
                    if ($(this).val() === "") {
                        $('#customer-id_' + rowId).val('');
                        $('#customer-code_' + rowId).val('');
                    }
                })
           .focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
        }

        function updateCustomerRowIndices() {
            var $rows = $('#customerTable tbody tr');
            $('#customerTable tbody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
                $(this).find('input, select').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/\[\d+\]/, `[${index}]`));
                    }

                    var id = $(this).attr('id');
                    if (id) {
                        $(this).attr('id', id.replace(/\d+$/, index));
                    }

                    var dataId = $(this).data('id');
                    if (dataId !== undefined) {
                        $(this).data('id', index);
                    }
                });
                $(this).attr('id', `row-${index}`);

                if ($rows.length === 1) {
                    $(this).find('.remove-row').hide(); 
                    $(this).find('.add-row').show(); 
                } else {
                    $(this).find('.remove-row').show(); 
                    $(this).find('.add-row').toggle(index === 0); 
                } 
            });

            initializeCustomerAutocomplete(".customer-autocomplete");
        }

        $('#customerTable').on('click', '.add-row', function (e) {
            e.preventDefault();
            var newRow = $('#customerTableBody tr:last').clone();
            var rowCount = $('#customerTableBody tr').length;

            newRow.find('td:first').text(rowCount + 1);
            newRow.attr('id', `row-${rowCount}`);
            newRow.find('input').val('');
            newRow.find('input, select').each(function () {
                var id = $(this).attr('id');
                if (id) {
                    $(this).attr('id', id.replace(/\d+$/, rowCount));
                }
                var dataId = $(this).data('id');
                if (dataId !== undefined) {
                    $(this).data('id', rowCount);
                }
            });
            newRow.find('select').each(function() {
                var selectId = $(this).attr('id');
                if (selectId) {
                    $(this).attr('id', selectId.replace(/\d+$/, rowCount));
                }
                $(this).val('');
                $(this).prop('disabled', true);
            });

            $('#customerTableBody').append(newRow);
            updateCustomerRowIndices();
            feather.replace();
            applyCapsLock();
        });

        $('#customerTable').on('click', '.remove-row', function(e) {
            e.preventDefault();
            var row = $(this).closest('tr');
            var rowId = row.find('input[name^="approved_customer["][name$="[id]"]').val();
            var customerRowId = $(this).closest('tr').find('input[data-id]').data('id');
            var customerIdToRemove = $('#customer-id_' + customerRowId).val();
            if (customerIdToRemove && selectedCustomerIds.includes(parseInt(customerIdToRemove))) {
                selectedCustomerIds.splice(selectedCustomerIds.indexOf(parseInt(customerIdToRemove)), 1);
            }
            if (rowId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Are you sure delete the record?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, keep it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/items/approved-customer/delete/' + rowId,
                            method: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(response) {
                                row.remove();
                                updateCustomerRowIndices();
                                Swal.fire('Deleted!', response.message, 'success');
                                location.reload();
                            },
                            error: function() {
                                Swal.fire('Failed!', response.message, 'error');
                            }
                        });
                    }
                });
            } else {
                row.remove();
                updateCustomerRowIndices();
            }
        });

        $('#addCustomer').on('click', function(e) {
            e.preventDefault();
            $('#customerTable').find('.add-row').first().trigger('click');
        });

        initializeCustomerAutocomplete(".customer-autocomplete");
        updateCustomerRowIndices();
        initializeVendorAutocomplete(".vendor-autocomplete");
        updateVendorRowIndices();

    });
</script>

<script>
    $(document).ready(function () {
        feather.replace();
        let isBomExists = @json($isBomExists);
        if (isBomExists) {
            $('#attributesTable .add-row, #attributesTable .remove-row').css({
                'pointer-events': 'none',
                'opacity': '0.5',
                'cursor': 'not-allowed'
            });
        }
        let rowIndex = $('#attributesTable tbody tr').length + 1;
        const attributeGroups = @json($attributeGroups);
        const attributesMap = {};
        attributeGroups.forEach(group => attributesMap[group.id] = []);
        let selectedGroupIds = [];
       function bindCheckboxEvents($row) {
            const $checkbox = $row.find('.all-checked');
            const $select = $row.find('.attribute-values');
            const $visibleSelect = $row.find('.attribute-value'); 
            const $hiddenSelect = $row.find('.attribute-values-hidden'); 
            const isChecked = $checkbox.prop('checked');
            $checkbox.val(isChecked ? '1' : '0');
            $select.prop('disabled', isChecked);

            if (isChecked) {
                $visibleSelect.hide();
                $hiddenSelect.show();
            } else {
                $visibleSelect.show();
                $hiddenSelect.hide();
            }
            $checkbox.off('change').on('change', function () {
                const isChecked = $(this).prop('checked');
                $(this).val(isChecked ? '1' : '0'); 
                $select.prop('disabled', isChecked); 
                if (isChecked) {
                    $visibleSelect.hide();
                    $hiddenSelect.show();
                    $select.val(['1']).trigger('change'); 
                } else {
                    $visibleSelect.show();
                    $hiddenSelect.hide();
                    $select.val([]).trigger('change'); 
                }
            });
        }
        function populateOptions(selectElement, options, defaultOption, textField, valueField) {
            selectElement.empty().append(new Option(defaultOption.text, defaultOption.value));
            options.forEach(option => {
                selectElement.append(new Option(option[textField], option[valueField]));
            });
            selectElement.trigger('change');
        }
        function disableSelectedOptions() {
            $('select[name^="attributes"][name$="[attribute_group_id]"]').each(function () {
                const selectedGroupId = $(this).val();
                const $options = $(this).find('option');
                $options.each(function () {
                    const optionValue = $(this).val();
                    $(this).prop('disabled', selectedGroupIds.includes(optionValue) && optionValue !== selectedGroupId);
                });
            });
        }

        function addRow(isDefault) {
            const actionIcon = isDefault
                ? `<a href="#" class="text-primary add-row"><i data-feather='plus-square'></i></a>`
                : `<a href="#" class="text-danger remove-row"><i data-feather='trash-2'></i></a>`;

            const newRow = `
                <tr>
                    <td>${rowIndex}</td>
                    <td><select name="attributes[${rowIndex}][attribute_group_id]" class="form-select mw-100 select2 attribute-group"></select></td>
                    <td>
                        <div class="d-flex gap-2 align-items-center">
                            <select name="attributes[${rowIndex}][attribute_id][]" class="form-select mw-100 select2 attribute-values" multiple></select>
                            <div class="form-check form-check-primary mt-25 custom-checkbox">
                                <input type="checkbox" class="form-check-input all-checked" name="attributes[${rowIndex}][all_checked]" value="0" id="allChecked-${rowIndex}">
                                <label class="form-check-label" for="allChecked-${rowIndex}">All</label>
                            </div>
                        </div>
                    </td>
                    <td>${actionIcon}</td>
                </tr>`;

            const $newRow = $(newRow);
            bindCheckboxEvents($newRow);
            const $attributeGroupSelect = $newRow.find('.attribute-group');
            const $attributeValuesSelect = $newRow.find('.attribute-values');

            $attributeGroupSelect.select2();
            $attributeValuesSelect.select2();
            populateOptions($attributeGroupSelect, attributeGroups, { text: 'Select', value: '' }, 'name', 'id');
            $('#attributesTable tbody').append($newRow);
       
            rowIndex++;
            disableSelectedOptions();

            $attributeGroupSelect.on('change', function () {
                const selectedValue = $(this).val();
                if (selectedValue && !selectedGroupIds.includes(selectedValue)) {
                    selectedGroupIds.push(selectedValue);
                }
                disableSelectedOptions();
                updateAttributeValues($attributeValuesSelect, selectedValue);
            });
        }
        function updateIcons() {
            $('#attributesTable tbody tr').each(function (index) {
                const $actionCell = $(this).find('td:last-child');
                if ($('#attributesTable tbody tr').length === 1) {
                    $actionCell.html('<a href="#" class="text-primary add-row"><i data-feather="plus-square"></i></a>');
                } else {
                    $actionCell.html(index === 0
                        ? '<a href="#" class="text-primary add-row"><i data-feather="plus-square"></a>'
                        : '<a href="#" class="text-danger remove-row"><i data-feather="trash-2"></i></a>'
                    );
                }
            });
            feather.replace();
        }

        function updateRowNumbers() {
            $('#attributesTable tbody tr').each(function (index) {
                $(this).find('td:first').text(index + 1);
                $(this).find('select[name^="attributes["][name$="[attribute_group_id]"]').attr('name', `attributes[${index}][attribute_group_id]`);
                $(this).find('select[name^="attributes["][name$="[attribute_id][]"]').attr('name', `attributes[${index}][attribute_id][]`);
                $(this).find('input[id^="BOMreq-"]').attr('id', `BOMreq-${index}`);
                $(this).find('label[for^="BOMreq-"]').attr('for', `BOMreq-${index}`);
            });
            rowIndex = $('#attributesTable tbody tr').length + 1;
        }

        function updateAttributeValues($select, groupId) {
            $select.empty().append(new Option('Select', ''));
            if (groupId && !attributesMap[groupId].length) {
                $.ajax({
                    url: `{{ url('/attributes') }}/${groupId}`,
                    method: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        if (Array.isArray(data)) {
                            attributesMap[groupId] = data;
                            populateOptions($select, data, { text: 'Select', value: '' }, 'value', 'id');
                            $select.find('option[value=""]').prop('disabled', true);
                        } else {
                            console.error('Unexpected response format:', data);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error fetching attributes:', error);
                    }
                });
            } else {
                populateOptions($select, attributesMap[groupId], { text: 'Select', value: '' }, 'value', 'id');
            }
        }

        $('#attributesTable').on('click', '.add-row', function (e) {
            e.preventDefault();
            addRow(false);
            updateIcons();
        });

        $('#attributesTable').on('click', '.remove-row', function (e) {
            e.preventDefault();
            const row = $(this).closest('tr');
            const groupId = row.find('.attribute-group').val();
            const groupIndex = selectedGroupIds.indexOf(groupId);
            if (groupIndex !== -1) {
                selectedGroupIds.splice(groupIndex, 1);
            }
            const attributeId = row.find('input[name^="attributes["][name$="[id]"]').val();
            if (attributeId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Are you sure you want to delete the record?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, keep it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/items/attribute/delete/${attributeId}`,
                            type: 'DELETE',
                            success: function (response) {
                                row.remove();
                                updateRowNumbers();
                                disableSelectedOptions();
                                updateIcons();
                                Swal.fire('Deleted!', response.message, 'success');
                                location.reload();
                            },
                            error: function (xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message || 'An error occurred while deleting the record.', 'error');
                            }
                        });
                    }
                });
            } else {
                row.remove();
                updateRowNumbers();
                disableSelectedOptions();
                updateIcons();
            }
        });
        function updateSelectedGroupIds() {
            const uniqueGroupIds = new Set();
            $('#attributesTable tbody tr').each(function () {
                const groupId = $(this).find('.attribute-group').val();
                if (groupId) {
                    uniqueGroupIds.add(groupId);
                }
            });
            selectedGroupIds = Array.from(uniqueGroupIds);
            disableSelectedOptions();
        }

        $('#attributesTable').on('change', '.attribute-group', function () {
            updateSelectedGroupIds();
            const selectedGroupId = $(this).val();
            const $valuesSelect = $(this).closest('tr').find('.attribute-values');
            updateAttributeValues($valuesSelect, selectedGroupId);
        });

        // Initialize rows
        $('#attributesTable tbody tr').each(function (index) {
            const $row = $(this);
            const attributeGroupId = $row.find('.attribute-group').data('value');
            const attributeIds = $row.find('.attribute-values').data('value');
            const isAllChecked = $row.find('.all-checked').prop('checked');
            const $visibleSelect = $row.find('.attribute-value');
            const $hiddenSelect = $row.find('.attribute-values-hidden');
            $row.attr('data-row-index', index);
            if (attributeGroupId) {
                $row.find('.attribute-group').val(attributeGroupId).trigger('change');
                updateAttributeValues($row.find('.attribute-values'), attributeGroupId);
                if (attributeIds) $row.find('.attribute-values').val(attributeIds.split(',')).trigger('change');
            }
            if (isAllChecked) {
                $visibleSelect.prop('disabled', true).hide();
                $hiddenSelect.prop('disabled', false).show();
            } else {
                $visibleSelect.prop('disabled', false).show();
                $hiddenSelect.prop('disabled', true).hide();
            }
            bindCheckboxEvents($row);

            const groupId = $row.find('.attribute-group').val();
            if (groupId && !selectedGroupIds.includes(groupId)) {
                selectedGroupIds.push(groupId);
            }
            const $select = $row.find('.attribute-values');
            const selectedAttributeIds = $select.val() || [];
            $select.find('option').each(function () {
                const optionVal = $(this).val();
                if (selectedAttributeIds.includes(optionVal)) {
                    $(this).attr('data-readonly', 'true');
                    $(this).css('color', 'gray');
                    $(this).prop('selected', true);
                } else {
                    $(this).css('color', '');
                }
            });
            $select.select2();
        });
        if ($('#attributesTable tbody tr').length === 0) {
            addRow(true);
        }
        updateIcons();
        disableSelectedOptions();
    });
</script>

<script>
    $(document).ready(function() {
        var itemCounter = $('#itemTable tbody tr').length + 1;
          const itemId = "{{ isset($item) ? $item->id : null }}"; 
        var addedItems = {};
        function initializeItemAutocomplete(selector) {
            $(selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url:"{{ url('/items/search') }}",
                        method: 'GET',
                        dataType: 'json',
                        data: {
                             term: request.term,
                             exclude_id: itemId
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: item.label,
                                    value: item.value, 
                                    code: item.code || '', 
                                    item_id: item.id
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching item data:', xhr.responseText);
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
                    $input.val(itemName);
                    $('#itemId').val(itemId);
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $('#itemId').val('');
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
        $('#addNewItem').click(function(e) {
            e.preventDefault();
            var $input = $('.item-autocomplete');
            var itemCode = $input.attr('data-code');
            var itemName = $input.attr('data-name');
            var itemId = $('#itemId').val();

            if (itemId && itemCode && itemName) {
                var itemAlreadyAdded = false;
                $('#itemTable tbody tr').each(function() {
                    var existingItemCode = $(this).find('input[name$="[item_code]"]').val();
                    var existingItemName = $(this).find('td').eq(2).text();
                    if (existingItemCode === itemCode || existingItemName === itemName) {
                        itemAlreadyAdded = true;
                        return false;
                    }
                });

                if (itemAlreadyAdded) {
                    alert('This item is already added to the table.');
                    return;
                }

                $('#itemTable tbody').find('tr').each(function() {
                    if ($(this).find('td').eq(0).text().trim() === 'No alternate items found.') {
                        $(this).remove(); 
                    }
                });

                var newRow = '<tr>' +
                    '<td>' + itemCounter + '</td>' +
                    '<td>' + itemCode + '</td>' +
                    '<td>' + itemName + '</td>' +
                    '<input type="hidden" name="alternateItems[' + (itemCounter - 1) + '][item_code]" value="' + itemCode + '" />' +
                    '<input type="hidden" name="alternateItems[' + (itemCounter - 1) + '][item_name]" value="' + itemName + '" />' +
                    '<input type="hidden" name="alternateItems[' + (itemCounter - 1) + '][alt_item_id]" value="' + itemId + '" />' +
                    '<td><a href="#" class="text-danger remove-item"><i data-feather="trash-2" class="me-50"></i></a></td>' +
                    '</tr>';

                $('#itemTable tbody').append(newRow);
                addedItems[itemCode] = true;
                itemCounter++;
                $input.val('').attr('data-code', '').attr('data-name', '');
                $('#itemId').val(''); 
                feather.replace();
                updateRowNumbers();
            } else {
                alert('Please select an item from the list.');
            }
           
        });
        $('#itemTable').on('click', '.remove-item', function(e) {
            e.preventDefault();
            var row = $(this).closest('tr'); 
            var itemCode = row.find('input[name^="alternateItem["][name$="[item_code]"]').val();
            var itemId = row.data('id');
            if (itemId) { 
            Swal.fire({
                title: 'Are you sure?',
                text: 'Are you sure you want to delete this record?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                        $.ajax({
                            url: `/items/alternate-item/delete/${itemId}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                row.remove(); 
                                delete addedItems[itemCode]; 
                                updateRowNumbers();
                                Swal.fire('Deleted!', response.message, 'success');
                                location.reload();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', response.message, 'error'); 
                            }
                        });
                }
            });
        } else {
                row.remove();
                updateRowNumbers();
            }
        });
        function updateRowNumbers() {
            if ($('#itemTable tbody tr').length === 0) {
                $('#itemTable tbody').html('<tr><td colspan="4" class="text-center">No alternate items found.</td></tr>');
            } else {
                $('#itemTable tbody tr').each(function(index) {
                    $(this).find('td').eq(0).text(index + 1);
                    $(this).find('input[name^="alternateItems["][name$="[item_code]"]').attr('name', 'alternateItems[' + index + '][item_code]');
                    $(this).find('input[name^="alternateItems["][name$="[item_name]"]').attr('name', 'alternateItems[' + index + '][item_name]');
                    $(this).find('input[name^="alternateItems["][name$="[alt_item_id]"]').attr('name', 'alternateItems[' + index + '][alt_item_id]');
                });
            }
            itemCounter = $('#itemTable tbody tr').length + 1;
        }
        initializeItemAutocomplete(".item-autocomplete");
        updateRowNumbers();
        
    });
</script>

<script>
    $(document).ready(function() {
        const checkboxes = $('.subTypeCheckbox');
        const typeRadios = $('input[name="type"]');
        var isEditable = @json($isEditable);
        var isItemReferenced= @json($isItemReferenced);
        
        function updateCheckboxStatesForGoods() {
            const rawMaterialChecked = $('#subType1').is(':checked');
            const wipChecked = $('#subType2').is(':checked');
            const finishedGoodsChecked = $('#subType3').is(':checked');
            const assetChecked = $('#subType5').is(':checked');
            const expenseChecked = $('#subType6').is(':checked');
            const rawTradeChecked = $('#subType4').is(':checked');
            const scrapChecked = $('input[name="is_scrap"]').is(':checked'); 
            $('#subType2').prop('disabled', rawMaterialChecked || finishedGoodsChecked || rawTradeChecked);
            $('#subType3').prop('disabled', rawMaterialChecked || wipChecked || rawTradeChecked);
            $('#subType1').prop('disabled', wipChecked || finishedGoodsChecked || rawTradeChecked);
            $('#subType5').prop('disabled', expenseChecked || rawMaterialChecked || rawTradeChecked);
            $('#subType6').prop('disabled', assetChecked || rawMaterialChecked || rawTradeChecked);
            $('#subType4').prop('disabled', rawMaterialChecked || wipChecked || finishedGoodsChecked || assetChecked || expenseChecked);
             if (scrapChecked) {
                $('.subTypeCheckbox').not('input[name="is_scrap"]').prop('disabled', true).prop('checked', false);
                $('input[name="is_scrap"]').prop('disabled', false);
            } else if (rawMaterialChecked || wipChecked || finishedGoodsChecked || assetChecked || expenseChecked || rawTradeChecked) {
                $('input[name="is_scrap"]').prop('disabled', true);
                checkboxes.not(':checked')
                    .not('input[name="is_traded_item"], input[name="is_asset"]')
                    .prop('disabled', true);
            } else {
                checkboxes.prop('disabled', false);
                $('input[name="is_scrap"]').prop('disabled', false);
            }
           const status = document.getElementById('documentStatus')?.value;
            if (isItemReferenced) {
                checkboxes.prop('disabled', true);
                $('input[name="is_traded_item"], input[name="is_asset"], input[name="is_scrap"]').prop('disabled', true);
            }
            $('a[href="#UOM"]').removeClass('d-none').css('display', '');
            $('a[href="#Details"]').removeClass('d-none').css('display', '');
            $('a[href="#Attributes"]').removeClass('d-none').css('display', '');
            $('#UOM').removeClass('d-none').css('display', '');
            $('#Details').removeClass('d-none').css('display', '');
            $('#Attributes').removeClass('d-none').css('display', '');
        }

        function updateCheckboxStatesForService() {
            checkboxes.prop('disabled', true); 
            
            $('input[name="is_traded_item"]').prop('checked', false).prop('disabled', true);
            $('input[name="is_asset"]').prop('checked', false).prop('disabled', true);
            $('input[name="is_scrap"]').prop('checked', false).prop('disabled', true);
            $('a[href="#UOM"]').addClass('d-none');
            $('a[href="#Details"]').addClass('d-none');
            $('a[href="#Assets"]').addClass('d-none');
            $('a[href="#Attributes"]').addClass('d-none');
            $('#UOM').addClass('d-none');
            $('#Details').addClass('d-none');
            $('#Attributes').addClass('d-none');
            $('#Assets').addClass('d-none');
            ['#UOM', '#Details', '#Attributes','#Assets'].forEach(function (selector) {
                $(selector).addClass('d-none').find('input, select, textarea').each(function () {
                    const $input = $(this);
                    $input.is(':checkbox') || $input.is(':radio') 
                        ? $input.prop('checked', false) : $input.val('');
                });
            });
            $('#item_code_label').text('Service Code');
            $('#item_name_label').text('Service Type');
            $('#item_initial_label').text('Service Initial');
            $('input[name="service_type"]').prop('checked', false);
            $('input[name="service_type"][value="non-stock"]').prop('checked', true);
            $('input[name="service_type"][value="stock"]').prop('disabled', true); 
        }
        function handleCheckboxChange() {
            const selectedType = typeRadios.filter(':checked').val();
            if (selectedType === 'Goods') {
                $('#item_code_label').text('Item Code');
                $('#item_name_label').text('Item Name');
                $('input[name="service_type"][value="non-stock"]').prop('checked', false);
                $('input[name="service_type"][value="stock"]').prop('disabled', false); 
                updateCheckboxStatesForGoods();
            } else if (selectedType === 'Service') {
                $('#item_code_label').text('Service Code');
                $('#item_name_label').text('Service Type');
                $('#item_initial_label').text('Service Initial');
                $('input[name="service_type"]').prop('checked', false);
                $('input[name="service_type"][value="non-stock"]').prop('checked', true);
                $('input[name="service_type"][value="stock"]').prop('disabled', true); 
                updateCheckboxStatesForService();
            }
        }

        typeRadios.change(function() {
            checkboxes.prop('checked', false); 
           if (isItemReferenced) {
                checkboxes.prop('disabled', true);
                $('input[name="is_traded_item"]').prop('disabled', true);
                $('input[name="is_asset"]').prop('disabled', true);
            } else {
                checkboxes.prop('disabled', false); 
                $('input[name="is_traded_item"]').prop('disabled', false);
                $('input[name="is_asset"]').prop('disabled', false);
            }
            const selectedType = $(this).val();
            if (selectedType === 'Goods') {
                $('#item_code_label').text('Item Code');
                $('#item_name_label').text('Item Name');
                $('input[name="service_type"][value="non-stock"]').prop('checked', false);
                $('input[name="service_type"][value="stock"]').prop('disabled', false); 
                updateCheckboxStatesForGoods();
            } else if (selectedType === 'Service') {
                $('#item_code_label').text('Service Code');
                $('#item_name_label').text('Service Type');
                $('#item_initial_label').text('Service Initial');
                $('input[name="service_type"]').prop('checked', false);
                $('input[name="service_type"][value="non-stock"]').prop('checked', true);
                $('input[name="service_type"][value="stock"]').prop('disabled', true); 
                updateCheckboxStatesForService();
            }
        });

        checkboxes.change(function() {
            if ($(this).is(':checked') && !$(this).is('input[name="is_traded_item"], input[name="is_asset"]')) {
                checkboxes.not(this)
                    .not('input[name="is_traded_item"], input[name="is_asset"]')
                    .prop('checked', false);
            }
            handleCheckboxChange();
        });
        handleCheckboxChange(); 
    });
</script>

<script>
    $(document).ready(function() {
        let specificationsAdded = {}; 
        $('#groupSelect').on('change', function() {
            const groupId = $(this).val();  
            $('#hiddenGroupId').val(groupId); 

            if (groupId) {
                fetchSpecificationsForGroup(groupId);
            } else {
                $('#specificationContainer').empty();
                $('#hiddenGroupId').val('');
            }
        });
        function fetchSpecificationsForGroup(groupId) {
            $('#specificationContainer').empty(); 
            if (specificationsAdded[groupId]) {
                displaySpecifications(specificationsAdded[groupId], groupId);
            } else {
                $.ajax({
                    url: `/product-specifications/specifications/${groupId}`,
                    method: 'GET',
                    success: function(data) {
                        if (data.specifications.length === 0) {
                            $('#specificationContainer').html('<div class="text-center">No specifications found for this group.</div>');
                            return;
                        }
                        specificationsAdded[groupId] = data.specifications;
                        displaySpecifications(data.specifications, groupId);
                    },
                    error: function(xhr) {
                        console.error('Error fetching specifications:', xhr.responseText);
                    }
                });
            }
        }
        function displaySpecifications(specifications, groupId) {
            const container = $('#specificationContainer');
            
            specifications.forEach((spec, index) => {
                const row = `
                    <div class="row mb-3" data-specification-id="${spec.id}">
                        <div class="col-md-2">
                            <input type="hidden" name="item_specifications[${index}][group_id]" value="${groupId}">
                            <input type="hidden" name="item_specifications[${index}][specification_id]" value="${spec.id}">
                            <input type="hidden" name="item_specifications[${index}][specification_name]" value="${spec.name}">
                            <label class="form-label">${spec.name}</label>
                        </div>
                        <div class="col-md-4">
                            <input type="text" id="spec_${index}" class="form-control" 
                                name="item_specifications[${index}][value]" 
                                placeholder="Enter value" 
                                data-specification-id="${spec.id}">
                        </div>
                    </div>
                `;
                container.append(row);
            });
        }
    });
</script>

<script>
    $(document).ready(function() {
        const itemCodeType = '{{ $itemCodeType }}';
        const itemNameInput = $('input[name="item_name"]');
        const itemNamee = itemNameInput.val().trim();
        const catInitialsInput = $('input[name="cat_initials"]');
        const itemInitialInput = $('input[name="item_initial"]');
        const subCatInitialsInput = $('input[name="cat_initials"]');
        const subTypeCheckboxes = $('.subTypeCheckbox');
        const itemCodeInput = $('input[name="item_code"]');
        const itemIdInput = $('input[name="item_id"]');
        const typeRadios = $('input[name="type"]');
        const isEditable = {{ isset($item) && $item->status === 'draft' ? 'true' : 'false' }};
        var isItemReferenced= @json($isItemReferenced);
        if (itemCodeType === 'Manual' && isEditable) {
            itemCodeInput.prop('readonly', false); 
        } else {
            itemCodeInput.prop('readonly', true); 
        }
        function getSelectedSubTypeSuffix() {
                let selectedSubTypes = [];
                let hasRawMaterial = false;
                let hasFinishedGoods = false;
                let hasWIP = false;
                let hasExpense = false;
                let hasAsset = $('#assetCheckbox').is(':checked');
                let hasTradedItem = $('#tradedItemCheckbox').is(':checked');
                let hasScrap = $('#scrapCheckbox').is(':checked');
                subTypeCheckboxes.each(function() {
                    if ($(this).is(':checked')) {
                        const label = $(this).next().text().trim();
                        selectedSubTypes.push(label);
                        if (label === 'Raw Material') hasRawMaterial = true;
                        if (label === 'Finished Goods') hasFinishedGoods = true;
                        if (label === 'WIP/Semi Finished') hasWIP = true;
                        if (label === 'Expense') hasExpense = true;
                    }
                });

                if (hasRawMaterial) return 'RM';
                if (hasFinishedGoods) return 'FG';
                if (hasWIP) return 'SF';
                if (hasExpense) return 'EX';
                if (hasScrap) return 'SC'; 
                if (hasAsset && hasTradedItem && !hasRawMaterial && !hasFinishedGoods && !hasWIP && !hasExpense) return 'AS';
                if (hasAsset && !hasRawMaterial && !hasFinishedGoods && !hasWIP && !hasExpense) return 'AS';
                if (hasTradedItem && !hasRawMaterial && !hasFinishedGoods && !hasWIP && !hasExpense) return 'TR';
            return '';
        }

        function getItemInitials(itemName) {
            const cleanedItemName = itemName.replace(/[^a-zA-Z0-9\s]/g, '');
            const words = cleanedItemName.split(/\s+/).filter(word => word.length > 0);
            let initials = '';
            if (words.length === 1) {
                initials = itemName.substring(0, 3).toUpperCase();
            } else if (words.length === 2) {
                initials = words[0].substring(0, 2).toUpperCase() + words[1][0].toUpperCase();
            } else if (words.length >= 3) {
                initials = words[0][0].toUpperCase() + words[1][0].toUpperCase() + words[2][0].toUpperCase();
            }
            return initials.substring(0, 3);
        }
        function generateItemCode() {
            if (isItemReferenced || itemCodeType === 'Manual') {
                return;
            }
            const itemName = itemNameInput.val().trim();
            const manualItemInitials = itemInitialInput.val().trim();
            const autoItemInitials = getItemInitials(itemName);
            const itemInitials = manualItemInitials || autoItemInitials;
            itemInitialInput.val(itemInitials);
            const subTypeSuffix = getSelectedSubTypeSuffix();
            const catInitials = (catInitialsInput.val() || '').trim();
            const subCatInitials = (subCatInitialsInput.val() || '').trim();
            const selectedType = typeRadios.filter(':checked').val();
            let prefix = '';

            if (selectedType === 'Service') {
                prefix = 'SR';
            }

            $.ajax({
                url: '{{ route('generate-item-code') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    cat_initials: catInitials,
                    sub_cat_initials: subCatInitials,
                    sub_type: subTypeSuffix,
                    item_initials: itemInitials,
                    item_id: itemIdInput.val(),
                    prefix: prefix
                },
                success: function(response) {
                    itemCodeInput.val((response.item_code || ''));
                },
                error: function() {
                    itemCodeInput.val('');
                }
            });
        }

        typeRadios.change(function() {
            generateItemCode();
        });

        itemInitialInput.on('input', function() {
            let value = $(this).val().toUpperCase();
            if (value.length > 3) {
                value = value.substring(0, 3);
            }
            $(this).val(value);
            if (value.length > 0 && itemCodeType === 'Auto') {
                generateItemCode();
            }
        });

        itemNameInput.on('input change', function() {
            const inputField = $(this);
            const currentCursorPos = inputField[0].selectionStart; 
            const itemName = $(this).val().trim();
            const cleanedItemName = itemName.replace(/[^a-zA-Z0-9\s]/g, '');
            const words = cleanedItemName.split(/\s+/).filter(word => word.length > 0);
            let initials = '';
            if (words.length === 1) {
                initials = itemName.substring(0, 3).toUpperCase();
            } else if (words.length === 2) {
                initials = words[0].substring(0, 2).toUpperCase() + words[1][0].toUpperCase();
            } else if (words.length >= 3) {
                initials = words[0][0].toUpperCase() + words[1][0].toUpperCase() + words[2][0].toUpperCase();
            }

            itemInitialInput.val(initials.substring(0, 3));
            
            requestAnimationFrame(function() {
                inputField[0].setSelectionRange(currentCursorPos, currentCursorPos);
            });

            if (itemCodeType === 'Auto') {
                generateItemCode();
            }
        });

        itemCodeInput.on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });

        subTypeCheckboxes.on('change', generateItemCode);
        catInitialsInput.on('change', generateItemCode);
        subCatInitialsInput.on('change', generateItemCode);
        if (itemNamee.length > 0) {
            const itemInitials = getItemInitials(itemNamee);
            itemInitialInput.val(itemInitials); 
        }
    });
</script>
<script>
    // storage-uom-start
    $(document).ready(function () {
        function syncStorageFields() {
            const uomName = $('select[name="uom_id"] option:selected').text().trim().toUpperCase();
            const storageUomName = $('select[name="storage_uom_id"] option:selected').text().trim().toUpperCase();
            const storageUomValue = $('select[name="storage_uom_id"]').val();
            const $conversionInput = $('input[name="storage_uom_conversion"]');
            const $countInput = $('input[name="storage_uom_count"]');

            if (storageUomValue) {
                if (uomName === storageUomName) {
                    $conversionInput.val(1);   
                    $conversionInput.prop('readonly', true);
                    $countInput.prop('readonly', false);
                } else {
                    $conversionInput.prop('readonly', false);
                    $countInput.val(1);  
                    $countInput.prop('readonly', true);
                }
                if (!$conversionInput.val()) $conversionInput.val(1);
                if (!$countInput.val()) $countInput.val(1);

            } else {
                $conversionInput.val('');
                $countInput.val('');
                $conversionInput.prop('readonly', false);
                $countInput.prop('readonly', false);
            }
        }
        syncStorageFields();
        $('select[name="uom_id"], select[name="storage_uom_id"]').on('change', syncStorageFields);
    });
    
    // storage-uom-end

    //CapsLock-start
    $(document).ready(function() {
        function applyCapsLock() {
            $('input[type="text"], input[type="number"]').each(function() {
                $(this).val($(this).val().toUpperCase());
            });
            $('input[type="text"], input[type="number"]').on('input', function() {
                var value = $(this).val().toUpperCase();  
                $(this).val(value); 
            });
        }
        applyCapsLock();
    });
   //CapsLock-end

  // asset-checkbox
   $(document).ready(function () {
       function toggleAssetTab() {
            if ($('#assetCheckbox').is(':checked')) {
                $('#assetTabLink').removeClass('d-none').show();
                $('.nav-link').removeClass('active'); 
                $('.tab-pane').removeClass('active show'); 
                $('#assetTabLink').addClass('active');
                $('#Assets').addClass('active show').removeClass('d-none').show();
            } else {
                $('#asset_category').val('').trigger('change');
                $('#expected_life').val('');
                $('select[name="maintenance_schedule"]').val('').trigger('change');
                $('input[name="brand_name"]').val('');
                $('input[name="model_no"]').val('');
                $('#assetTabLink').removeClass('active');
                $('#Assets').removeClass('active show');
                $('#assetTabLink').hide();
            }
        }
        toggleAssetTab();

        $('#assetCheckbox').change(function () {
            toggleAssetTab();
        });
        $('#asset_category').change(function() {
            var categoryId = $(this).val();
            $.ajax({
                url: '/items/get-asset-data/' + encodeURIComponent(categoryId),
                method: 'GET',
                success: function(data) {
                    $('#expected_life').val(data.expected_life_years);
                    $('select[name="maintenance_schedule"]').val(data.maintenance_schedule).trigger('change');
                }
            });
        });
    });

    //asset-checkbox-end

    //inspection
    $(document).ready(function() {
        function toggleInspectionChecklist() {
            if ($('#is_inspection').val() == '1') {
                $('#inspectionCheckContainer').show();
            } else {
                $('#inspectionCheckContainer').hide();
                $('.inspection-autocomplete').val('');
                $('.inspection_checklist_id').val('');
            }
        }
        function toggleShelfLife() {
            if ($('#ExpiryCheck').is(':checked')) {
                $('#shelfLifeContainer').show();
            } else {
                $('#shelfLifeContainer').hide();
                $('input[name="shelf_life_days"]').val('');
            }
        }
        toggleInspectionChecklist();
        toggleShelfLife();
        $('#is_inspection').change(function() {
            toggleInspectionChecklist();
        });
        $('#ExpiryCheck').change(function() {
            toggleShelfLife();
        });
    });

     //inspection-end
     var currentRevNo = $("#revisionNumber").val();
     $(document).on('change', '#revisionNumber', function (e) {
        e.preventDefault();
        const selectedRev = e.target.value;
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('revisionNumber', selectedRev);
        $("#revisionNumber").val(currentRevNo);
        window.open(currentUrl.toString(), '_blank');
    });
     //approval-start
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
        data.append('status', $('#status_hidden_input').val());
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
    function setApproval()
    {
        document.getElementById('action_type').value = "approve";
        document.getElementById('approve_reject_heading_label').textContent = "Approve Item";

    }
    function setReject()
    {
        document.getElementById('action_type').value = "reject";
        document.getElementById('approve_reject_heading_label').textContent = "Reject Item";
    }
    function revokeDocument() {
    const itemId = "{{ isset($item) ? $item->id : null }}"; 
        if (itemId) {
            $.ajax({
                url: "{{ route('item.revoke') }}", 
                method: 'POST',
                dataType: 'json',
                data: {
                    id: itemId 
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
                        window.location.href = "{{ route('item.index') }}"; 
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching item data:', xhr.responseText);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Some internal error occured',
                        icon: 'error',
                    });
                }
            });
        }
    }
    function closeModal(id)
    {
        $('#' + id).modal('hide');
    }
    function openModal(id)
    {
        $('#' + id).modal('show');
    }
    function disableAllFieldsAndTabs() {
        document.querySelectorAll('input, select, textarea').forEach(el => {
            if (el.classList.contains('cannot_disable')) {
                return;
            }
            el.disabled = true;
        });
        $('#attributesTable tbody tr').each(function () {
            const $row = $(this);
            const $select = $row.find('.attribute-values');
            $select.prop('disabled', true);
        });

        let isBomExists = @json($isBomExists);
        if (isBomExists) {
            $('#attributesTable .add-row, #attributesTable .remove-row').css({
                'pointer-events': 'none',
                'opacity': '0.5',
                'cursor': 'not-allowed'
            });
        }
    }

    $(document).ready(function () {
        const status = document.getElementById('documentStatus').value;
        if (status === 'submitted' || status === 'approved' || status === 'approval_not_required') {
            disableAllFieldsAndTabs();
        }
    });

   function enableAmendmentFields() {
     const isItemReferenced = @json($isItemReferenced);
     const currentType = @json($item->type); 
        const fieldsToDisable = [
            'item_code',
            'uom_id',
            'sub_types[]',
            'type',
            'is_traded_item',
            'is_asset'
        ];
        
        document.querySelectorAll('input, select, textarea').forEach(field => {
            field.disabled = false;
            field.readOnly = false;
        });
        document.querySelectorAll('.attribute-values-hidden select').forEach(select => {
            select.disabled = true;
        });
        
        if (isItemReferenced) {
            fieldsToDisable.forEach(fieldName => {
                const fields = document.querySelectorAll(`[name="${fieldName}"]`);
                fields.forEach(field => {
                    field.disabled = true;
                    field.readOnly = true;
                });
            });
        } else {
            fieldsToDisable.forEach(fieldName => {
                const fields = document.querySelectorAll(`[name="${fieldName}"]`);
                fields.forEach(field => {
                    field.disabled = false;
                    field.readOnly = false;
                });
            });
        }

       if (currentType === 'Service') {
            ['sub_types[]', 'is_traded_item', 'is_asset','is_scrap'].forEach(name => {
                document.querySelectorAll(`[name="${name}"]`).forEach(field => {
                    field.disabled = true;
                    field.readOnly = true;
                });
            });
        }
        const checkbox = document.getElementById('customSwitch3');
        if (checkbox) {
            checkbox.disabled = false;
        }

        const amendDeleteBtn = document.getElementById('btnAmendDelete');
        if (amendDeleteBtn) {
            amendDeleteBtn.style.setProperty('display', 'inline-block', 'important');
        }

        let isBomExists = @json($isBomExists);
        if (isBomExists) {
            $('#attributesTable').css({
                'pointer-events': '',
                'opacity': '',
                'cursor': ''
            });
            $('#attributesTable .add-row, #attributesTable .remove-row').css({
                'pointer-events': 'none',
                'opacity': '0.5',
                'cursor': 'not-allowed'
            });
        }
       const tradedCheckbox = document.getElementById('tradedItemCheckbox');
        if (tradedCheckbox) {
            tradedCheckbox.disabled = (currentType === 'Service');
        }
    }

    function amendConfirm()
    {
        enableAmendmentFields();
        const amendButton = document.getElementById('amendShowButton');
        if (amendButton) {
            amendButton.style.display = "none";
        }
        const buttonParentDiv = document.getElementById('buttonsDiv');
        const newSubmitButton = document.createElement('button');
        newSubmitButton.type = "button";
        newSubmitButton.id = "amend-submit-button";
        newSubmitButton.className = "btn btn-primary btn-sm mb-50 mb-sm-0 submit-button";
        newSubmitButton.innerHTML = `<i data-feather="check-circle"></i> Submit`;
        newSubmitButton.value = "submitted"; 
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


    }

    function openAmendConfirmModal()
    {
        $("#amendConfirmPopup").modal("show");
    }

    function submitAmend()
    {
        let remark = $("#amendConfirmPopup").find('[name="amend_remarks"]').val();
        $("#action_type_main").val("amendment");
        $("#amendConfirmPopup").modal('hide');
        $("#item_form").submit();
    }

    function addFiles(element, previewElementId) {
        const input = element;
        const allowedMaxFilesCount = Number(element.getAttribute('max_file_count') ? element.getAttribute('max_file_count') : 1);
        const files = Array.from(input.files); // Convert new FileList to array
        const dt = new DataTransfer();
        const inputId = input.name.replace('[]','');
        // Initialize storage for this input if not already initialized
        if (!fileInputData[inputId]) {
            fileInputData[inputId] = [];
            addedFilesCount = 0;
        } else {
            addedFilesCount = fileInputData[inputId].length;
        }

        if ((files.length + fileInputData[inputId].length) > allowedMaxFilesCount) 
        {
            Swal.fire({
                title: 'Error!',
                text: "Maximum " + allowedMaxFilesCount + " files are allowed",
                icon: 'error',
            });
            let prevAllFiles = fileInputData[inputId] ? fileInputData[inputId] : [];
            let tempDt = new DataTransfer();
            prevAllFiles.forEach((fileElement) => {
                tempDt.items.add(fileElement);
            });
            input.files = tempDt.files;
            return;
        }

        // Combine old and new files
        let allFiles = [...fileInputData[inputId], ...files];
        var invalidFile = {};

        // Validate files
        for (let i = 0; i < allFiles.length; i++) {
            const file = allFiles[i];
            const fileExtension = file.name.split('.').pop().toLowerCase();

            if (!ALLOWED_EXTENSIONS.includes(fileExtension) || !ALLOWED_MIME_TYPES.includes(file.type)) {
                invalidFile.message = 'Please select valid files';
                break;
            }
            const fileSize = (file.size / 1024).toFixed(2);
            if (fileSize > MAX_FILE_SIZE) {
                invalidFile.message = 'Please select files with size not more than 5MB';
                break;
            }
        }

        // Stop if there's an invalid file
        if (invalidFile && invalidFile.message) {
            Swal.fire({
                title: 'Error!',
                text: invalidFile.message,
                icon: 'error',
            });
            element.value = ''; // Reset file input
            return;
        } else {
            // Add all files to DataTransfer and rebuild the preview
            allFiles.forEach((file, i) => {
                dt.items.add(file);
                if (!fileInputData[inputId].some(f => f.name === file.name && f.size === file.size)) {
                    const fileUrl = URL.createObjectURL(file);
                    appendFilePreviews(fileUrl, previewElementId, i);
                }
            });

            // Update the global object for this input
            fileInputData[inputId] = allFiles.reduce((unique, file) => {
                if (!unique.some(f => f.name === file.name && f.size === file.size)) {
                    unique.push(file);
                }
                return unique;
            }, []);

            // Update the file input's FileList
            input.files = dt.files;

            // Reset and re-render SVG icons (if applicable)
            feather.replace({
                width: 20,
                height: 20,
            });
        }
    }
     document.addEventListener('DOMContentLoaded', function () {
        const switchInput = document.getElementById('customSwitch3');
        const hiddenInput = document.getElementById('status_hidden_input');

        if (switchInput && hiddenInput) {
            switchInput.addEventListener('change', function () {
                hiddenInput.value = this.checked ? 'active' : 'inactive';
            });
        }
    });
 //approval-end
</script>
@endsection

