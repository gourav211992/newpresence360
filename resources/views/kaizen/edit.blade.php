@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <form class="form" role="post-data" method="POST" action="{{ url('kaizen/update/' . $kaizen->id) }}"
                redirect="{{ route('kaizen.index') }}">
                @csrf
                @method('PUT')
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Edit Kaizen</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="{{ route('kaizen.dashboard') }}">Home</a>
                                            </li>
                                            <li class="breadcrumb-item active">Edit</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <a href="{{ route('kaizen.index') }}" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                        data-feather="arrow-left-circle"></i> Back</a>
                                <button type="button" class="btn btn-primary btn-sm mb-50 mb-sm-0"
                                    data-request="ajax-submit" data-target="[role=post-data]"><i
                                        data-feather="check-circle"></i> Submit </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-body">
                    <section id="basic-datatable">
                        <div class="row">
                            <div class="col-md-8">
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
                                            <div class="col-md-12">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Department <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <select class="form-select select2" name="department_id">
                                                            @foreach ($departments as $department)
                                                                <option value="{{ $department->id }}"
                                                                    {{ $kaizen->department_id == $department->id ? 'selected' : '' }}>
                                                                    {{ $department->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Kaizen Team <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <select class="form-select team_id kaizen_team" name="team_id[]"
                                                            multiple>
                                                            <option>Select</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Kaizen No.</label>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-control">{{ $kaizen->kaizen_no }}</div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Kaizen Date <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="date" class="form-control" name="date"
                                                            value="{{ $kaizen->kaizen_date }}" />
                                                    </div>
                                                </div>
                                                <div class="row mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Before Kaizen <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-4 attachment-container-before">
                                                        <input type="file"
                                                            class="form-control attachment-input-before kaizen-attachment"
                                                            name="before_kaizen[0]" id="before-kaizen-0"
                                                            onchange="handleFileChange(event, 0, 'before')">
                                                        <h6 class="font-small-2 my-1">Only Pdf, Jpg, Gif, Png, Jpeg formats
                                                            are
                                                            allowed. </h6>
                                                        <div id="preview-before">
                                                            @if (isset($attachments[\App\Helpers\CommonHelper::BEFORE_KAIZEN]))
                                                                @forelse($attachments[\App\Helpers\CommonHelper::BEFORE_KAIZEN] as $id => $attachment)
                                                                    @php
                                                                        $extension = pathinfo(
                                                                            $attachment,
                                                                            PATHINFO_EXTENSION,
                                                                        );
                                                                        $isImage = in_array(strtolower($extension), [
                                                                            'jpg',
                                                                            'jpeg',
                                                                            'png',
                                                                            'gif',
                                                                        ]);
                                                                    @endphp
                                                                    <div class="image-uplodasection"
                                                                        id="existing-before-{{ $id }}">
                                                                        <a href="{{ asset($attachment) }}" target="_blank">
                                                                            @if ($isImage)
                                                                                <i data-feather="image"
                                                                                    class="fileuploadicon"></i>
                                                                            @elseif (strtolower($extension) === 'pdf')
                                                                                <i data-feather="file-text"
                                                                                    class="fileuploadicon"></i>
                                                                            @else
                                                                                <i data-feather="file"
                                                                                    class="fileuploadicon"></i>
                                                                            @endif
                                                                        </a>
                                                                        <div class="delete-img text-danger">
                                                                            <a href="javascript:;"
                                                                                data-url="{{ route('kaizen.remove-attachment', ['id' => $id]) }}"
                                                                                data-request="remove" class="text-danger"><i
                                                                                    data-feather="x"></i></a>
                                                                        </div>
                                                                    </div>
                                                                @empty
                                                                @endforelse
                                                            @endif
                                                        </div>
                                                        <div id="kaizen-before-error"></div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">After Kaizen <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-4 attachment-container-after">
                                                        <input type="file"
                                                            class="form-control attachment-input-after kaizen-attachment"
                                                            name="after_kaizen[0]" id="after-kaizen-0"
                                                            onchange="handleFileChange(event, 0, 'after')">
                                                        <h6 class="font-small-2 my-1">Only Pdf, Jpg, Gif, Png, Jpeg formats
                                                            are
                                                            allowed. </h6>
                                                        <div id="preview-after">
                                                            @if (isset($attachments[\App\Helpers\CommonHelper::AFTER_KAIZEN]))
                                                                @forelse($attachments[\App\Helpers\CommonHelper::AFTER_KAIZEN] as $id => $attachment)
                                                                    @php
                                                                        $extension = pathinfo(
                                                                            $attachment,
                                                                            PATHINFO_EXTENSION,
                                                                        );
                                                                        $isImage = in_array(strtolower($extension), [
                                                                            'jpg',
                                                                            'jpeg',
                                                                            'png',
                                                                            'gif',
                                                                        ]);
                                                                    @endphp
                                                                    <div class="image-uplodasection"
                                                                        id="existing-after-{{ $id }}">
                                                                        <a href="{{ asset($attachment) }}"
                                                                            target="_blank">
                                                                            @if ($isImage)
                                                                                <i data-feather="image"
                                                                                    class="fileuploadicon"></i>
                                                                            @elseif (strtolower($extension) === 'pdf')
                                                                                <i data-feather="file-text"
                                                                                    class="fileuploadicon"></i>
                                                                            @else
                                                                                <i data-feather="file"
                                                                                    class="fileuploadicon"></i>
                                                                            @endif
                                                                        </a>
                                                                        <div class="delete-img text-danger">
                                                                            <a href="javascript:;"
                                                                                data-url="{{ route('kaizen.remove-attachment', ['id' => $id]) }}"
                                                                                data-request="remove"
                                                                                class="text-danger"><i
                                                                                    data-feather="x"></i></a>
                                                                        </div>
                                                                    </div>
                                                                @empty
                                                                @endforelse
                                                            @endif
                                                        </div>
                                                        <div id="kaizen-after-error"></div>
                                                    </div>
                                                </div>
                                                <div class="row  align-items-center  mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Problem <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <textarea class="form-control" name="problem">{{ $kaizen->problem }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Countermeasure <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <textarea class="form-control" name="counter_measure">{{ $kaizen->counter_measure }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Benefits <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <textarea class="form-control" name="benefits">{{ $kaizen->benefits }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Select Approver <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <select class="form-select approver_id" name="approver_id">
                                                            <option>Select</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Select Occurence <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <select class="form-select" name="occurence">
                                                            <option value=""
                                                                {{ $kaizen->occurence = '' ? 'selected' : '' }}>
                                                                Select</option>
                                                            <option value="one time"
                                                                {{ $kaizen->occurence = 'one time' ? 'selected' : '' }}>One
                                                                Time
                                                            </option>
                                                            <option value="monthly"
                                                                {{ $kaizen->occurence = 'monthly' ? 'selected' : '' }}>
                                                                Monthly
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body customernewsection-form">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div
                                                    class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                                                    <div>
                                                        <h4 class="card-title text-theme">Improvements</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <div class="form-check form-check-primary custom-checkbox">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="productivity" name="improvement_type[]"
                                                                value="{{ App\Helpers\CommonHelper::PRODUCTIVITY }}"
                                                                {{ $kaizen->productivity_imp_id ? 'checked' : '' }}>
                                                            <label class="form-label"
                                                                for="productivity">Productivity</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <div class="form-check form-check-primary custom-checkbox">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="quality" name="improvement_type[]"
                                                                value="{{ App\Helpers\CommonHelper::QUALITY }}"
                                                                {{ $kaizen->quality_imp_id ? 'checked' : '' }}>
                                                            <label class="form-label" for="quality">Quality</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <div class="form-check form-check-primary custom-checkbox">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="cost" name="improvement_type[]"
                                                                value="{{ App\Helpers\CommonHelper::COST }}"
                                                                {{ $kaizen->cost_imp_id ? 'checked' : '' }}>
                                                            <label class="form-label" for="cost">Cost</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <div class="form-check form-check-primary custom-checkbox">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="delivery" name="improvement_type[]"
                                                                value="{{ App\Helpers\CommonHelper::DELIVERY }}"
                                                                {{ $kaizen->delivery_imp_id ? 'checked' : '' }}>
                                                            <label class="form-label" for="delivery">Delivery</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <div class="form-check form-check-primary custom-checkbox">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="moral" name="improvement_type[]"
                                                                value="{{ App\Helpers\CommonHelper::MORAL }}"
                                                                {{ $kaizen->moral_imp_id ? 'checked' : '' }}>
                                                            <label class="form-label" for="moral">Moral</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <div class="form-check form-check-primary custom-checkbox">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="innovation" name="improvement_type[]"
                                                                value="{{ App\Helpers\CommonHelper::INNOVATION }}"
                                                                {{ $kaizen->innovation_imp_id ? 'checked' : '' }}>
                                                            <label class="form-label" for="innovation">Innovation</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <div class="form-check form-check-primary custom-checkbox">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="safety" name="improvement_type[]"
                                                                value="{{ App\Helpers\CommonHelper::SAFETY }}"
                                                                {{ $kaizen->safety_imp_id ? 'checked' : '' }}>
                                                            <label class="form-label" for="safety">SAFETY</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="accordionWrapa50" role="tablist" aria-multiselectable="true">
                                                    <div class="accordion-item border" id="productivity-accordion"
                                                        style="display: none">
                                                        <h2 class="accordion-header" id="productivity-heading">
                                                            <button type="button"
                                                                class="accordion-button collapsed no-bg text-dark font-small-4"
                                                                data-bs-toggle="collapse" data-bs-target="#productivity">
                                                                <strong>Productivity</strong>
                                                            </button>
                                                        </h2>
                                                        <div class="accordion-collapse collapse show" id="productivity">
                                                            <div class="accordion-body pt-0">
                                                                <div class="table-responsive-md">
                                                                    <table
                                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Select Productivity <span
                                                                                        class="text-danger">*</span></th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>
                                                                                    <select class="form-select select2"
                                                                                        name="improvement[{{ App\Helpers\CommonHelper::PRODUCTIVITY }}]">
                                                                                        <option value="">Select One
                                                                                        </option>
                                                                                        @if (isset($improvements[App\Helpers\CommonHelper::PRODUCTIVITY]))
                                                                                            @forelse ($improvements[App\Helpers\CommonHelper::PRODUCTIVITY] as $id => $improvement)
                                                                                                <option
                                                                                                    value="{{ $id }}"
                                                                                                    {{ $kaizen->productivity_imp_id == $id ? 'selected' : '' }}>
                                                                                                    {{ $improvement }}
                                                                                                </option>
                                                                                            @empty
                                                                                            @endforelse
                                                                                        @endif
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="accordion-item border" id="quality-accordion"
                                                        style="display: none">
                                                        <h2 class="accordion-header" id="quality-heading">
                                                            <button type="button"
                                                                class="accordion-button collapsed no-bg text-dark font-small-4"
                                                                data-bs-toggle="collapse" data-bs-target="#quality">
                                                                <strong>Quality</strong>
                                                            </button>
                                                        </h2>
                                                        <div class="accordion-collapse collapse show" id="quality">
                                                            <div class="accordion-body pt-0">
                                                                <div class="table-responsive-md">
                                                                    <table
                                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Select Quality <span
                                                                                        class="text-danger">*</span></th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>
                                                                                    <select class="form-select select2"
                                                                                        name="improvement[{{ App\Helpers\CommonHelper::QUALITY }}]">
                                                                                        <option value="">Select One
                                                                                        </option>
                                                                                        @if (isset($improvements[App\Helpers\CommonHelper::QUALITY]))
                                                                                            @forelse ($improvements[App\Helpers\CommonHelper::QUALITY] as $id => $improvement)
                                                                                                <option
                                                                                                    value="{{ $id }}"
                                                                                                    {{ $kaizen->quality_imp_id == $id ? 'selected' : '' }}>
                                                                                                    {{ $improvement }}
                                                                                                </option>
                                                                                            @empty
                                                                                            @endforelse
                                                                                        @endif
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="accordion-item border" id="cost-accordion"
                                                        style="display: none">
                                                        <h2 class="accordion-header" id="cost-heading">
                                                            <button type="button"
                                                                class="accordion-button collapsed no-bg text-dark font-small-4"
                                                                data-bs-toggle="collapse" data-bs-target="#cost">
                                                                <strong>Cost</strong>
                                                            </button>
                                                        </h2>
                                                        <div class="accordion-collapse collapse show" id="cost">
                                                            <div class="accordion-body pt-0">
                                                                <div class="table-responsive-md">
                                                                    <table
                                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Select Cost <span
                                                                                        class="text-danger">*</span></th>
                                                                                <th>Saving Amount <span
                                                                                        class="text-danger">*</span></th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>
                                                                                    <select class="form-select select2"
                                                                                        name="improvement[{{ App\Helpers\CommonHelper::COST }}]">
                                                                                        <option value="">Select One
                                                                                        </option>
                                                                                        @if (isset($improvements[App\Helpers\CommonHelper::COST]))
                                                                                            @forelse ($improvements[App\Helpers\CommonHelper::COST] as $id => $improvement)
                                                                                                <option
                                                                                                    value="{{ $id }}"
                                                                                                    {{ $kaizen->cost_imp_id == $id ? 'selected' : '' }}>
                                                                                                    {{ $improvement }}
                                                                                                </option>
                                                                                            @empty
                                                                                            @endforelse
                                                                                        @endif
                                                                                    </select>
                                                                                </td>
                                                                                <td><input type="text"
                                                                                        class="form-control mw-50"
                                                                                        name="improvement[cost_saving_amt]"
                                                                                        value="{{ $kaizen->cost_saving_amt }}">
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="accordion-item border" id="delivery-accordion"
                                                        style="display: none">
                                                        <h2 class="accordion-header" id="delivery-heading">
                                                            <button type="button"
                                                                class="accordion-button collapsed no-bg text-dark font-small-4"
                                                                data-bs-toggle="collapse" data-bs-target="#delivery">
                                                                <strong>Delivery</strong>
                                                            </button>
                                                        </h2>
                                                        <div class="accordion-collapse collapse show" id="delivery">
                                                            <div class="accordion-body pt-0">
                                                                <div class="table-responsive-md">
                                                                    <table
                                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Select Delivery <span
                                                                                        class="text-danger">*</span></th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>
                                                                                    <select class="form-select select2"
                                                                                        name="improvement[{{ App\Helpers\CommonHelper::DELIVERY }}]">
                                                                                        <option value="">Select One
                                                                                        </option>
                                                                                        @if (isset($improvements[App\Helpers\CommonHelper::DELIVERY]))
                                                                                            @forelse ($improvements[App\Helpers\CommonHelper::DELIVERY] as $id => $improvement)
                                                                                                <option
                                                                                                    value="{{ $id }}"
                                                                                                    {{ $kaizen->delivery_imp_id == $id ? 'selected' : '' }}>
                                                                                                    {{ $improvement }}
                                                                                                </option>
                                                                                            @empty
                                                                                            @endforelse
                                                                                        @endif
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="accordion-item border" id="moral-accordion"
                                                        style="display: none">
                                                        <h2 class="accordion-header" id="moral-heading">
                                                            <button type="button"
                                                                class="accordion-button collapsed no-bg text-dark font-small-4"
                                                                data-bs-toggle="collapse" data-bs-target="#moral">
                                                                <strong>Moral</strong>
                                                            </button>
                                                        </h2>
                                                        <div class="accordion-collapse collapse show" id="moral">
                                                            <div class="accordion-body pt-0">
                                                                <div class="table-responsive-md">
                                                                    <table
                                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Select Moral <span
                                                                                        class="text-danger">*</span></th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>
                                                                                    <select class="form-select select2"
                                                                                        name="improvement[{{ App\Helpers\CommonHelper::MORAL }}]">
                                                                                        <option value="">Select One
                                                                                        </option>
                                                                                        @if (isset($improvements[App\Helpers\CommonHelper::MORAL]))
                                                                                            @forelse ($improvements[App\Helpers\CommonHelper::MORAL] as $id => $improvement)
                                                                                                <option
                                                                                                    value="{{ $id }}"
                                                                                                    {{ $kaizen->moral_imp_id == $id ? 'selected' : '' }}>
                                                                                                    {{ $improvement }}
                                                                                                </option>
                                                                                            @empty
                                                                                            @endforelse
                                                                                        @endif
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="accordion-item border" id="innovation-accordion"
                                                        style="display: none">
                                                        <h2 class="accordion-header" id="innovation-heading">
                                                            <button type="button"
                                                                class="accordion-button collapsed no-bg text-dark font-small-4"
                                                                data-bs-toggle="collapse" data-bs-target="#innovation">
                                                                <strong>Innovation</strong>
                                                            </button>
                                                        </h2>
                                                        <div class="accordion-collapse collapse show" id="innovation">
                                                            <div class="accordion-body pt-0">
                                                                <div class="table-responsive-md">
                                                                    <table
                                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Select innovation <span
                                                                                        class="text-danger">*</span></th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>
                                                                                    <select class="form-select select2"
                                                                                        name="improvement[{{ App\Helpers\CommonHelper::INNOVATION }}]">
                                                                                        <option value="">Select One
                                                                                        </option>
                                                                                        @if (isset($improvements[App\Helpers\CommonHelper::INNOVATION]))
                                                                                            @forelse ($improvements[App\Helpers\CommonHelper::INNOVATION] as $id => $improvement)
                                                                                                <option
                                                                                                    value="{{ $id }}"
                                                                                                    {{ $kaizen->innovation_imp_id == $id ? 'selected' : '' }}>
                                                                                                    {{ $improvement }}
                                                                                                </option>
                                                                                            @empty
                                                                                            @endforelse
                                                                                        @endif
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="accordion-item border" id="safety-accordion"
                                                        style="display: none">
                                                        <h2 class="accordion-header" id="safety-heading">
                                                            <button type="button"
                                                                class="accordion-button collapsed no-bg text-dark font-small-4"
                                                                data-bs-toggle="collapse" data-bs-target="#safety">
                                                                <strong>safety</strong>
                                                            </button>
                                                        </h2>
                                                        <div class="accordion-collapse collapse show" id="safety">
                                                            <div class="accordion-body pt-0">
                                                                <div class="table-responsive-md">
                                                                    <table
                                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Select safety <span
                                                                                        class="text-danger">*</span></th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>
                                                                                    <select class="form-select select2"
                                                                                        name="improvement[{{ App\Helpers\CommonHelper::SAFETY }}]">
                                                                                        <option value="">Select One
                                                                                        </option>
                                                                                        @if (isset($improvements[App\Helpers\CommonHelper::SAFETY]))
                                                                                            @forelse ($improvements[App\Helpers\CommonHelper::SAFETY] as $id => $improvement)
                                                                                                <option
                                                                                                    value="{{ $id }}"
                                                                                                    {{ $kaizen->safety_imp_id == $id ? 'selected' : '' }}>
                                                                                                    {{ $improvement }}
                                                                                                </option>
                                                                                            @empty
                                                                                            @endforelse
                                                                                        @endif
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
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
            </form>
        </div>
    </div>
    <!-- END: Content-->
@endsection
@section('scripts')
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>
    <script>
        let debounceTimer;

        $(document).ready(function() {
            const selectedTeams = @json($kaizen->kaizenTeam);
            const selectedApprover = @json($kaizen->approver_id);

            initTeamSelect2($('.team_id'));
            initTeamSelect2($('.approver_id'));

            // ✅ Populate selected team members
            if (selectedTeams.length > 0) {
                selectedTeams.forEach(function(teamMember) {
                    let option = new Option(`${teamMember.name} (${teamMember.email})`, teamMember.id, true,
                        true);
                    $('.team_id').append(option).trigger('change');
                });
            }

            // ✅ Populate selected approver
            if (selectedApprover) {
                // You may need to fetch approver's name/email from backend or pass it to Blade
                $.ajax({
                    url: "{{ route('kaizen.fetch-employees') }}", // ⬅️ Create this route to fetch employee by ID
                    data: {
                        id: selectedApprover
                    },
                    success: function(response) {
                        let approver = response.data;
                        console.log(approver);
                        let option = new Option(`${approver.name} (${approver.email})`, approver.id,
                            true, true);
                        $('.approver_id').append(option).trigger('change');
                    }
                });
            }

        });

        function initTeamSelect2($element) {
            $element.select2({
                placeholder: "Select Team Name...",
                minimumInputLength: 2,
                ajax: {
                    transport: function(params, success, failure) {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(function() {
                            $.ajax(params).then(success);
                        }, 400); // 👈 Debounce delay: 400ms
                        return {
                            abort: function() {
                                clearTimeout(debounceTimer);
                            }
                        };
                    },
                    url: "{{ route('kaizen.fetch-employees') }}",
                    dataType: 'json',
                    data: function(params) {
                        return {
                            search: $.trim(params.term),
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.data.employees.map(function(employee) {
                                return {
                                    id: employee.id,
                                    text: `${employee.name} (${employee.email})`
                                };
                            }),
                            pagination: {
                                more: data.pagination
                            }
                        };
                    },
                    cache: true
                }
            });
        }
    </script>

    <script>
        var add_attachment_field;
        var totalSize = 0;

        function handleFileChange(e, index, type = 'before') {
            let totalSize = 0;
            let fileTypes = ["jpg", "jpeg", "png", "pdf", 'gif']; // acceptable file types
            let input = e.target;
            let file = input.files[0];
            let previewId = (type === 'before') ? 'preview-before' : 'preview-after';
            let currentUploads = document.querySelectorAll(`.image-uplodasection[id^="${type}-upload-section-"]`).length;

            // ✅ Prevent more than 3 uploads
            if (currentUploads >= 3) {
                e.target.value = "";
                Swal.fire('Limit Reached',
                    `You can only upload up to 3 ${type === 'before' ? 'Before Kaizen' : 'After Kaizen'} files.`,
                    "warning");
                return;
            }

            if (file) {
                document.getElementById(previewId).style.display = 'block';
                let extension = file.name.split(".").pop().toLowerCase();
                let isSuccess = fileTypes.indexOf(extension) > -1;
                let size = file.size;
                totalSize += size / 1024 / 1024; // convert size to MB

                if (!isSuccess) {
                    e.target.value = "";
                    Swal.fire(
                        'Info',
                        "File format not supported (jpg,jpeg,png,pdf,gif only). Kindly select again.",
                        "warning"
                    );
                    return;
                }

                if (totalSize > 10) {
                    e.target.value = "";
                    Swal.fire(
                        'Info',
                        "You can upload a maximum of 10 MB files. Kindly select again.",
                        "warning"
                    );
                    return;
                }

                // Create a preview for each file
                let reader = new FileReader();
                reader.onload = (e) => {
                    let html = previewFile(file, extension, index, type);
                    document.getElementById(previewId).insertAdjacentHTML('beforeend', html);
                    feather.replace();
                }
                reader.readAsDataURL(file);
            }

            addNewAttachmentField(index + 1, type);

        }

        function previewFile(file, extension, index, type = 'before') {
            let fileUrl = URL.createObjectURL(file);
            let sectionId = `${type}-upload-section-${index}`;
            let previewHtml =
                `<div class="image-uplodasection" id="${sectionId}"><a href="${fileUrl}" target="_blank">`;
            if (["jpg", "jpeg", "png", "gif"].indexOf(extension) > -1) {
                previewHtml += `<i data-feather="image" class="fileuploadicon"></i>`;
            } else {
                previewHtml += `<i data-feather="file-text" class="fileuploadicon"></i>`;
            }
            previewHtml +=
                `</a><div class="delete-img text-danger" onclick="removeImage(${index}, '${type}')"><i data-feather="x"></i></div></div>`;

            return previewHtml;
        }

        function addNewAttachmentField(index, type = 'before') {
            let inputId = `${type}-kaizen-${index - 1}`;
            var previousField = document.getElementById(inputId);
            if (previousField) {
                previousField.style.display = 'none';
            }

            var newField = document.createElement('input');
            newField.type = 'file';
            newField.name = `${type}_kaizen[${index}]`;
            newField.classList.add('form-control', 'attachment-input');
            newField.id = `${type}-kaizen-${index}`;
            newField.setAttribute('onchange', `handleFileChange(event, ${index}, '${type}')`);

            var attachmentFieldsContainer = document.querySelector(`.attachment-container-${type}`);
            var nextSibling = previousField.nextElementSibling;
            attachmentFieldsContainer.insertBefore(newField, nextSibling);
        }

        function removeImage(index, type = 'before') {
            let sectionId = `${type}-upload-section-${index}`;
            let fieldId = `${type}-kaizen-${index}`;

            let attachmentRow = document.getElementById(sectionId);
            if (attachmentRow) {
                attachmentRow.remove();
            }

            let attachmentInput = document.getElementById(fieldId);
            if (attachmentInput) {
                attachmentInput.remove();
            }
        }
    </script>

    <script>
        function toggleAccordionByCheckbox($checkbox) {
            var id = $checkbox.attr('id');
            var accordionId = '#' + id + '-accordion';
            if ($checkbox.is(':checked')) {
                $(accordionId).show();
            } else {
                $(accordionId).hide();
            }
        }

        $(document).ready(function() {
            // On page load, toggle all accordions
            $('input[type="checkbox"]').each(function() {
                toggleAccordionByCheckbox($(this));
            });

            // On checkbox change, toggle accordion
            $('input[type="checkbox"]').on('change', function() {
                toggleAccordionByCheckbox($(this));
            });
        });
    </script>
@endsection
