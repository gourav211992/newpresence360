@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 col-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Bin</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('bins') }}">Bins</a></li>
                                        <li class="breadcrumb-item active">Edit Bin</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <a href="javascript: history.go(-1)" class="btn btn-secondary btn-sm"><i
                                    data-feather="arrow-left-circle"></i> Back</a>
                            <button  type="submit" form="stock-form" class="btn btn-primary btn-sm"><i
                                    data-feather="check-circle"></i>Submit</button>
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
                                    <form id="stock-form" method="POST" action="{{ route('bin.update', $erpBin->id) }}">
                                        @csrf
                                        @method('POST')
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
                                                        <label class="form-label">Store<span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" name="erp_store_id" id="erp_store_id" required>
                                                            <option disabled selected value="">Select Store</option>
                                                            @foreach ($erpStores as $erpStore)
                                                                <option value="{{ $erpStore->id }}" @if($erpStore->id == $erpBin->erp_store_id) selected @endif>{{ $erpStore->store_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Rack<span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" name="erp_rack_id" id="erp_rack_id" required>
                                                            <option disabled selected value="">Select Rack</option>
                                                            @foreach ($erpRacks as $erpRack)
                                                                <option value="{{ $erpRack->id }}" @if($erpRack->id == $erpBin->erp_rack_id) selected @endif>{{ $erpRack->rack_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Shelf Name<span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" name="erp_shelf_id" id="erp_shelf_id" required>
                                                            <option disabled selected value="">Select Shelf</option>
                                                            @foreach ($erpShelfs as $erpShelf)
                                                                <option value="{{ $erpShelf->id }}" @if($erpShelf->id == $erpBin->erp_rack_id) selected @endif>{{ $erpShelf->shelf_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Bin Code<span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="bin_code" class="form-control" required
                                                            value="{{ $erpBin->bin_code }}" />
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Bin Name <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="bin_name" class="form-control" required
                                                            value="{{ $erpBin->bin_name }}" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-3 border-start">
                                                <div class="row align-items-center mb-2">
                                                    <div class="col-md-12">
                                                        <label
                                                            class="form-label text-primary"><strong>Status</strong></label>
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="status-active" name="status"
                                                                    value="Active" class="form-check-input" checked
                                                                    {{ $erpBin->status == 'Active' ? 'checked' : '' }} />
                                                                <label class="form-check-label fw-bolder"
                                                                    for="status-active">Active</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="status-inactive" name="status"
                                                                    value="Inactive" class="form-check-input"
                                                                    {{ $erpBin->status == 'Inactive' ? 'checked' : '' }} />
                                                                <label class="form-check-label fw-bolder"
                                                                    for="status-inactive">Inactive</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
    $(document).ready(function() {
        $('#erp_store_id').change(function() {
            var storeId = $(this).val();
            if (storeId) {
                $.ajax({
                    url: '{{ route('racks.data') }}',
                    type: 'GET',
                    data: { erp_store_id: storeId },
                    success: function(data) {
                        var options = '<option disabled selected value="">Select Rack</option>';
                        $.each(data, function(index, rack) {
                            options += '<option value="' + rack.id + '" ' + (rack.id == '{{ $erpBin->erp_rack_id }}' ? 'selected' : '') + '>' + rack.rack_name + '</option>';
                        });
                        $('#erp_rack_id').html(options);
                    },
                    error: function() {
                        $('#erp_rack_id').html('<option disabled selected value="">Error fetching racks</option>');
                    }
                });
            } else {
                $('#erp_rack_id').html('<option disabled selected value="">Select Store First</option>');
            }
        });

        $('#erp_rack_id').change(function() {
            var storeId = $(this).val();
            if (storeId) {
                $.ajax({
                    url: '{{ route('shelfs.data') }}',
                    type: 'GET',
                    data: { erp_rack_id: storeId },
                    success: function(data) {
                        var options = '<option disabled selected value="">Select Shelf</option>';
                        $.each(data, function(index, shelf) {
                            options += '<option value="' + shelf.id  + '" ' + (shelf.id  == '{{ $erpBin->erp_shelf_id }}' ? 'selected' : '') + '>' + shelf.shelf_name + '</option>';
                        });
                        $('#erp_shelf_id').html(options);
                    },
                    error: function() {
                        $('#erp_shelf_id').html('<option disabled selected value="">Error fetching shelfs</option>');
                    }
                });
            } else {
                $('#erp_shelf_id').html('<option disabled selected value="">Select Rack First</option>');
            }
        });
    });
    </script>
@endsection

