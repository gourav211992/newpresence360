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
                                        <li class="breadcrumb-item active">View Detail</li>


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

                            @if ($buttons['approve'])
                                <button type="button" class="btn btn-primary btn-sm" id="approved-button" name="action"
                                    value="approved"><i data-feather="check-circle"></i> Approve</button>
                                <button type="button" id="reject-button"
                                    class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i
                                        data-feather="x-circle"></i> Reject</button>
                            @endif
                            @if ($buttons['amend'])
                                <button type="button" data-bs-toggle="modal" data-bs-target="#amendmentconfirm"
                                    class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i>
                                    Amendment</button>
                            @endif
                            @if ($buttons['post'])
                                <button id="postButton" onclick="onPostVoucherOpen();" type="button"
                                    class="btn btn-warning btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i
                                        data-feather="check-circle"></i> Post</button>
                            @endif
                            @if ($buttons['voucher'])
                                <button type="button" onclick="onPostVoucherOpen('posted');"
                                    class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                                    <i data-feather="file-text"></i> Voucher</button>
                            @endif


                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">



                <section id="basic-datatable">
                    <div class="row">
                        <form id="fixed-asset-split-form">

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


                                                        @php
                                                            use App\Helpers\Helper;
                                                        @endphp
                                                        <div class="col-md-6 text-sm-end">
                                                            <span
                                                                class="badge rounded-pill {{ App\Helpers\ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$data->document_status] ?? '' }} forminnerstatus">
                                                                <span class="text-dark">Status</span>
                                                                : <span
                                                                    class="{{ App\Helpers\ConstantHelper::DOCUMENT_STATUS_CSS[$data->document_status] ?? '' }}">
                                                                    @if ($data->document_status == App\Helpers\ConstantHelper::APPROVAL_NOT_REQUIRED)
                                                                        Approved
                                                                    @else
                                                                        {{ ucfirst($data->document_status) }}
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
                                                        <label class="form-label" for="book_id">Series <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" id="book_id" name="book_id" required
                                                            disabled>
                                                            <option value="{{ $data->book_id }}">
                                                                {{ $data?->book?->book_code }}
                                                            </option>

                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="document_number">Doc No <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" id="document_number"
                                                            name="document_number" required disabled
                                                            value="{{ $data->document_number }}">
                                                    </div>
                                                </div>


                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="document_date">Doc Date <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" class="form-control indian-number "
                                                            id="document_date" name="document_date"
                                                            value="{{ $data->document_date }}" readonly required>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Category <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="old_category_id"
                                                            disabled id="old_category" required>
                                                            @foreach ($categories as $category)
                                                                <option value="{{ $category->id }}"
                                                                    {{ $data->old_category_id == $category->id ? 'selected' : '' }}>
                                                                    {{ $category->name }}
                                                                </option>
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
                                                        <select id="location" class="form-select" disabled
                                                            name="location_id" required>
                                                            @foreach ($locations as $location)
                                                                <option value="{{ $location->id }}"
                                                                    {{ $data->location_id == $location->id ? 'selected' : '' }}>
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
                                                            name="cost_center_id" required disabled>
                                                        </select>
                                                    </div>

                                                </div>

                                            </div>
                                            @include('partials.approval-history', [
                                                'document_status' => $data->document_status,
                                                'revision_number' => $data->revision_number,
                                            ])



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
                                                            <select id="asset_id" name="asset_id"
                                                                class="form-control indian-number  mw-100 p_ledgerselecct"
                                                                disabled required>
                                                                <option value="{{ $data->asset_id }}">
                                                                    {{ $data?->asset?->asset_code }}
                                                                    ({{ $data?->asset?->asset_name }})
                                                                </option>

                                                            </select>
                                                        </div>
                                                    </div>

                                                    <!-- Sub-Asset Code -->
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="sub_asset_id">Sub-Asset Code
                                                                <span class="text-danger">*</span></label>
                                                            <select id="sub_asset_id" name="sub_asset_id"
                                                                class="form-control indian-number  mw-100 c_ledgerselecct"
                                                                disabled required>
                                                                <option value="{{ $data->sub_asset_id }}">
                                                                    {{ $data?->subAsset?->sub_asset_code }}
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <!-- Last Date of Dep. -->
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="last_dep_date">Last Date of
                                                                Posted <span class="text-danger">*</span></label>
                                                            @php
                                                                $lastDate = $data?->capitalize_date;
                                                                $adjustedDate = $lastDate
                                                                    ? \Carbon\Carbon::parse($lastDate)
                                                                        ->subDay()
                                                                        ->format('Y-m-d')
                                                                    : '';
                                                            @endphp

                                                            <input type="date" id="last_dep_date" disabled
                                                                value="{{ $data->subAsset->capitalize_date != $data->subAsset->last_dep_date ? $adjustedDate : '' }}"
                                                                name="last_dep_date" class="form-control indian-number" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="expiry_date">Last Date of
                                                                Dep.<span class="text-danger">*</span></label>
                                                            <input type="date" id="expiry_date" name="expiry_date"
                                                                class="form-control" readonly
                                                                value="{{ $data->subAsset->expiry_date }}" />
                                                        </div>
                                                    </div>

                                                    <!-- Current Value -->
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="current_value_asset">Current
                                                                Value
                                                                <span class="text-danger">*</span></label>
                                                            <input type="text" id="current_value_asset"
                                                                name="current_value_asset"
                                                                value="{{ $data?->subAsset?->current_value_after_dep }}"
                                                                class="form-control indian-number " disabled required />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="salvage_value">Salvage
                                                                Value
                                                                <span class="text-danger">*</span></label>
                                                            <input type="text" id="salvage_value_asset"
                                                                value="{{ $data->subAsset->salvage_value }}"
                                                                name="salvage_value_asset" class="form-control" disabled
                                                                required />
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
                                                <div hidden class="col-md-6 text-sm-end">
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
                                                                <th width="200">Asset Code</th>
                                                                <th width="200">Asset Name</th>
                                                                <th width="200">Sub Asset Code</th>
                                                                <th width="200">Category</th>
                                                                <th width="200">Ledger</th>
                                                                <th width="50">Est. Life</th>
                                                                <th width="200">Capitalize Date</th>
                                                                <th width="50">Quantity</th>
                                                                <th class="text-end">Current Value</th>
                                                                <th class="text-end">Dep %</th>
                                                                <th class="text-end">Salvage Value</th>

                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrntableselectexcel">
                                                            @foreach (json_decode($data->sub_assets) as $subAsset)
                                                                <tr >
                                                                    <td class="poprod-decpt">
                                                                        <input type="text" required placeholder="Enter"
                                                                            class="form-control mw-100 mb-25 asset-code-input"
                                                                            readonly
                                                                            oninput="this.value = this.value.toUpperCase();"
                                                                            value="{{ $subAsset?->asset_code ?? '' }}" />
                                                                        <span class="text-danger code_error" style="font-size:12px"></span>
                                                                    </td>
                                                                    <td class="poprod-decpt">
                                                                        <input type="text" required placeholder="Enter"
                                                                            readonly
                                                                            class="form-control mw-100 mb-25 asset-name-input"
                                                                            oninput="syncInputAcrossSameAssets(this)"
                                                                            value="{{ $subAsset->asset_name ?? '' }}" />
                                                                    </td>
                                                                    <td class="poprod-decpt">
                                                                        <input type="text" required placeholder="Enter"
                                                                            disabled
                                                                            class="form-control mw-100 mb-25 sub-asset-code-input"
                                                                            value="{{ $subAsset->sub_asset_id ?? '' }}" />
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" required placeholder="Enter"
                                                                            readonly
                                                                            class="form-control mw-100 mb-25 category-input"
                                                                            value="{{ $subAsset->category_input ?? '' }}" />
                                                                        <input type="hidden" class="category"
                                                                            value="{{ $subAsset->category ?? '' }}" />
                                                                        <input type="hidden" class="salvage_per"
                                                                            value="{{ $subAsset->salvage_per ?? '' }}" />
                                                                    </td>
                                                                    <td>
                                                                        <select class="form-control mw-100 mb-25 ledger"
                                                                            disabled required>
                                                                            <option value=""
                                                                                {{ old('ledger') ? '' : 'selected' }}>
                                                                                Select</option>
                                                                            @foreach ($ledgers as $ledger)
                                                                                <option value="{{ $ledger->id }}"
                                                                                    {{ $subAsset->ledger == $ledger->id ? 'selected' : '' }}>
                                                                                    {{ $ledger->name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" required
                                                                            class="form-control mw-100 mb-25 life"
                                                                            oninput="syncInputAcrossSameAssets(this)"
                                                                            value="{{ $subAsset->life ?? '' }}" readonly>
                                                                    </td>
                                                                    <td>
                                                                        <input type="date" required disabled
                                                                            class="form-control mw-100 mb-25 capitalize_date"
                                                                            oninput="syncInputAcrossSameAssets(this)"
                                                                            value="{{ $subAsset->capitalize_date ?? '' }}" />
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" required disabled
                                                                            value="1"
                                                                            class="form-control mw-100 quantity-input"
                                                                            readonly />
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" required
                                                                            class="form-control mw-100 text-end current-value-input indian-number"
                                                                            readonly
                                                                            value="{{ $subAsset->current_value ?? '' }}"
                                                                            oninput="calculateTotals()" min="1" />
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" required
                                                                            class="form-control mw-100 text-end dep_per"
                                                                            value="{{ $subAsset->dep_per ?? '' }}"
                                                                            readonly />
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" required
                                                                            class="form-control mw-100 text-end salvage-value-input indian-number"
                                                                            value="{{ $subAsset->salvage_value ?? '' }}"
                                                                            min="1" readonly />
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



                                                    <div class="col-md-3 d-none">
                                                        <div class="mb-1">
                                                            <label class="form-label">Category <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select select2" id="category" required>
                                                                <option value=""
                                                                    {{ old('category') ? '' : 'selected' }}>
                                                                    Select</option>
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

                                                    <div class="col-md-3 d-none">
                                                        <div class="mb-1">
                                                            <label class="form-label">Ledger <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select select2" id="ledger" required>
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
                                                            <select class="form-select select2" id="ledger_group" disabled
                                                                name="ledger_group_id">
                                                                @foreach ($groups as $group)
                                                                    <option value="{{ $group->id }}"
                                                                        {{ $group->id == $data->ledger_group_id ? 'selected' : '' }}>
                                                                        {{ $group?->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3 d-none">
                                                        <div class="mb-1">
                                                            <label class="form-label">Capitalize Date <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="date" class="form-control"
                                                                id="capitalize_date" readonly
                                                                value="{{ $data->capitalize_date }}" required />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Maint. Schedule <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select" name="maintenance_schedule"
                                                                id="maintenance_schedule" required disabled>
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


                                                    <div class="col-md-3 d-none">
                                                        <div class="mb-1">
                                                            <label class="form-label">Est. Useful Life (yrs) <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" id="useful_life"
                                                                value="{{ $data->useful_life }}" required />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Salvage Value <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control indian-number"
                                                                name="salvage_value" id="salvage_value" readonly
                                                                value="{{ $data->salvage_value }}" required />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3 d-none">
                                                        <div class="mb-1">
                                                            <label class="form-label">Dep % <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="number" class="form-control"
                                                                id="depreciation_rate"
                                                                value="{{ $data->depreciation_percentage }}" readonly />
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
                                                            <input type="text" class="form-control indian-number" required
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
                                <input id = "voucher_date" class="form-control indian-number " disabled="" value="">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Currency <span class="text-danger">*</span></label>
                                <input id = "voucher_currency" class="form-control indian-number " disabled="" value="">
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
									<tbody id="posting-table"></tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<div class="text-end">
					<button style="margin: 1%;" onclick = "postVoucher(this);" id="posting_button" type = "button" class="btn btn-primary btn-sm waves-effect waves-float waves-light">Submit</button>
				</div>
			</div>
		</div>
	</div>
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
           <div class="modal-content">
              <form class="ajax-input-form" method="POST" action="{{ route('finance.fixed-asset.split.approval') }}" data-redirect="{{ route('finance.fixed-asset.split.index') }}" enctype='multipart/form-data'>
                 @csrf
                 <input type="hidden" name="action_type" id="action_type">
                 <input type="hidden" name="id" value="{{$data->id ?? ''}}">
                 <div class="modal-header">
                    <div>
                       <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="popupTitle">
                          Approve Application
                       </h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                 </div>
                 <div class="modal-body pb-2">
                    <div class="row mt-1">
                       <div class="col-md-12">
                          <div class="mb-1">
                             <label class="form-label">Remarks {{-- <span class="text-danger">*</span> --}}</label>
                             <textarea name="remarks" class="form-control indian-number "></textarea>
                          </div>
                            <div class="row">
                    <div class = "col-md-8">
                        <div class="mb-1">
                            <label class="form-label">Upload Document</label>
                            <input type="file" id="ap_file" name = "attachment[]" multiple class="form-control cannot_disable" onchange = "addFiles(this, 'approval_files_preview');" max_file_count = "2"/>
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
                    <button type="reset" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button> 
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
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>Asset Split</strong>? After Amendment this action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
      </div>
    @endsection




@section('scripts')
 <script src="{{asset('assets/js/fileshandler.js')}}"></script>
    <script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })



        $(document).on('click', '.mrntableselectexcel tr', function() {
    $(this).addClass('trselected').siblings().removeClass('trselected');
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
            newRow = ` <tr >
                <td class="customernewsection-form">
                    <div class="form-check form-check-primary custom-checkbox">
                    <input type="checkbox" class="form-check-input row-check">
                    <label class="form-check-label"></label>
                    </div>
                </td>
                <td class="poprod-decpt">
                    <input type="text" required placeholder="Enter" class="form-control mw-100 mb-25 asset-code-input" oninput="this.value = this.value.toUpperCase();" />
                    <span class="text-danger code_error" style="font-size:12px"></span>
                </td>
                <td class="poprod-decpt">
                    <input type="text" required placeholder="Enter" class="form-control mw-100 mb-25 asset-name-input" oninput="syncInputAcrossSameAssets(this)" />
                </td>
                <td class="poprod-decpt">
                    <input type="text" required placeholder="Enter" disabled class="form-control mw-100 mb-25 sub-asset-code-input" />
                </td>
                 <td>
               <input type="text" required placeholder="Enter" class="form-control mw-100 mb-25 category-input" />
                 <input type="hidden" class="category"/> 
                 <input type="hidden" class="salvage_per"/> 
               
              </td>
              <td>
               <select class="form-control mw-100 mb-25 ledger" required>
                                                                <option value=""
                                                                    {{ old('ledger') ? '' : 'selected' }}>Select</option>
                                                                @foreach ($ledgers as $ledger)
                                                                    <option value="{{ $ledger->id }}"
                                                                        {{ old('ledger') == $ledger->id ? 'selected' : '' }}>
                                                                        {{ $ledger->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            </td>
              <td>
              <select class="ledger-group form-select mw-100 mb-25" required>
                </select>
                </td>
              <td>
                <input type="text" required class="form-control mw-100 mb-25 life" oninput="syncInputAcrossSameAssets(this)"> 
                </td>
              <td>
                <input type="date" required class="form-control mw-100 mb-25 capitalize_date" oninput="syncInputAcrossSameAssets(this)"/>
              </td>
                <td>
                    <input type="text" required disabled value="1" class="form-control mw-100 quantity-input" />
                </td>
                <td>
                    <input type="text" required class="form-control mw-100 text-end current-value-input"  oninput="calculateTotals()" max="${Current}" min="1" />
                </td>
                <td>
                <input type="text" required class="form-control mw-100 text-end dep_per" readonly />
              </td>
                <td>
                    <input type="text" required class="form-control mw-100 text-end salvage-value-input" min="1" readonly />
                </td>
                 
                </tr> `;
            $(".mrntableselectexcel tr").removeClass('trselected');
            $('.mrntableselectexcel').append(newRow);
            initializeCategoryAutocomplete('.category-input');
            //updateSubAssetCodes();
        }


        $('#delete_new_sub_asset').on('click', function() {
            let totalRows = $('.mrntableselectexcel tr').length;
            let checkedRows = $('.mrntableselectexcel tr .row-check:checked').length;
            console.log(totalRows, checkedRows);

            if ((totalRows - checkedRows) < 1) {
                showToast('warning', 'At least one row must remain.');
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
                dateInput.setAttribute("min", "{{ $financialStartDate }}");
                dateInput.setAttribute("max", "{{ $financialEndDate }}");
            } else if (backDateAllowed) {
                dateInput.setAttribute("max", today);
                dateInput.setAttribute("min", "{{ $financialStartDate }}");
            } else if (futureDateAllowed) {
                dateInput.setAttribute("min", today);
                dateInput.setAttribute("max", "{{ $financialEndDate }}");
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
        $('#fixed-asset-split-form').on('submit', function(e) {
            e.preventDefault(); // Always prevent default first

            collectSubAssetDataToJson();
            document.getElementById('document_status').value = 'submitted';

            let currentValueAsset = parseFloat($('#current_value_asset').val()) || 0;
            let totalCurrentValue = parseFloat($('#current_value').val()) || 0;

            if (totalCurrentValue > currentValueAsset) {
                showToast('error', 'Total Current Value cannot be greater than Asset Current Value.');
                return false;
            } else if (totalCurrentValue <= 0) {
                showToast('error', 'Total Current Value must be greater than 0.');
                return false;
            }

            let isValid = true;
            $('.asset-code-input').each(function(index) {
                if ($(this).hasClass('is-invalid')) {
                    isValid = false;
                }
            });
            if (isValid == false) {
                showToast('error', 'Code Already Exist.');
                return false;
            }

            // Submit form manually if validation passes
            this.submit();
        });


        $(document).ready(function() {
            $(document).on('change', '.ledger', function() {
                const $row = $(this).closest('tr');
                const ledgerId = $(this).val();
                console.log(ledgerId);
                const $ledgerGroupSelect = $row.find('.ledger-group');

                if (ledgerId) {
                    $.ajax({
                        url: '{{ route('finance.fixed-asset.getLedgerGroups') }}',
                        method: 'GET',
                        data: {
                            ledger_id: ledgerId,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $ledgerGroupSelect.empty(); // Clear previous options
                            response.forEach(item => {
                                $ledgerGroupSelect.append(
                                    `<option value="${item.id}">${item.name}</option>`
                                );
                            });

                        },
                        error: function() {
                            showToast('error', 'Error fetching group items.');
                        }
                    });
                    $ledgerGroupSelect.prop('disabled',true);
                } else {
                    $ledgerGroupSelect.empty();
                }
                //syncInputAcrossSameAssets(this);
            });
            $('.ledger').trigger('change');


            $('.select2').select2();
            //calculateTotals();

            $(document).ready(function() {

                $("#asset_search_input").autocomplete({
                    source: function(request, response) {
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                    'content')
                            },
                            url: '{{ route('finance.fixed-asset.asset-search') }}',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                q: request.term,
                                split: "{{ $data->id }}",
                                location: $('#location').val(),
                                cost_center: $('#cost_center').val(),
                                category: $('#old_category').val(),
                            },
                            success: function(data) {
                                response(data.map(function(item) {
                                    return {
                                        label: item.asset_code + ' (' +
                                            item.asset_name + ')',
                                        value: item.id,
                                    };
                                }));
                            },
                            error: function() {
                                response([]);
                            }
                        });
                    },
                    minLength: 0,
                    select: function(event, ui) {
                        const asset = ui.item.asset;

                        // Set the input box and hidden ID field
                        $(this).val(ui.item.label);
                        $('#asset_id').val(ui.item.value);
                        $('#subasset_search_input').val('');
                        $('#sub_asset_id').val('');
                        $('#last_dep_date')
                            .val('')
                            .removeAttr('min')
                            .removeAttr('max')
                            .prop('readonly', true);
                        $('.capitalize_date').attr('min', '{{ $financialStartDate }}').attr(
                            'max', '{{ $financialEndDate }}').prop('readonly', false);

                        $('#current_value_asset').val('');

                        add_blank();

                        return false; // Prevent default behavior
                    },
                    change: function(event, ui) {
                        if (!ui.item) {
                            $(this).val('');
                            $('#asset_id').val('');
                            $('#subasset_search_input').val('');
                            $('#sub_asset_id').val('');
                            $('#last_dep_date')
                                .val('')
                                .removeAttr('min')
                                .removeAttr('max')
                                .prop('readonly', true);
                            $('.capitalize_date').attr('min', '{{ $financialStartDate }}')
                                .attr('max', '{{ $financialEndDate }}').prop('readonly',
                                false);

                            $('#current_value_asset').val('');
                            add_blank();

                        }
                    },
                    focus: function(event, ui) {
                        return false; // Prevent default behavior
                    }
                }).focus(function() {
                    if (this.value === '') {
                        $(this).autocomplete('search');
                    }
                });
                $("#subasset_search_input").autocomplete({
                    source: function(request, response) {
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                    'content')
                            },
                            url: '{{ route('finance.fixed-asset.sub_asset_search') }}',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                id: $('#asset_id').val(),
                                split: "{{ $data->id }}",
                                q: request.term
                            },
                            success: function(data) {
                                response(data.map(function(item) {
                                    return {
                                        label: item.sub_asset_code,
                                        value: item.id,
                                        asset: item.asset,
                                        sub_asset: item
                                    };
                                }));
                            },
                            error: function() {
                                response([]);
                            }
                        });
                    },
                    minLength: 0,
                    select: function(event, ui) {
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
                        $('#last_dep_date')
                            .val('')
                            .removeAttr('min')
                            .removeAttr('max')
                            .prop('readonly', true);
                        $('.capitalize_date').attr('min', '{{ $financialStartDate }}').attr(
                            'max', '{{ $financialEndDate }}').prop('readonly', false);


                        // Handle depreciation date
                        if (sub_asset.last_dep_date !== sub_asset.capitalize_date) {
                            let lastDepDate = new Date(asset.last_dep_date);
                            lastDepDate.setDate(lastDepDate.getDate() - 1);
                            let formattedDate = lastDepDate.toISOString().split('T')[0];
                            let today = new Date().toISOString().split('T')[0];
                            $('#last_dep_date')
                                .val(formattedDate)
                                .attr('min', formattedDate)
                                .attr('max', today)
                                .prop('readonly', false);
                            $('.capitalize_date')
                                .removeAttr('min')
                                .removeAttr('max').prop('readonly', true);
                        }

                        $('.capitalize_date').val(sub_asset.last_dep_date);
                        $('#depreciation_rate').val(asset.depreciation_percentage);
                        $('#depreciation_rate_year').val(asset.depreciation_percentage_year);
                        $('#useful_life').val(asset.useful_life);
                        $('#maintenance_schedule').val(asset.maintenance_schedule);
                        $('#current_value_asset').val(sub_asset.current_value_after_dep);
                        $('#total_depreciation').val(sub_asset.total_depreciation);
                        add_blank();

                        return false; // Prevent default behavior
                    },
                    change: function(event, ui) {
                        if (!ui.item) {
                            $(this).val('');
                            $('#current_value_asset').val("");
                            $('#last_dep_date')
                                .val('')
                                .removeAttr('min')
                                .removeAttr('max')
                                .prop('readonly', true);
                            $('.capitalize_date').attr('min', '{{ $financialStartDate }}')
                                .attr('max', '{{ $financialEndDate }}').prop('readonly',
                                false);

                            $('#sub_asset_id').val('');
                            $('#category').val("");
                            $('#ledger').val("");
                            $('#ledger_group').val("");
                            $('.capitalize_date').val("");
                            $('#depreciation_rate').val("");
                            $('#depreciation_rate_year').val("");
                            $('#useful_life').val("");
                            $('#maintenance_schedule').val("");
                            $('#current_value_asset').val("");
                            $('#total_depreciation').val("");

                            add_blank();

                        }
                    },
                    focus: function(event, ui) {
                        return false; // Prevent default behavior
                    }
                }).focus(function() {
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
            const assetCodeToCategoryId = {}; // Store the first encountered category for each asset code
            const assetCodeToLedger = {}; // Store the first encountered ledger for each asset code
            const assetCodeToLedgerGroup = {}; // Store the first encountered ledger group for each asset code
            const asstCodeToLife = {};
            const assetCodeToCategoryText = {};
            const assetCodeToSalvage = {};
            const capitalizeDate = {};



            $('.mrntableselectexcel tr').each(function() {
                const $row = $(this);

                const assetCode = $row.find('.asset-code-input').val().trim();
                $.ajax({
                    url: '{{ route('finance.fixed-asset.check-code') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        code: assetCode
                    },
                    success: function(response) {
                        const $input = $row.find('.asset-code-input');
                        const $errorEl = $row.find('.code_error'); // Use class instead of ID

                        if (response.exists) {
                            $errorEl.text('Code already exists.');
                            $input.addClass('is-invalid');
                        } else {
                            $errorEl.text('');
                            $input.removeClass('is-invalid');
                        }
                    }
                });


                const $assetNameInput = $row.find('.asset-name-input');
                const $subAssetInput = $row.find('.sub-asset-code-input');




                if (assetCode !== '') {
                    // Count sub-assets per asset code
                    assetCodeCounts[assetCode] = (assetCodeCounts[assetCode] || 0) + 1;
                    const subAssetCode = `${assetCode}-${String(assetCodeCounts[assetCode]).padStart(2, '0')}`;
                    $subAssetInput.val(subAssetCode);

                    // Handle asset name consistency
                    const currentAssetName = $assetNameInput.val().trim();

                    if (!assetCodeToName[assetCode] && currentAssetName !== '' && !assetCodeToCategoryId[
                            assetCode] && !assetCodeToCategoryText[assetCode] && !assetCodeToLedger[assetCode] && !
                        assetCodeToLedgerGroup[assetCode] && !asstCodeToLife[assetCode] && !assetCodeToSalvage[
                            assetCode] && !capitalizeDate[assetCode]) {
                        // First time seeing this asset code — store its name
                        assetCodeToName[assetCode] = currentAssetName;
                        assetCodeToCategoryId[assetCode] = $row.find('.category-input').val().trim();
                        assetCodeToCategoryText[assetCode] = $row.find('.category').val().trim();
                        assetCodeToLedger[assetCode] = $row.find('.ledger').val();
                        assetCodeToLedgerGroup[assetCode] = $row.find('.ledger-group').val();
                        asstCodeToLife[assetCode] = $row.find('.life').val().trim();
                        assetCodeToSalvage[assetCode] = $row.find('.salvage_per').val().trim();
                        capitalizeDate[assetCode] = $row.find('.capitalize_date').val().trim();

                    } else if (assetCodeToName[assetCode]) {
                        $assetNameInput.val(assetCodeToName[assetCode]);
                        $row.find('.category-input').val(assetCodeToCategoryId[assetCode]).trigger('change');
                        $row.find('.category').val(assetCodeToCategoryText[assetCode]).trigger('change');
                        $row.find('.ledger').val(assetCodeToLedger[assetCode]).trigger('change');
                        $row.find('.ledger-group').val(assetCodeToLedgerGroup[assetCode]).trigger('change');
                        $row.find('.life').val(asstCodeToLife[assetCode]);
                        $row.find('.salvage_per').val(assetCodeToSalvage[assetCode]);
                        $row.find('.capitalize_date').val(capitalizeDate[assetCode]);

                    }
                } else {
                    $subAssetInput.val('');

                }

            });
            calculateTotals();

        }

        $('#ledger').change(function() {
            if ($(this).val() == "") {
                return;
            }

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
        $('#old_category').on('change', function() {
            $('.mrntableselectexcel').empty();
            add_blank();
            $('#asset_search_input').val('');
            $('#asset_id').val('');
            $('#subasset_search_input').val('');
            $('#sub_asset_id').val('');
            $('#last_dep_date')
                .val('')
                .removeAttr('min')
                .removeAttr('max')
                .prop('readonly', true);
            $('.capitalize_date').attr('min', '{{ $financialStartDate }}').attr('max', '{{ $financialEndDate }}')
                .prop('readonly', false);

            $('#current_value_asset').val('');
            loadLocation();
            $('#category').val($(this).val()).trigger('change');


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
                const category = $row.find('.category').val()?.trim() || '';
                const categoryInput = $row.find('.category-input').val()?.trim() || '';
                const ledger = $row.find('.ledger').val() || '';
                const ledgerGroup = $row.find('.ledger-group').val() || '';
                const life = $row.find('.life').val()?.trim() || '';
                const salvagePer = $row.find('.salvage_per').val()?.trim() || '';
                const depPer = $row.find('.dep_per').val()?.trim() || '';
                const capitalizeDate = $row.find('.capitalize_date').val()?.trim() || '';


                if (assetCode !== '') {
                    subAssetData.push({
                        asset_code: assetCode,
                        asset_name: assetName,
                        sub_asset_id: subAssetCode,
                        quantity: quantity,
                        current_value: currentValue,
                        salvage_value: salvageValue,
                        category: category,
                        category_input: categoryInput,
                        ledger: ledger,
                        ledger_group: ledgerGroup,
                        life: life,
                        salvage_per: salvagePer,
                        dep_per: depPer,
                        capitalize_date: capitalizeDate,
                    });
                }
            });

            $('#sub_assets').val(JSON.stringify(subAssetData));
        }


        $(document).on('input change', '.asset-code-input', updateSubAssetCodes);
        $('#location').on('change', function() {
            var locationId = $(this).val();
            var selectedCostCenterId = '{{ $data->cost_center_id ?? '' }}';

            if (locationId) {
                // Build the route manually
                var url = '{{ route('finance.fixed-asset.get-cost-centers') }}';

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        location_id: locationId,
                        category_id: $('#old_category').val(),
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.length == 0) {
                            $('#cost_center').empty();
                            $('#cost_center').prop('required', false);
                            $('.cost_center').hide();
                            // loadCategories();
                        } else {
                            $('.cost_center').show();
                            $('#cost_center').prop('required', true);
                            $('#cost_center').empty(); // Clear previous options
                            $.each(data, function(key, value) {
                                let selected = (value.id == selectedCostCenterId) ? 'selected' :
                                    '';
                                $('#cost_center').append('<option value="' + value.id + '" ' +
                                    selected + '>' + value.name + '</option>');
                            });
                            $('#cost_center').trigger('change');
                        }
                    },
                    error: function() {
                        $('#cost_center').empty();
                    }
                });
            } else {
                $('#cost_center').empty();
            }
        });

        function add_blank() {
            $('.mrntableselectexcel').empty();
            let blank_row = ` <tr >
              <td class="customernewsection-form">
                <div class="form-check form-check-primary custom-checkbox">
                  <input type="checkbox" class="form-check-input row-check">
                  <label class="form-check-label"></label>
                </div>
              </td>
              <td class="poprod-decpt">
                <input type="text" required placeholder="Enter" class="form-control mw-100 mb-25 asset-code-input" oninput="this.value = this.value.toUpperCase();" />
                <span class="text-danger code_error" style="font-size:12px"></span>
              </td>
              <td class="poprod-decpt">
                <input type="text" required placeholder="Enter" class="form-control mw-100 mb-25 asset-name-input" oninput="syncInputAcrossSameAssets(this)"/>
              </td>
              <td class="poprod-decpt">
                <input type="text" required placeholder="Enter" disabled class="form-control mw-100 mb-25 sub-asset-code-input" />
              </td>
              <td>
               <input type="text" required placeholder="Enter" class="form-control mw-100 mb-25 category-input" />
                 <input type="hidden" class="category"/> 
                 <input type="hidden" class="salvage_per"/> 
               
              </td>
              <td>
              <select class="form-control mw-100 mb-25 ledger" required>
                                                                <option value=""
                                                                    {{ old('ledger') ? '' : 'selected' }}>Select</option>
                                                                @foreach ($ledgers as $ledger)
                                                                    <option value="{{ $ledger->id }}"
                                                                        {{ old('ledger') == $ledger->id ? 'selected' : '' }}>
                                                                        {{ $ledger->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                             </td>
              <td>
                <select class="ledger-group form-select mw-100 mb-25" required>
                </select>
                
              </td>
              <td>
                <input type="text" required class="form-control mw-100 mb-25 life" oninput="syncInputAcrossSameAssets(this)"> 
                </td>
              <td>
                <input type="date" required class="form-control mw-100 mb-25 capitalize_date" oninput="syncInputAcrossSameAssets(this)"/>
              </td>
              <td>
                <input type="text" required disabled value="1" class="form-control mw-100 quantity-input" />
              </td>
              <td>
                <input type="text" required class="form-control mw-100 text-end current-value-input"  oninput="calculateTotals()" min="1" />
              </td>
              <td>
                <input type="text" required class="form-control mw-100 text-end dep_per" readonly />
              </td>
              <td>
                <input type="text" required class="form-control mw-100 text-end salvage-value-input" min="1" readonly />
              </td>
              
            </tr>`;
            $('.mrntableselectexcel').append(blank_row);
            initializeCategoryAutocomplete('.category-input');

            if ($('#last_dep_date').val() != "") {
                console.log("last_dep");
                let lastDepDate = new Date($('#last_dep_date').val());
                lastDepDate.setDate(lastDepDate.getDate() - 1);
                let formattedDate = lastDepDate.toISOString().split('T')[0];
                let today = new Date().toISOString().split('T')[0];
                $('.capitalize_date')
                    .removeAttr('min')
                    .removeAttr('max').prop('readonly', true);
                $('#last_dep_date').triger('change');



            } else {
                $('.capitalize_date').attr('min', '{{ $financialStartDate }}').attr('max',
                    '{{ $financialEndDate }}').prop('readonly', false);
            }



        }


        function loadLocation(selectlocation = null) {
            $('#cost_center').empty();
            $('#cost_center').prop('required', false);
            $('.cost_center').hide();
            if (!$('#old_category').val()) {
                return;
            }
            const url = '{{ route('finance.fixed-asset.get-locations') }}';

            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    category_id: $('#old_category').val(),
                },
                dataType: 'json',
                success: function(data) {
                    const $category = $('#location');
                    $category.empty();

                    $.each(data, function(key, value) {
                        const isSelected = selectlocation == value.id ? ' selected' : '';
                        $category.append('<option value="' + value.id + '"' + isSelected + '>' + value
                            .name + '</option>');
                    });
                    $('#location').trigger('change');
                },
                error: function() {
                    $('#location').empty();
                }
            });
        }
        loadLocation('{{ $data->location_id ?? '' }}');
        $('#last_dep_date').on('change', function() {
            let selectedDate = new Date($(this).val());
            if (!isNaN(selectedDate)) {
                selectedDate.setDate(selectedDate.getDate() + 1);
                let nextDate = selectedDate.toISOString().split('T')[0];
                $('.capitalize_date').val(nextDate);
            }
        });

        function syncInputAcrossSameAssets(element) {
            const $this = $(element);
            const row = $this.closest('tr');
            const value = $this.val();
            const assetName = row.find('.asset-code-input').val().trim();

            // Get the first class that identifies the field (excluding utility classes)
            const fieldClass = $this.attr('class').split(' ').find(cls => ['life', 'ledger', 'ledger-group',
                'category-input', 'salvage_per', 'asset-name-input', 'category', 'capitalize_date'
            ].includes(cls));

            if (!fieldClass) return;

            $('.mrntableselectexcel tr').each(function() {
                const $otherRow = $(this);
                const otherAssetName = $otherRow.find('.asset-code-input').val().trim();

                if (otherAssetName === assetName && $otherRow[0] !== row[0]) {
                    const $target = $otherRow.find(`.${fieldClass}`);
                    if ($target.length) {
                        $target.val(value);
                        if (fieldClass === 'category-input')
                            $target.trigger('change'); // Trigger change for category input

                    }
                } else if (fieldClass === 'capitalize_date' && $otherRow[0] !== row[0]) {
                    const $target = $otherRow.find(`.${fieldClass}`);
                    if ($target.length) {
                        $target.val(value);
                        $('.capitalize_date').val(value);

                    }
                }
            });
            calculateTotals();
        }


        function calculateTotals() {
            let totalQuantity = 0;
            let totalCurrentValue = 0;
            let totalSalvageValue = 0;
            let depreciationType = document.getElementById("depreciation_type").value;
            let method = document.getElementById("depreciation_method").value;



            $('.mrntableselectexcel tr').each(function() {
                const $row = $(this);
                const $salvageValueInput = $row.find('.salvage-value-input');
                const $depRateInput = $row.find('.dep_per');
                const quantity = parseFloat($row.find('.quantity-input').val()) || 0;
                const currentValue = parseFloat($row.find('.current-value-input').val()) || 0;
                const depreciationPercentage = parseFloat($row.find('.salvage_per').val()) || 0;
                const usefulLife = parseFloat($row.find('.life').val()) || 0;
                const salvageValue = (currentValue * (depreciationPercentage / 100)).toFixed(2);
                $salvageValueInput.val(salvageValue);



                // Ensure all required values are provided
                if (!depreciationType || !currentValue || !depreciationPercentage || !usefulLife || !method) {
                    // if (!depreciationType) console.log("Missing: depreciationType");
                    // if (!currentValue) console.log("Missing: currentValue");
                    // if (!depreciationPercentage) console.log("Missing: depreciationPercentage");
                    // if (!usefulLife) console.log("Missing: usefulLife");
                    // if (!method) console.log("Missing: method");
                    return;
                }

                let depreciationRate = 0;
                if (method === "SLM") {
                    depreciationRate = ((((currentValue - salvageValue) / usefulLife) / currentValue) * 100)
                        .toFixed(2);
                } else if (method === "WDV") {
                    depreciationRate = ((1 - Math.pow(salvageValue / currentValue, 1 / usefulLife)) * 100).toFixed(
                        2);
                }
                //console.log(depreciationRate);

                $depRateInput.val(depreciationRate);


                // Accumulate totals
                totalSalvageValue += parseFloat(salvageValue);
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


        }
         $(document).on('click', '#approved-button', (e) => {
            let actionType = 'approve';
            $("#approveModal").find("#action_type").val(actionType);
            $("#approveModal").modal('show');
            });

            $(document).on('click', '#reject-button', (e) => {
            let actionType = 'reject';
            $("#approveModal").find("#action_type").val(actionType);
            $("#approveModal").modal('show');
            });
            function resetPostVoucher()
        {
            document.getElementById('voucher_doc_no').value = '';
            document.getElementById('voucher_date').value = '';
            document.getElementById('voucher_book_code').value = '';
            document.getElementById('voucher_currency').value = '';
            document.getElementById('posting-table').innerHTML = '';
            document.getElementById('posting_button').style.display = 'none';
        }

        function onPostVoucherOpen(type = "not_posted")
        {
            // resetPostVoucher();
            const apiURL = "{{route('finance.fixed-asset.split.posting.get')}}";
            $.ajax({
                url: apiURL + "?book_id=" + $("#book_id").val() + "&document_id=" + "{{isset($data) ? $data -> id : ''}}",
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
                    var voucherEntriesHTML = ``;
                    Object.keys(voucherEntries.ledgers).forEach((voucher) => {
                        voucherEntries.ledgers[voucher].forEach((voucherDetail, index) => {
                            voucherEntriesHTML += `
                            <tr>
                            <td>${voucher}</td>
                            <td class="indian-number fw-bolder text-dark">${voucherDetail.ledger_group_code ? voucherDetail.ledger_group_code : ''}</td>
                            <td>${voucherDetail.ledger_code ? voucherDetail.ledger_code : ''}</td>
                            <td>${voucherDetail.ledger_name ? voucherDetail.ledger_name : ''}</td>
                            <td class="indian-number text-end">${voucherDetail.debit_amount > 0 ? parseFloat(voucherDetail.debit_amount).toFixed(2) : ''}</td>
                            <td class="indian-number text-end">${voucherDetail.credit_amount > 0 ? parseFloat(voucherDetail.credit_amount).toFixed(2) : ''}</td>
                            </tr>
                            `
                        });
                    });
                    voucherEntriesHTML+= `
                    <tr>
                        <td colspan="4" class="fw-bolder text-dark text-end">Total</td>
                        <td class="indian-number fw-bolder text-dark text-end">${voucherEntries.total_debit.toFixed(2)}</td>
                        <td class="indian-number fw-bolder text-dark text-end">${voucherEntries.total_credit.toFixed(2)}</td>
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

                function postVoucher(element) {
    Swal.fire({
        title: 'Are you sure?',
        text: " Note: Once Submit the Voucher you are not able to redo the entry.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, post it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const bookId = "{{ isset($data) ? $data->book_id : '' }}";
            const documentId = "{{ isset($data) ? $data->id : '' }}";
            const postingApiUrl = "{{ route('finance.fixed-asset.split.post') }}";

            if (bookId && documentId) {
                $.ajax({
                    url: postingApiUrl,
                    type: "POST",
                    dataType: "json",
                    contentType: "application/json",
                    data: JSON.stringify({
                        book_id: bookId,
                        document_id: documentId,
                    }),
                    success: function (data) {
                        const response = data.data;
                        if (response.status) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                            });
                            location.href = '{{route("finance.fixed-asset.split.index")}}';
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message,
                                icon: 'error',
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Some internal error occurred',
                            icon: 'error',
                        });
                    }
                });
            }
        }
    });
}



        $('#ap_file').prop('disabled', false).prop('readonly', false);
        $('#revisionNumber').prop('disabled', false).prop('readonly', false);
         const amendmentRoute = "{{ route('finance.fixed-asset.split.edit',$data->id) }}";
       
        
        $(document).on('click', '#amendmentSubmit', (e) => {
            // let actionUrl = "{{ route('finance.fixed-asset.split.amendment', $data->id) }}";
            // fetch(actionUrl).then(response => {
            //     return response.json().then(data => {
            //         if (data.status == 200) {
            //             Swal.fire({
            //                     title: 'Success!',
            //                     text: data.message,
            //                     icon: 'success'
            //                 }).then(() => {
            //                     window.location.href = "{{ route('finance.fixed-asset.split.edit', $data->id) }}";
            //                 });
            
            //         } else {
            //             Swal.fire({
            //                 title: 'Error!',
            //                 text: data.message,
            //                 icon: 'error'
            //             });
            //             $('#amendmentconfirm').modal('hide');
            //         }
            //     });
            // });
                e.preventDefault();
                let url = new URL(amendmentRoute, window.location.origin); // full absolute URL
                url.searchParams.set('amendment', 1);
                window.location.href = url.toString(); // or window.location.replace(...)


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

    </script>

    <!-- END: Content-->
@endsection
