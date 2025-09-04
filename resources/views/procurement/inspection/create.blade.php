@extends('layouts.app')
@section('styles')
    <style>
        #mrnModal .table-responsive {
            overflow-y: auto;
            max-height: 300px;
            /* Set the height of the scrollable body */
            position: relative;
        }

        #mrnModal .mrn-order-detail {
            width: 100%;
            border-collapse: collapse;
        }

        #mrnModal .mrn-order-detail thead {
            position: sticky;
            top: 0;
            /* Stick the header to the top of the table container */
            background-color: white;
            /* Optional: Make sure header has a background */
            z-index: 1;
            /* Ensure the header stays above the body content */
        }

        #mrnModal .mrn-order-detail th {
            background-color: #f8f9fa;
            /* Optional: Background for the header */
            text-align: left;
            padding: 8px;
        }

        #mrnModal .mrn-order-detail td {
            padding: 8px;
        }

        .tooltip-inner {
            text-align: left
        }

        .subStore {
            display: none;
        }

        .pass-label {
            font-weight: 500;
        }

        #inspectionChecklistModal .table-responsive {
            overflow-y: auto;
            max-height: 300px;
            /* Set the height of the scrollable body */
            position: relative;
        }

        #inspectionChecklistModal .po-order-detail {
            width: 100%;
            border-collapse: collapse;
        }

        #inspectionChecklistModal .po-order-detail thead {
            position: sticky;
            top: 0;
            /* Stick the header to the top of the table container */
            background-color: white;
            /* Optional: Make sure header has a background */
            z-index: 1;
            /* Ensure the header stays above the body content */
        }

        #inspectionChecklistModal .po-order-detail th {
            background-color: #f8f9fa;
            /* Optional: Background for the header */
            text-align: left;
            padding: 8px;
        }

        #inspectionChecklistModal .po-order-detail td {
            padding: 8px;
        }
    </style>
