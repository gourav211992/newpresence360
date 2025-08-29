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
    <form class="ajax-input-form" method="POST" action="{{ route('item.store') }}" data-redirect="{{ route('item.index') }}">
    @csrf
    <input type="hidden" name="item_code_type" value="{{ $itemCodeType }}">
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
                                        <li class="breadcrumb-item active">Add New</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <a href="{{ route('item.index') }}" class="btn btn-secondary btn-sm">
                              <i data-feather="arrow-left-circle"></i> Back
                            </a>
                            <input type="hidden" name="document_status" id="document_status">
                            <button type="submit" class="btn btn-warning btn-sm submit-button" value="draft" >
                                <i data-feather="save"></i> Save as Draft
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm submit-button" value="submitted">
                                <i data-feather="check-circle"></i> Submit
                            </button>
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
                                                            <a href="{{route(name: 'bill.of.material.index')}}"  target="_blank" class="text-primary add-contactpeontxt mt-25 me-1"><i data-feather='file-text'></i> Bill of Material</a>
                                                            <div class="form-check form-check-primary form-switch statusactiinactive">
                                                                <input type="hidden" name="status" id="status_hidden_input" value="inactive">
                                                                <input type="checkbox" checked class="form-check-input" id="customSwitch3" />
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
                                                                    {{ $type === 'Goods' ? 'checked' : '' }}
                                                                >
                                                                <label class="form-check-label fw-bolder" for="{{ $type }}">
                                                                    {{ ucfirst($type) }}
                                                                </label>
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
                                                        <input type="text" name="category_name" class="form-control category-autocomplete" placeholder="Type to search group">
                                                        <input type="hidden" name="subcategory_id" class="category-id">
                                                        <input type="hidden" name="category_type" class="category-type" value="Product">
                                                        <input type="hidden" name="cat_initials" class="cat_initials-id" value="">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <a href="{{route('categories.index')}}"  target="_blank" class="voucehrinvocetxt mt-0">Add Group</a>
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
                                                                    <input type="checkbox" class=" form-check-input subTypeCheckbox" id="subType{{ $subType->id }}" name="sub_types[]" value="{{ $subType->id }}">
                                                                    <label class="form-check-label" for="subType{{ $subType->id }}">{{ $subType->name }}</label>
                                                                </div>
                                                            @endforeach

                                                            {{-- Scrap --}}
                                                            <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                <input type="hidden" name="is_scrap" value="0">
                                                                <input type="checkbox" class="form-check-input subTypeCheckbox" id="scrapCheckbox" name="is_scrap" value="1">
                                                                <label class="form-check-label" for="scrapCheckbox">Scrap</label>
                                                            </div>
                                                            {{-- Traded Item --}}
                                                            <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                <input type="hidden" name="is_traded_item" value="0">
                                                                <input type="checkbox"class="form-check-input subTypeCheckbox" id="tradedItemCheckbox" name="is_traded_item" value="1">
                                                                <label class="form-check-label" for="tradedItemCheckbox">Traded Item</label>
                                                            </div>

                                                            {{-- Asset --}}
                                                            <div class="form-check form-check-primary mt-25 custom-checkbox me-0">
                                                                <input type="hidden" name="is_asset" value="0">
                                                                <input type="checkbox" class="form-check-input subTypeCheckbox" id="assetCheckbox" name="is_asset" value="1">
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
                                                            <input type="text" name="item_name" class="form-control item-name-autocomplete" placeholder="Enter Item Name" />
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">
                                                                <span id="item_initial_label">Item Initial</span><span class="text-danger">*</span>
                                                            </label>
                                                        </div>
                                                        <div class="col-md-2 mb-1 mb-sm-0">
                                                            <input type="text" name="item_initial" class="form-control" placeholder="Enter Item Initial" />
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label" ><span id="item_code_label">Item Code</span><span class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <input type="text" name="item_code" class="form-control" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">HSN/SAC<span class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5 mb-1 mb-sm-0">
                                                            <input type="text" name="hsn_name" id="hsn-autocomplete_1" class="form-control hsn-autocomplete" data-id="1" placeholder="Select HSN/SAC"/>
                                                            <input type="hidden" class="hsn-id" name="hsn_id" />
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">Inventory UOM <span class="text-danger">*</span></label>  
                                                        </div>
                                                        <div class="col-md-2 mb-1 mb-sm-0">
                                                              <select name="uom_id" class="form-select select2">
                                                                @foreach ($units as $unit)
                                                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center mb-1"> 
                                                        <div class="col-md-3">  
                                                            <label class="form-label">Cost Price</label> 
                                                        </div>
                                                        <div class="col-md-3 mb-1 mb-sm-0 middleinputerror">
                                                            <div class="input-group">
                                                                <input type="text" name="cost_price" class="form-control cost-price-input" placeholder="Enter Cost Price">
                                                                <select class="form-select currency-select" name="cost_price_currency_id">
                                                                    @foreach($currencies as $currency)
                                                                        <option value="{{ $currency->id }}" data-short-name="{{ $currency->short_name }}"
                                                                            @if(isset($organization) && $currency->id == $organization->currency_id) selected @endif>
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
                                                                <input type="text" name="sell_price" class="form-control sell-price-input" placeholder="Enter Sell Price">
                                                                <select class="form-select currency-select" name="sell_price_currency_id">
                                                                    @foreach($currencies as $currency)
                                                                        <option value="{{ $currency->id }}" data-short-name="{{ $currency->short_name }}"
                                                                            @if(isset($organization) && $currency->id == $organization->currency_id) selected @endif>
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
                                                        <div class="col-md-9" >
                                                            <textarea name="item_remark" id="item_remark" class="form-control" rows="1"></textarea>
                                                        </div>
                                                    </div> 
                                            </div>
                                           </div>
                                            <div class="col-md-3 border-start">
                                                <!-- <div class="row align-items-center mb-2">
                                                    <div class="col-md-12">
                                                        <label class="form-label text-primary"><strong>Status</strong></label>
                                                        <div class="demo-inline-spacing">
                                                            @foreach ($status as $option)
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input
                                                                        type="radio"
                                                                        id="status_{{ strtolower($option) }}"
                                                                        name="status"
                                                                        value="{{ $option }}"
                                                                        class="form-check-input"
                                                                        {{ $option == 'active' ? 'checked' : '' }} >
                                                                        <label class="form-check-label fw-bolder" for="status_{{ strtolower($option) }}">
                                                                            {{ucfirst($option)}}
                                                                        </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div> -->
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
                                                                    <select id="groupSelect" name="item_specifications[group_id]" class="form-select mw-100 select2 specificationId">
                                                                        <option value="">Select Group</option>
                                                                        @foreach ($specificationGroups as $group)
                                                                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
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
                                                                    <input type="text" class="form-control numberonly" name="min_stocking_level" />
                                                                </div>
                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">Max Stocking Level</label>
                                                                    <input type="text" class="form-control numberonly" name="max_stocking_level" />
                                                                </div>

                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">Reorder Level</label>
                                                                    <input type="text" class="form-control numberonly" name="reorder_level" />
                                                                </div>
                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">Minimum Order Qty</label>
                                                                    <input type="text" class="form-control numberonly" name="minimum_order_qty" />
                                                                </div>
                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">Lead Days</label>
                                                                    <input type="text" class="form-control numberonly" name="lead_days" />
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
                                                                    <input type="number" class="form-control" step="any" name="po_positive_tolerance" />
                                                                </div>
                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">PO Negative Tolerance</label>
                                                                    <input type="number" class="form-control" step="any" name="po_negative_tolerance" />
                                                                </div>
                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">SO Positive Tolerance</label>
                                                                    <input type="number" class="form-control" step="any" name="so_positive_tolerance" />
                                                                </div>
                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">SO Negative Tolerance</label>
                                                                    <input type="number" class="form-control" step="any" name="so_negative_tolerance" />
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
                                                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">Conversion</label>
                                                                    <input type="text" name="storage_uom_conversion" class="form-control" placeholder="Enter Conversion">
                                                                </div>

                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">No of Pack</label>
                                                                    <input type="number" name="storage_uom_count" class="form-control" placeholder="Enter No of Pack">
                                                                </div>

                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">Weight (kg)</label>
                                                                    <input type="number" step="0.0001" name="storage_weight" class="form-control" placeholder="Enter Weight">
                                                                </div>

                                                                <div class="col-md-3 mb-1">
                                                                    <label class="form-label">Volume (cft)</label>
                                                                    <input type="number" step="0.0001" name="storage_volume" class="form-control" placeholder="Enter Volume">
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
                                                                        <option value="1">Yes</option>
                                                                        <option value="0" selected>No</option>
                                                                    </select>
                                                                </div>

                                                                <div class="col-md-6" id="inspectionCheckContainer">
                                                                    <div class="row align-items-center mb-1">
                                                                       <label class="form-label">Inspection Checklist</label>
                                                                        <div class="col-md-8">
                                                                            <input type="text" name="inspection_checklist_name" class="form-control inspection-autocomplete" placeholder="Search Inspection Checklist" />
                                                                            <input type="hidden" name="inspection_checklist_id" class="form-control inspection_checklist_id" value="" />
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
                                                                        <input type="checkbox" class="form-check-input" id="Serial" name="is_serial_no" value="1">
                                                                        <label class="form-check-label" for="Serial">Yes/No</label>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-2">
                                                                   <label class="form-label">Batch No</label>
                                                                    <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                        <input type="hidden" name="is_batch_no" value="0">
                                                                        <input type="checkbox" class="form-check-input" id="Batch" name="is_batch_no" value="1">
                                                                        <label class="form-check-label" for="Batch">Yes/No</label> 
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-2">
                                                                    <label class="form-label">Expiry</label>
                                                                    <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                        <input type="hidden" name="is_expiry" value="0">
                                                                        <input type="checkbox" class="form-check-input" id="ExpiryCheck" name="is_expiry" value="1">
                                                                        <label class="form-check-label" for="ExpiryCheck">Yes/No</label>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-3" id="shelfLifeContainer" style="display: none;">
                                                                    <label class="form-label">Shelf Life in Days</label>
                                                                    <input type="text" class="form-control numberonly" name="shelf_life_days" />
                                                                </div>
                                                            </div>

                                                            <!-- <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Storage Type</label>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <select name="storage_type" class="form-select mw-100">
                                                                                    <option value="">Select</option>
                                                                                    @foreach ($storageTypes as $type)
                                                                                        <option value="{{ $type }}">
                                                                                            {{ ucfirst($type) }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div> -->
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
                                                                        <tr id="row-0">
                                                                            <td>1</td>
                                                                            <td>
                                                                                <select name="alternate_uoms[0][uom_id]" class="form-select mw-100"></select>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" name="alternate_uoms[0][conversion_to_inventory]" class="form-control mw-100">
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" name="alternate_uoms[0][cost_price]" class="form-control cost-price-alternate mw-100">
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" name="alternate_uoms[0][sell_price]" class="form-control sell-price-alternate mw-100">
                                                                            </td>
                                                                            <td>
                                                                            <div class="demo-inline-spacing">
                                                                                <div class="form-check form-check-primary mt-25">
                                                                                    <input type="radio" id="isDefaultPurchase0" name="alternate_uoms[0][is_purchasing]" value="1" class="form-check-input">
                                                                                    <label class="form-check-label fw-bolder" for="isDefaultPurchase0">Purchase</label>
                                                                                </div>
                                                                                <div class="form-check form-check-primary mt-25">
                                                                                    <input type="radio" id="isDefaultSelling0" name="alternate_uoms[0][is_selling]" value="1" class="form-check-input">
                                                                                    <label class="form-check-label fw-bolder" for="isDefaultSelling0">Selling</label>
                                                                                </div>
                                                                            </div>
                                                                            </td>
                                                                            <td>
                                                                                <a href="#" class="text-primary add-row"> <i data-feather="plus-square" class="me-50"></i></a>
                                                                                <a href="#" class="text-danger remove-row"><i data-feather="trash-2"></i></a>
                                                                            </td>
                                                                        </tr>
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
                                                                    <input class="form-control item-autocomplete"
                                                                        data-name=""
                                                                        data-code=""
                                                                        placeholder="Search Item"
                                                                        autocomplete="off">
                                                                    <input type="hidden" id="itemId" name="item_id">
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <a href="#" id="addNewItem" class="text-primary add-contactpeontxt mt-1 mt-sm-0">
                                                                        <i data-feather='plus'></i> Add New
                                                                    </a>
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
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>

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
                                                                        <tr id="row-0">
                                                                            <td>1</td>
                                                                            <td>
                                                                                <input type="text" name="approved_customer[0][customer_name]" class="form-control mw-100 customer-autocomplete" data-id="0" placeholder="Search Customer">
                                                                                <input type="hidden" id="customer-id_0" name="approved_customer[0][customer_id]" class="customer-id">
                                                                            </td>
                                                                            <td><input type="text" name="approved_customer[0][customer_code]"  id="customer-code_0" class="form-control mw-100" readonly></td>
                                                                            <td><input type="text" name="approved_customer[0][item_code]" class="form-control mw-100"></td>
                                                                            <td><input type="text" name="approved_customer[0][item_name]" class="form-control mw-100"></td>
                                                                            <td><input type="text" name="approved_customer[0][item_details]" class="form-control mw-100"></td>
                                                                            <td>
                                                                             <input type="text" name="approved_customer[0][sell_price]" id="sell-price_0" class="form-control sell-price-approved-customer  mw-100">
                                                                            </td>
                                                                            <td>
                                                                             <select name="approved_customer[0][uom_id]"  id="uom_0" class="form-select mw-100" disabled></select>
                                                                            </td>
                                                                            <td>
                                                                                <a href="#" class="text-primary add-row"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                <a href="#" class="text-danger remove-row"><i data-feather="trash-2"></i></a>
                                                                            </td>
                                                                        </tr>
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
                                                                        <tr id="row-0">
                                                                            <td>1</td>
                                                                            <td>
                                                                                <input type="text" name="approved_vendor[0][vendor_name]" class="form-control mw-100 vendor-autocomplete" data-id="0" placeholder="Search Vendor">
                                                                                <input type="hidden" id="vendor-id_0" name="approved_vendor[0][vendor_id]" class="vendor-id" value="">
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" name="approved_vendor[0][vendor_code]" class="form-control mw-100" id="item-code_0" readonly>
                                                                            </td>
                                                                            <td><input type="text" name="approved_vendor[0][cost_price]" id="cost-price_0" class="form-control cost-price-approved-vendor mw-100"></td>
                                                                            <td>
                                                                             <select name="approved_vendor[0][uom_id]"  id="uom_0" class="form-select mw-100" disabled></select>
                                                                            </td>
                                                                            <td>
                                                                                <a href="#" class="text-primary add-row"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                <a href="#" class="text-danger remove-row"><i data-feather="trash-2"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>

                                                        <div class="tab-pane" id="Assets">
                                                            <!-- Asset Category -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label for="asset_category" class="form-label">Category<span class="text-danger">*</span></label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <select id="asset_category" name="asset_category_id" class="form-select mw-100 select2">
                                                                        <option value="">Select</option>
                                                                        @foreach($fixedAssetCategories as $fixedAssetCategory)
                                                                            <option value="{{ $fixedAssetCategory->asset_category_id }}">
                                                                                {{ $fixedAssetCategory->assetCategory->name ?? 'N/A' }}
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
                                                                    <input type="number" id="expected_life" name="expected_life" class="form-control">
                                                                </div>
                                                            </div>

                                                          <!-- Maintenance Schedule -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label for="maintenance_schedule" class="form-label">Maint.Schedule<span class="text-danger">*</span></label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <select class="form-select mw-100" name="maintenance_schedule" id="maintenance_schedule">
                                                                        @php
                                                                            $schedules = ['weekly', 'monthly', 'quarterly', 'semi-annually', 'annually'];
                                                                        @endphp
                                                                        <option value="">Select</option>
                                                                        @foreach($schedules as $schedule)
                                                                            <option value="{{ $schedule }}">{{ ucfirst($schedule) }}</option>
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
                                                                    <input type="text" id="brand_name" name="brand_name" value="" class="form-control" maxlength="255">
                                                                </div>
                                                            </div>

                                                            <!-- Model No. -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label for="model_no" class="form-label">Model No.<span class="text-danger">*</span></label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="text" id="model_no" name="model_no" value="" class="form-control" maxlength="255">
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
                </section>
            </div>
        </div>
    </div>
  </form>
    <!-- END: Content-->
@endsection
@section('scripts')
<script>
    $(document).ready(function () {
        function applyCapsLock() {
            $('input[type="text"], input[type="number"]').each(function() {
                $(this).val($(this).val().toUpperCase());
            });
            $('input[type="text"], input[type="number"]').on('input', function() {
                var value = $(this).val().toUpperCase();  
                $(this).val(value); 
            });
        }
        var units = @json($units);
        var purchasingUOMIds = [];
        var selectedUOMIds = [];
        var initialUOM = $("select[name='uom_id']").find(":selected").text().trim();
        var selectedUOMId = $("select[name='uom_id']").find(":selected").val();

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
            $('#alternateUOMTable tbody tr').each(function () {
                var $select = $(this).find('select[name*="[uom_id]"]');
                var selectedValue = $select.val();
                var $options = $select.find('option');
                $options.each(function () {
                    var optionValue = $(this).val();
                    if (selectedUOMIds.includes(optionValue) && optionValue !== selectedValue) {
                        $(this).prop('disabled', true);
                    } else {
                        $(this).prop('disabled', false);
                    }
                });
            });
        }

        function updateRowIndices() {
            var $rows = $('#alternateUOMTable tbody tr');
            $('#alternateUOMTable tbody tr').each(function (index) {
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

        $('#alternateUOMTable').on('click', '.add-row', function (e) {
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

        $('#alternateUOMTable').on('click', '.remove-row', function (e) {
            e.preventDefault();
            $(this).closest('tr').remove();
            var uomId = $(this).closest('tr').find('select[name*="[uom_id]"]').val();
            var index = selectedUOMIds.indexOf(uomId);
            if (index !== -1) {
                selectedUOMIds.splice(index, 1);
            }
            updateRowIndices();
            disableSelectedUOMOptions();
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

        $('#alternateUOMTable').find('select[name*="[uom_id]"]').each(function() {
            populateDropdown($(this), $(this).val());
        });

        updateRowIndices();
        handleRadioSelection();
        disableSelectedUOMOptions();

        var selectedVendorIds = [];

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
                }
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
            newRow.find('select').prop('disabled', true);
            $('#vendorTableBody').append(newRow);
            updateVendorRowIndices();
            feather.replace();
            applyCapsLock();
        });

        $('#vendorTable').on('click', '.remove-row', function (e) {
            e.preventDefault();
            var rowId = $(this).closest('tr').find('input[data-id]').data('id');
            var vendorId = $('#vendor-id_' + rowId).val();
            if (vendorId && selectedVendorIds.includes(parseInt(vendorId))) {
                selectedVendorIds.splice(selectedVendorIds.indexOf(parseInt(vendorId)), 1);
            }
            $(this).closest('tr').remove();
            updateVendorRowIndices();
        });

        $('#addVendor').on('click', function (e) {
            e.preventDefault();
            $('#vendorTable').find('.add-row').first().trigger('click');
        });

        var selectedCustomerIds = [];

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
                        $(this).val(ui.item.company_name);
                        $('#customer-id_' + rowId).val(ui.item.id);
                        $('#customer-code_' + rowId).val(ui.item.customer_code);
                        return false;
                    }
                }).on('input', function() {
                    var rowId = $(this).data('id');
                    if ($(this).val() === "") {
                        $('#customer-id_' + rowId).val('');
                        $('#customer-code_' + rowId).val('');
                    }
                }).focus(function() {
                    if (this.value === "") {
                        $(this).autocomplete("search", "");
                    }
                });
        }

        function updateCustomerRowIndices() {
        var $rows = $('#customerTable tbody tr');
        $('#customerTable tbody tr').each(function (index) {
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
            newRow.find('select').prop('disabled', true);
            $('#customerTableBody').append(newRow);
            updateCustomerRowIndices();
            feather.replace();
            applyCapsLock();
        });

        $('#customerTable').on('click', '.remove-row', function (e) {
            e.preventDefault();
            var rowId = $(this).closest('tr').find('input[data-id]').data('id');
            var customerId = $('#customer-id_' + rowId).val();
            if (customerId && selectedCustomerIds.includes(parseInt(customerId))) {
                selectedCustomerIds.splice(selectedCustomerIds.indexOf(parseInt(customerId)), 1);
            }
            $(this).closest('tr').remove();
            updateCustomerRowIndices();
        });

        $('#addCustomer').on('click', function (e) {
            e.preventDefault();
            $('#customerTable').find('.add-row').first().trigger('click');
        });
        updateCustomerRowIndices();
        updateVendorRowIndices();
        initializeCustomerAutocomplete(".customer-autocomplete");
        initializeVendorAutocomplete(".vendor-autocomplete");
    });
</script>

<script>
    $(document).ready(function() {
        feather.replace();
        var rowIndex = $('#attributesTable tbody tr').length + 1;
        var attributeGroups = @json($attributeGroups);
        var attributesMap = {};
        var selectedGroupIds = [];

        attributeGroups.forEach(group => {
            attributesMap[group.id] = [];
        });
        function populateOptions(selectElement, options, defaultOption, textField, valueField) {
            selectElement.empty().append(new Option(defaultOption.text, defaultOption.value));
            if (options && Array.isArray(options)) {
                options.forEach(option => {
                    selectElement.append(new Option(option[textField], option[valueField]));
                });
            } else {
                console.error("No valid options to populate:", options);
            }
            selectElement.trigger('change');
        }
        function disableSelectedOptions() {
            $('select[name^="attributes"][name$="[attribute_group_id]"]').each(function() {
                var selectedGroupId = $(this).val();  
                var $options = $(this).find('option'); 
                $options.each(function() {
                    var optionValue = $(this).val(); 
                    if (selectedGroupIds.includes(optionValue) && optionValue !== selectedGroupId) {
                        $(this).prop('disabled', true); 
                    } else {
                        $(this).prop('disabled', false); 
                    }
                });
            });
        }

        function addRow(isDefault) {
            var actionIcon = isDefault
                ? `<a href="#" class="text-primary add-row"><i data-feather='plus-square'></i></a>`
                : `<a href="#" class="text-danger remove-row"><i data-feather='trash-2'></i></a>`;

            var newRow = `
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

            var $newRow = $(newRow);
            var $attributeGroupSelect = $newRow.find('.attribute-group');
            var $attributeValuesSelect = $newRow.find('.attribute-values');

            $attributeGroupSelect.select2();
            $attributeValuesSelect.select2();
            populateOptions($attributeGroupSelect, attributeGroups, { text: 'Select', value: '' }, 'name', 'id');
            $('#attributesTable tbody').append($newRow);
            feather.replace();

            rowIndex++;
            disableSelectedOptions();
            $attributeGroupSelect.on('change', function() {
                var selectedValue = $(this).val();
                selectedGroupIds = [];
                $('#attributesTable tbody tr').each(function() {
                    var groupId = $(this).find('.attribute-group').val();
                    if (groupId && !selectedGroupIds.includes(groupId)) {
                        selectedGroupIds.push(groupId);
                    }
                });
                disableSelectedOptions();
                var $valuesSelect = $(this).closest('tr').find('.attribute-values');
                updateAttributeValues($valuesSelect, selectedValue);
            });
            // Checkbox toggles
            $newRow.find('.all-checked').on('change', function() {
                var isChecked = $(this).is(':checked');
                var $select = $(this).closest('tr').find('.attribute-values');
                $(this).val(isChecked ? '1' : '0');
                $select.prop('disabled', isChecked).val(isChecked ? ['1'] : []).trigger('change');
            });

        }

        function updateIcons() {
            $('#attributesTable tbody tr').each(function(index) {
                var $actionCell = $(this).find('td:last-child');
                if ($('#attributesTable tbody tr').length === 1) {
                    $actionCell.html('<a href="#" class="text-primary add-row"><i data-feather="plus-square"></i></a>');
                } else {
                    $actionCell.html(index === 0
                        ?  '<a href="#" class="text-primary add-row"><i data-feather="plus-square"></i></a>'  +
                           '<a href="#" class="text-danger remove-row"><i data-feather="trash-2"></i></a>'
                        : '<a href="#" class="text-danger remove-row"><i data-feather="trash-2"></i></a>'
                    );
                }
            });
            feather.replace();
        }

        function updateRowNumbers() {
            $('#attributesTable tbody tr').each(function(index) {
                $(this).find('td').eq(0).text(index + 1);
                $(this).find('select[name^="attributes["][name$="[attribute_group_id]"]').attr('name', 'attributes[' + index + '][attribute_group_id]');
                $(this).find('select[name^="attributes["][name$="[attribute_id][]"]').attr('name', 'attributes[' + index + '][attribute_id][]');
                $(this).find('input[id^="BOMreq-"]').attr('id', 'BOMreq-' + index);
                $(this).find('label[for^="BOMreq-"]').attr('for', 'BOMreq-' + index);
            });
            rowIndex = $('#attributesTable tbody tr').length + 1;
        }

        function updateAttributeValues($select, groupId) {
            $select.empty().append(new Option('Select', ''));
            if (groupId && !attributesMap[groupId].length) {
                $.ajax({
                    url: "{{ url('/attributes') }}" + '/' + groupId,
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (Array.isArray(data)) {
                            attributesMap[groupId] = data;
                            populateOptions($select, data, { text: 'Select', value: '' }, 'value', 'id');
                            $select.find('option[value=""]').prop('disabled', true);
                        } else {
                            console.error('Unexpected response format:', data);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching attributes:', error);
                    }
                });
            } else {
                populateOptions($select, attributesMap[groupId], { text: 'Select', value: '' }, 'value', 'id');
            }
        }
        $('#attributesTable').on('click', '.add-row', function(e) {
            e.preventDefault();
            addRow(false);
            updateIcons();
        });

        $('#attributesTable').on('click', '.remove-row', function(e) {
            e.preventDefault();
            var $row = $(this).closest('tr');
            var groupId = $row.find('.attribute-group').val();
            var index = selectedGroupIds.indexOf(groupId); 
            if (index !== -1) {
                selectedGroupIds.splice(index, 1);
            }
            $row.remove(); 
            updateRowNumbers(); 
            updateIcons(); 
            disableSelectedOptions();
        });

        $('#attributesTable').on('change', '.attribute-group', function() {
            var selectedGroupId = $(this).val();
            var $valuesSelect = $(this).closest('tr').find('.attribute-values');
            updateAttributeValues($valuesSelect, selectedGroupId);
        });

        if ($('#attributesTable tbody tr').length === 0) addRow(true);
    });
</script>
<script>
    $(document).ready(function() {
        var itemCounter = $('#itemTable tbody tr').length + 1;
        var addedItems = {};

        function initializeItemAutocomplete(selector) {
            $(selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                       url: "{{ url('/items/search') }}",
                        method: 'GET',
                        dataType: 'json',
                        data: {
                           term: request.term
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
            }).on('focus', function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
        }

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
                $('.item-autocomplete').each(function() {
                    if (!$(this).data('ui-autocomplete')) {
                        initializeItemAutocomplete(this);
                    }
                });
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
            var itemCode = row.find('input[name^="alternateItems["][name$="[item_code]"]').val();
            row.remove();
            delete addedItems[itemCode];
            updateRowNumbers();
        });

        initializeItemAutocomplete(".item-autocomplete");
        updateRowNumbers();
    });
</script>

<script>
    $(document).ready(function() {
        const checkboxes = $('.subTypeCheckbox');
        const typeRadios = $('input[name="type"]');
        function updateCheckboxStatesForGoods() {
            const rawMaterialChecked = $('#subType1').is(':checked');
            const wipChecked = $('#subType2').is(':checked');
            const finishedGoodsChecked = $('#subType3').is(':checked');
            const assetChecked = $('#subType5').is(':checked');
            const expenseChecked = $('#subType6').is(':checked');
            const rawTradeChecked = $('#subType4').is(':checked');
            const isScrapChecked = $('#scrapCheckbox').is(':checked');
            if (isScrapChecked) {
                $('.subTypeCheckbox').not('#scrapCheckbox').prop('checked', false).prop('disabled', true);
                return; 
            }
            $('#subType2').prop('disabled', rawMaterialChecked || finishedGoodsChecked || rawTradeChecked);
            $('#subType3').prop('disabled', rawMaterialChecked || wipChecked || rawTradeChecked);
            $('#subType1').prop('disabled', wipChecked || finishedGoodsChecked || rawTradeChecked);
            $('#subType5').prop('disabled', expenseChecked || rawMaterialChecked || rawTradeChecked);
            $('#subType6').prop('disabled', assetChecked || rawMaterialChecked || rawTradeChecked);
            $('#subType4').prop('disabled', rawMaterialChecked || wipChecked || finishedGoodsChecked || assetChecked || expenseChecked);
            if (rawMaterialChecked || wipChecked || finishedGoodsChecked || assetChecked || expenseChecked || rawTradeChecked) {
                checkboxes.not(':checked').not($('input[name="is_traded_item"]')).not($('input[name="is_asset"]')).prop('disabled', true);
            } else {
                checkboxes.prop('disabled', false);
            }
            $('a[href="#UOM"]').removeClass('d-none').css('display', '');
            $('a[href="#Attributes"]').removeClass('d-none').css('display', '');
            $('a[href="#UOM"]').removeClass('d-none').css('display', '');
            $('#UOM').removeClass('d-none').css('display', '');
            $('#Details').removeClass('d-none').css('display', '');
            $('#Attributes').removeClass('d-none').css('display', '');
               
        }

        function updateCheckboxStatesForService() {
            checkboxes.prop('disabled', true); 
            $('input[name="is_traded_item"]').prop('checked', false).prop('disabled', true);
            $('input[name="is_asset"]').prop('checked', false).prop('disabled', true);
            $('a[href="#UOM"]').addClass('d-none');
            $('a[href="#Details"]').addClass('d-none');
            $('a[href="#Attributes"]').addClass('d-none');
            $('a[href="#UOM"]').addClass('d-none');
            $('a[href="#Assets"]').addClass('d-none');
            $('#UOM').addClass('d-none');
            $('#Details').addClass('d-none');
            $('#UOM').addClass('d-none');
            $('#Attributes').addClass('d-none');
            $('#Assets').addClass('d-none');
            ['#UOM', '#Details', '#Attributes','#Assets'].forEach(function (selector) {
                $(selector).addClass('d-none').find('input, select, textarea').each(function () {
                    const $input = $(this);
                    $input.is(':checkbox') || $input.is(':radio') 
                        ? $input.prop('checked', false) : $input.val('');
                });
            });
        }
        function handleCheckboxChange() {
            const selectedType = typeRadios.filter(':checked').val();
             const isScrapChecked = $('#scrapCheckbox').is(':checked');
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
            checkboxes.prop('disabled', false); 
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
        checkboxes.change(handleCheckboxChange);
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
    const catInitialsInput = $('input[name="cat_initials"]');
    const itemInitialInput = $('input[name="item_initial"]');
    const subCatInitialsInput = $('input[name="cat_initials"]'); 
    const subTypeCheckboxes = $('.subTypeCheckbox');
    const itemCodeInput = $('input[name="item_code"]'); 
    const typeRadios = $('input[name="type"]');
    if (itemCodeType === 'Manual') {
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
            initials = words[0].substring(0, 3).toUpperCase();
        } else if (words.length === 2) {
            initials = words[0].substring(0, 2).toUpperCase() + words[1][0].toUpperCase();
        } else if (words.length >= 3) {
            initials = words[0][0].toUpperCase() + words[1][0].toUpperCase() + words[2][0].toUpperCase();
        }

        return initials.substring(0, 3); 
    }
    function generateItemCode() {
        if (itemCodeType === 'Manual') {
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
                item_initials: itemInitials ,
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
    if (itemCodeType === 'Auto') {
        generateItemCode(); 
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
            initials = words[0].substring(0, 3).toUpperCase();
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
                    if (!$countInput.val()) $countInput.val(1);

                } else {
                    $conversionInput.prop('readonly', false);
                    if (!$conversionInput.val()) $conversionInput.val(1);

                    $countInput.val(1);
                    $countInput.prop('readonly', true);
                }
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
  
  //Capslock-start
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
   //Capslock-end
  // asset-start
    $(document).ready(function () {
        function toggleAssetTab() {
            if ($('#assetCheckbox').is(':checked')) {
                $('#assetTabLink').removeClass('d-none').show();
                $('.nav-link').removeClass('active'); 
                $('.tab-pane').removeClass('active show'); 
                $('#assetTabLink').addClass('active');
                $('#Assets').addClass('active show').removeClass('d-none').show();
            } else {
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
   // asset-end
   // inspection-start
    $(document).ready(function() {
        function toggleInspectionChecklist() {
            var val = $('#is_inspection').val();
            if (val == '1') {
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

    document.addEventListener('DOMContentLoaded', function () {
        const switchInput = document.getElementById('customSwitch3');
        const hiddenInput = document.getElementById('status_hidden_input');
        hiddenInput.value = switchInput.checked ? 'active' : 'inactive';
        switchInput.addEventListener('change', function () {
            hiddenInput.value = switchInput.checked ? 'active' : 'inactive';
        });
    });

  // inspection-end
</script>
@endsection
