@extends('layouts.app')

@section('content')
 @php
    $isEquipmentSegment = ($currentUrlSegment === 'equipment-categories');
@endphp
<form class="ajax-input-form" method="POST" action="{{ route('categories.update', $category->id) }}"  data-redirect="{{ $currentUrlSegment === 'equipment-categories' ? route('equipment-categories.index') : route('categories.index') }}">>
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
                                                        @if(!$isEquipmentSegment)<option value="">Select Type</option>  @endif
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
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Parent Group Name</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select name="parent_id" id="parent_id" class="form-select mw-100 select2">
                                                        <option value="">Select Group</option>
                                                        @foreach($categories as $parentCategory)
                                                            <option value="{{ $parentCategory->id }}"
                                                                @if(isset($category->parent_id) && $category->parent_id == $parentCategory->id)
                                                                    selected
                                                                @endif>
                                                                {{ $parentCategory->full_name}}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Group Name <span class="text-danger">*</span></label>
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
                                                    <label class="form-label">Group Initials<span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name ="cat_initials" id="cat_initials_display" value="{{ $category->cat_initials ?? ($category->sub_cat_initials ?? '') }}" class="form-control" />
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1"id="hsn-section">
                                                <div class="col-md-3">
                                                    <label class="form-label">HSN/SAC</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="hsn_name" id="hsn-autocomplete_1" class="form-control hsn-autocomplete" data-id="1" placeholder="Select HSN/SAC" autocomplete="off" value="{{ $category->hsn ? $category->hsn->code : '' }}"/>
                                                    <input type="hidden" class="hsn-id" name="hsn_id" value="{{ $category->hsn_id ?? '' }}"/>
                                                </div>
                                            </div>
                                            <div id="inspection-checklist" class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Inspection Checklist</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="inspection_checklist_name" id="inspection_checklist_id" class="form-control inspection-autocomplete" placeholder="Search Inspection Checklist" value="{{ $category->inspectionChecklist ? $category->inspectionChecklist->name : '' }}" />
                                                    <input type="hidden" name="inspection_checklist_id" class="inspection_checklist_id" value="{{ $category->inspection_checklist_id ?? '' }}" />
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
        var lastLevel = @json($isLastLevel);
        var hsnValue = @json($category->hsn ? $category->hsn->code : ''); 
        var hsnId = @json($category->hsn_id ?? ''); 
        var initialVal = $('#cat_initials_display').val().trim();
        var nameVal = $('input[name="name"]').val().trim();
        function handleHSNSectionVisibility() {
            if ($('#category-type').val() === 'Product') {
                $('#hsn-section').show();
                $('#inspection-checklist').show();
                if (lastLevel == 1) {
                    $('#hsn-autocomplete_1').prop('disabled', false);
                    $('.inspection-autocomplete').prop('disabled', false);
                } else{
                    $('#hsn-autocomplete_1').prop('disabled', true);
                    $('.inspection-autocomplete').prop('disabled', true);
                }
                            
            } else {
                $('#hsn-section').hide();
                $('#inspection-checklist').hide();
                $('#hsn-autocomplete_1').val('');  
                $('.hsn-id').val(''); 
                $('.inspection-autocomplete').val(''); 
                $('.inspection_checklist_id').val(''); 
            }
        }
        // Function to fetch HSN based on parent
        function fetchHsn(parentId) {
            var categoryType = $('#category-type').val();
            if (categoryType === 'Product' && parentId) {
                $.ajax({
                    url: '{{ route("categories.getHsnByParent") }}',
                    method: 'GET',
                    data: { parent_id: parentId },
                    success: function(response) {
                        if (response.hsn && response.hsn_id) {
                            $('#hsn-autocomplete_1').val(response.hsn);
                            $('.hsn-id').val(response.hsn_id);
                        } else {
                            $('#hsn-autocomplete_1').val('');
                            $('.hsn-id').val('');
                        }
                    }
                });
            } else {
                $('#hsn-autocomplete_1').val('');
                $('.hsn-id').val('');
            }
        }

        $('#parent_id').on('change', function() {
        var parentId = $(this).val();
            fetchHsn(parentId);
        });

        $('#category-type').on('change', function() {
            handleHSNSectionVisibility();
            $('#parent_id').trigger('change');
        });
       
        function applyCapsLock() {
            $('input[type="text"], input[type="number"]').each(function() {
                $(this).val($(this).val().toUpperCase());
            });
            $('input[type="text"], input[type="number"]').on('input', function() {
                var value = $(this).val().toUpperCase();  
                $(this).val(value); 
            });
        }
        function generateInitials(itemName) {
            const cleanedItemName = itemName.replace(/[^a-zA-Z0-9\s]/g, '');
            const words = cleanedItemName.split(/\s+/).filter(word => word.length > 0);
            let initials = '';
            if (words.length === 1) {
                initials = words[0].substring(0, 2).toUpperCase();
            } else {
                initials = words[0][0].toUpperCase() + words[1][0].toUpperCase();
            }

            return initials;
        }

        $('input[name="name"]').on('input', function() {
            const categoryName = $(this).val();
            const initials = generateInitials(categoryName);
            $('#cat_initials_display').val(initials);
        });
        if (!hsnValue && !hsnId) {
            var selectedParentId = $('#parent_id').val();
            if (selectedParentId) {
                fetchHsn(selectedParentId);
            }
        }
        if (!initialVal || initialVal === '') {
            var generatedInitials = generateInitials(nameVal);
            $('#cat_initials_display').val(generatedInitials);
        }
        applyCapsLock();
        handleHSNSectionVisibility();
    });
</script>
@endsection
