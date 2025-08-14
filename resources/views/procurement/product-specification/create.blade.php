@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <form class="ajax-input-form" method="POST" action="{{ route('product-specifications.store') }}" data-redirect="{{ route('product-specifications.index') }}">
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
                                    <h2 class="content-header-title float-start mb-0">Product Specifications</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="{{ route('product-specifications.index') }}">Home</a></li>
                                            <li class="breadcrumb-item active">Add New</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                            <a href="{{ route('product-specifications.index') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</a>
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
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                            </div>
                                            <div class="col-md-9">
                                                <!-- Title Section -->
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Group<span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="name" class="form-control" placeholder="Enter Group Name" />
                                                        @error('name')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Description Section -->
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Description</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <textarea name="description" class="form-control" placeholder="Enter Description"></textarea>
                                                        @error('description')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Status Section -->
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

                                                <!-- Details Section -->
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-9">
                                                        <div class="table-responsive-md">
                                                            <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">
                                                                <thead>
                                                                    <tr>
                                                                        <th>S.NO</th>
                                                                        <th>Name<span class="text-danger">*</span></th>
                                                                        <th>Description</th> 
                                                                        <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="details-box">
                                                                      <tr data-index="0">
                                                                            <td>
                                                                                <input type="hidden" name="specification_details[0][specification_no]" class="specification-no-hidden text-end" value="1" />
                                                                                <span class="specification-no-display">1</span>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" name="specification_details[0][name]" class="form-control mw-100" placeholder="Enter Name" />
                                                                            </td>
                                                                            <td>
                                                                               <textarea name="specification_details[0][description]" class="form-control mw-100" rows="1" style="resize: none;" placeholder="Enter Description"></textarea>
                                                                            </td>
                                                                            <td>
                                                                                <a href="#" class="text-primary add-row"><i data-feather="plus-square"></i></a>
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
    var $tableBody = $('#details-box');
    function applyCapsLock() {
        $('input[type="text"], input[type="number"]').each(function() {
            $(this).val($(this).val().toUpperCase());
        });
        $('input[type="text"], input[type="number"]').on('input', function() {
            var value = $(this).val().toUpperCase();  
            $(this).val(value); 
        });
    }
    function updateSpecificationNumbers() {
        var $rows = $('#details-box tr');
        $tableBody.find('tr').each(function(index) {
            $(this).find('.specification-no-hidden').val(index + 1);
            $(this).find('.specification-no-display').text(index + 1);
            $(this).find('input[name^="specification_details"]').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                }
            });
            $(this).find('textarea[name^="specification_details"]').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                }
            });
            if ($rows.length === 1) {
                $(this).find('.delete-row').hide(); 
                $(this).find('.add-row').show(); 
            } else {
                $(this).find('.delete-row').show(); 
                $(this).find('.add-row').toggle(index === 0); 
            }
        });
    }

    $('.add-row').on('click', function(e) {
        e.preventDefault();
        var $currentRow = $(this).closest('tr');
        var $newRow = $currentRow.clone();
        var rowCount = $tableBody.find('tr').length;
        $newRow.find('input').each(function() {
            var name = $(this).attr('name');
            if (name) {
                $(this).attr('name', name.replace(/\[\d+\]/, '[' + rowCount + ']'));
            }
            $(this).val(''); 
            $(this).removeClass('is-invalid');
        });
        $newRow.find('textarea').val('');
        $newRow.find('.ajax-validation-error-span').remove();
        $tableBody.append($newRow);
        updateSpecificationNumbers(); 
        feather.replace(); 
        applyCapsLock();
    });
    $tableBody.on('click', '.delete-row', function(e) {
        e.preventDefault();
        $(this).closest('tr').remove(); 
        updateSpecificationNumbers(); 
    });

    updateSpecificationNumbers(); 
    applyCapsLock();
});
</script>

@endsection
