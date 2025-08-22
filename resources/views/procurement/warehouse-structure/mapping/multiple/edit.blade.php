@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <form class="ajax-input-form" method="POST"
            action="{{ route('warehouse-multiple-mapping.update', $level->store_id) . '?sub_store=' . $level->sub_store_id . '&wh_level=' . $level->id }}"
            data-redirect="/warehouse-mappings" enctype="multipart/form-data">
            @csrf
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 col-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">
                                        Warehouse Hierarchy
                                    </h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a href="{{ route('/') }}">Home</a>
                                            </li>
                                            <li class="breadcrumb-item">
                                                <a href="{{ route('warehouse-mapping.index') }}">
                                                    Warehouse Hierarchy
                                                </a>
                                            </li>
                                            <li class="breadcrumb-item active">
                                                Edit
                                            </li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <input type="hidden" name="document_status" value="draft" id="document_status">
                                <a href="javascript: history.go(-1)" class="btn btn-secondary btn-sm">
                                    <i data-feather="arrow-left-circle"></i> Back
                                </a>
                                <button type="button" class="btn btn-primary btn-sm submit-button" id="submit-button"
                                    name="action" value="submitted">
                                    <i data-feather="check-circle"></i> Submit
                                </button>
                                <a href="{{ route('warehouse-mapping.print-labels', $level->store_id) . '?sub_store=' . $level->sub_store_id . '&wh_level=' . $level->id }}"
                                    target="_blank"
                                    class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer">
                                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                        <path
                                            d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2">
                                        </path>
                                        <rect x="6" y="14" width="12" height="8"></rect>
                                    </svg>
                                    Print Labels
                                </a>
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
                                                <div class="newheader  border-bottom mb-2 pb-25">
                                                    <h4 class="card-title text-theme">Basic Information</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">
                                                            Location <span class="text-danger">*</span>
                                                        </label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="position-relative">
                                                            <select class="form-select select2 store_id" name="store_id"
                                                                readonly>
                                                                <option value="{{ $level->store_id }}">
                                                                    {{ $level?->store?->store_name }}
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">
                                                            Warehouse <span class="text-danger">*</span>
                                                        </label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="position-relative">
                                                            <select class="form-select select2 sub_store_id"
                                                                name="sub_store_id" readonly>
                                                                <option value="{{ $level->sub_store_id }}">
                                                                    {{ $level?->sub_store?->name }}
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1 warehouse-level">
                                                    <div class="col-md-3">
                                                        <label class="form-label">
                                                            Structure <span class="text-danger">*</span>
                                                        </label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="position-relative">
                                                            <select class="form-select select2 level_id" name="level_id"
                                                                readonly>
                                                                <option value="{{ $level->id }}">
                                                                    {{ $level?->name }}
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 border-start">
                                                <div class="row align-items-center mb-2">
                                                    <div class="col-md-12">
                                                        <label class="form-label text-primary">
                                                            <strong>Status</strong>
                                                        </label>
                                                        <div class="demo-inline-spacing">
                                                            @foreach ($status as $statusOption)
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="status_{{ $statusOption }}"
                                                                        name="status" value="{{ $statusOption }}"
                                                                        class="form-check-input"
                                                                        {{ $statusOption === 'active' ? 'checked' : '' }}>
                                                                    <label class="form-check-label fw-bolder"
                                                                        for="status_{{ $statusOption }}">
                                                                        {{ ucfirst($statusOption) }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
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
                                                        <h4 class="card-title text-theme">Structure Detail</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-sm-end">
                                                    <a href="javascript:;" id="deleteBtn"
                                                        class="btn btn-sm btn-outline-danger me-50 deleteBtn">
                                                        <i data-feather="x-circle"></i> Delete
                                                    </a>
                                                    <a href="javascript:;" id="addNewItemBtn"
                                                        class="btn btn-sm btn-outline-primary addNewItemBtn">
                                                        <i data-feather="plus"></i> Add New
                                                    </a>
                                                    <input type="hidden" name="module_type" value="edit">
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
                                                                <th width="50px" class="customernewsection-form">
                                                                    <div
                                                                        class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input"
                                                                            id="Email">
                                                                        <label class="form-check-label"
                                                                            for="Email"></label>
                                                                    </div>
                                                                </th>
                                                                <th width="100">Structure Name</th>
                                                                <th width="100">Storage Point</th>
                                                                <th width="300">Parent</th>
                                                                {{-- <th width="200">Hierarchy</th> --}}
                                                                <th width="100">Max Weight (Kg)</th>
                                                                <th width="100">Max Volume (CUM)</th>
                                                                <th width="100">Current Weight (Kg)</th>
                                                                <th width="100">Current Volume (CUM)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrntableselectexcel">
                                                            @foreach ($whDetails as $key => $val)
                                                                @php
                                                                    $groupIndex = $loop->iteration;
                                                                    $record = $val->first();
                                                                    $selectedParentIds = $val
                                                                        ->pluck('parent_id')
                                                                        ->unique()
                                                                        ->filter()
                                                                        ->toArray();
                                                                @endphp
                                                                <tr data-index="{{ $loop->iteration }}">
                                                                    <td class="customernewsection-form">
                                                                        <div
                                                                            class="form-check form-check-primary custom-checkbox">
                                                                            <input type="checkbox"
                                                                                class="form-check-input" id="Email">
                                                                            <label class="form-check-label"
                                                                                for="Email"></label>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" placeholder="Enter"
                                                                            class="form-control mw-100 mb-25"
                                                                            name="details[{{ $loop->iteration }}][name]"
                                                                            readonly value="{{ $record->name }}">
                                                                    </td>
                                                                    <td>
                                                                        <div
                                                                            class="form-check form-check-primary custom-checkbox">
                                                                            <input class="form-check-input"
                                                                                type="checkbox"
                                                                                name="details[{{ $loop->iteration }}][storage_point]"
                                                                                id="storage_point"
                                                                                {{ $record->is_storage_point ? 'checked' : 'disabled' }}
                                                                                {{ $isLastLevel === 1 ? 'disabled' : '' }}>
                                                                            <label class="form-check-label"
                                                                                for="storage_point"></label>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <input type="hidden"
                                                                                name="details[{{ $loop->iteration }}][is_first_level]"
                                                                                value="{{ $record->is_first_level }}">
                                                                            <input type="hidden"
                                                                                name="details[{{ $loop->iteration }}][is_last_level]"
                                                                                value="{{ $record->is_last_level }}">
                                                                            @if (!empty($parentDetails))
                                                                                <select
                                                                                    name="details[{{ $groupIndex }}][parent_id][]"
                                                                                    class="form-select mw-100 parent-dropdown select2 parent_id"
                                                                                    multiple style="min-width: 200px;">
                                                                                    @foreach ($parentDetails as $parent)
                                                                                        <option
                                                                                            value="{{ $parent->id }}"
                                                                                            {{ in_array($parent->id, $selectedParentIds) ? 'selected' : '' }}>
                                                                                            {{ $parent->name }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                                <input type="checkbox"
                                                                                    class="form-check-input select-all-parents" />
                                                                            @endif
                                                                            @foreach ($val as $item)
                                                                                <input type="hidden"
                                                                                    name="details[{{ $groupIndex }}][detail_ids][]"
                                                                                    value="{{ $item->id }}">
                                                                            @endforeach
                                                                        </div>
                                                                    </td>
                                                                    {{-- <td class="parent-hierarchy">
                                                                    @php
                                                                        $whDetail = \App\Models\WhDetail::find($record->parent_id);
                                                                    @endphp

                                                                    @if ($whDetail)
                                                                        {!! $whDetail->parent_names !!}
                                                                    @endif
                                                                </td> --}}
                                                                    <td>
                                                                        <input type="text" placeholder="Enter"
                                                                            class="form-control mw-100 mb-25"
                                                                            name="details[{{ $loop->iteration }}][max_weight]"
                                                                            value="{{ $record->max_weight }}"
                                                                            {{ $record->is_storage_point !== 1 ? 'readonly' : '' }}>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" placeholder="Enter"
                                                                            class="form-control mw-100 mb-25"
                                                                            name="details[{{ $loop->iteration }}][max_volume]"
                                                                            value="{{ $record->max_volume }}"
                                                                            {{ $record->is_storage_point !== 1 ? 'readonly' : '' }}>
                                                                    </td>
                                                                    <td>
                                                                        {{ number_format($record->current_weight, 2) }}
                                                                    </td>
                                                                    <td>
                                                                        {{ number_format($record->current_volume, 2) }}
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
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </form>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript" src="{{ asset('assets/js/modules/wm-multi.js') }}"></script>
    <script></script>
@endsection
