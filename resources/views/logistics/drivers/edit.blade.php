@extends('layouts.app')

@section('content')
<form class="ajax-input-form" method="POST" 
      action="{{ route('logistics.driver.update', $driver->id) }}" 
      data-redirect="{{ route('logistics.driver.index') }}"
      enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">

            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Edit Driver</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                                        <li class="breadcrumb-item active">Edit</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <a href="{{ route('logistics.driver.index') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</a>
                             <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                    data-url="{{ route('logistics.driver.destroy', $driver->id) }}" 
                                    data-redirect="{{ route('logistics.driver.index') }}"
                                    data-message="Are you sure you want to delete this driver?">
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
                                                <h4 class="card-title text-theme">Basic Information</h4>
                                                <p class="card-text">Update the details</p>
                                            </div>
                                        </div>

                                        <div class="col-md-9">
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Employee</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <select name="user_id" id="user_id" class="form-select select2">
                                                        <option value="">Select</option>
                                                        @foreach ($employees as $employee)
                                                            <option value="{{ $employee->id }}" data-name="{{ $employee->name }}"
                                                            data-email="{{ $employee->email }}"
                                                            data-mobile="{{ $employee->mobile }}" {{ old('user_id', $driver->user_id) == $employee->id ? 'selected' : '' }}>
                                                                {{ $employee->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                              
                                            </div>
                                                <div class="row mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Name<span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input name="name" id="name" class="form-control" value="{{ old('name', $driver->name) }}" placeholder="Enter Driver's Name">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Experience (Yr)<span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input name="experience_years" id="experience_years" class="form-control" value="{{ old('experience_years', $driver->experience_years) }}" placeholder="Ex.2">
                                                </div>

                                            </div>

                                            <div class="row mb-1">

                                                <div class="col-md-2">
                                                    <label class="form-label">Email Id</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input name="email" id="email" class="form-control" value="{{ old('email', $driver->email) }}" placeholder="abc@domain.com">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Mobile No.<span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input name="mobile_no" id="mobile_no" class="form-control" value="{{ old('mobile_no', $driver->mobile_no) }}" placeholder="Ex.1234567890">
                                                </div>
                                            </div>



                                            <div class="row mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">License No.<span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input name="license_no" id="license_no" class="form-control" value="{{ old('license_no', $driver->license_no) }}" placeholder="HR-0987654321">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">License Expiry Date<span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="date" name="license_expiry_date" id="license_expiry_date" class="form-control" value="{{ old('license_expiry_date', $driver->license_expiry_date) }}">
                                                </div>
                                            </div>

                                            <!-- License Images -->
                                            <div class="row mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Front Side Of License</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="file" name="license_front" class="form-control" onchange="simpleFileValidation(this)">
                                                  @if ($driver->licenseFrontMedia)
                                                    <img src="{{ asset('storage/driver_uploads/' . $driver->licenseFrontMedia->file_name) }}" class="mt-1" width="120" alt="License Front">
                                                 @endif

                                                </div>

                                                <div class="col-md-2">
                                                    <label class="form-label">Back Side Of License</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="file" name="license_back" class="form-control" onchange="simpleFileValidation(this)">
                                                     @if ($driver->licenseBackMedia)
                                                    <img src="{{ asset('storage/driver_uploads/' . $driver->licenseBackMedia->file_name) }}" class="mt-1" width="120">
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- ID Proof Images -->
                                            <div class="row mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Front Side Of ID Proof</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="file" name="id_proof_front" class="form-control" onchange="simpleFileValidation(this)">
                                                    @if($driver->idProofFrontMedia)
                                                        <img src="{{ asset('storage/driver_uploads/' . $driver->idProofFrontMedia->file_name) }}" class="mt-1" width="120">
                                                    @endif
                                                </div>

                                                <div class="col-md-2">
                                                    <label class="form-label">Back Side Of ID Proof</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="file" name="id_proof_back" class="form-control" onchange="simpleFileValidation(this)">
                                                    @if($driver->idProofBackMedia)
                                                        <img src="{{ asset('storage/driver_uploads/' . $driver->idProofBackMedia->file_name) }}" class="mt-1" width="120">
                                                    @endif
                                                </div>
                                            </div>
                                             <span class="text-danger font-small-2">Note: All File size should Min: 10KB and Max: 2MB (JPG, JPEG, PNG, PDF)</span>
                                        </div>

                                        <div class="col-md-3 border-start">
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-12">
                                                    <label class="form-label">Status</label>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="demo-inline-spacing">
                                                        @foreach ($status as $statusOption)
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="status_{{ $statusOption }}" 
                                                                    name="status" 
                                                                    value="{{ $statusOption }}" 
                                                                    class="form-check-input"
                                                                    {{ old('status', $driver->status) === $statusOption ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder" for="status_{{ $statusOption }}">
                                                                    {{ ucfirst($statusOption) }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div> <!-- end right column -->

                                    </div> <!-- end row -->
                                </div> <!-- end card-body -->
                            </div> <!-- end card -->
                        </div> <!-- end col -->
                    </div> <!-- end row -->
                </section>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $('#user_id').on('change', function () {
            var selected = $(this).find('option:selected');

            var name = selected.data('name') || '';
            var email = selected.data('email') || '';
            var mobile = selected.data('mobile') || '';

            $('#name').val(name);
            $('#email').val(email);
            $('#mobile_no').val(mobile);
        });
    });
</script>
<script>
    const ALLOWED_EXTENSIONS_SIMPLE = ['pdf', 'jpg', 'jpeg', 'png'];
    const ALLOWED_MIME_TYPES_SIMPLE = ['application/pdf', 'image/jpeg', 'image/png'];
    const MAX_FILE_SIZE_SIMPLE = 2048; 

    function simpleFileValidation(element) {
        const input = element;
        const files = Array.from(input.files);
        const dt = new DataTransfer();

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileExtension = file.name.split('.').pop().toLowerCase();
            const fileSize = (file.size / 1024).toFixed(2); 

            if (!ALLOWED_EXTENSIONS_SIMPLE.includes(fileExtension) || !ALLOWED_MIME_TYPES_SIMPLE.includes(file.type)) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Only PDF, JPG, JPEG, PNG files are allowed.',
                    icon: 'error',
                });
                input.value = '';
                return;
            }

            if (fileSize > MAX_FILE_SIZE_SIMPLE) {
                Swal.fire({
                    title: 'Error!',
                    text: 'File size must not exceed 2MB.',
                    icon: 'error',
                });
                input.value = '';
                return;
            }

            dt.items.add(file);
        }

        input.files = dt.files;
    }
</script>
@endsection