@endsection
@section('content')
    <form class="ajax-input-form" method="POST" action="{{ route('inspection.store') }}" data-redirect="/inspection"
        enctype="multipart/form-data">
        <input type="hidden" name="tax_required" id="tax_required" value="">
        @csrf
        <div class="app-content content ">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">
                                        {{$servicesBooks['services'][0]->name ?? "Material Receipt"}}
                                    </h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="/">Home</a>
                                            </li>
                                            <li class="breadcrumb-item active">Add New</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <input type="hidden" name="document_status" value="draft" id="document_status">
                                <button type="button" onClick="javascript: history.go(-1)"
                                    class="btn btn-secondary btn-sm mb-50 mb-sm-0">
                                    <i data-feather="arrow-left-circle"></i> Back
                                </button>
                                <button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button"
                                    name="action" value="draft">
                                    <i data-feather='save'></i> Save as Draft
                                </button>
                                <button type="submit" class="btn btn-primary btn-sm submit-button" name="action"
                                    value="submitted">
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
                                                <div
                                                    class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                                                    <div>
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Series <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" id="book_id" name="book_id">
                                                            <!-- <option value="">Select</option> -->
                                                            @foreach($books as $book)
                                                                <option value="{{$book->id}}">{{ucfirst($book->book_code)}}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <!-- <input type="hidden" name="mrn_no" id="book_code"> -->
                                                        <input type="hidden" name="book_code" id="book_code">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Document No <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="document_number" class="form-control"
                                                            id="document_number">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Document Date <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" class="form-control" value="{{date('Y-m-d')}}"
                                                            name="document_date">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Location <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select header_store_id" id="header_store_id"
                                                            name="header_store_id">
                                                            @foreach($locations as $erpStore)
                                                                <option value="{{$erpStore->id}}" {{ old('header_store_id', $selectedStoreId ?? '') == $erpStore->id ? 'selected' : '' }}>
                                                                    {{ ucfirst($erpStore->store_name) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Main Store <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select sub_store" id="sub_store_id"
                                                            name="sub_store_id">
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Rejected Store</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select rejected_sub_store rejected_sub_store_id"
                                                            id="rejected_sub_store_id" name="rejected_sub_store_id">
                                                        </select>
                                                    </div>
                                                </div>
                                                <!-- <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Reference No </label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <input type="text" name="reference_number" class="form-control">
                                                        </div>
                                                    </div> -->
                                                <div class="row align-items-center mb-1 d-none" id="reference_from">
                                                    <div class="col-md-3">
                                                        <label class="form-label">
                                                            Reference From
                                                        </label>
                                                    </div>
                                                    <div class="col-md-5 action-button">
                                                        <button type="button"
                                                            class="btn btn-outline-primary btn-sm mb-0 mrnSelect">
                                                            <i data-feather="plus-square"></i>
                                                            Outstanding GRN
                                                        </button>
                                                        <input type="hidden" name="module_type" id="module_type"
                                                            class="module_type" value="mrn">
                                                    </div>
                                                    <div class="row align-items-center mb-1" id="referenceNoDiv"
                                                        style="display: none;">
                                                        <div class="col-md-5">
                                                            <input type="hidden" name="reference_type"
                                                                class="form-control reference_type"
                                                                id="reference_type_input" readonly>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" id="vendor_section">
                                    <div class="col-md-12">
                                        <div class="card quation-card">
                                            <div class="card-header newheader">
                                                <div>
                                                    <h4 class="card-title">Vendor Details</h4>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Vendor <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" placeholder="Select"
                                                                class="form-control mw-100 ledgerselecct" id="vendor_name"
                                                                name="vendor_name" readonly />
                                                            <input type="hidden" id="vendor_id" name="vendor_id" />
                                                            <input type="hidden" id="vendor_code" name="vendor_code" />
                                                            <input type="hidden" id="shipping_id" name="shipping_id" />
                                                            <input type="hidden" id="billing_id" name="billing_id" />
                                                            <input type="hidden" id="hidden_state_id"
                                                                name="hidden_state_id" />
                                                            <input type="hidden" id="hidden_country_id"
                                                                name="hidden_country_id" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Currency <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select" name="currency_id">
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Payment Terms <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select" name="payment_term_id">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="customer-billing-section h-100">
                                                            <p>Vendor Address</p>
                                                            <div class="bilnbody">
                                                                <div class="genertedvariables genertedvariablesnone">
                                                                    <label class="form-label w-100">Vendor Address <span
                                                                            class="text-danger">*</span> <a
                                                                            href="javascript:;"
                                                                            class="float-end font-small-2 editAddressBtn"
                                                                            data-type="billing"><i
                                                                                data-feather='edit-3'></i> Edit</a></label>
                                                                    <div class="mrnaddedd-prim billing_detail">-</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="customer-billing-section h-100">
                                                            <p>Billing Address</p>
                                                            <div class="bilnbody">
                                                                <div class="genertedvariables genertedvariablesnone">
                                                                    <label class="form-label w-100">Billing Address <span
                                                                            class="text-danger">*</span>
                                                                        {{-- <a href="javascript:;"
                                                                            class="float-end font-small-2 editAddressBtn"
                                                                            data-type="billing"><i
                                                                                data-feather='edit-3'></i> Edit</a> --}}
                                                                    </label>
                                                                    <div class="mrnaddedd-prim org_address">-</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="customer-billing-section h-100">
                                                            <p>Delivery Address</p>
                                                            <div class="bilnbody">
                                                                <div class="genertedvariables genertedvariablesnone">
                                                                    <label class="form-label w-100">Delivery Address <span
                                                                            class="text-danger">*</span>
                                                                        {{-- <a href="javascript:;"
                                                                            class="float-end font-small-2 editAddressBtn"
                                                                            data-type="billing"><i
                                                                                data-feather='edit-3'></i> Edit</a> --}}
                                                                    </label>
                                                                    <div class="mrnaddedd-prim delivery_address">-</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" id="general_section">
                                    <div class="col-md-12">
                                        <div class="card quation-card">
                                            <div class="card-header newheader">
                                                <div>
                                                    <h4 class="card-title">General Information</h4>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Gate Entry No.
                                                                <!-- <span class="text-danger">*</span> -->
                                                            </label>
                                                            <input type="text" name="gate_entry_no" id="gate_entry_no"
                                                                class="form-control  gate_entry_no"
                                                                placeholder="Enter Gate Entry no">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Gate Entry Date
                                                                <!-- <span class="text-danger">*</span> -->
                                                            </label>
                                                            <input type="date" name="gate_entry_date"
                                                                class="form-control  gate-entry gate_entry_date"
                                                                id="datepicker2" placeholder="Enter Gate Entry Date">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                E-Way Bill No.
                                                                <!-- <span class="text-danger">*</span> -->
                                                            </label>
                                                            <input type="text" name="eway_bill_no"
                                                                class="form-control  eway_bill_no"
                                                                placeholder="Enter Eway Bill No.">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Consignment No.
                                                                <!-- <span class="text-danger">*</span> -->
                                                            </label>
                                                            <input type="text" name="consignment_no"
                                                                class="form-control consignment_no"
                                                                placeholder="Enter Consignment No.">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Supplier Invoice No.
                                                            </label>
                                                            <input type="text" name="supplier_invoice_no"
                                                                class="form-control  supplier_invoice_no"
                                                                placeholder="Enter Supplier Invoice No.">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Supplier Invoice Date
                                                                <!-- <span class="text-danger">*</span> -->
                                                            </label>
                                                            <input type="date" name="supplier_invoice_date"
                                                                class="form-control  gate-entry supplier_invoice_date"
                                                                id="datepicker3" placeholder="Enter Supplier Invoice Date">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Transporter Name
                                                            </label>
                                                            <input type="text" name="transporter_name"
                                                                class="form-control  transporter_name"
                                                                placeholder="Enter Transporter Name">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Vehicle No.
                                                                <i class="ml-2 fas fa-info-circle text-primary"
                                                                    data-bs-toggle="tooltip" data-bs-html="true"
                                                                    title="Format:<br>[A-Z]{2} – 2 uppercase letters (e.g., 'MH')<br>[0-9]{2} – 2 digits (e.g., '12')<br>[A-Z]{0,3} – 0 to 3 uppercase letters (e.g., 'AB', 'ABZ')<br>[0-9]{4} – 4 digits (e.g., '1234')"></i>
                                                            </label>
                                                            <input type="text" name="vehicle_no"
                                                                class="form-control vehicle_no"
                                                                placeholder="Enter Vehicle No." />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card" id="item_section">
                                    <div class="card-body customernewsection-form">
                                        <div class="border-bottom mb-2 pb-25">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader">
                                                        <h4 class="card-title text-theme">Item Wise Detail</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-sm-end">
                                                    <!-- <a href="javascript:;" id="deleteBtn" class="btn btn-sm btn-outline-danger me-50">
                                                            <i data-feather="x-circle"></i> Delete
                                                        </a> -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="table-responsive pomrnheadtffotsticky">
                                                    <table id="itemTable"
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"
                                                        data-json-key="components_json" data-row-selector="tr[id^='row_']">
                                                        <thead>
                                                            <tr>
                                                                <th class="customernewsection-form">
                                                                    <div
                                                                        class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input"
                                                                            id="Email">
                                                                        <label class="form-check-label" for="Email"></label>
                                                                    </div>
                                                                </th>
                                                                <th width="150px">Item Code</th>
                                                                <th width="240px">Item Name</th>
                                                                <th>Attributes</th>
                                                                <th>UOM</th>
                                                                <th>Batch</th>
                                                                <th class="text-end">GRN Qty</th>
                                                                <th class="text-end">Inspected Qty</th>
                                                                <th class="text-end">Acpt. Qty</th>
                                                                <th class="text-end">Rej. Qty</th>
                                                                <th width="50px">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrntableselectexcel">
                                                        </tbody>
                                                        <tfoot>
                                                            <tr valign="top">
                                                                <td rowspan="10" colspan="11">
                                                                    <table class="table border" id="itemDetailDisplay">
                                                                        <tr>
                                                                            <td class="p-0">
                                                                                <h6
                                                                                    class="text-dark mb-0 bg-light-primary py-1 px-50">
                                                                                    <strong>Item Details</strong></h6>
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
                                                                <input type="file" name="attachment[]" class="form-control"
                                                                    onchange="addFiles(this,'main_mrn_file_preview')"
                                                                    multiple>
                                                                <span
                                                                    class="text-primary small">{{__("message.attachment_caption")}}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6" style="margin-top:19px;">
                                                            <div class="row" id="main_mrn_file_preview">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Final Remarks</label>
                                                            <textarea type="text" rows="4" name="remarks"
                                                                class="form-control"
                                                                placeholder="Enter Remarks here..."></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div></div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
        <!-- Outstanding MRN Modal  -->
        @include('procurement.inspection.partials.outstanding-mrn-modal')
        <!-- Inspection CHecklist Modal  -->
        @include('procurement.inspection.partials.inspection-checklist-modal')
        <!-- Batch Detail Modal  -->
        @include('procurement.inspection.partials.item-batch-modal')
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
                        <table class="mt-1 table myrequesttablecbox table-striped mrn-order-detail custnewpo-detail">
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
                    <button type="button" {{-- data-bs-dismiss="modal" --}}
                        class="btn btn-primary submitAttributeBtn">Select</button>
                    <!-- <button type="button" data-bs-dismiss="modal" class="btn btn-primary">Select</button> -->
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
@endsection
@section('scripts')
    <script type="text/javascript">
        let actionUrlTax = '{{route("inspection.tax.calculation")}}';
        var qtyChangeUrl = '{{ route("inspection.get.validate-quantity") }}';
    </script>
    <script type="text/javascript" src="{{asset('assets/js/modules/common-datatable.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/modules/common-attr-ui.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/modules/inspection.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/modules/inspection-checklist.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/modules/inspection-item-batch.js')}}"></script>
    <script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
    <script>
        window.onload = function () {
            localStorage.removeItem('selectedMrnIds');
            currentProcessType = null;
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        };
        let currentProcessType = null;
        let tableRowCount = 0;

        $(document).on('change', '#book_id', (e) => {
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
            let actionUrl = '{{route("book.get.doc_no_and_parameters")}}' + '?book_id=' + bookId + '&document_date=' + document_date;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        // console.log('data', data.data);
                        $("#book_code").val(data.data.book_code);
                        if (!data.data.doc.document_number) {
                            $("#document_number").val('');
                        }
                        $("#document_number").val(data.data.doc.document_number);
                        if (data.data.doc.type == 'Manually') {
                            $("#document_number").attr('readonly', false);
                        } else {
                            $("#document_number").attr('readonly', true);
                        }
                        const parameters = data.data.parameters;
                        setServiceParameters(parameters);
                        if (parameters?.tax_required.some(val => val.toLowerCase() === 'yes')) {
                            $("#tax_required").val(parameters?.tax_required[0]);
                        } else {
                            $("#tax_required").val("");
                        }

                    }
                    if (data.status == 404) {
                        $("#book_code").val('');
                        $("#document_number").val('');
                        $("#tax_required").val("");
                        const docDateInput = $("[name='document_date']");
                        docDateInput.removeAttr('min');
                        docDateInput.removeAttr('max');
                        docDateInput.val(new Date().toISOString().split('T')[0]);
                        Swal.fire({
                            title: 'Error!',
                            text: data.message ?? "Please update first reference from service param.",
                            icon: 'error',
                        });
                    }
                });
            });
        }
        /*for trigger on edit cases*/
        setTimeout(() => {
            let bookId = $("#book_id").val();
            getDocNumberByBookId(bookId);
        }, 0);
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
            if (isFeature && isPast) {
                docDateInput.removeAttr('min');
                docDateInput.removeAttr('max');
            }

            /*Reference from*/
            let reference_from_service = parameters.reference_from_service;
            if (reference_from_service.length) {
                let po = '{{\App\Helpers\ConstantHelper::MRN_SERVICE_ALIAS}}';
                if (reference_from_service.includes(po)) {
                    $("#reference_from").removeClass('d-none');
                } else {
                    $("#reference_from").addClass('d-none');
                }
                if (reference_from_service.includes('d')) {
                    $("#addNewItemBtn").removeClass('d-none');
                } else {
                    $("#addNewItemBtn").addClass('d-none');
                }
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: "Please update first reference from service param.",
                    icon: 'error',
                });
                setTimeout(() => {
                    location.href = '{{route("inspection.index")}}';
                }, 1500);
            }
        }

        /*Vendor drop down*/
        function initializeAutocomplete1(selector, type) {
            $(selector).autocomplete({
                minLength: 0,
                source: function (request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type: 'vendor_list'
                        },
                        success: function (data) {
                            response($.map(data, function (item) {
                                return {
                                    id: item.id,
                                    label: item.company_name,
                                    code: item.vendor_code,
                                    addresses: item.addresses
                                };
                            }));
                        },
                        error: function (xhr) {
                            console.error('Error fetching customer data:', xhr.responseText);
                        }
                    });
                },
                select: function (event, ui) {
                    var $input = $(this);
                    var itemName = ui.item.value;
                    var itemId = ui.item.id;
                    var itemCode = ui.item.code;
                    $input.attr('data-name', itemName);
                    $input.val(itemName);
                    $("#vendor_id").val(itemId);
                    $("#vendor_code").val(itemCode);
                    vendorOnChange(itemId);
                    return false;
                },
                change: function (event, ui) {
                    console.log("changess!");
                    if (!ui.item) {
                        $(this).val("");
                        $(this).attr('data-name', '');
                    }
                }
            }).focus(function () {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
        }
        // initializeAutocomplete1("#vendor_name");

        function vendorOnChange(vendorId, type = null, typeId = null) {
            let store_id = $("[name='header_store_id']").val() || '';
            let actionUrl = "{{route('inspection.get.address')}}"
                + '?id=' + vendorId +
                '&store_id=' + store_id +
                '&type=' + type +
                '&typeId=' + typeId;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.data?.currency_exchange?.status == false) {
                        $("#vendor_name").val('');
                        $("#vendor_id").val('');
                        $("#vendor_code").val('');
                        $("#hidden_state_id").val('');
                        $("#hidden_country_id").val('');
                        // $("#vendor_id").trigger('blur');
                        $("select[name='currency_id']").empty().append('<option value="">Select</option>');
                        $("select[name='payment_term_id']").empty().append('<option value="">Select</option>');
                        // $(".shipping_detail").text('-');
                        $(".billing_detail").text('-');
                        Swal.fire({
                            title: 'Error!',
                            text: data.data?.currency_exchange.message,
                            icon: 'error',
                        });
                        return false;
                    }
                    if (data.data.status == 200) {
                        $("#vendor_name").val(data?.data?.vendor?.company_name);
                        $("#vendor_id").val(data?.data?.vendor?.id);
                        $("#vendor_code").val(data?.data?.vendor.vendor_code);
                        let curOption = `<option value="${data.data.currency.id}">${data.data.currency.name}</option>`;
                        let termOption = `<option value="${data.data.paymentTerm.id}">${data.data.paymentTerm.name}</option>`;
                        $('[name="currency_id"]').empty().append(curOption);
                        $('[name="payment_term_id"]').empty().append(termOption);
                        $("#billing_id").val(data.data.vendor_address.id);
                        $(".billing_detail").text(data.data.vendor_address.display_address);
                        $(".delivery_address").text(data.delivery_address.display_address);
                        $(".org_address").text(data.delivery_address.display_address);

                        $("#hidden_state_id").val(data.data.vendor_address.state.id);
                        $("#hidden_country_id").val(data.data.vendor_address.country.id);
                    } else {
                        if (data.data.error_message) {
                            $("#vendor_name").val('');
                            $("#vendor_id").val('');
                            $("#vendor_code").val('');
                            $("#hidden_state_id").val('');
                            $("#hidden_country_id").val('');
                            // $("#vendor_id").trigger('blur');
                            $("select[name='currency_id']").empty().append('<option value="">Select</option>');
                            $("select[name='payment_term_id']").empty().append('<option value="">Select</option>');
                            // $(".shipping_detail").text('-');
                            $(".billing_detail").text('-');
                            Swal.fire({
                                title: 'Error!',
                                text: data.data.error_message,
                                icon: 'error',
                            });
                            return false;
                        }
                    }
                });
            });
        }

        function initializeAutocomplete2(selector, type) {
            $(selector).autocomplete({
                source: function (request, response) {
                    let selectedAllItemIds = [];
                    $("#itemTable tbody [id*='row_']").each(function (index, item) {
                        if (Number($(item).find('[name*="item_id"]').val())) {
                            selectedAllItemIds.push(Number($(item).find('[name*="item_id"]').val()));
                        }
                    });
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type: 'goods_item_list',
                            selectedAllItemIds: JSON.stringify(selectedAllItemIds)
                        },
                        success: function (data) {
                            response($.map(data, function (item) {
                                return {
                                    id: item.id,
                                    label: `${item.item_name} (${item.item_code})`,
                                    code: item.item_code || '',
                                    item_id: item.id,
                                    item_name: item.item_name,
                                    is_inspection: item.is_inspection,
                                    uom_name: item.uom?.name,
                                    uom_id: item.uom_id,
                                    hsn_id: item.hsn?.id,
                                    hsn_code: item.hsn?.code,
                                    alternate_u_o_ms: item.alternate_u_o_ms,
                                };
                            }));
                        },
                        error: function (xhr) {
                            console.error('Error fetching customer data:', xhr.responseText);
                        }
                    });
                },
                minLength: 0,
                select: function (event, ui) {
                    let $input = $(this);
                    let itemCode = ui.item.code;
                    let itemName = ui.item.value;
                    let itemN = ui.item.item_name;
                    let itemId = ui.item.item_id;
                    let uomId = ui.item.uom_id;
                    let uomName = ui.item.uom_name;
                    let hsnId = ui.item.hsn_id;
                    let hsnCode = ui.item.hsn_code;
                    let isInspection = ui.item.is_inspection;
                    $input.attr('data-name', itemName);
                    $input.attr('data-code', itemCode);
                    $input.attr('data-id', itemId);

                    let closestTr = $input.closest('tr');
                    closestTr.find('[name*=item_id]').val(itemId);
                    closestTr.find('[name*=item_code]').val(itemCode);
                    closestTr.find('[name*=item_name]').val(itemN);
                    closestTr.find('[name*=hsn_id]').val(hsnId);
                    closestTr.find('[name*=hsn_code]').val(hsnCode);
                    closestTr.find('[name*=is_inspection]').val(isInspection);
                    closestTr.find("td[id*='itemAttribute_']").html(defautAttrBtn);
                    $input.val(itemCode);
                    let uomOption = `<option value=${uomId}>${uomName}</option>`;
                    if (ui.item?.alternate_u_o_ms) {
                        for (let alterItem of ui.item.alternate_u_o_ms) {
                            uomOption += `<option value="${alterItem.uom_id}" ${alterItem.is_purchasing ? 'selected' : ''}>${alterItem.uom?.name}</option>`;
                        }
                    }
                    closestTr.find('[name*=uom_id]').append(uomOption);
                    closestTr.find('.attributeBtn').trigger('click');
                    setTimeout(() => {
                        if (ui.item.is_attr) {
                            $input.closest('tr').find('.attributeBtn').trigger('click');
                        } else {
                            $input.closest('tr').find('.attributeBtn').trigger('click');
                            $input.closest('tr').find('[name*="[order_qty]"]').val('').focus();
                        }
                    }, 100);

                    getItemDetail(closestTr);
                    return false;
                },
                change: function (event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        // $('#itemId').val('');
                        $(this).attr('data-name', '');
                        $(this).attr('data-code', '');
                    }
                }
            }).focus(function () {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
        }

        /*Delete Row*/
        $(document).on('click', '#deleteBtn', (e) => {
            let itemIds = [];
            $('#itemTable > tbody .form-check-input').each(function () {
                if ($(this).is(":checked")) {
                    itemIds.push($(this).val());
                }
            });
            if (itemIds.length) {
                itemIds.forEach(function (item, index) {
                    let poItemHiddenId = $(`#row_${item}`).find("input[name*='[mrn_item_hidden_ids]']").val();

                    if (poItemHiddenId) {
                        let idsToRemove = poItemHiddenId.split(',');
                        let selectedMrnIds = localStorage.getItem('selectedMrnIds');
                        if (selectedMrnIds) {
                            selectedMrnIds = JSON.parse(selectedMrnIds);
                            let updatedIds = selectedMrnIds.filter(id => !idsToRemove.includes(id));
                            localStorage.setItem('selectedMrnIds', JSON.stringify(updatedIds));
                        }
                    }
                    $(`#row_${item}`).remove();
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: "Please first add & select row item.",
                    icon: 'error',
                });
            }
            if (!$("tr[id*='row_']").length) {
                $("#itemTable > thead .form-check-input").prop('checked', false);
                $("select[name='currency_id']").prop('disabled', false);
                $("select[name='payment_term_id']").prop('disabled', false);
                $(".editAddressBtn").removeClass('d-none');
                // $("#vendor_name").prop('readonly',false);
                $(".header_store_id").prop('disabled', false);
                getLocation();
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
                Swal.fire({
                    title: 'Error!',
                    text: "Please select first item name.",
                    icon: 'error',
                });
            }
        });

        /*For comp attr*/
        function getItemAttribute(itemId, rowCount, selectedAttr, tr) {
            let actionUrl = '{{route("inspection.item.attr")}}' + '?item_id=' + itemId + `&rowCount=${rowCount}&selectedAttr=${selectedAttr}`;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        $("#attribute tbody").empty();
                        $("#attribute table tbody").append(data.data.html);
                        $(tr).find('td:nth-child(2)').find("[name*=attr_name]").remove();
                        $(tr).find('td:nth-child(2)').append(data.data.hiddenHtml);
                        $(tr).find("td[id*='itemAttribute_']").attr('attribute-array', JSON.stringify(data.data.itemAttributeArray));
                        if (data.data.attr) {
                            $("#attribute").modal('show');
                            $(".select2").select2();
                        }
                        qtyEnabledDisabled();
                    }
                });
            });
        }

        // Event listener for Edit Address button click
        $(document).on('click', '.editAddressBtn', (e) => {
            let addressType = $(e.target).closest('a').attr('data-type');
            let vendorId = $("#vendor_id").val();
            let onChange = 0;
            let addressId = addressType === 'shipping' ? $("#shipping_id").val() : $("#billing_id").val();
            let actionUrl = `{{route("inspection.edit.address")}}?type=${addressType}&vendor_id=${vendorId}&address_id=${addressId}&onChange=${onChange}`;
            fetch(actionUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200) {
                        $("#edit-address .modal-dialog").html(data.data.html);
                        $("#address_type").val(addressType);
                        $("#hidden_vendor_id").val(vendorId);
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
            let actionUrl = `{{route("inspection.edit.address")}}?type=${addressType}&vendor_id=${vendorId}&address_id=${addressId}&onChange=${onChange}`;
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
            const countrySelect = $('#country_id');
            fetch('/countries')
                .then(response => response.json())
                .then(data => {
                    countrySelect.empty();
                    countrySelect.append('<option value="">Select Country</option>');
                    data.data.countries.forEach(country => {
                        const isSelected = country.value == selectedAddress.country.id;
                        countrySelect.append(new Option(country.label, country.value, isSelected, isSelected));
                    });
                    if (selectedAddress.country.id) {
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
                                const isSelected = state.value == selectedAddress.state.id;
                                stateSelect.append(new Option(state.label, state.value, isSelected, isSelected));
                            });
                            if (selectedAddress.state.id) {
                                stateSelect.trigger('change');
                            }
                        })
                        .catch(error => console.error('Error fetching states:', error));
                }
            });
            $('#state_id').on('change', function () {
                let stateValue = $(this).val();
                let citySelect = $('#city_id');
                citySelect.empty().append('<option value="">Select City</option>');
                if (stateValue) {
                    fetch(`/cities/${stateValue}`)
                        .then(response => response.json())
                        .then(data => {
                            data.data.cities.forEach(city => {
                                const isSelected = city.value == selectedAddress.city.id;
                                citySelect.append(new Option(city.label, city.value, isSelected, isSelected));
                            });
                        })
                        .catch(error => console.error('Error fetching cities:', error));
                }
            });
            $("#pincode").val(selectedAddress.pincode);
            $("#address").val(selectedAddress.address);
        }

        /*Display item detail*/
        $(document).on('input change focus', '#itemTable tr input ', function (e) {
            let currentTr = e.target.closest('tr');
            getItemDetail(currentTr);
        });

        function getItemDetail(currentTr, type=null) {
            // Normalize to the <tr id="row_X" data-index="X">
            const getVal = (selector) => {
                let el = $(currentTr).find(selector);
                return el.length ? el.val() : '';
            };

            let itemId = getVal("[name*='[item_id]']");
            if (!itemId) return;

            let selectedAttr = [];
            $(currentTr).find("[name*='[attr_name]']").each(function () {
                const val = $(this).val();
                if (val) selectedAttr.push(val);
            });

            let data = {
                item_id: itemId,
                mrn_header_id: getVal("[name*='[mrn_header_id]']"),
                mrn_detail_id: getVal("[name*='[mrn_detail_id]']"),
                remark: getVal("[name*='[remark]']"),
                uom_id: getVal("[name*='[uom_id]']"),
                qty: getVal("[name*='[order_qty]']"),
                selectedAttr: JSON.stringify(selectedAttr),
                type: currentProcessType,
            };

            let actionUrl = '{{ route('inspection.get.itemdetail') }}?' + new URLSearchParams(data).toString();

            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if(data.status == 200) {
                        // Update the modal or display section
                        $("#itemDetailDisplay").html(data.data.html);
                    }
                });
            });
        }


        /*Tbl row highlight*/
        $(document).on('click', '.mrntableselectexcel tr', (e) => {
            $(e.target.closest('tr')).addClass('trselected').siblings().removeClass('trselected');
        });
        $(document).on('keydown', function (e) {
            if (e.which == 38) {
                /*bottom to top*/
                $('.trselected').prev('tr').addClass('trselected').siblings().removeClass('trselected');
            } else if (e.which == 40) {
                /*top to bottom*/
                $('.trselected').next('tr').addClass('trselected').siblings().removeClass('trselected');
            }
            if ($('.trselected').length) {
                // $('html, body').scrollTop($('.trselected').offset().top - 200);
            }
        });

        /* Address Submit */
        $(document).on('click', '.submitAddress', function (e) {
            $('.ajax-validation-error-span').remove();
            e.preventDefault();
            var innerFormData = new FormData();
            $('#edit-address').find('input,textarea,select').each(function () {
                innerFormData.append($(this).attr('name'), $(this).val());
            });
            var method = "POST";
            var url = '{{route("inspection.address.save")}}';
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
                    if (data.status == 200) {
                        let addressType = $("#address_type").val();
                        if (addressType == 'shipping') {
                            $("#shipping_id").val(data.data.new_address.id);
                        } else {
                            $("#billing_id").val(data.data.new_address.id);
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

        /*submit attribute*/
        $(document).on('click', '.submitAttributeBtn', (e) => {
            let rowCount = $("[id*=row_].trselected").attr('data-index');
            $(`[name="components[${rowCount}][order_qty]"]`).focus();
            $("#attribute").modal('hide');
            getItemDetail(rowCount);
        });

        /*Open GRN model*/
        let mrnOrderTable;
        $(document).on('click', '.mrnSelect', (e) => {
            tableRowCount = $('.mrntableselectexcel tr').length;
            $("#mrnModal").modal('show');
            currentProcessType = 'mrn';
            openMrnRequest();
            const tableSelector = '#mrnModal .mrn-order-detail';
            $(tableSelector).DataTable().clear().destroy();
            getMrn();
            if ($(tableSelector).length) {
                if ($.fn.DataTable.isDataTable(tableSelector)) {
                    mrnOrderTable = $(tableSelector).DataTable();
                    mrnOrderTable.ajax.reload();
                }
            }
        });

        function getSelectedMrnTypes() {
            let moduleTypes = [];
            $('.mrn_item_checkbox:checked').each(function () {
                moduleTypes.push($(this).attr('data-module')); // Corrected: Get attribute value instead of setting it
            });
            return moduleTypes;
        }

        function openMrnRequest() {
            initializeAutocompleteQt("vendor_code_input_qt", "vendor_id_qt_val", "vendor_list", "vendor_code", "company_name");
            initializeAutocompleteQt("document_no_input_qt", "document_id_qt_val", "mrn_document_qt", "document_number", "");
            initializeAutocompleteQt("so_no_input_qt", "so_qt_val", "so_qt", "book_code", "document_number");
        }

        function initializeAutocompleteQt(selector, selectorSibling, typeVal, labelKey1, labelKey2 = "") {
            let modalType = '#mrnModal';
            $("#" + selector).autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type: typeVal,
                            vendor_id: $("#vendor_id_qt_val").val(),
                            header_book_id: $("#book_id").val(),
                            store_id: $("#store_id").val() || '',
                        },
                        success: function (data) {
                            response($.map(data, function (item) {
                                // return {
                                //     id: item.id,
                                //     label: `${item[labelKey1]} ${labelKey2 ? (item[labelKey2] ? '(' + item[labelKey2] + ')' : '') : ''}`,
                                //     code: item[labelKey1] || '',
                                // };
                                let label = '';

                                if ('document_number' in item && 'book_code' in item) {
                                    label = `${item.book_code}-${item.document_number}`;
                                } else if ('company_name' in item) {
                                    label = item.company_name;
                                }

                                return {
                                    id: item.id,
                                    label: label,
                                    code: item.book_code || item.vendor_code || '',
                                };
                            }));
                        },
                        error: function (xhr) {
                            console.error('Error fetching customer data:', xhr.responseText);
                        }
                    });
                },
                appendTo: modalType,
                minLength: 0,
                select: function (event, ui) {
                    var $input = $(this);
                    $input.val(ui.item.label);
                    $("#" + selectorSibling).val(ui.item.id);
                    $('#mrnModal .mrn-order-detail').DataTable().ajax.reload();
                    return false;
                },
                change: function (event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#" + selectorSibling).val("");
                        $('#mrnModal .mrn-order-detail').DataTable().ajax.reload();
                    }
                }
            }).focus(function () {
                if (this.value === "") {
                    $("#" + selectorSibling).val("");
                    $('#mrnModal .mrn-order-detail').DataTable().ajax.reload();
                    $(this).autocomplete("search", "");
                }
            }).blur(function () {
                if (this.value === "") {
                    $("#" + selectorSibling).val("");
                    $('#mrnModal .mrn-order-detail').DataTable().ajax.reload();
                }
            })
        }

        function renderData(data) {
            return data ? data : '';
        }

        function getDynamicParams() {
            let document_date = '',
                header_book_id = '',
                series_id = '',
                document_number = '',
                item_id = '',
                vendor_id = '',
                store_id = '',
                so_id = '',
                item_search = '',
                selected_mrn_ids = '';

            if (currentProcessType === 'mrn') {
                let selectedMrnIds = localStorage.getItem('selectedMrnIds') ?? '[]';
                selectedMrnIds = JSON.parse(selectedMrnIds);
                selectedMrnIds = encodeURIComponent(JSON.stringify(selectedMrnIds));
                document_date = $("[name ='document_date']").val() || '',
                    header_book_id = $("#book_id").val() || '',
                    series_id = $("#book_id_qt_val").val() || '',
                    document_number = $("#document_id_qt_val").val() || '',
                    item_id = $("#item_id_qt_val").val() || '',
                    vendor_id = $("#vendor_id_qt_val").val(),
                    store_id = $(".header_store_id").val() || '',
                    sub_store_id = $(".sub_store").val() || '',
                    so_id = $("#po_so_qt_val").val() || '',
                    item_search = $("#item_name_search").length ? $("#item_name_search").val() : '';
                selected_mrn_ids = encodeURIComponent(selectedMrnIds)
            }
            return {
                so_id: so_id,
                type: 'create',
                item_id: item_id,
                store_id: store_id,
                series_id: series_id,
                vendor_id: vendor_id,
                item_search: item_search,
                sub_store_id: sub_store_id,
                document_date: document_date,
                header_book_id: header_book_id,
                document_number: document_number,
                selected_mrn_ids: selected_mrn_ids
            };
        }

        function getMrn() {
            const ajaxUrl = '{{ route("inspection.get.mrn", ["type" => "create"]) }}';
            var columns = [];
            columns = [
                { data: 'id', visible: false, orderable: true, searchable: false },
                { data: 'select_checkbox', name: 'select_checkbox', orderable: false, searchable: false },
                { data: 'vendor', name: 'vendor', render: renderData, orderable: false, searchable: false },
                { data: 'doc_no', name: 'doc_no', render: renderData, orderable: false, searchable: false },
                { data: 'doc_date', name: 'doc_date', render: renderData, orderable: false, searchable: false },
                { data: 'item_code', name: 'item_code', render: renderData, orderable: false, searchable: false },
                { data: 'item_name', name: 'item_name', render: renderData, orderable: false, searchable: false },
                { data: 'attributes', name: 'attributes', render: renderData, orderable: false, searchable: false },
                {
                    data: 'order_qty', name: 'order_qty', render: renderData, orderable: false, searchable: false, createdCell: function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                {
                    data: 'inspection_qty', name: 'inspection_qty', render: renderData, orderable: false, searchable: false, createdCell: function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                {
                    data: 'balance_qty', name: 'balance_qty', render: renderData, orderable: false, searchable: false, createdCell: function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
            ];
            initializeDataTableCustom('#mrnModal .mrn-order-detail',
                ajaxUrl,
                columns,
            );
        }

        $(document).on('keyup', '#item_name_search', (e) => {
            $('#mrnModal .mrn-order-detail').DataTable().ajax.reload();
        });

        /*Checkbox for po/si item list*/
        $(document).on('change', '.mrn-order-detail > thead .form-check-input', (e) => {
            if (e.target.checked) {
                $(".mrn-order-detail > tbody .form-check-input").each(function () {
                    $(this).prop('checked', true);
                });
            } else {
                $(".mrn-order-detail > tbody .form-check-input").each(function () {
                    $(this).prop('checked', false);
                });
            }
        });

        function getSelectedMrnIDS() {
            let ids = [];
            let referenceNos = [];

            $('.mrn_item_checkbox:checked').each(function () {
                ids.push($(this).val());
                referenceNo = $(this).siblings("input[type='hidden'][name='reference_no']").val();
                if (referenceNo) {
                    referenceNos.push(referenceNo);
                }
            });
            return {
                ids: ids,
                referenceNos: referenceNos
            };
        }

        $(document).on('click', '.mrnProcess', (e) => {
            let result = getSelectedMrnIDS();
            let ids = result.ids;
            let referenceNo = result.referenceNos[0];
            let idsLength = ids.length;
            currentProcessType = 'mrn';
            if (!ids.length) {
                $("#mrnModal").modal('hide');
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select at least one one mrn',
                    icon: 'error',
                });
                return false;
            }

            let moduleTypes = getSelectedMrnTypes();
            $("[name='mrn_item_ids']").val(ids);
            $("#addNewItemBtn").hide();
            if (referenceNo) {
                $("#referenceNoDiv").show();
                $("#reference_number_input").val(referenceNo);
            } else {
                $("#referenceNoDiv").hide();
                $("#reference_number_input").val('');
            }
            $("#reference_type_input").val('mrn');

            // for component item code
            function initializeAutocomplete2(selector, type) {
                $(selector).autocomplete({
                    minLength: 0,
                    source: function (request, response) {
                        let selectedAllItemIds = [];
                        $("#itemTable tbody [id*='row_']").each(function (index, item) {
                            if (Number($(item).find('[name*="[item_id]"]').val())) {
                                selectedAllItemIds.push(Number($(item).find('[name*="[item_id]"]').val()));
                            }
                        });
                        $.ajax({
                            url: '/search',
                            method: 'GET',
                            dataType: 'json',
                            data: {
                                q: request.term,
                                type: 'goods_item_list',
                                selectedAllItemIds: JSON.stringify(selectedAllItemIds)
                            },
                            success: function (data) {
                                response($.map(data, function (item) {
                                    return {
                                        id: item.id,
                                        label: `${item.item_name} (${item.item_code})`,
                                        code: item.item_code || '',
                                        item_id: item.id,
                                        item_name: item.item_name,
                                        uom_name: item.uom?.name,
                                        uom_id: item.uom_id,
                                        hsn_id: item.hsn?.id,
                                        hsn_code: item.hsn?.code,
                                        alternate_u_o_ms: item.alternate_u_o_ms,
                                        is_attr: item.item_attributes_count,
                                    };
                                }));
                            },
                            error: function (xhr) {
                                console.error('Error fetching customer data:', xhr.responseText);
                            }
                        });
                    },
                    select: function (event, ui) {
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
                        $input.closest('tr').find('[name*="[item_id]"]').val(itemId);
                        $input.closest('tr').find('[name*=item_code]').val(itemCode);
                        $input.closest('tr').find('[name*=item_name]').val(itemN);
                        $input.closest('tr').find('[name*=hsn_id]').val(hsnId);
                        $input.closest('tr').find('[name*=hsn_code]').val(hsnCode);
                        $input.closest('tr').find("td[id*='itemAttribute_']").html(defautAttrBtn);
                        $input.val(itemCode);
                        let uomOption = `<option value=${uomId}>${uomName}</option>`;
                        if (ui.item?.alternate_u_o_ms) {
                            for (let alterItem of ui.item.alternate_u_o_ms) {
                                uomOption += `<option value="${alterItem.uom_id}" ${alterItem.is_purchasing ? 'selected' : ''}>${alterItem.uom?.name}</option>`;
                            }
                        }
                        $input.closest('tr').find('[name*=uom_id]').append(uomOption);
                        $input.closest('tr').find("input[name*='attr_group_id']").remove();
                        setTimeout(() => {
                            if (ui.item.is_attr) {
                                $input.closest('tr').find('.attributeBtn').trigger('click');
                            } else {
                                $input.closest('tr').find('.attributeBtn').trigger('click');
                                $input.closest('tr').find('[name*="[order_qty]"]').val('').focus();
                            }
                        }, 100);
                        getItemDetail($input.closest('tr'), currentProcessType);
                        return false;
                    },
                    change: function (event, ui) {
                        if (!ui.item) {
                            $(this).val("");
                            // $('#itemId').val('');
                            $(this).attr('data-name', '');
                            $(this).attr('data-code', '');
                        }
                    }
                }).focus(function () {
                    if (this.value === "") {
                        $(this).autocomplete("search", "");
                    }
                });
            }

            let currencyId = $("select[name='currency_id']").val();
            let transactionDate = $("input[name='document_date']").val() || '';
            let groupItems = [];
            $('tr[data-group-item]').each(function () {
                let groupItemData = $(this).data('group-item');
                groupItems.push(groupItemData);
            });

            groupItems = JSON.stringify(groupItems);
            let current_row_count = $("tbody tr[id*='row_']").length;
            let processData = {
                ids: ids,
                type: 'mrn',
                module_type: moduleTypes,
            };

            asnProcess(processData, 'mrn-process');
        });

        function initializeAutocompleteTED(selector, idSelector, nameSelector, type, percentageVal) {
            $("#" + selector).autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type: type,
                        },
                        success: function (data) {
                            response($.map(data, function (item) {
                                return {
                                    id: item.id,
                                    label: `${item.name}`,
                                    percentage: `${item.percentage}`,
                                };
                            }));
                        },
                        error: function (xhr) {
                            console.error('Error fetching customer data:', xhr.responseText);
                        }
                    });
                },
                minLength: 0,
                select: function (event, ui) {
                    var $input = $(this);
                    var itemName = ui.item.label;
                    var itemId = ui.item.id;
                    var itemPercentage = ui.item.percentage;

                    $input.val(itemName);
                    $("#" + idSelector).val(itemId);
                    $("#" + nameSelector).val(itemName);
                    $("#" + percentageVal).val(itemPercentage).trigger('keyup');
                    return false;
                },
                change: function (event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#" + idSelector).val("");
                        $("#" + nameSelector).val("");
                    }
                }
            }).focus(function () {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
        }

        function getLocation(locationId = '') {
            let actionUrl = '{{ route("store.get") }}' + '?location_id=' + locationId;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        let options = '';
                        data.data.locations.forEach(function (location) {
                            options += `<option value="${location.id}">${location.store_code}</option>`;
                        });
                        $("[name='header_store_id']").empty().append(options);
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                        });
                    }
                });
            });
        }

        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
            $("td.dynamic-colspan").attr("colspan", 11);
            $("td.dynamic-summary-colspan").attr("colspan", 10);
        })

        // Clear GRN Process
        $(document).on('click', '.clearMrnFilter', (e) => {
            $("#item_name_input_qt").val('');
            $("#item_id_qt_val").val('');
            $("#store").val('');
            $("#store_id").val('');
            $("#sub_store").val('');
            $("#sub_store_id").val('');
            $("#vendor_code_input_qt").val('');
            $("#vendor_id_qt_val").val('');
            $("#book_code_input_qt").val('');
            $("#book_id_qt_val").val('');
            $("#document_no_input_qt").val('');
            $("#document_id_qt_val").val('');
            $("#so_no_input_qt").val('');
            $("#so_qt_val").val('');
            $("#item_name_search").val('');
            $('#mrnModal .mrn-order-detail').DataTable().ajax.reload();
        });

        $(document).on("autocompletechange autocompleteselect", "#store", function (event, ui) {
            let storeId = ui?.item?.id || '';
            initializeAutocompleteQt("sub_store", "sub_store_id", "sub_store", "name", "");
        });

        // GRN Process
        function asnProcess(asnData, moduleProcess) {
            const current_row_count = $("tbody tr[id*='row_']").length;

            const ids = JSON.stringify(asnData.ids);
            const moduleTypes = JSON.stringify(asnData.module_type);
            const moduleType = asnData.module_type?.[0] ?? 'mrn';
            const processType = asnData.type;
            let idsLength = ids.length;

            const currencyId = $("[name='currency_id']").val();
            const transactionDate = $("[name='document_date']").val();
            const type = $("meta[name='route-type']").attr("content"); // blade->meta

            const baseRoute = '{{ route("inspection.process.mrn-item") }}';
            const actionUrl = baseRoute
                .replace(':type', type)
                + '?ids=' + encodeURIComponent(ids)
                + '&moduleTypes=' + moduleTypes
                + '&tableRowCount=' + tableRowCount
                + '&currency_id=' + encodeURIComponent(currencyId)
                + '&d_date=' + encodeURIComponent(transactionDate)
                + '&current_row_count=' + current_row_count;

            fetch(actionUrl)
                .then(res => res.json())
                .then(data => {
                    if (data.status !== 200) return handleProcessError(data.message);

                    const {
                        vendor,
                        pos,
                        mrnHeader,
                        moduleType
                    } = data.data;

                    const modelType = 'mrn';
                    const order = data.data.mrnHeader;
                    // console.log(vendor?.id, modelType, order.id);
                    $("#reference_type_input").val(modelType);

                    vendorOnChange(vendor?.id, modelType, order.id);

                    const getSelectedIdsFn = getSelectedMrnIDS;
                    const hiddenFieldName = 'mrn_item_ids';
                    const localStorageKey = 'selectedMrnIds';

                    const newIds = getSelectedIdsFn().ids;
                    const existingIds = JSON.parse(localStorage.getItem(localStorageKey) || '[]');
                    const mergedIds = Array.from(new Set([...existingIds, ...newIds]));
                    localStorage.setItem(localStorageKey, JSON.stringify(mergedIds));
                    $(`[name='${hiddenFieldName}']`).val(mergedIds.join(','));

                    if ($("#itemTable .mrntableselectexcel").find("tr[id*='row_']").length) {
                        $("#itemTable .mrntableselectexcel tr[id*='row_']:last").after(pos);
                    } else {
                        $("#itemTable .mrntableselectexcel").empty().append(pos);
                    }
                    initializeAutocomplete2(".comp_item_code");
                    $("#mrnModal").modal('hide');
                    // focusAndScrollToLastRowInput();

                    // UI Locks
                    $("select[name='currency_id'], select[name='payment_term_id']").prop('disabled', true);
                    $("#vendor_name").prop('readonly', true);
                    $(".editAddressBtn").addClass('d-none');

                    if (feather) {
                        feather.replace({
                            width: 14,
                            height: 14
                        });
                    }

                    // Supplier details

                    if (mrnHeader) {
                        $("[name='gate_entry_no']").val(mrnHeader.gate_entry_no);
                        $("[name='gate_entry_date']").val(mrnHeader.gate_entry_date);
                        $("[name='supplier_invoice_no']").val(mrnHeader.supplier_invoice_no);
                        $("[name='supplier_invoice_date']").val(mrnHeader.supplier_invoice_date);
                        $("[name='consignment_no']").val(mrnHeader.consignment_no);
                        $("[name='eway_bill_no']").val(mrnHeader.eway_bill_no);
                        $("[name='transporter_name']").val(mrnHeader.transporter_name);
                        $("[name='vehicle_no']").val(mrnHeader.vehicle_no);
                    } else {
                        $("[name='supplier_invoice_no'], [name='supplier_invoice_date'], [name='consignment_no'], [name='eway_bill_no'], [name='transporter_name'], [name='vehicle_no']").val('');
                    }
                    $("#reference_type_input").val(modelType);

                    setTimeout(() => {
                        if (idsLength > 1) {
                            $("#itemTable .mrntableselectexcel tr").each(function (index, item) {
                                if (tableRowCount > 0) {
                                    currentIndex = tableRowCount + 1;
                                }
                                let currentIndex = index + 1;
                                setAttributesUIHelper(currentIndex, "#itemTable");
                            });
                        }
                        currentIndex = tableRowCount + 1;
                        setAttributesUIHelper(currentIndex, "#itemTable");
                    }, 500);
                })
                .catch(() => {
                    Swal.fire({
                        title: 'Error!',
                        text: 'An unexpected error occurred while processing GRN.',
                        icon: 'error'
                    });
                });
        }

        function handleProcessError(message = 'Invalid data') {
            // console.log('message', message);
            $(".editAddressBtn").removeClass('d-none');
            $("#vendor_name").val('').prop('readonly', false);
            $("#vendor_id, #vendor_code, #hidden_state_id, #hidden_country_id").val('');
            $("select[name='currency_id'], select[name='payment_term_id']").prop('readonly', false).html('<option value="">Select</option>');
            $(".shipping_detail, .billing_detail").text('-');
            $("#reference_from").removeClass('d-none');
            $('.asn_process').prop('disabled', false);
            $("#reference_type_input").val('');

            Swal.fire({
                title: 'Error!',
                text: message,
                icon: 'error'
            });
        }
    </script>
@endsection