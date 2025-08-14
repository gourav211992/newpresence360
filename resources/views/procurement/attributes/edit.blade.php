@extends('layouts.app')

@section('content')
<form class="ajax-input-form" method="POST" action="{{ route('attributes.update', $attributeGroup->id) }}" data-redirect="{{ url('/attributes') }}">
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
                                <h2 class="content-header-title float-start mb-0">Edit Attribute</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('attributes.index') }}">Home</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('attributes.index') }}">Attributes</a></li>
                                        <li class="breadcrumb-item active">Edit</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                              <a href="{{ route('attributes.index') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</a>
                                <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                        data-url="{{ route('attributes.destroy', $attributeGroup->id) }}" 
                                        data-redirect="{{ route('attributes.index') }}"
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
                                                <h4 class="card-title text-theme">Edit Attribute</h4>
                                                <p class="card-text">Update the details</p>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Attribute Name <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="name" class="form-control" placeholder="Enter Name" value="{{ $attributeGroup->name }}"  {{ isset($isAttributeReferenced['status']) && $isAttributeReferenced['status'] === true ? 'readonly' : '' }}/>
                                                    @error('name')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Attribute Short Name <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="short_name" class="form-control" placeholder="Enter Short Name" value="{{ $attributeGroup->short_name }}"  {{ isset($isAttributeReferenced['status']) && $isAttributeReferenced['status'] === true ? 'readonly' : '' }}/>
                                                    @error('short_name')
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
                                                                    {{ $statusOption == old('status', $attributeGroup->status) ? 'checked' : '' }}
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
                                                            <th>Attribute Value <span class="text-danger">*</span></th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="sub-category-box">
                                                        @forelse ($attributeGroup->attributes as $key => $subattr)
                                                            <tr data-id="{{ $subattr->id }}">
                                                            <input type="hidden" name="subattribute[{{ $key }}][id]" value="{{ $subattr->id }}">
                                                                <td>{{ $key + 1 }}</td>
                                                                <td><input type="text" name="subattribute[{{ $key }}][value]" class="form-control mw-100" value="{{ $subattr->value }}"  @if(isset($attributeReferences[$subattr->id])) readonly @endif /></td>
                                                                <td>
                                                                    <a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                        <tr id="template-row">
                                                            <td></td>
                                                            <td><input type="text" name="subattribute[0][value]" class="form-control mw-100" /></td>
                                                            <td>
                                                                <a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
                                                            </td>
                                                        </tr>
                                                        @endforelse
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="3" class="text-end">
                                                                <a href="#" class="add-contactpeontxt mt-0 text-primary addnew mt-0">
                                                                    <i data-feather="plus"></i> Add New Item
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tfoot>
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
            $(this).find('.delete-row').prop('disabled', true); 
                $(this).find('.delete-row').css('opacity', '0.5'); 
            } else {
                $(this).find('.delete-row').prop('disabled', false); 
                $(this).find('.delete-row').css('opacity', '1'); 
            }
        });
    }

    function applyCapsLock() {
        $('input[type="text"], input[type="number"]').each(function() {
            $(this).val($(this).val().toUpperCase());
        });
        $('input[type="text"], input[type="number"]').on('input', function() {
            var value = $(this).val().toUpperCase();  
            $(this).val(value); 
        });
    }

    $('.addnew').on('click', function(e) {
        e.preventDefault();
        var $lastRow = $tableBody.find('tr:last').clone();
        var rowCount = $tableBody.children().length;
        $lastRow.find('[name]').each(function() {
            var name = $(this).attr('name');
            $(this).attr('name', name.replace(/\[\d+\]/, '[' + rowCount + ']'));
            $(this).removeClass('is-invalid');
        });
        $lastRow.find('input[type="hidden"][name$="[id]"]').val('');
        $lastRow.removeAttr('data-id');
        $lastRow.find('input').val('');
        $lastRow.find('.ajax-validation-error-span').remove();
        $lastRow.find('input[readonly]').removeAttr('readonly');
        $tableBody.append($lastRow);
        updateRowIndices();
        feather.replace(); 
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
                        url: '/attributes/attributes-detail/' + attributeDetailId,  
                        type: 'DELETE',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'), 
                        },
                        success: function(response) {
                            if (response.status) {
                                $row.remove();
                                Swal.fire('Deleted!', response.message, 'success');
                                updateRowIndices();
                            } else {
                                Swal.fire('Error!', response.message || 'Could not delete attribute detail.', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON.message || 'An error occurred while deleting the attribute detail.', 'error');
                        }
                    });
                }
            });
        } else {
            $row.remove();
            updateRowIndices();
        }
    });

    updateRowIndices();
    applyCapsLock();
});
</script>
@endsection






