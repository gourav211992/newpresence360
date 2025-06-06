@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <form class="ajax-input-form" method="POST" action="{{ route('gov-relation-management.update', $govRelationManagement->id) }}" data-redirect="{{ url('/gov-relation-management') }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="book_code" id="book_code_input" value="{{ $govRelationManagement->book_code }}">
        <input type="hidden" name="doc_number_type" id="doc_number_type" value="{{ $govRelationManagement->doc_number_type }}">
        <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern" value="{{ $govRelationManagement->doc_reset_pattern }}">
        <input type="hidden" name="doc_prefix" id="doc_prefix" value="{{ $govRelationManagement->doc_prefix }}">
        <input type="hidden" name="doc_suffix" id="doc_suffix" value="{{ $govRelationManagement->doc_suffix }}">
        <input type="hidden" name="doc_no" id="doc_no" value="{{ $govRelationManagement->doc_no }}">

        <div class="app-content content">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header">
                    <div class="row">
                        <div class="content-header-left col-md-6 col-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Edit Government Relation Management</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="{{ route('gov-relation-management.index') }}">Home</a></li>
                                            <li class="breadcrumb-item active">Edit Gov Relation Management</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
                            <button type="submit" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Save</button>
                        </div>
                    </div>
                </div>
                <div class="content-body">
                    <section id="basic-datatable">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Series <span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-md-5">
                                                <select class="form-select" name="book_id" id="series" required>
                                                    <option value="" disabled>Select</option>
                                                    @foreach ($series as $ser)
                                                        <option value="{{ $ser->id }}" {{ old('series', $govRelationManagement->book_id) == $ser->id ? 'selected' : '' }}>
                                                            {{ $ser->book_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Request No. <span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="document_number" readonly id="requestno" value="{{ old('requestno', $govRelationManagement->document_number) }}">
                                            </div>
                                        </div>
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="date" onchange="getDocNumberByBookId()" class="form-control" name="document_date" id="document_date" value="{{ old('document_date', $govRelationManagement->document_date) }}">
                                                @error('document_date')
                                                <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Party Type <span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-md-5">
                                                <select class="form-select" name="user_type_id" required>
                                                    <option value="" disabled>Select</option>
                                                    @foreach ($userTypes as $type)
                                                        <option value="{{ $type->id }}" {{ old('user_type_id', $govRelationManagement->user_type_id) == $type->id ? 'selected' : '' }}>
                                                            {{ $type->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Party Name <span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-md-5">
                                                <input class="form-control" type="text" name="party_name" value="{{$govRelationManagement->party_name}}" placeholder="Enter Party Name">
                                            </div>
                                        </div>
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Assignee<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-md-5">
                                                <select name="userable_id" class="form-control" id="user_type_select">
                                                    <option value="">Select Assignee</option>
                                                    @foreach($stakeholders as $stakeholder)
                                                        <option value="{{ $stakeholder['id'] }}" {{ old('userable_id', $govRelationManagement->userable_id) == $stakeholder['id'] ? 'selected' : '' }}>
                                                            {{ $stakeholder['name'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <input type="hidden" name="userable_type" id="user_type" value="{{ old('userable_type', $govRelationManagement->userable_type) }}">
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Interaction Type<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-md-5">
                                                <select name="interaction_type_id" class="form-control">
                                                    <option value="">Select Interaction Type</option>
                                                    @foreach($interactionTypes as $type)
                                                        <option value="{{ $type->id }}" {{ old('interaction_type_id', $govRelationManagement->interaction_type_id) == $type->id ? 'selected' : '' }}>
                                                            {{ $type->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Notes <span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-md-5">
                                                <textarea class="form-control" name="notes">{{ old('notes', $govRelationManagement->notes) }}</textarea>
                                            </div>
                                            @error('notes')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Follow up action<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-md-5">
                                                <textarea class="form-control" name="followup">{{ old('followup', $govRelationManagement->followup) }}</textarea>
                                            </div>
                                            @error('followup')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
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

        document.getElementById('user_type_select').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var userType = selectedOption.getAttribute('data-type');
            document.getElementById('user_type').value = userType;
        });

        function getDocNumberByBookId() {
            let currentDate = new Date().toISOString().split('T')[0];
            let bookId = $('#series').val();
            let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId + "&document_date=" + currentDate;

            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        $("#book_code_input").val(data.data.book_code);
                        if (!data.data.doc.document_number) {
                            $("#requestno").val('');
                            $('#doc_number_type').val('');
                            $('#doc_reset_pattern').val('');
                            $('#doc_prefix').val('');
                            $('#doc_suffix').val('');
                            $('#doc_no').val('');
                        } else {
                            $("#requestno").val(data.data.doc.document_number);
                            $('#doc_number_type').val(data.data.doc.type);
                            $('#doc_reset_pattern').val(data.data.doc.reset_pattern);
                            $('#doc_prefix').val(data.data.doc.prefix);
                            $('#doc_suffix').val(data.data.doc.suffix);
                            $('#doc_no').val(data.data.doc.doc_no);
                        }
                        if (data.data.doc.type == 'Manually') {
                            $("#requestno").attr('readonly', false);
                        } else {
                            $("#requestno").attr('readonly', true);
                        }
                    }
                    if (data.status == 404) {
                        $("#requestno").val('');
                        $('#doc_number_type').val('');
                        $('#doc_reset_pattern').val('');
                        $('#doc_prefix').val('');
                        $('#doc_suffix').val('');
                        $('#doc_no').val('');
                        alert(data.message);
                    }
                });
            });
        }
    </script>
@endsection
