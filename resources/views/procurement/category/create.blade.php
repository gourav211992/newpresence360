@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <form class="ajax-input-form" method="POST" action="{{ route('categories.store') }}" data-redirect="{{ url('/categories') }}">
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
                                    <h2 class="content-header-title float-start mb-0">Category</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Home</a></li>
                                            <li class="breadcrumb-item active">Add New</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                            <a href="{{ route('categories.index') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</a>
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
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Type <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select name="type" class="form-select" id="category-type">
                                                            <option value="">Select Type</option>
                                                            @foreach ($categoryTypes as $type)
                                                                <option value="{{ $type }}" 
                                                                        {{ old('type') == $type ? 'selected' : '' }}>
                                                                    {{ $type }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('type')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <!-- HSN/SAC Field Section -->
                                                <div id="hsn-section" class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">HSN/SAC <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="hsn_name" id="hsn-autocomplete_1" class="form-control hsn-autocomplete" data-id="1" placeholder="Select HSN/SAC"/>
                                                        <input type="hidden" class="hsn-id" name="hsn_id" />
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="name" class="form-control" placeholder="Enter Category Name" value="{{ old('name') }}" />
                                                        @error('name')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Category Initials<span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name ="cat_initials" id="cat_initials_display" class="form-control" />
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
                                                                        {{ $statusOption == 'active' ? 'checked' : '' }}
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
                                                    <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">
                                                        <thead>
                                                            <tr>
                                                                <th>S.NO</th>
                                                                <th>Sub Category Name<span class="text-danger">*</span></th>
                                                                <th>Sub Category Initials<span class="text-danger">*</span></th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="sub-category-box">
                                                            <tr class="sub-category-template">
                                                                <td>1</td>
                                                                <td><input type="text" name="subcategories[0][name]" class="form-control mw-100" placeholder="Enter Sub Category Name" /></td>
                                                                <td><input type="text" class="form-control sub_cat_initials_display" name="subcategories[0][sub_cat_initials]" /></td> <!-- Display subcategory initials -->
                                                                <td>
                                                                    <a href="#" class="text-primary add-address"><i data-feather="plus-square"></i></a>
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
    var $tableBody = $('#sub-category-box');
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
        var $rows = $('#sub-category-box tr');
        $tableBody.find('tr').each(function(index) {
            var $row = $(this);
            $row.find('td').eq(0).text(index + 1);
            $row.find('input[name]').each(function() {
                var name = $(this).attr('name');
                $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
            });
            if ($rows.length === 1) {
                $(this).find('.delete-row').hide(); 
                $(this).find('.add-address').show(); 
            } else {
                $(this).find('.delete-row').show(); 
                $(this).find('.add-address').toggle(index === 0); 
            }
        });
        if ($tableBody.children().length === 0) {
            addNewRow();
        }
    }
    function addNewRow() {
        var rowCount = $tableBody.children().length; 
        var $currentRow = $tableBody.find('tr:last'); 
        var $newRow = $currentRow.clone(); 
        $newRow.find('input').each(function() {
            var name = $(this).attr('name');
            $(this).attr('name', name.replace(/\[\d+\]/, '[' + rowCount + ']')); 
            $(this).val('');
            $(this).removeClass('is-invalid');
        });
        $newRow.find('.ajax-validation-error-span').remove();
        $tableBody.append($newRow); 
        updateRowIndices(); 
        feather.replace();
        applyCapsLock();
    }
    function generateInitials(name) {
        var words = name.split(' '); 
        if (words.length === 1) {
            return words[0].substring(0, 2).toUpperCase();  
        } else {
            return (words[0].charAt(0) + words[1].charAt(0)).toUpperCase(); 
        }
    }

    $('input[name="name"]').on('input', function() {
        var categoryName = $(this).val();
        var initials = generateInitials(categoryName);
        $('#cat_initials_display').val(initials);
    });

    $tableBody.on('input', 'input[name^="subcategories"][name$="[name]"]', function() {
        var $this = $(this);
        var subcategoryName = $this.val();
        var initials = generateInitials(subcategoryName);
        $this.closest('tr').find('input[name^="subcategories"][name$="[sub_cat_initials]"]').val(initials);
    });

    $tableBody.on('click', '.delete-row', function(e) {
        e.preventDefault();
        var $row = $(this).closest('tr');
        $row.remove();
        updateRowIndices();
    });

    $tableBody.on('click', '.add-address', function(e) {
        e.preventDefault();
        addNewRow();
    });
    if ($tableBody.children().length === 0) {
        addNewRow();
    }
    applyCapsLock();
});
</script>
<script>
    function handleHSNSectionVisibility() {
        if ($('#category-type').val() === 'Product') {
            $('#hsn-section').show();
        } else {
            $('#hsn-section').hide();
            $('#hsn-autocomplete_1').val('');  
            $('.hsn-id').val(''); 
        }
    }

    $('#category-type').on('change', function() {
        handleHSNSectionVisibility(); 
    });
    handleHSNSectionVisibility();
</script>
@endsection
