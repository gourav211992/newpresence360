@extends('layouts.app')
@section('content')
    <form class="ajax-input-form" method="POST" action="{{ url($routeAlias) }}/import-save"
        data-redirect="{{ url($routeAlias) }}" enctype='multipart/form-data'>
        @csrf
        <input type="hidden" name="type" value="{{ $serviceAlias }}">
        <div class="app-content content ">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        @include('layouts.partials.breadcrumb-add-edit', [
                            'title' =>
                                $routeAlias == 'quotation-bom' ? 'Quotation BOM Import' : 'Production BOM Import',
                            'menu' => 'Home',
                            'menu_url' => url('home'),
                            'sub_menu' => 'Import',
                        ])
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <input type="hidden" name="document_status" id="document_status">
                                <button onClick="javascript: history.go(-1)"
                                    class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i>
                                    Back</button>
                                <button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button"
                                    name="action" value="draft"><i data-feather='save'></i> Save as Draft</button>
                                <button type="submit" class="btn btn-primary btn-sm submit-button" name="action"
                                    value="submitted"><i data-feather="check-circle"></i> Submit</button>
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
                                        {{-- <div class="border-bottom mb-2 pb-25">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader ">
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> --}}
                                        <div class="border-bottom mb-2 pb-25">
                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <div class="newheader">
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 d-flex align-items-center justify-content-end">
                                                    <a href="{{ url($routeAlias) }}/download-sample"
                                                        class="btn btn-outline-primary waves-effect">
                                                        <i class="fas fa-download me-1"></i> Download Sample
                                                    </a>
                                                    <a class="d-none btn btn-outline-danger waves-effect download-error-file-url mx-1"
                                                        href="#">
                                                        <i class="fas fa-download me-1"></i> Dowload Error File
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="">
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Series <span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <select class="form-select" id="book_id" name="book_id">
                                                                @foreach ($books as $book)
                                                                    <option value="{{ $book->id }}">
                                                                        {{ ucfirst($book->book_code) }}</option>
                                                                @endforeach
                                                            </select>
                                                            <input type="hidden" name="book_code" id="book_code">
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">BOM No <span
                                                                    class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <input type="text" name="document_number"
                                                                class="form-control" id="document_number">
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">BOM Date <span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <input type="date" class="form-control"
                                                                value="{{ date('Y-m-d') }}" name="document_date">
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Import File <span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <input type="file" accept=".xlsx, .xls, .csv"
                                                                name="attachment" class="form-control"
                                                                onchange = "addFiles(this,'main_bom_file_preview')">
                                                            <span
                                                                class="text-primary small">{{ __('(Allowed formats: .xlsx, .xls, .csv)') }}</span>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="row" id="main_bom_file_preview">
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
    {{-- <script type="text/javascript" src="{{asset('assets/js/modules/bom.js')}}"></script> --}}
    <script type="text/javascript" src="{{ asset('app-assets/js/file-uploader.js') }}"></script>
    <script type="text/javascript">
        $(function() {
            setTimeout(() => {
                if ($("#book_id").val()) {
                    $("#book_id").trigger('change');
                }
            }, 0);
            $(document).on('change', '#book_id', (e) => {
                let bookId = e.target.value;
                if (bookId) {
                    getDocNumberByBookId(bookId);
                } else {
                    $("#document_number").val('');
                    $("#book_id").val('');
                    $("#document_number").attr('readonly', false);
                }
            });

            function getDocNumberByBookId(bookId) {
                let document_date = $("[name='document_date']").val();
                let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId +
                    '&document_date=' + document_date;
                fetch(actionUrl).then(response => {
                    return response.json().then(data => {
                        if (data.status == 200) {
                            $("#book_code").val(data.data.book_code);
                            if (!data.data.doc.document_number) {
                                $("#document_number").val('');
                            }
                            $("#document_number").val(data.data.doc.document_number);
                            if (data.data.doc.type == 'Manually') {
                                $("#document_number").attr('readonly', false);
                            } else {
                                $("#document_number").attr('readonly', true);
                            }
                            const parameters = data.data.parameters;
                            setServiceParameters(parameters);
                        }
                        if (data.status == 404) {
                            $("#book_code").val('');
                            $("#document_number").val('');
                            const docDateInput = $("[name='document_date']");
                            docDateInput.removeAttr('min');
                            docDateInput.removeAttr('max');
                            docDateInput.val(new Date().toISOString().split('T')[0]);
                            alert(data.message);
                        }
                    });
                });
            }

            /*Set Service Parameter*/
            function setServiceParameters(parameters) {
                /*Date Validation*/
                const docDateInput = $("[name='document_date']");
                let isFeature = false;
                let isPast = false;
                if (parameters.future_date_allowed && parameters.future_date_allowed.includes('yes')) {
                    let futureDate = new Date();
                    futureDate.setDate(futureDate.getDate() /*+ (parameters.future_date_days || 1)*/ );
                    docDateInput.val(futureDate.toISOString().split('T')[0]);
                    docDateInput.attr("min", new Date().toISOString().split('T')[0]);
                    isFeature = true;
                } else {
                    isFeature = false;
                    docDateInput.attr("max", new Date().toISOString().split('T')[0]);
                }
                if (parameters.back_date_allowed && parameters.back_date_allowed.includes('yes')) {
                    let backDate = new Date();
                    backDate.setDate(backDate.getDate() /*- (parameters.back_date_days || 1)*/ );
                    docDateInput.val(backDate.toISOString().split('T')[0]);
                    // docDateInput.attr("max", "");
                    isPast = true;
                } else {
                    isPast = false;
                    docDateInput.attr("min", new Date().toISOString().split('T')[0]);
                }
                /*Date Validation*/
                if (isFeature && isPast) {
                    docDateInput.removeAttr('min');
                    docDateInput.removeAttr('max');
                }
                let reference_from_service = parameters.reference_from_service;
                if (!reference_from_service.length) {
                    Swal.fire({
                        title: 'Error!',
                        text: "Please update first reference from service param.",
                        icon: 'error',
                    });
                    setTimeout(() => {
                        location.href = "{{ url($routeAlias) }}";
                    }, 1500);
                }
            }
        });
    </script>
@endsection
