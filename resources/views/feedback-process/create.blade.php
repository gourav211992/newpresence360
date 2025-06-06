@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <form class="ajax-input-form" method="POST" action="{{ route('feedback-process.store') }}" data-redirect="{{ url('/feedback-process') }}">
        @csrf
        <input type ="hidden" name="book_code" id ="book_code_input">
        <input type="hidden" name="doc_number_type" id="doc_number_type">
        <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern">
        <input type="hidden" name="doc_prefix" id="doc_prefix">
        <input type="hidden" name="doc_suffix" id="doc_suffix">
        <input type="hidden" name="doc_no" id="doc_no">
        <div class="app-content content">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header">
                    <div class="row">
                        <div class="content-header-left col-md-6 col-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Feedback Process</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="{{ route('feedback-process.index') }}">Home</a></li>
                                            <li class="breadcrumb-item active">Feedback Process</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
                            <button type="submit" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Create</button>
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
                                                <label class="form-label">Series <span
                                                        class="text-danger">*</span></label>
                                            </div>

                                            <div class="col-md-5">
                                                <select class="form-select" name="book_id" id="series" required>
                                                    <option value="" disabled selected>Select</option>
                                                    @foreach ($series as $ser)
                                                        <option value="{{ $ser->id }}">{{ $ser->book_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Request No. <span
                                                        class="text-danger">*</span></label>
                                            </div>

                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="document_number" readonly
                                                       id="requestno" value="{{ old('requestno') }}">
                                                <input type="hidden" name="status" id="status" value="">
                                            </div>
                                        </div>
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Date <span
                                                        class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="date" onchange="getDocNumberByBookId()"
                                                       class="form-control" name="document_date" id="document_date"
                                                       value="{{ old('document_date', date('Y-m-d')) }}">
                                                @error('document_date')
                                                <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Party Type <span
                                                        class="text-danger">*</span></label>
                                            </div>

                                            <div class="col-md-5">
                                                <select class="form-select" name="user_type_id" required>
                                                    <option value="" disabled selected>Select</option>
                                                    @foreach ($userTypes as $type)
                                                        <option value="{{ $type->id }}">{{ $type->name }}
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
                                                <input class="form-control" type="text" name="party_name" placeholder="Enter Party Name">
                                            </div>
                                        </div>
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Interaction Type<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-md-5">
                                                <select name="interaction_type_id" class="form-control">
                                                    <option value="">Select Interaction Type</option>
                                                    @foreach($interactionTypes as $type)
                                                        <option value="{{$type->id}}" {{ old('type') == $type->id ? 'selected' : '' }}>{{$type->name}}</option>
                                                    @endforeach
                                                </select>
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
                                                        <option value="{{ $stakeholder['id'] }}">
                                                            {{ $stakeholder['name'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <input type="hidden" name="userable_type" id="user_type">
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Action Taken <span
                                                        class="text-danger">*</span></label>
                                            </div>

                                            <div class="col-md-5">
                                                <textarea class="form-control" name="action" placeholder=""></textarea>
                                            </div>
                                            @error('notes')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Comment<span
                                                        class="text-danger">*</span></label>
                                            </div>

                                            <div class="col-md-5">
                                                <textarea class="form-control" name="comment" placeholder=""></textarea>
                                            </div>
                                            @error('followup')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        {{--                                        <div class="row align-items-center mb-1">--}}
                                        {{--                                            <div class="col-md-3">--}}
                                        {{--                                                <label class="form-label">Name<span class="text-danger">*</span></label>--}}
                                        {{--                                            </div>--}}
                                        {{--                                            <div class="col-md-5">--}}
                                        {{--                                                <input type="text" name="name" class="form-control" value="" placeholder="Enter Document Name" />--}}
                                        {{--                                            </div>--}}
                                        {{--                                        </div>--}}
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
        $('#series').on('change', function() {
            getDocNumberByBookId();
        });
        document.getElementById('user_type_select').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var userType = selectedOption.getAttribute('data-type');
            document.getElementById('user_type').value = userType;
        });
        function getDocNumberByBookId() {
            let currentDate = new Date().toISOString().split('T')[0];
            let bookId = $('#series').val();
            // console.log('bookid:' + bookId);
            let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId + "&document_date=" +
                currentDate;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    console.log(data);
                    if (data.status == 200) {
                        console.log(data);
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
