@extends('layouts.app')

@section('content')
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Maintenance Type</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                        <li class="breadcrumb-item active">Maintenance Type Master</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <button id="submitBtn" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="check-circle"></i> Submit</button>
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
                                    <div class="newheader border-bottom mb-2 pb-25">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h4 class="card-title text-theme">Basic Information</h4>
                                                <p class="card-text">Fill the details</p>
                                            </div>
                                            <div class="col-md-6 mt-sm-0 mt-50 text-sm-end">
                                                <button id="deleteRows"
                                                    class="btn btn-outline-danger btn-sm mb-50 mb-sm-0"><i
                                                        data-feather="x-circle"></i> Delete</button>
                                                <button id="addRow"
                                                    class="btn btn-outline-primary btn-sm mb-50 mb-sm-0"><i
                                                        data-feather="plus"></i> Add New</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="table-responsive-md">
                                                <form id="maintTypeForm" onsubmit="return false;">
                                                    @csrf
                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"
                                                        id="maintTypeTable">
                                                        <thead>
                                                            <tr>
                                                                <th width="80px" class="customernewsection-form">
                                                                    <div
                                                                        class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input"
                                                                            id="selectAll">
                                                                        <label class="form-check-label"
                                                                            for="selectAll"></label>
                                                                    </div>
                                                                </th>
                                                                <th>Type Name <span class="text-danger">*</span></th>
                                                                <th>Description</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="maintTypeTbody">
                                                            <!-- Dynamic rows will go here -->
                                                        </tbody>
                                                    </table>
                                                </form>
                                                <div id="maintTypeMsg" style="margin-top: 10px;"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Floating Submit Button (optional) -->
                                    <button id="floatingSubmitBtn" class="btn btn-primary"
                                        style="position: fixed; bottom: 30px; right: 30px; z-index:999; display:none"><i
                                            data-feather="check-circle"></i> Submit</button>
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
        $(function() {
            fetchRows();

            // Fetch records and populate table
            function fetchRows() {
                $('.preloader').show();
                $.get('{{ route('maintenance-types.index') }}', function(res) {
                    let html = '';
                    if (res.data && res.data.length) {
                        res.data.forEach(function(row, idx) {
                            html += rowTemplate(row, idx);
                        });
                    }
                    $('#maintTypeTbody').html(html);
                    $('#selectAll').prop('checked', false);

                }).always(function() {
                    $('.preloader').hide();
                });
            }

            // Row template (status as string)
            function rowTemplate(row, idx) {
                return `<tr data-row-id="${row.id || ''}">
            <td>
                <div class="form-check form-check-primary custom-checkbox">
                    <input type="checkbox" class="form-check-input selectRow">
                    <input type="hidden" name="rows[${idx}][id]" value="${row.id || ''}">
                </div>
            </td>
            <td>
                <input type="text" name="rows[${idx}][name]" value="${row.name || ''}" class="form-control mw-100" required />
            </td>
            <td>
                <input type="text" name="rows[${idx}][description]" value="${row.description || ''}" class="form-control mw-100" />
            </td>
            <td>
                <div class="customernewsection-form ">
                    <div class="demo-inline-spacing">
                        <div class="form-check form-check-primary mt-0 me-1">
                            <input type="radio" name="rows[${idx}][status]" class="form-check-input" value="Active" ${row.status == 'Active' ? 'checked' : ''}>
                            <label class="form-check-label fw-bolder">Active</label>
                        </div>
                        <div class="form-check form-check-primary mt-0 me-0">
                            <input type="radio" name="rows[${idx}][status]" class="form-check-input" value="Inactive" ${row.status == 'Inactive' ? 'checked' : ''}>
                            <label class="form-check-label fw-bolder">Inactive</label>
                        </div>
                    </div>
                </div>
            </td>
        </tr>`;
            }

            // Add new row
            $('#addRow').click(function(e) {
                e.preventDefault();
                let idx = $('#maintTypeTbody tr').length;
                let row = {
                    id: '',
                    name: '',
                    description: '',
                    status: 'Active'
                };
                $('#maintTypeTbody').append(rowTemplate(row, idx));
            });

            // Select all
            $(document).on('change', '#selectAll', function() {
                $('.selectRow').prop('checked', $(this).is(':checked'));
            });

            $(document).on('change', '.selectRow', function() {
                if ($('.selectRow:checked').length === 0) {
                    // If no rows are selected, uncheck the "select all" checkbox
                    $('#selectAll').prop('checked', false);

                } else if ($('.selectRow').length === $('.selectRow:checked').length) {
                    // If all rows are selected, check the "select all" checkbox
                    $('#selectAll').prop('checked', true);
                }
            });

            // Delete rows (UI + DB)
            $('#deleteRows').click(function(e) {
                e.preventDefault();
                let ids = [];
                let hasSelection = false;

                // Track rows to remove from UI even if they don't have an ID
                $('#maintTypeTbody tr').each(function() {
                    let $chk = $(this).find('.selectRow');
                    let $id = $(this).find('input[type="hidden"]').val();
                    if ($chk.is(':checked')) {
                        hasSelection = true;
                        if ($id) {
                            ids.push($id);
                        }
                    }
                });

                if (!hasSelection) {
                    Swal.fire({
                        icon: 'info',
                        title: 'No Selection',
                        text: 'Please select at least one record to delete.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Confirm deletion
                Swal.fire({
                    icon: 'warning',
                    title: 'Are you sure?',
                    text: 'Selected records will be deleted!',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('.preloader').show();
                        if (ids.length === 0) {
                            $('#maintTypeTbody tr').each(function() {
                                if ($(this).find('.selectRow').is(':checked')) {
                                    $(this).remove();
                                }
                            });
                            $('.preloader').hide();
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Selected records removed.',
                                confirmButtonText: 'OK'
                            });
                            $('#selectAll').prop('checked', false);
                        } else {
                            // Proceed with AJAX delete
                            $.ajax({
                                url: '{{ route('maintenance-types.delete') }}',
                                type: 'DELETE',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    ids: ids
                                },
                                success: function(res) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: res.success ||
                                            'Records deleted successfully.',
                                        confirmButtonText: 'OK'
                                    });
                                    fetchRows();
                                    $('#selectAll').prop('checked', false);
                                },
                                error: function() {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Error: Could not delete records.'
                                    });
                                },
                                complete: function() {
                                    $('.preloader').hide();
                                }
                            });
                        }
                    }
                });
            });

            // Submit from both buttons
            $('#submitBtn, #floatingSubmitBtn').on('click', function(e) {
                e.preventDefault();
                submitRows();
            });

            $(window).on('scroll', function() {
                if ($(window).scrollTop() > 150) {
                    $('#floatingSubmitBtn').fadeIn();
                } else {
                    $('#floatingSubmitBtn').fadeOut();
                }
            });

            // Save all rows to DB (create/update)
            function submitRows() {
                let rows = [];
                let emptyNameFound = false;
                let firstEmptyField = null;
                $('#maintTypeTbody tr').each(function(idx) {
                    let $tr = $(this);
                    let nameVal = $tr.find('input[name^="rows["][name$="[name]"]').val();
                    let row = {
                        id: $tr.find('input[type="hidden"]').val() || null,
                        name: nameVal,
                        description: $tr.find('input[name^="rows["][name$="[description]"]').val(),
                        status: $tr.find('input[type="radio"]:checked').val()
                    };
                    // Only add row if name is not empty
                    if (!nameVal || nameVal.trim() === "") {
                        emptyNameFound = true;
                        if (!firstEmptyField) {
                            firstEmptyField = $tr.find('input[name^="rows["][name$="[name]"]');
                        }

                    } else {
                        // Only push row if name is valid
                        rows.push(row);
                    }
                });

                if (emptyNameFound) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Type Name is required for all records.',
                        confirmButtonText: 'OK'
                    });
                    if (firstEmptyField) {
                        firstEmptyField.focus();
                    }
                    return;
                }

                if (!rows.length) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Empty Submission',
                        text: 'Please add at least one valid record.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                let submitBtn = $('#submitBtn');
                submitBtn.prop('disabled', true);
                $('.preloader').show();

                $.ajax({
                    url: '{{ route('maintenance-types.store') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        rows: rows
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: res.success || 'Records saved successfully.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload(); // Reload the page after user clicks 'OK'
                        });
                        setTimeout(() => {
                            location.reload();
                        }, 3500);
                        fetchRows();
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            console.error('Validation errors:', errors);
                            // Row-wise errors from backend
                            if (Array.isArray(errors.rows)) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Validation Error',
                                    html: errors.rows.map(msg => `<div>${msg}</div>`).join(''),
                                });
                            } else {
                                // Field-level errors from FormRequest
                                let firstMsg = Object.values(errors)[0];
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Validation Error',
                                    text: firstMsg,
                                });
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Save failed! Please try again.',
                            });
                        }
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false);
                        $('.preloader').hide();
                    }
                });
            }
        });
    </script>
@endsection
