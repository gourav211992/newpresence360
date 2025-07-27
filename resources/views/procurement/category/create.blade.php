@extends('layouts.app')

@section('content')
 @php
    $isEquipmentSegment = ($currentUrlSegment === 'equipment-categories');
 @endphp
    <!-- BEGIN: Content-->
    <form class="ajax-input-form" method="POST" action="{{ route('categories.store') }}" data-redirect="{{ $currentUrlSegment === 'equipment-categories' ? route('equipment-categories.index') : route('categories.index') }}">
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
                                                             @if(!$isEquipmentSegment)<option value="">Select Type</option>  @endif
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
                                               
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Parent Group Name </label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select name="parent_id" id="parent_id" class="form-select mw-100 select2">
                                                            <option value="">Select Group</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Group Name <span class="text-danger">*</span></label>
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
                                                        <label class="form-label">Group Initials<span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name ="cat_initials" id="cat_initials_display" class="form-control" />
                                                    </div>
                                                </div>
                                                 <!-- HSN/SAC Field Section -->
                                                 <div id="hsn-section" class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">HSN/SAC</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="hsn_name" id="hsn-autocomplete_1" class="form-control hsn-autocomplete" data-id="1" placeholder="Select HSN/SAC"/>
                                                        <input type="hidden" class="hsn-id" name="hsn_id" />
                                                    </div>
                                                </div>
                                                <div id="inspection-checklist" class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Inspection Checklist</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="inspection_checklist_name" class="form-control inspection-autocomplete" placeholder="Search Inspection Checklist" />
                                                        <input type="hidden" name="inspection_checklist_id" class="form-control inspection_checklist_id" value="" />            
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
    function handleHSNSectionVisibility() {
        if ($('#category-type').val() === 'Product') {
            $('#hsn-section').show();
            $('#inspection-checklist').show();
        } else {
            $('#hsn-section').hide();
            $('#inspection-checklist').hide();
            $('#hsn-autocomplete_1').val('');
            $('.hsn-id').val('');
            $('.inspection-autocomplete').val('');
            $('.inspection_checklist_id').val('');
        }
    }

    $('#category-type').on('change', function() {
        handleHSNSectionVisibility();
        var selectedType = $(this).val();
        $.ajax({
            url: '{{ route("categories.byType") }}', 
            method: 'GET',
            data: { type: selectedType },
            success: function(data) {
                $('#parent_id').empty();
                $('#parent_id').append('<option value="">Select Group</option>');
                $.each(data, function(index, category) {
                    $('#parent_id').append('<option value="' + category.id + '">' + category.full_name + '</option>');
                });
            }
        });
    });
   $('#category-type').trigger('change');
    // Parent category में change
    $('#parent_id').on('change', function() {
        var parentId = $(this).val();
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
    applyCapsLock();
    handleHSNSectionVisibility();
});
</script>
@endsection
