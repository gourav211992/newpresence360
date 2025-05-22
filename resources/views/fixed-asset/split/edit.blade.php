@extends('layouts.app')

@section('content')
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Split</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">Edit</li>


                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <a href="{{ route('finance.fixed-asset.split.index') }}"> <button
                                    class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</button>
                            </a>
                            @if($data->document_status=='draft')
                            <button class="btn btn-outline-primary btn-sm mb-50 mb-sm-0" type="button" id="save-draft-btn">
                                <i data-feather="save"></i> Save as Draft
                            </button>
                         
                            <button type="submit" form="fixed-asset-split-form" class="btn btn-primary btn-sm"
                                id="submit-btn">
                                <i data-feather="check-circle"></i> Submit
                            </button>
                            @endif
                         
                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">



                <section id="basic-datatable">
                    <div class="row">
                        <form id="fixed-asset-split-form" method="POST"
                            action="{{ route('finance.fixed-asset.split.update',$data->id) }}" enctype="multipart/form-data">

                            @csrf
                            @method('PUT')

                            <input type="hidden" name="sub_assets" value="{{$data->sub_assets}}" id="sub_assets">
                            <input type="hidden" name="doc_number_type" id="doc_number_type" value="{{$data->doc_number_type}}">
                            <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern" value="{{$data->doc_reset_pattern}}">
                            <input type="hidden" name="doc_prefix" id="doc_prefix" value="{{$data->doc_prefix}}">
                            <input type="hidden" name="doc_suffix" id="doc_suffix" value="{{$data->doc_suffix}}">
                            <input type="hidden" name="doc_no" id="doc_no" value="{{$data->doc_no}}">
                            <input type="hidden" name="document_status" id="document_status" value="">
                            <input type="hidden" name="dep_type" id="depreciation_type" value="{{$dep_type}}">
                            
                            <div class="col-12">


                                <div class="card">
                                    <div class="card-body customernewsection-form">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="newheader border-bottom mb-2 pb-25  ">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h4 class="card-title text-theme">Basic Information</h4>
                                                            <p class="card-text">Fill the details</p>
                                                        </div>


                                                        <div class="col-md-6 text-sm-end" hidden>
                                                            <span
                                                                class="badge rounded-pill badge-light-secondary forminnerstatus">
                                                                Status : <span class="text-success">Approved</span>
                                                            </span>
                                                        </div>

                                                    </div>
                                                </div>

                                            </div>




                                            <div class="col-md-8">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="book_id">Series <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" id="book_id" name="book_id" required disabled>
                                                            @foreach ($series as $book)
                                                            <option value="{{ $book->id }}" {{ isset($data) && $data->book_id == $book->id ? 'selected' : '' }}>
                                                                {{ $book->book_code }}
                                                            </option>
                                                        @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                              

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="document_number">Doc No <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" id="document_number" readonly value="{{$data->document_number}}"
                                                            name="document_number" required>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="document_date">Doc Date <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" class="form-control" id="document_date" 
                                                            name="document_date" value="{{$data->document_date}}" required>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Location <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select id="location" class="form-select"
                                                            name="location_id" required>
                                                            @foreach ($locations as $location)
                                                                <option value="{{ $location->id }}" {{$data->location_id==$location->id?"selected":""}}>
                                                                    {{ $location->store_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                </div>
                                                <div class="row align-items-center mb-1 cost_center">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Cost Center <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select id="cost_center" class="form-select"
                                                            name="cost_center_id" required>
                                                        </select>
                                                    </div>

                                                </div>


                                            </div>


                                            <div class="col-md-4">

                                                {{-- History Code --}}

                                            </div>

                                        </div>
                                    </div>
                                </div>


                                <div class="row customernewsection-form">
                                    <div class="col-md-12">
                                        <div class="card quation-card">
                                            <div class="card-header newheader">
                                                <div>
                                                    <h4 class="card-title">Old Asset Details</h4>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                     <!-- Asset Code & Name -->
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="asset_id">Asset Code &
                                                                Name <span class="text-danger">*</span></label>
                                                            <input type="text" id="asset_search_input" value="{{$data?->asset?->asset_code."(".$data?->asset?->asset_name.")"}}" class="form-control">
                                                            <input type="hidden" id="asset_id" name="asset_id" value="{{$data->asset_id}}">
                                                        </div>
                                                    </div>

                                                    <!-- Sub-Asset Code -->
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="sub_asset_id">Sub-Asset Code
                                                                <span class="text-danger">*</span></label>
                                                          <input type="text" id="subasset_search_input"  class="form-control" value="{{$data?->subAsset?->sub_asset_code}}">
                                                            <input type="hidden" id="sub_asset_id" name="sub_asset_id" value="{{$data->sub_asset_id}}">
                                                          </div>
                                                    </div>


                                                    <!-- Last Date of Dep. -->
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="last_dep_date">Last Date of
                                                                Dep. </label>
                                                            <input type="date" id="last_dep_date" value="{{$data->asset->capitalize_date!=$data->asset->last_dep_date?$data->asset->last_dep_date:""}}" 
                                                            name="last_dep_date"
                                                                class="form-control" readonly/>
                                                        </div>
                                                    </div>

                                                    <!-- Current Value -->
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="current_value_asset">Current Value
                                                                <span class="text-danger">*</span></label>
                                                            <input type="text" id="current_value_asset" value="{{$data->subAsset->current_value_after_dep}}" name="current_value_asset"
                                                                class="form-control" disabled required />
                                                        </div>
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
                                                        <h4 class="card-title text-theme">New Asset Detail</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-sm-end">
                                                    <a href="#" id="delete_new_sub_asset"
                                                        class="btn btn-sm btn-outline-danger me-50">
                                                        <i data-feather="x-circle"></i> Delete</a>
                                                    <a href="#" id= "add_new_sub_asset"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i data-feather="plus"></i> Add New</a>
                                                </div>
                                            </div>
                                        </div>





                                        <div class="row">

                                            <div class="col-md-12">


                                                <div class="table-responsive pomrnheadtffotsticky">
                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                        <thead>
                                                            <tr>
                                                                <th class="customernewsection-form">
                                                                    <div
                                                                        class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input"
                                                                            id="Email">
                                                                        <label class="form-check-label"
                                                                            for="Email"></label>
                                                                    </div>
                                                                </th>
                                                                <th width="200">Asset Code</th>
                                                                <th>Asset Name</th>
                                                                <th width="200">Sub Asset Code</th>
                                                                <th width="100">Quantity</th>
                                                                <th class="text-end">Current Value</th>
                                                                <th class="text-end">Salvage Value</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrntableselectexcel">
                                                            @foreach(json_decode($data->sub_assets) as $item)
                                                            <tr class="trselected">
                                                                <td class="customernewsection-form">
                                                                    <div class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input row-check">
                                                                        <label class="form-check-label"></label>
                                                                    </div>
                                                                </td>
                                                                <td class="poprod-decpt">
                                                                    <input type="text" required placeholder="Enter" value="{{$item->asset_code}}" class="form-control mw-100 mb-25 asset-code-input" />
                                                                </td>
                                                                <td class="poprod-decpt">
                                                                    <input type="text" required placeholder="Enter" value="{{$item->asset_name}}" class="form-control mw-100 mb-25 asset-name-input" />
                                                                </td>
                                                                <td class="poprod-decpt">
                                                                    <input type="text" required placeholder="Enter" disabled value="{{$item->sub_asset_id}}" class="form-control mw-100 mb-25 sub-asset-code-input" />
                                                                </td>
                                                                <td>
                                                                    <input type="text" required disabled value="1" class="form-control mw-100 quantity-input" />
                                                                </td>
                                                                <td>
                                                                    <input type="text" required value="{{$item->current_value}}" class="form-control mw-100 text-end current-value-input" max="${Current}" min="1" />
                                                                </td>
                                                                <td>
                                                                    <input type="text" required value="{{$item->salvage_value??""}}" class="form-control mw-100 text-end salvage-value-input" min="1" readonly />
                                                                </td>
                                                            </tr>
                                                            @endforeach

                                                        </tbody>


                                                    </table>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>



                                <div class="row customernewsection-form">
                                    <div class="col-md-12">
                                        <div class="card quation-card">
                                            <div class="card-header newheader">
                                                <div>
                                                    <h4 class="card-title">Asset Details</h4>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Category <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select select2" name="category_id"
                                                                id="category" required>
                                                                <option value="">Select</option>
                                                                @foreach ($categories as $category)
                                                                    <option value="{{ $category->id }}"
                                                                        {{ $data->category_id == $category->id ? 'selected' : '' }}>
                                                                        {{ $category->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>




                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Quantity <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="quantity"
                                                                id="quantity" value="{{ $data->quantity }}" readonly />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Ledger <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select select2" name="ledger_id"
                                                                id="ledger" required>
                                                                <option value="">Select</option>
                                                                @foreach ($ledgers as $ledger)
                                                                    <option value="{{ $ledger->id }}"
                                                                        {{ $data->ledger_id == $ledger->id ? 'selected' : '' }}>
                                                                        {{ $ledger->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>

                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Ledger Group <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select select2" name="ledger_group_id"
                                                                id="ledger_group" required>
                                                                <option value="{{$data->ledger_group_id}}">{{$data->ledgerGroup->name}}</option> 
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Capitalize Date <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="date" class="form-control"
                                                                name="capitalize_date" id="capitalize_date" readonly
                                                                value="{{ $data->capitalize_date }}" required />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Maint. Schedule <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select" name="maintenance_schedule"
                                                                id="maintenance_schedule" required>
                                                                <option value=""
                                                                    {{ $data->maintenance_schedule == '' ? 'selected' : '' }}>
                                                                    Select</option>
                                                                <option value="weekly"
                                                                    {{ $data->maintenance_schedule == 'weekly' ? 'selected' : '' }}>
                                                                    Weekly</option>
                                                                <option value="monthly"
                                                                    {{ $data->maintenance_schedule == 'monthly' ? 'selected' : '' }}>
                                                                    Monthly</option>
                                                                <option value="quarterly"
                                                                    {{ $data->maintenance_schedule == 'quarterly' ? 'selected' : '' }}>
                                                                    Quarterly</option>
                                                                <option value="semi-annually"
                                                                    {{ $data->maintenance_schedule == 'semi-annually' ? 'selected' : '' }}>
                                                                    Semi-Annually</option>
                                                                <option value="annually"
                                                                    {{ $data->maintenance_schedule == 'annually' ? 'selected' : '' }}>
                                                                    Annually</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Dep. Method <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" name="depreciation_method"
                                                                id="depreciation_method" class="form-control"
                                                                value="{{ $data->depreciation_method }}" readonly />
                                                        </div>
                                                    </div>


                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Est. Useful Life (yrs) <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="useful_life"
                                                                id="useful_life" value="{{ $data->useful_life }}"
                                                                oninput="updateDepreciationValues()" required />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Salvage Value <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control"
                                                                name="salvage_value" id="salvage_value" readonly
                                                                value="{{ $data->salvage_value }}" required />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Dep % <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="number" class="form-control"
                                                                id="depreciation_rate" value="{{$data->depreciation_percentage}}" name="depreciation_percentage"
                                                                readonly />
                                                            <input type="hidden" value="{{ $dep_percentage }}"
                                                                id="depreciation_percentage" />
                                                            <input type="hidden" id="depreciation_rate_year"
                                                                name="depreciation_percentage_year" />

                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Total Dep. <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="number" id="total_depreciation"
                                                                name="total_depreciation" class="form-control"
                                                                value="0" readonly />
                                                        </div>
                                                    </div>




                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Current Value <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" required
                                                                name="current_value" id="current_value"
                                                                value="{{ $data->current_value }}" readonly />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>





                            </div>
                        </form>
                    </div>
                    <!-- Modal to add new record -->

                </section>


            </div>
        </div>
    </div>
    <!-- END: Content-->

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>



    <div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1"
        aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body alertmsg text-center warning">
                    <i data-feather='alert-circle'></i>
                    <h2>Are you sure?</h2>
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>MRN</strong>? After Amendment
                        this action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Confirm</button>
                </div>
            </div>
        </div>
    </div>
@endsection




@section('scripts')
    <script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })



        $(".mrntableselectexcel tr").click(function() {
            $(this).addClass('trselected').siblings().removeClass('trselected');
            value = $(this).find('td:first').html();
        });

        $(document).on('keydown', function(e) {
            if (e.which == 38) {
                $('.trselected').prev('tr').addClass('trselected').siblings().removeClass('trselected');
            } else if (e.which == 40) {
                $('.trselected').next('tr').addClass('trselected').siblings().removeClass('trselected');
            }
            $('.mrntableselectexcel').scrollTop($('.trselected').offset().top - 40);
        });

        $('#add_new_sub_asset').on('click', function() {
            const subAssetCode = $('#sub_asset_id').val();
            genereateSubAssetRow(subAssetCode);
        });

        function genereateSubAssetRow(code) {
            let Current = $('#current_value_asset').val();
            let subAssetId = $('#sub_asset_id').val();
            let assetId = $('#asset_id').val();
            let newRow = '';
            newRow = `
                <tr class="trselected">
                <td class="customernewsection-form">
                    <div class="form-check form-check-primary custom-checkbox">
                        <input type="checkbox" class="form-check-input row-check">
                        <label class="form-check-label"></label>
                    </div>
                </td>
                <td class="poprod-decpt">
                    <input type="text" required placeholder="Enter" class="form-control mw-100 mb-25 asset-code-input" />
                </td>
                <td class="poprod-decpt">
                    <input type="text" required placeholder="Enter" class="form-control mw-100 mb-25 asset-name-input" />
                </td>
                <td class="poprod-decpt">
                    <input type="text" required placeholder="Enter" disabled class="form-control mw-100 mb-25 sub-asset-code-input" />
                </td>
                <td>
                    <input type="text" required disabled value="1" class="form-control mw-100 quantity-input" />
                </td>
                <td>
                    <input type="text" required class="form-control mw-100 text-end current-value-input" max="${Current}" min="1" />
                </td>
                 <td>
                                                                    <input type="text" required class="form-control mw-100 text-end salvage-value-input" min="1" readonly />
                                                                </td>
            </tr>


                `;
                    $(".mrntableselectexcel tr").removeClass('trselected');
                    $('.mrntableselectexcel').append(newRow);
                    updateSubAssetCodes();
        }


         $('#delete_new_sub_asset').on('click', function() {
    let totalRows = $('.mrntableselectexcel tr').length;
    let checkedRows = $('.mrntableselectexcel tr .row-check:checked').length;
    console.log(totalRows, checkedRows);

    if ((totalRows - checkedRows) < 1) {
        showToast('warning','At least one row must remain.');
        return;
    }

    $('.mrntableselectexcel .row-check:checked').closest('tr').remove();
    updateSubAssetCodes();
});

        function resetParametersDependentElements(data) {
            let backDateAllowed = false;
            let futureDateAllowed = false;

            if (data != null) {
                console.log(data.parameters.back_date_allowed);
                if (Array.isArray(data?.parameters?.back_date_allowed)) {
                    for (let i = 0; i < data.parameters.back_date_allowed.length; i++) {
                        if (data.parameters.back_date_allowed[i].trim().toLowerCase() === "yes") {
                            backDateAllowed = true;
                            break; // Exit the loop once we find "yes"
                        }
                    }
                }
                if (Array.isArray(data?.parameters?.future_date_allowed)) {
                    for (let i = 0; i < data.parameters.future_date_allowed.length; i++) {
                        if (data.parameters.future_date_allowed[i].trim().toLowerCase() === "yes") {
                            futureDateAllowed = true;
                            break; // Exit the loop once we find "yes"
                        }
                    }
                }
                //console.log(backDateAllowed, futureDateAllowed);

            }

            const dateInput = document.getElementById("document_date");

            // Determine the max and min values for the date input
            const today = moment().format("YYYY-MM-DD");

            if (backDateAllowed && futureDateAllowed) {
                dateInput.setAttribute("min","{{$financialStartDate}}");
                dateInput.setAttribute("max","{{$financialEndDate}}");
            } else if (backDateAllowed) {
                dateInput.setAttribute("max", today);
                dateInput.setAttribute("min","{{$financialStartDate}}");
            } else if (futureDateAllowed) {
                dateInput.setAttribute("min", today);
                dateInput.setAttribute("max","{{$financialEndDate}}");
            } else {
                dateInput.setAttribute("min", today);
                dateInput.setAttribute("max", today);
            
            }
        }

        $('#book_id').on('change', function() {
            resetParametersDependentElements(null);
            let currentDate = new Date().toISOString().split('T')[0];
            let document_date = $('#document_date').val();
            let bookId = $('#book_id').val();
            let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId +
                "&document_date=" + document_date;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        resetParametersDependentElements(data.data);
                      
                    }
                    
                });
            });
        });
        $('#book_id').trigger('change');
        document.getElementById('save-draft-btn').addEventListener('click', function() {
            document.getElementById('document_status').value = 'draft';
            collectSubAssetDataToJson();

    let currentValueAsset = parseFloat($('#current_value_asset').val()) || 0;
    let totalCurrentValue = parseFloat($('#current_value').val()) || 0;

    if (totalCurrentValue > currentValueAsset) {
        showToast('error', 'Total Current Value cannot be greater than Asset Current Value.');
        return false;
    }
    else if (totalCurrentValue <= 0) {
        showToast('error', 'Total Current Value must be greater than 0.');
        return false;
    }


            document.getElementById('fixed-asset-split-form').submit();
        });


        $('#fixed-asset-split-form').on('submit', function(e) {
    e.preventDefault(); // Always prevent default first

    collectSubAssetDataToJson();
    document.getElementById('document_status').value = 'submitted';

    let currentValueAsset = parseFloat($('#current_value_asset').val()) || 0;
    let totalCurrentValue = parseFloat($('#current_value').val()) || 0;

    if (totalCurrentValue > currentValueAsset) {
        showToast('error', 'Total Current Value cannot be greater than Asset Current Value.');
        return false;
    }
    else if (totalCurrentValue <= 0) {
        showToast('error', 'Total Current Value must be greater than 0.');
        return false;
    }

    // Submit form manually if validation passes
    this.submit();
});


        $(document).ready(function() {
            $('.select2').select2();
            updateDepreciationValues();
          
               $(document).ready(function() {
    
                $("#asset_search_input").autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            url: '{{ route("finance.fixed-asset.asset-search") }}',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                q: request.term,
                                split:"{{$data->id}}",
                            },
                            success: function (data) {
                                response(data.map(function (item) {
                                    return {
                                        label: item.asset_code + ' (' + item.asset_name + ')',
                                        value: item.id,
                                    };
                                }));
                            },
                            error: function () {
                                response([]);
                            }
                        });
                    },
                    minLength: 0,
                    select: function (event, ui) {
                        const asset = ui.item.asset;

                        // Set the input box and hidden ID field
                        $(this).val(ui.item.label);
                        $('#asset_id').val(ui.item.value);
                         $('#subasset_search_input').val('');
                        $('#sub_asset_id').val('');
                            $('#last_dep_date').val('');
                            $('#current_value_asset').val('');
                           
                        add_blank();

                        return false; // Prevent default behavior
                    },
                    change: function (event, ui) {
                        if (!ui.item) {
                            $(this).val('');
                            $('#asset_id').val('');
                            $('#subasset_search_input').val('');
                            $('#sub_asset_id').val('');
                            $('#last_dep_date').val('');
                            $('#current_value_asset').val('');
                            add_blank();

                        }
                    },
                    focus: function (event, ui) {
                        return false; // Prevent default behavior
                    }
                }).focus(function () {
                    if (this.value === '') {
                        $(this).autocomplete('search');
                    }
                });
                $("#subasset_search_input").autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            url: '{{ route("finance.fixed-asset.sub_asset_search") }}',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                id:$('#asset_id').val(),
                                split:"{{$data->id}}",
                                q: request.term
                            },
                            success: function (data) {
                                response(data.map(function (item) {
                                    return {
                                        label: item.sub_asset_code,
                                        value: item.id,
                                        asset: item.asset,
                                        sub_asset:item
                                    };
                                }));
                            },
                            error: function () {
                                response([]);
                            }
                        });
                    },
                    minLength: 0,
                    select: function (event, ui) {
                        const asset = ui.item.asset;
                        const sub_asset = ui.item.sub_asset

                        // Set the input box and hidden ID field
                        $(this).val(ui.item.label);
                        $('#sub_asset_id').val(ui.item.value);
                        console.log(asset);

                        // Fill other fields directly
                        $('#category').val(asset.category_id).trigger('change');
                        $('#ledger').val(asset.ledger_id).trigger('change');
                        $('#ledger_group').val(asset.ledger_group_id).trigger('change');
                        $('#last_dep_date').val("");

                        // Handle depreciation date
                        if (asset.last_dep_date !== asset.capitalize_date) {
                            let lastDepDate = new Date(asset.last_dep_date);
                            lastDepDate.setDate(lastDepDate.getDate() - 1);
                            let formattedDate = lastDepDate.toISOString().split('T')[0];
                            $('#last_dep_date').val(formattedDate);
                        }

                        $('#capitalize_date').val(asset.last_dep_date);
                        $('#depreciation_rate').val(asset.depreciation_percentage);
                        $('#depreciation_rate_year').val(asset.depreciation_percentage_year);
                        $('#useful_life').val(asset.useful_life);
                        $('#maintenance_schedule').val(asset.maintenance_schedule);
                        $('#current_value_asset').val(sub_asset.current_value_after_dep);
                        $('#total_depreciation').val(sub_asset.total_depreciation);
                        add_blank();

                        return false; // Prevent default behavior
                    },
                    change: function (event, ui) {
                        if (!ui.item) {
                            $(this).val('');
                            $('#current_value_asset').val("");
                            $('#last_dep_date').val("");
                            $('#sub_asset_id').val('');
                            $('#category').val("");
                            $('#ledger').val("");
                            $('#ledger_group').val("");
                            $('#capitalize_date').val("");
                            $('#depreciation_rate').val("");
                            $('#depreciation_rate_year').val("");
                            $('#useful_life').val("");
                            $('#maintenance_schedule').val("");
                            $('#current_value_asset').val("");
                            $('#total_depreciation').val("");
                       
                            add_blank();

                        }
                    },
                    focus: function (event, ui) {
                        return false; // Prevent default behavior
                    }
                }).focus(function () {
                    if (this.value === '') {
                        $(this).autocomplete('search');
                    }
                });

           
        });
        });

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
            showToast('error',
                "@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach"
            );
        @endif

        // Function to update sub-asset codes based on current asset codes in all rows
        function updateSubAssetCodes() {
    const assetCodeCounts = {};
    const assetCodeToName = {}; // Store the first encountered name for each asset code

    let totalQuantity = 0;
    let totalCurrentValue = 0;
    let totalSalvageValue = 0;
    let depreciationPercentage = parseFloat(document.getElementById("depreciation_percentage").value) || 0;


    $('.mrntableselectexcel tr').each(function() {
        const $row = $(this);

        const assetCode = $row.find('.asset-code-input').val().trim();
        const $assetNameInput = $row.find('.asset-name-input');
        const $subAssetInput = $row.find('.sub-asset-code-input');
        const $salvageValueInput = $row.find('.salvage-value-input');

        const quantity = parseFloat($row.find('.quantity-input').val()) || 0;
        const currentValue = parseFloat($row.find('.current-value-input').val()) || 0;

        const salvageValue = (currentValue * (depreciationPercentage / 100)).toFixed(2);
        $salvageValueInput.val(salvageValue);
      
        if (assetCode !== '') {
            // Count sub-assets per asset code
            assetCodeCounts[assetCode] = (assetCodeCounts[assetCode] || 0) + 1;
            const subAssetCode = `${assetCode}-${String(assetCodeCounts[assetCode]).padStart(2, '0')}`;
            $subAssetInput.val(subAssetCode);

            // Handle asset name consistency
            const currentAssetName = $assetNameInput.val().trim();

            if (!assetCodeToName[assetCode] && currentAssetName !== '') {
                // First time seeing this asset code — store its name
                assetCodeToName[assetCode] = currentAssetName;
            } else if (assetCodeToName[assetCode]) {
                // Set name from previously stored value
                $assetNameInput.val(assetCodeToName[assetCode]);
            }
        } else {
            $subAssetInput.val('');
        }

        // Accumulate totals
        totalSalvageValue+=parseFloat(salvageValue);
        totalQuantity += quantity;
        totalCurrentValue += currentValue;
    });

    $('#quantity').val(totalQuantity);
    
    let currentValueAsset = parseFloat($('#current_value_asset').val()) || 0;
    if (totalCurrentValue > currentValueAsset) {
        showToast('error', 'Total Current Value cannot be greater than Asset Current Value.');
    }

    $('#current_value').val(totalCurrentValue.toFixed(2));
    $('#salvage_value').val(totalSalvageValue.toFixed(2));
    updateDepreciationValues();
   
}
        $('#ledger').change(function() {
            let groupDropdown = $('#ledger_group');
            $.ajax({
                url: '{{ route('finance.fixed-asset.getLedgerGroups') }}',
                method: 'GET',
                data: {
                    ledger_id: $(this).val(),
                    _token: $('meta[name="csrf-token"]').attr(
                        'content') // CSRF token
                },
                success: function(response) {
                    groupDropdown.empty(); // Clear previous options

                    response.forEach(item => {
                        groupDropdown.append(
                            `<option value="${item.id}">${item.name}</option>`
                        );
                    });

                },
                error: function() {
                    showToast('error', 'Error fetching group items.');
                }
            });

        });
        $('#category').on('change', function() {

            var category_id = $(this).val();
            if (category_id) {
                $.ajax({
                    type: "GET",
                    url: "{{ route('finance.fixed-asset.setup.category') }}?category_id=" + category_id,
                    success: function(res) {
                        if (res) {
                            $('#ledger').val(res.ledger_id).select2();
                            $('#ledger').trigger('change');
                            $('#ledger_group').val(res.ledger_group_id).select2();
                            $('#maintenance_schedule').val(res.maintenance_schedule);
                            $('#useful_life').val(res.expected_life_years);
                        }
                    }
                });
            }
        });

        function collectSubAssetDataToJson() {
            const subAssetData = [];

            $('.mrntableselectexcel tr').each(function() {
                const $row = $(this);

                const assetCode = $row.find('.asset-code-input').val()?.trim() || '';
                const assetName = $row.find('td:eq(2) input').val()?.trim() || '';
                const subAssetCode = $row.find('.sub-asset-code-input').val()?.trim() || '';
                const quantity = parseFloat($row.find('.quantity-input').val()) || 0;
                const currentValue = parseFloat($row.find('.current-value-input').val()) || 0;
                const salvageValue = parseFloat($row.find('.salvage-value-input').val()) || 0;
               
                if (assetCode !== '') {
                    subAssetData.push({
                        asset_code: assetCode,
                        asset_name: assetName,
                        sub_asset_id: subAssetCode,
                        quantity: quantity,
                        current_value: currentValue,
                        salvage_value:salvageValue,
                    });
                }
            });

            $('#sub_assets').val(JSON.stringify(subAssetData));
        }
        function updateDepreciationValues() {
    let depreciationType = document.getElementById("depreciation_type").value;
    let currentValue = parseFloat(document.getElementById("current_value").value) || 0;
    let depreciationPercentage = parseFloat(document.getElementById("depreciation_percentage").value) || 0;
    let usefulLife = parseFloat(document.getElementById("useful_life").value) || 0;
    let method = document.getElementById("depreciation_method").value;

    // Ensure all required values are provided
    if (!depreciationType || !currentValue || !depreciationPercentage || !usefulLife || !method) {
        return;
    }
    

    // Determine financial date based on depreciation type
    let financialDate;
    let financialEnd = new Date("{{$financialEndDate}}");
    
    
    // Extract the financial year-end month and day
    let financialEndMonth = financialEnd.getMonth(); 
    let financialEndDay = financialEnd.getDate();
    let devidend = 1; 

    switch (depreciationType) {
       case 'half_yearly':
            devidend = 2; // Adjust dividend for half-yearly
            break;

        case 'quarterly':
            devidend = 4; // Adjust dividend for quarterly
            break;

        case 'monthly':
            devidend = 12; // Adjust dividend for monthly
            break;

    }

    let salvageValue = (parseFloat($('#salvage_value').val())).toFixed(2);

    let depreciationRate = 0;
    if (method === "SLM") {
        depreciationRate = ((((currentValue - salvageValue) / usefulLife) / currentValue)*100).toFixed(2);
    } else if (method === "WDV") {
        depreciationRate = ((1 - Math.pow(salvageValue / currentValue, 1 / usefulLife))*100).toFixed(2);
    }

    let totalDepreciation = 0;
    document.getElementById("salvage_value").value = salvageValue;
    console.log("dep_rate"+depreciationRate+"devidend"+devidend);
    document.getElementById("depreciation_rate").value = depreciationRate;
    document.getElementById("depreciation_rate_year").value = depreciationRate;
    document.getElementById("total_depreciation").value = totalDepreciation;
}
 
        $(document).on('input change', '.asset-code-input,.asset-name-input, .quantity-input, .current-value-input', updateSubAssetCodes);
        $('#location').on('change', function () {
    var locationId = $(this).val();

    if (locationId) {
        // Build the route manually
        var url = '{{ route("cost-center.get-cost-center", ":id") }}'.replace(':id', locationId);
        var selectedCostCenterId = '{{ $data->cost_center_id ?? '' }}'; // Use null coalescing for safety

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if(data.length==0){
                    $('#cost_center').empty(); 
                $('#cost_center').prop('required', false);
                $('.cost_center').hide();
                }
                else{
                    $('.cost_center').show();
                    $('#cost_center').prop('required', true);
                $('#cost_center').empty(); // Clear previous options
                $.each(data, function (key, value) {
                        let selected = (value.id == selectedCostCenterId) ? 'selected' : '';
                        $('#cost_center').append('<option value="' + value.id + '" ' + selected + '>' + value.name + '</option>');
                    });
            }
            },
            error: function () {
                $('#cost_center').empty();
            }
        });
    } else {
        $('#cost_center').empty();
    }
});

