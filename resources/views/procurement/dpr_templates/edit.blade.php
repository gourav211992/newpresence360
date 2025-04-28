@extends('layouts.app')

@section('content')
    <form method="POST" action="{{ route('dpr-template.update', ['id' => $drp_template->id]) }}">
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
                                    <h2 class="content-header-title float-start mb-0">Edit Template</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="{{ route('dpr-template.index') }}">Home</a>
                                            </li>
                                            <li class="breadcrumb-item"><a href="{{ route('dpr-template.index') }}">DPR
                                                    Template</a></li>
                                            <li class="breadcrumb-item active">Edit</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <a href="{{ route('dpr-template.index') }}" class="btn btn-secondary btn-sm"><i
                                        data-feather="arrow-left-circle"></i> Back</a>
                                {{-- <button type="button"
                                    class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                     data-url="{{ route('dpr-template.destroy', $attributeGroup->id) }}" 
                                        data-redirect="{{ route('dpr-template.index') }}" data-message="Are you sure you want to delete this item?">
                                     <i data-feather="trash-2" class="me-50"></i>
                                </button> --}}
                                <button type="submit" class="btn btn-primary btn-sm" id="submit-button"><i
                                        data-feather="check-circle"></i> Update</button>
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
                                                    <h4 class="card-title text-theme">Edit Template</h4>
                                                    <p class="card-text">Update the details</p>
                                                </div>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Template Name <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="name" class="form-control"
                                                            placeholder="Enter Name"
                                                            value="{{ old('name', $drp_template->template_name) }}" />
                                                        @error('name')
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
                                                                    <input type="radio" id="status_{{ $statusOption }}"
                                                                        name="status" value="{{ $statusOption }}"
                                                                        class="form-check-input"
                                                                        {{ $statusOption == old('status', $drp_template->status) ? 'checked' : '' }}>
                                                                    <label class="form-check-label fw-bolder"
                                                                        for="status_{{ $statusOption }}">
                                                                        {{ ucfirst($statusOption) }}
                                                                    </label>
                                                                </div>
                                                            @endforeach

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
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        // $(document).ready(function() {
        //     var $tableBody = $('#sub-category-box');
        //     function updateRowIndices() {
        //         $tableBody.find('tr').each(function(index) {
        //             var $row = $(this);
        //             $row.find('td').eq(0).text(index + 1);
        //             $row.find('input[name]').each(function() {
        //                 var name = $(this).attr('name');
        //                 $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
        //             });
        //             $row.find('.delete-row').show(); 
        //             $row.find('.add-address').toggle(index === 0);
        //         });
        //     }

        //     $('.add-address').on('click', function(e) {
        //         e.preventDefault();
        //         var $currentRow = $(this).closest('tr');
        //         var $newRow = $currentRow.clone();
        //         var rowCount = $tableBody.children().length;
        //         $newRow.find('[name]').each(function() {
        //             var name = $(this).attr('name');
        //             $(this).attr('name', name.replace(/\[\d+\]/, '[' + rowCount + ']'));
        //         });
        //         $newRow.attr('data-id', '');
        //         $newRow.find('input').val('');
        //         $tableBody.append($newRow);
        //         updateRowIndices();

        //         feather.replace();
        //     });

        //     $tableBody.on('click', '.delete-row', function(e) {
        //     e.preventDefault();
        //     var $row = $(this).closest('tr');
        //     var attributeDetailId = $row.data('id'); 
        //         if (attributeDetailId) {
        //             Swal.fire({
        //                 title: 'Are you sure?',
        //                 text: 'Are you sure you want to delete this record?',
        //                 icon: 'warning',
        //                 showCancelButton: true,
        //                 confirmButtonText: 'Yes, delete it!',
        //                 cancelButtonText: 'No, keep it'
        //             }).then((result) => {
        //                 if (result.isConfirmed) {
        //                     $.ajax({
        //                         url: '/attributes/attributes-detail/' + attributeDetailId,  
        //                         type: 'DELETE',
        //                         data: {
        //                             _token: $('meta[name="csrf-token"]').attr('content'), 
        //                         },
        //                         success: function(response) {
        //                             if (response.status) {
        //                                 $row.remove();
        //                                 Swal.fire('Deleted!', response.message, 'success');
        //                                 updateRowIndices();
        //                             } else {
        //                                 Swal.fire('Error!', response.message || 'Could not delete attribute detail.', 'error');
        //                             }
        //                         },
        //                         error: function(xhr) {
        //                             Swal.fire('Error!', xhr.responseJSON.message || 'An error occurred while deleting the attribute detail.', 'error');
        //                         }
        //                     });
        //                 }
        //             });
        //         } else {
        //             $row.remove();
        //             updateRowIndices();
        //         }
        //     });

        //     updateRowIndices();
        // });
    </script>
@endsection
