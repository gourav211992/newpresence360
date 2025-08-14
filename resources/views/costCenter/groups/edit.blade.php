@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 col-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Edit Cost Group</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('cost-group.index') }}">Cost Groups</a>
                                        </li>
                                        <li class="breadcrumb-item active">Edit New</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">

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
                                            <form id="costCenter" action="{{ route('cost-group.update', $data->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')  

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Parent Cost Group</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select name="parent_cost_group_id" class="form-select" required>
                                                            <option disabled selected>Select</option>
                                                            @foreach ($groups as $group)
                                                                <option value="{{ $group->id }}" @if($group->id==$data->parent_cost_group_id) selected @endif>{{ $group->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Group</label>
                                                        <span
                                                        class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" name="name" class="form-control"
                                                            required value="{{ $data->name }}"/>
                                                            @error('name')
                                                                <span class="alert alert-danger" style="font-size:12px">{{ $message }}</span>
                                                            @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Status</label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="customColorRadio3" name="status"
                                                                    value="active" class="form-check-input" @if($data->status=="active") checked @endif>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio3">Active</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="customColorRadio4" name="status" @if($data->status=="inactive") checked @endif
                                                                    value="inactive" class="form-check-input">
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio4">Inactive</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mt-3">
                                                    <button type="button" onClick="javascript: history.go(-1)"
                                                        class="btn btn-secondary btn-sm">
                                                        <i data-feather="arrow-left-circle"></i> Back
                                                    </button>
                                                    <button type="submit" class="btn btn-primary btn-sm ms-1">
                                                        <i data-feather="check-circle"></i> Submit
                                                    </button>
                                                </div>
                                            </form>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function () {
    const existingCostGroups = @json($existingCostGroups); // Pass from controller
    const redirectUrl = "{{ route('cost-group.index') }}";
        $('#costCenter').on('submit', function (e) {
            e.preventDefault(); // Prevent full page reload
            $('.preloader').show();
            let submitBtn = $('#submitBtn');
            let form = $(this);
            submitBtn.prop('disabled', true);

            // Clear previous error messages
            // form.find('.alert.alert-danger').remove();
            let name = $('input[name="name"]').val()?.trim().toLowerCase();
            if (existingCostGroups.map(n => n.toLowerCase()).includes(name)) {
                $('.preloader').hide();
                submitBtn.prop('disabled', false);
                Swal.fire({
                    icon: 'error',
                    title: 'Duplicate Entry',
                    text: 'A Cost Group with this name already exists.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: 'OK'
                });
                return; // Stop further execution
            }

            $.ajax({
                url: form.attr('action'),
                method: form.attr('method'),
                data: form.serialize(),
                success: function (response) {
                    $('.preloader').hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Created!',
                        text: 'Cost Group updated successfully.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        form.trigger('reset');
                        location.href = redirectUrl;

                    });
                },
                error: function (xhr) {
                    $('.preloader').hide();
                    submitBtn.prop('disabled', false);

                    if (xhr.status === 422) {
                        // Validation errors
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function (field, messages) {
                            Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: messages[0],
                            });
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong. Please try again.',
                        });
                    }
                }
            });
        });
    });
    </script>

    <!-- END: Content-->

    <!-- END: Content-->
@endsection
