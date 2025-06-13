@extends('layouts.app')

@section('content')
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
                            <h2 class="content-header-title float-start mb-0">Edit Group</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('ledger-groups.index') }}">Groups</a>
                                    </li>
                                    <li class="breadcrumb-item active">Edit</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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
                                        <form id="groupEditForm">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" id="id" value="{{ $data->id }}">

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Parent Group</label>
                                                    <span
                                                    class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select @if(!$update) disabled @endif name="parent_group_id" class="form-select select2" >
                                                        <option selected value="">Select</option>
                                                        @foreach ($parents as $parent)
                                                            <option value="{{ $parent->id }}" {{ old('parent_group_id', $data->parent_group_id) == $parent->id ? 'selected' : '' }}>
                                                                {{ $parent->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Group Name</label>
                                                    <span
                                                            class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input @if(!$update) disabled @endif type="text" oninput="generatePrefix()" name="name" class="form-control" required value="{{ old('name', $data->name) }}" />
                                                    @error('name')
                                                        <span class="alert alert-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                             <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Prefix <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input @if(!$update) disabled @endif type="text" name="prefix" required
                                                            oninput="checkUnique()" class="form-control text-uppercase"
                                                            maxlength="3" pattern="[A-Z]{1,3}"
                                                            title="Enter up to 3 uppercase letters"
                                                            value="{{ $data->prefix }}" required
                                                            oninput="this.value = this.value.toUpperCase()" />
                                                        @error('prefix')
                                                            <span class="alert alert-danger">{{ $message }}</span>
                                                        @enderror
                                                        <span id="prefix-feedback" class="text-danger small"></span>
                                                    </div>
                                                </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Status</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="demo-inline-spacing">
                                                        <div class="form-check form-check-primary mt-25">
                                                            <input type="radio" id="customColorRadio3" name="status" value="active" class="form-check-input"
                                                                {{ old('status', $data->status) == "active" ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-bolder" for="customColorRadio3">Active</label>
                                                        </div>
                                                        <div class="form-check form-check-primary mt-25">
                                                            <input type="radio" id="customColorRadio4" name="status" value="inactive" class="form-check-input"
                                                                {{ old('status', $data->status) == "inactive" ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-bolder" for="customColorRadio4">Inactive</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <a type="button" onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm">
                                                    <i data-feather="arrow-left-circle"></i> Back
                                                </a>
                                                @if($update)
                                                <button type="submit" id="submitBtn" class="btn btn-primary btn-sm ms-1">
                                                    <i data-feather="check-circle"></i> Submit
                                                </button>
                                                @endif
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
<!-- END: Content-->
@endsection

@section('scripts')
<script>
    const existingGroupNames = @json($existingGroupname);

    $(document).ready(function () {
        $('#groupEditForm').on('submit', function (e) {
            e.preventDefault();

            const groupId = $('#id').val();
            const name = $('input[name="name"]').val()?.trim().toLowerCase();
            const form = $(this);
            const formData = form.serialize();
            $('.preloader').show();
            let submitBtn = $('#submitBtn');
            submitBtn.prop('disabled', true);

            // Client-side duplicate name check
            if (existingGroupNames.includes(name)) {
                $('.preloader').hide();
                submitBtn.prop('disabled', false);
               Swal.fire({
                    icon: 'error',
                    title: 'Duplicate Entry',
                    text: 'Group name already exists. Please choose a different name.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: 'OK'
                });
                return;
            }

            $.ajax({
                url: `/ledger-groups/${groupId}`, // assumes resourceful route
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    $('.preloader').hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: 'Group updated successfully.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = "{{ route('ledger-groups.index') }}";
                    });
                },
                error: function (xhr) {
                    $('.preloader').hide();
                    submitBtn.prop('disabled', false);
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        let message = Object.values(errors)[0][0];
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: message
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong. Please try again.'
                        });
                    }
                }
            });
        });

        // Optional: Feather icons refresh
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const masterSelect = document.getElementById('master_group_id');
        const parentSelect = document.getElementById('parent_group_id');

        const parentOptions = @json($parents);

        function updateParents() {
            const masterGroupId = masterSelect.value;

            parentSelect.innerHTML = '<option disabled selected>Select</option>';

            parentOptions.forEach(function (parent) {
                if (parent.master_group_id == masterGroupId) {
                    const option = document.createElement('option');
                    option.value = parent.id;
                    option.textContent = parent.name;
                    parentSelect.appendChild(option);
                }
            });

            // Set the currently selected parent group if available
            if (parentSelect.dataset.selectedParent) {
                parentSelect.value = parentSelect.dataset.selectedParent;
            }
        }

        masterSelect.addEventListener('change', updateParents);

        // Initialize parent options based on the default master group
        updateParents();

        // Set the selected parent group on page load after options are populated
        parentSelect.addEventListener('change', function () {
            parentSelect.dataset.selectedParent = parentSelect.value;
        });

        // Initialize selected value from data
        parentSelect.dataset.selectedParent = "{{ $data->parent_group_id }}";
        updateParents();  // Ensure the options are updated
    });
        const prefix = $('input[name="prefix"]');
        const name = $('input[name="name"]');

        function generatePrefix() {

            $.ajax({
                url: '{{ route('generate-group-prefix') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    name: name.val(),
                },
                success: function(response) {
                    prefix.val((response.prefix || ''));
                },
                error: function() {
                    prefix.val('');
                }
            });
        }

        function checkUnique() {
            var feedback = $('#prefix-feedback');

            $.ajax({
                url: '{{ route('groups-check-prefix') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    prefix: prefix.val(),
                    id:'{{$data->id}}',
                },
                success: function(response) {
                    if (response.is_unique) {
                        feedback.text('');
                    } else {
                        feedback.text('Prefix is already in use.');
                    }

                    // Optionally update the field with suggested unique prefix
                    if (response.prefix) {
                        prefix.val(response.prefix);
                    }
                },
                error: function() {
                    feedback.text('Error checking prefix.');
                }
            });
        }
</script>

@endsection