$('#location').trigger('change');
function add_blank(){
    $('.mrntableselectexcel').empty();
                let blank_row = `<tr class="trselected">
                                                                <td class="customernewsection-form">
                                                                    <div class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input row-check">
                                                                        <label class="form-check-label"></label>
                                                                    </div>
                                                                </td>
                                                                <td class="poprod-decpt">
                                                                    <input type="text" required placeholder="Enter" class="form-control mw-100 mb-25 asset-code-input" />
                                                                </td>
                                                                <td class="poprod-decpt">
                                                                    <input type="text" required placeholder="Enter" class="form-control mw-100 mb-25 asset-name-input" />
                                                                </td>
                                                                <td class="poprod-decpt">
                                                                    <input type="text" required placeholder="Enter" disabled class="form-control mw-100 mb-25 sub-asset-code-input" />
                                                                </td>
                                                                <td>
                                                                    <input type="text" required disabled value="1" class="form-control mw-100 quantity-input" />
                                                                </td>
                                                                <td>
                                                                    <input type="text" required class="form-control mw-100 text-end current-value-input" min="1" />
                                                                </td>
                                                                    <td>
                                                                    <input type="text" required class="form-control mw-100 text-end salvage-value-input" min="1" readonly />
                                                                </td>
                                                            </tr>`;
                                                            $('.mrntableselectexcel').append(blank_row);

}


    </script>
    <!-- END: Content-->
@endsection
