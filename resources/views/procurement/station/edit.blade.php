@extends('layouts.app')

@section('content')
<form class="ajax-input-form" method="POST" action="{{ route('stations.update', $station->id) }}" data-redirect="{{ url('/stations') }}">
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
                                <h2 class="content-header-title float-start mb-0">Edit Station</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('stations.index') }}">Home</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('stations.index') }}">Stations</a></li>
                                        <li class="breadcrumb-item active">Edit</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                          <a href="{{ route('stations.index') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</a>
                            <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                data-url="{{ route('stations.destroy', $station->id) }}" 
                                data-redirect="{{ route('stations.index') }}"
                                data-message="Are you sure you want to delete this station?">
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
                                                <h4 class="card-title text-theme">Edit Station</h4>
                                                <p class="card-text">Update the details</p>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                        {{-- <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Station Group <span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-md-5">
                                                <select name="station_group_id" class="form-select">
                                                    <option value="">Select Station Group</option>
                                                    @foreach($stationGroups as $group)
                                                        <option value="{{ $group->id }}" {{ $station->station_group_id ?? '' == $group->id ? 'selected' : '' }}>
                                                            {{ $group->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('station_group_id')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div> --}}
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="name" class="form-control" placeholder="Enter Name" value="{{ old('name', $station->name) }}" />
                                                    @error('name')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Alias</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="alias" class="form-control" placeholder="Enter Alias" value="{{ old('alias', $station->alias) }}" />
                                                    @error('alias')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Is Consumption?</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input
                                                                    type="checkbox"
                                                                    id="is_consumption"
                                                                    name="is_consumption"
                                                                    class="form-check-input"
                                                                    {{ old('is_consumption', $station->is_consumption) == 'yes' ? 'checked' : '' }}
                                                                >
                                                            </div>
                                                    </div>
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
                                                                    {{ old('status', $station->status) == $statusOption ? 'checked' : '' }}
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
                                            <div class="table-responsive-md">
                                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">
                                                    <thead>
                                                        <tr>
                                                            <th>S.No</th>
                                                            <th>Line</th>
                                                            <th>Supervisor Name</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="sub-station-box">
                                                        @forelse ($station->lines as $key => $subStation)
                                                            <tr data-id="{{ $subStation->id }}">
                                                                <td>{{ $key + 1 }}</td>
                                                                <input type="hidden" name="lines[{{ $key }}][id]" value="{{ $subStation->id }}">
                                                                <td><input type="text" name="lines[{{ $key }}][name]" class="form-control mw-100" value="{{ $subStation?->name }}" /></td>
                                                                <td><input type="text" name="lines[{{ $key }}][supervisor_name]" class="form-control mw-100" value="{{ $subStation?->supervisor_name }}"/></td>
                                                                <td>
                                                                    <a href="#" class="text-primary add-address"><i data-feather="plus-square"></i></a> 
                                                                    <a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                        <tr id="template-row">
                                                            <td></td>
                                                            <td><input type="text" name="lines[0][name]" class="form-control mw-100" /></td>
                                                            <td><input type="text" name="lines[0][supervisor_name]" class="form-control mw-100" /></td>
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
    var $tableBody = $('#sub-station-box');
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
        var $rows = $('#sub-station-box tr');
        var rowCount = $tableBody.find('tr').length;
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
        });
    }
    function addNewRow() {
        var rowCount = $tableBody.children().length; 
        var $currentRow = $tableBody.find('tr:last'); 
        var $newRow = $currentRow.clone(); 
        $newRow.find('input').each(function() {
            var name = $(this).attr('name');
            $(this).attr('name', name.replace(/\[\d+\]/, '[' + rowCount + ']')); 
            $(this).val(''); 
        });
        $newRow.attr('data-id', '');
        $tableBody.append($newRow); 
        updateRowIndices(); 
        feather.replace();
        applyCapsLock();
    }

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
                        url: '/stations/substation/' + subCategoryId, 
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}' 
                        },
                        success: function(response) {
                            if (response.status) {
                                $row.remove();
                                Swal.fire('Deleted!', response.message, 'success');
                                updateRowIndices();
                                location.reload();
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
        }
    });

    $tableBody.on('click', '.add-address', function(e) {
        e.preventDefault();
        addNewRow(); 
    });

    if ($tableBody.children().length === 0) {
        addNewRow(); 
    }
    updateRowIndices();
    applyCapsLock();
});
</script>
@endsection
