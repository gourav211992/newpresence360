@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <form class="ajax-input-form" method="POST" action="{{ route('product-specifications.update', $productSpecification->id) }}" data-redirect="{{ url('/product-specifications') }}">
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
                                    <h2 class="content-header-title float-start mb-0">Product Specifications</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="{{ route('product-specifications.index') }}">Home</a></li>
                                            <li class="breadcrumb-item"><a href="{{ route('product-specifications.index') }}">Product Specifications</a></li>
                                            <li class="breadcrumb-item active">Edit</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                           <a href="{{ route('product-specifications.index') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</a>
                            <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                    data-url="{{ route('product-specifications.destroy', $productSpecification->id) }}" 
                                    data-redirect="{{ route('product-specifications.index') }}"
                                    data-message="Are you sure you want to delete this item?">
                                <i data-feather="trash-2" class="me-50"></i> Delete
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm"><i data-feather="check-circle"></i> Update</button>
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
                                                    <h4 class="card-title text-theme">Product Specification</h4>
                                                    <p class="card-text">Update the details</p>
                                                </div>
                                            </div>
                                            <div class="col-md-9">
                                                <!-- Title Section -->
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Group <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="name" class="form-control" placeholder="Enter Group Name" value="{{ $productSpecification->name ?? '' }}" />
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
                                                        <textarea name="description" class="form-control" placeholder="Enter Description">{{ $productSpecification->description ?? '' }}</textarea>
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
                                                                        {{ $statusOption == old('status', $productSpecification->status) ? 'checked' : '' }}
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

                                                <!-- Specification Details Section -->
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
                                                                <tbody id="description-box">
                                                                    @forelse ($productSpecification->details as $index => $detail)
                                                                        <tr data-index="{{ $index }}" data-id="{{ $detail->id }}">
                                                                        <input type="hidden" name="specification_details[{{ $index }}][id]" value="{{ $detail->id }}">
                                                                            <td>
                                                                                <input type="hidden" name="specification_details[{{ $index }}][specification_no]" class="specification-no-hidden text-end" value="{{ $index + 1 }}" />
                                                                                <span class="specification-no-display">{{ $index + 1 }}</span>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" name="specification_details[{{ $index }}][name]" class="form-control name mw-100" placeholder="Enter Name" value="{{ $detail->name ?? '' }}" />
                                                                            </td>
                                                                            <td>
                                                                               <textarea name="specification_details[{{ $index }}][description]" class="form-control description mw-100" rows="1" style="resize: none;" placeholder="Enter Description">{{ $detail->description ?? '' }}</textarea>
                                                                            </td>
                                                                            <td>
                                                                                <a href="#" class="text-primary add-row"><i data-feather="plus-square"></i></a>
                                                                                <a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
                                                                            </td>
                                                                            
                                                                        </tr>
                                                                    @empty
                                                                        <tr data-index="0">
                                                                            <td>
                                                                                <input type="hidden" name="specification_details[0][specification_no]" class="specification-no-hidden text-end" value="1" />
                                                                                <span class="specification-no-display">1</span>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" name="specification_details[0][name]" class="form-control name mw-100" rows="1" style="resize: none;" placeholder="Enter Name" />
                                                                            </td>
                                                                            <td>
                                                                               <textarea name="specification_details[0][description]" class="form-control mw-100" placeholder="Enter Description"></textarea>
                                                                            </td>
                                                                            <td>
                                                                                <a href="#" class="text-primary add-row"><i data-feather="plus-square"></i></a>
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
    var $tableBody = $('#description-box');
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
        $tableBody.find('tr').each(function(index) {
            $(this).find('.specification-no-hidden').val(index + 1);
            $(this).find('.specification-no-display').text(index + 1);
            $(this).find('[name]').each(function() {
                var name = $(this).attr('name');
                var newName = name.replace(/\[\d+\]/, '[' + index + ']');
                $(this).attr('name', newName);
            });
            $(this).find('.delete-row').show();
            $(this).find('.add-row').toggle(index === 0);
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
        $newRow.attr('data-id', '');
        $newRow.find('.ajax-validation-error-span').remove();
        $tableBody.append($newRow);
        updateSpecificationNumbers(); 
        applyCapsLock();
    });

    $tableBody.on('click', '.delete-row', function(e) {
    e.preventDefault();
    var $row = $(this).closest('tr');
    var attributeDetailId = $row.data('id'); 
        if (attributeDetailId) {
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
                        url: '/product-specifications/specification-detail/' + attributeDetailId,  
                        type: 'DELETE',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'), 
                        },
                        success: function(response) {
                            if (response.status) {
                                $row.remove();
                                Swal.fire('Deleted!', response.message, 'success');
                                updateSpecificationNumbers();
                            } else {
                                Swal.fire('Error!', response.message || 'Could not deletespecification detail.', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON.message || 'An error occurred while deleting the specification detail.', 'error');
                        }
                    });
                }
            });
        } else {
            $row.remove();
            updateSpecificationNumbers();
        }
    });

    updateSpecificationNumbers();
    applyCapsLock();
});
</script>
@endsection
