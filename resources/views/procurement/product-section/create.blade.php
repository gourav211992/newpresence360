@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <form class="ajax-input-form" method="POST" action="{{ route('product-sections.store') }}" data-redirect="{{ url('/product-sections') }}">
        @csrf
        <div class="app-content content">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 col-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Product Section</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="{{ route('product-sections.index') }}">Home</a></li>
                                            <li class="breadcrumb-item active">Add New</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                            <a href="{{ route('product-sections.index') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</a>
                            <button type="submit" class="btn btn-primary btn-sm"><i data-feather="check-circle"></i> Create</button>
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
                                                <div class="newheader border-bottom mb-2 pb-25">
                                                    <h4 class="card-title text-theme">Basic Information</h4>
                                                    <p class="card-text">Fill in the details below to create a new product section.</p>
                                                </div>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="name" class="form-control" placeholder="Enter Name" value="{{ old('name') }}" />
                                                        @error('name')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Description </label>
                                                    </div>
                                                    <div class="col-md-5">
                                                      <textarea name="description" class="form-control" placeholder="Enter Description">{{ old('description') }}</textarea>
                                                        @error('description')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Status</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="demo-inline-spacing">
                                                            @foreach ($status as $statusOption)
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input
                                                                        type="radio"
                                                                        id="status_{{ $statusOption }}"
                                                                        name="status"
                                                                        value="{{ $statusOption }}"
                                                                        class="form-check-input"
                                                                        {{ old('status', 'active') == $statusOption ? 'checked' : '' }}
                                                                    >
                                                                    <label class="form-check-label fw-bolder" for="status_{{ $statusOption }}">
                                                                        {{ ucfirst($statusOption) }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        @error('status')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="table-responsive-md">
                                                    <h5 class="card-title">Product Section Details</h5>
                                                    <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">
                                                        <thead>
                                                            <tr>
                                                                <th>S.No</th>
                                                                <th>Name<span class="text-danger">*</span></th>
                                                                <th>Description</th>
                                                                <th>Station</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="details-box">
                                                            <tr>
                                                                <td>1</td>
                                                                <td>
                                                                    <input type="text" name="details[0][name]" class="form-control mw-100" placeholder="Enter Name" />
                                                                    @error('details.*.name')
                                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <textarea name="details[0][description]" class="form-control mw-100" placeholder="Enter Description" rows="1" style="resize: none;">{{ old('details.0.description') }}</textarea>
                                                                    @error('details.*.description')
                                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <select name="details[0][station_id]" class="form-select mw-100">
                                                                        <option value="">Select Station</option>
                                                                        @foreach ($stations as $station)
                                                                            <option value="{{ $station->id }}">{{ $station->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    @error('details.*.station_id')
                                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <a href="#" class="text-primary add-detail"> <i data-feather="plus-square"></i></a>
                                                                    <a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
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
                    </section>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    var $detailsBody = $('#details-box');
    function applyCapsLock() {
        $('input[type="text"], input[type="number"]').each(function() {
            $(this).val($(this).val().toUpperCase());
        });
        $('input[type="text"], input[type="number"]').on('input', function() {
            var value = $(this).val().toUpperCase();  
            $(this).val(value); 
        });
    }
    function updateRowIndices() {
        var $rows = $('#details-box tr');
        $detailsBody.find('tr').each(function(index) {
            var $row = $(this);
            $row.find('td').eq(0).text(index + 1);
            $row.find('input[name], select[name]').each(function() {
                var name = $(this).attr('name');
                $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
            });
            if ($rows.length === 1) {
                $(this).find('.delete-row').hide(); 
                $(this).find('.add-detail').show(); 
            } else {
                $(this).find('.delete-row').show(); 
                $(this).find('.add-detail').toggle(index === 0); 
            }
        });
    }
    $detailsBody.on('click', '.add-detail', function(e) {
        e.preventDefault();
        var $currentRow = $(this).closest('tr');
        var $newRow = $currentRow.clone();
        var rowCount = $detailsBody.children().length; 
        $newRow.find('[name]').each(function() {
            var name = $(this).attr('name');
            $(this).attr('name', name.replace(/\[\d+\]/, '[' + rowCount + ']'));
            $(this).removeClass('is-invalid');
        });
        $newRow.find('input').val('');
        $newRow.find('textarea').val('');
        $newRow.find('select').val('');
        $newRow.find('.ajax-validation-error-span').remove();
        $detailsBody.append($newRow);
        updateRowIndices();
        feather.replace();
        applyCapsLock();
    });
    $detailsBody.on('click', '.delete-row', function(e) {
        e.preventDefault();
        $(this).closest('tr').remove();
        updateRowIndices();
    });
    updateRowIndices();
    applyCapsLock();
});
</script>

@endsection
