@extends('layouts.app')

@section('content')
<form class="ajax-input-form" method="POST" action="{{ route('categories.update', $category->id) }}" data-redirect="{{ url('/categories') }}">
    @csrf
    @method('PUT')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 col-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Edit Category</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Home</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Categories</a></li>
                                        <li class="breadcrumb-item active">Edit</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                        <a href="{{ route('categories.index') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</a>
                            <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                    data-url="{{ route('categories.destroy', $category->id) }}" 
                                    data-redirect="{{ route('categories.index') }}"
                                    data-message="Are you sure you want to delete this item?">
                                <i data-feather="trash-2" class="me-50"></i> Delete
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm" id="submit-button"><i data-feather="check-circle"></i> Update</button>
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
                                            <div class="newheader border-bottom mb-2 pb-25">
                                                <h4 class="card-title text-theme">Edit Category</h4>
                                                <p class="card-text">Update the details</p>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                           <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Type <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select name="type" class="form-select" id="category-type"style="pointer-events: none; background-color: transparent; ">
                                                        <option value="">Select Type</option>
                                                        @foreach ($categoryTypes as $type)
                                                            <option value="{{ $type }}" 
                                                                    {{ old('type', $category->type) == $type ? 'selected' : '' }}>
                                                                {{ $type }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('type')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1"id="hsn-section">
                                                <div class="col-md-3">
                                                    <label class="form-label">HSN/SAC<span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="hsn_name" id="hsn-autocomplete_1" class="form-control hsn-autocomplete" data-id="1" placeholder="Select HSN/SAC" autocomplete="off" value="{{ $category->hsn ? $category->hsn->code : '' }}"/>
                                                    <input type="hidden" class="hsn-id" name="hsn_id" value="{{ $category->hsn_id ?? '' }}"/>
                                                </div>
                                            </div>
                                            
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Category Name <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="name" class="form-control" placeholder="Enter Name" value="{{$category->name}}" />
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
                                                    <input type="text" name ="cat_initials" id="cat_initials_display" value="{{$category->cat_initials}}" class="form-control" />
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
                                                                    {{ $statusOption == old('status', $category->status) ? 'checked' : '' }}
                                                                >
                                                                <label class="form-check-label fw-bolder" for="status_{{ $statusOption }}">
                                                                    {{ ucfirst($statusOption) }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="table-responsive-md">
                                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">
                                                    <thead>
                                                        <tr>
                                                            <th>S.NO</th>
                                                            <th>Sub Category Name <span class="text-danger">*</span></th>
                                                            <th>Sub Category Initials<span class="text-danger">*</span></th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="sub-category-box">
                                                        @forelse ($category->subCategories as $key => $subCategory)
                                                            <tr data-id="{{ $subCategory->id }}">
                                                                <td>{{ $key + 1 }}</td>
                                                                <input type="hidden" name="subcategories[{{ $key }}][id]" value="{{ $subCategory->id }}">
                                                                <td><input type="text" name="subcategories[{{ $key }}][name]" class="form-control mw-100" value="{{ $subCategory->name }}" /></td>
                                                                <td><input type="text" name="subcategories[{{ $key }}][sub_cat_initials]" class="form-control sub_cat_initials_display" value="{{ $subCategory->sub_cat_initials }}"  /></td> 
                                                                <td>
                                                                    <a href="#" class="text-primary add-address"><i data-feather="plus-square"></i></a>
                                                                    <a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                        <tr  id="template-row">
                                                            <td></td>
                                                            <td><input type="text" name="subcategories[0][name]" class="form-control mw-100" /></td>
                                                            <td><input type="text" class="form-control sub_cat_initials_display"name=subcategories[0][sub_cat_initials] /></td> 
                                                            <td>
                                                                <a href="#" class="text-primary add-address"><i data-feather="plus-square"></i></a>
                                                                <a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
                                                          </td>
                                                        </tr>
                                                        @endforelse
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
            feather.replace(); 
        });
    }
    function generateInitials(name) {
        var words = name.split(' ');
        return words.length === 1 ? words[0].substring(0, 2).toUpperCase() : (words[0].charAt(0) + words[1].charAt(0)).toUpperCase();
    }
    $('input[name="name"]').on('input', function() {
        var initials = generateInitials($(this).val());
        $('#cat_initials_display').val(initials);
    });
    $tableBody.on('input', 'input[name^="subcategories"][name$="[name]"]', function() {
        var initials = generateInitials($(this).val());
        $(this).closest('tr').find('input[name^="subcategories"][name$="[sub_cat_initials]"]').val(initials);
    });

    $('input[name="cat_initials"], input[name^="subcategories"][name$="[sub_cat_initials]"]').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });
    $tableBody.on('click', '.add-address', function(e) {
        e.preventDefault();
        var $currentRow = $(this).closest('tr');
        var $newRow = $currentRow.clone(); 
        $newRow.find('input').val(''); 
        $newRow.find('[name]').each(function() {
            var name = $(this).attr('name');
            $(this).attr('name', name.replace(/\[\d+\]/, '[' + $tableBody.children().length + ']'));
            $(this).removeClass('is-invalid');
        });
        $newRow.attr('data-id', '');
        $newRow.find('.add-address').remove(); 
        $newRow.find('td:last-child').html('<a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>'); // Add only delete button
        $newRow.find('.ajax-validation-error-span').remove();
        $tableBody.append($newRow); 
        updateRowIndices();
        feather.replace(); 
        applyCapsLock();
    });
    $tableBody.on('click', '.delete-row', function(e) {
        e.preventDefault();
        var $row = $(this).closest('tr');
        var subCategoryId = $row.data('id');
        if (subCategoryId) {
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
                        url: '/categories/subcategory/' + subCategoryId, 
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}' 
                        },
                        success: function(response) {
                            if (response.status) {
                                $row.remove();
                                Swal.fire('Deleted!', response.message, 'success');
                                updateRowIndices();
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON.message, 'error');
                        }

                    });
                }
            });
        } else {
            $row.remove();
            updateRowIndices();
        }
    });
    if ($tableBody.children().length === 0) {
        var initialRow = `<tr>
            <td>1</td>
            <td><input type="text" name="subcategories[0][name]" /></td>
            <td><input type="text" name="subcategories[0][sub_cat_initials]" /></td>
            <td>
                <a href="#" class="text-primary add-address"><i data-feather="plus-square"></i></a>
                <a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
            </td>
        </tr>`;
        $tableBody.append(initialRow);
    }
    
    updateRowIndices();
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
