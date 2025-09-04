@extends('layouts.app')
@section('styles')
<style>
/* Red active tab */
.nav-tabs .text-danger:after {
    background: linear-gradient(30deg, #dc3545, rgba(220, 53, 69, 0.5)) !important;
}
</style>
@endsection
@section('content')
        <div class="app-content content ">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        @include('layouts.partials.breadcrumb-add-edit', [
                            'title' => 'Purchase Order',
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
                                <button type="button" id = "draft-button" onclick = "uploadOrders('draft');" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 d-none"
                                    name="action" ><i data-feather='save'></i> Draft</button>
                                <button type="button" id = "submit-button" onclick = "uploadOrders('submitted');"  class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 d-none"
                                    name="action" ><i data-feather='check-circle'></i> Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-body">
                    <section id="basic-datatable">
                        <div class="row">
                            <div class="col-12">
                                <div class="card mb-1">
                                    <div class="card-body customernewsection-form">
                                        <div class="border-bottom mb-2 pb-25">
                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <div class="newheader">
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 d-flex align-items-center justify-content-end">
                                                    <a download href="{{$sampleFile}}" class="btn btn-outline-primary">
                                                        <i class="fas fa-download me-1"></i> Download Sample
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
                                                            <label class="form-label">Procurement Type <span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <select class="form-select" id="procurement_type" name="procurement_type">
                                                            </select>
                                                        </div>
                                                    </div>


                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Location <span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <select class="form-select" id="location_id" name="location_id">
                                                                @foreach ($stores as $store)
                                                                    <option value="{{ $store->id }}">
                                                                        {{ ($store->store_name) }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Import File <span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <input type="file" id = "attachment_input" accept=".xlsx, .xls, .csv" name="attachment" class="form-control">
                                                            <span class="text-primary small">{{__("(Allowed formats: .xlsx, .xls, .csv)")}}</span>
                                                        </div>
                                                        <div class="col-md-4 mb-2">
                                                        <button type="button" onclick = "uploadFile();" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0"
                                                            name="action" ><i data-feather='upload-cloud'></i> Upload</button>
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

                <div class="col-md-12 col-12 d-none" id = "upload-status-section">
                    <div class="text-primary mb-1">Only Valid records will be imported on submission</div>
                           <div class="card  new-cardbox"> 
                                <ul class="nav nav-tabs border-bottom" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#Succeded">Valid Records &nbsp;<span id="success-count">(0)</span></a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-danger" data-bs-toggle="tab" href="#Failed">Invalid Records &nbsp;<span id="failed-count">(0)</span></a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane active" id="Succeded">
                                        <div class="table-responsive">
									        <table class="datatables-basic table myrequesttablecbox"> 
                                                <thead>
                                                    <tr>
                                                    {!! $headers !!}
                                                    </tr>
                                                </thead>
                                                <tbody id="success-table-body">
                                                    <tr>
                                                        <td colspan = "9">No records found</td>
                                                    <tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="Failed">
                                        <div class="table-responsive">
									        <table class="datatables-basic table myrequesttablecbox"> 
                                                <thead>
                                                    <tr>
                                                        {!! $headers !!}
                                                        <th class = "no-wrap">Errors</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="failed-table-body">
                                                    <tr>
                                                        <td colspan = "10">No records found</td>
                                                    <tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
            </div>
        </div>
@endsection
@section('scripts')
<script type="text/javascript" src="{{ asset('app-assets/js/file-uploader.js') }}"></script>
<script>
    function uploadFile()
    {
        //Build Form Data
        let formData = new FormData();
        let file = $("#attachment_input");
        //Table Ids
        let successTable = document.getElementById('success-table-body');
        let failTable = document.getElementById('failed-table-body');
        let importSection = document.getElementById('upload-status-section');
        let successSectionCount = document.getElementById('success-count');
        let failCountSection = document.getElementById('failed-count');
        let draftButton = document.getElementById('draft-button');
        let submitButton = document.getElementById('submit-button');
        let successCount = 0;
        let errorCount = 0;
        //Check for Attachment
        if (!file || file.length <= 0) {
            Swal.fire({
                title: 'Error!',
                text: 'Please select a file first',
                icon: 'error',
            });
            return;
        }
        //Check if atlease one attachment is attached
        if (!file[0]?.files || file[0]?.files.length <= 0) {
            Swal.fire({
                title: 'Error!',
                text: 'Please select a file first',
                icon: 'error',
            });
            return;
        }
        let bookId = $("#book_id").val() || '';
        let locationId = $("#location_id").val() || '';
        let procurementType = $("#procurement_type").val() || '';
        formData.append('book_id', bookId);
        formData.append('location_id', locationId);
        formData.append('attachment', file[0].files[0]);
        formData.append('procurement_type', procurementType);
        //Hit the AJAX
        $.ajax({
            url: "{{route('purchaseOrder.import.save')}}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                //Loader
                document.getElementById('erp-overlay-loader').style.display = "flex";
            },
            success: function (response) {
                let data = response.data;
                let dataSuccessHTML = ``;
                let dataErrorHTML = ``;
                if (data && (data.valid_records > 0 || data.invalid_records > 0)) {
                    successTable.innerHTML = data.validUI;
                    failTable.innerHTML = data.invalidUI;
                    successSectionCount.innerHTML = `(${data.valid_records})`;
                    failCountSection.innerHTML = `(${data.invalid_records})`;
                    if (data.valid_records > 0) {
                        draftButton.classList.remove('d-none');
                        submitButton.classList.remove('d-none');
                    }
                    importSection.classList.remove('d-none');
                } else {
                    successTable.innerHTML = `
                    <tr>
                        <td colspan = "9">No records found</td>
                    <tr>
                    `;
                    failTable.innerHTML = `
                    <tr>
                        <td colspan = "10">No records found</td>
                    <tr>
                    `;
                    successSectionCount.innerHTML = `(0)`;
                    failCountSection.innerHTML = `(0)`;
                    draftButton.classList.add('d-none');
                    submitButton.classList.add('d-none');
                    importSection.classList.add('d-none');
                    Swal.fire({
                        title: 'Error!',
                        text: 'No items found from excel',
                        icon: 'error',
                    });
                }
            },
            error: function (xhr) {
                let errorResponse = xhr.responseJSON;
                document.getElementById('erp-overlay-loader').style.display = "none";
                successTable.innerHTML = `
                <tr>
                    <td colspan = "9">No records found</td>
                <tr>
                `;
                failTable.innerHTML = `
                <tr>
                    <td colspan = "10">No records found</td>
                <tr>
                `;
                successSectionCount.innerHTML = `(0)`;
                failCountSection.innerHTML = `(0)`;
                draftButton.classList.add('d-none');
                submitButton.classList.add('d-none');
                importSection.classList.add('d-none');
                Swal.fire({
                    title: 'Error!',
                    text: errorResponse?.message ? errorResponse?.message : 'Some internal error occured. Please try again later.',
                    icon: 'error',
                });
            },
            complete: function () {
                document.getElementById('erp-overlay-loader').style.display = "none";
            }
        });
    }

    function uploadOrders(documentStatus = 'draft')
    {
        window.removeEventListener('beforeunload', handleBeforeUnload);
        //Hit the AJAX
        $.ajax({
            url: "{{route('purchaseOrder.import.store')}}",
            type: 'POST',
            data: {
                book_id : $("#book_id").val(),
                location_id : $("#location_id").val(),
                procurement_type : $("#procurement_type").val(),
                document_status : documentStatus
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                //Loader
                document.getElementById('erp-overlay-loader').style.display = "flex";
            },
            success: function (response) {
                if (response.message) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                    });
                    window.location.href = "{{ $redirectUrl ?? route('po.index',['type' => 'purchase-order']) }}";
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message ? response.message : 'Some internal error occured, Please try again after some time.',
                        icon: 'error',
                    });
                }
            },
            error: function (xhr) {
                let errorResponse = xhr.responseJSON;
                Swal.fire({
                        title: 'Error!',
                        text: errorResponse?.message ? errorResponse?.message : 'Some internal error occured, Please try again after some time.',
                        icon: 'error',
                });
            },
            complete: function () {
                document.getElementById('erp-overlay-loader').style.display = "none";
            }
        });
    }
    
    window.addEventListener('beforeunload', handleBeforeUnload);

    function handleBeforeUnload(e)
    {
        let draftButton = document.getElementById('draft-button');
        let submitButton = document.getElementById('submit-button');
        if (!draftButton.classList.contains('d-none') && !submitButton.classList.contains('d-none')) {
            e.preventDefault();
            e.returnValue = '';
        }
    }
    $('#book_id').on('change',function(){
        var bookSelect = document.getElementById('book_id');
        var bookId = bookSelect.value; // ✅ get the actual book_id
        var selectedBook = bookSelect.options[bookSelect.selectedIndex]?.text;

        // ✅ use current date instead of #order_date_input
        var today = new Date().toISOString().split('T')[0];

        let actionUrl = '{{ route("book.get.doc_no_and_parameters") }}'
            + '?book_id=' + bookId
            + "&document_date=" + today;

        $.ajax({
            url: actionUrl,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                // Loader
                document.getElementById('erp-overlay-loader').style.display = "flex";
            },
            success: function (response) {
                if (response.data) {
                    // ✅ set book_code
                    $("#book_code").val(response.data.book_code ?? '');

                    // ✅ handle parameters if provided
                    if (response.data.parameters) {
                        let paramData = response.data.parameters;

                        /* Procurement Type */
                        const poProcurementType = paramData?.po_procurement_type || '';
                        const $procurementTypeSelect = $('#procurement_type');
                        const PO_PROCUREMENT_TYPE_VALUES = @json(\App\Helpers\CommonHelper::PO_PROCUREMENT_TYPE_VALUES);

                        $procurementTypeSelect.empty();

                        if (Array.isArray(poProcurementType) && poProcurementType[0] === 'All') {
                            // Show all available procurement types
                            PO_PROCUREMENT_TYPE_VALUES.forEach(function(value) {
                                $procurementTypeSelect.append(
                                    $('<option>', {
                                        value: value,
                                        text: value,
                                        selected: false
                                    })
                                );
                            });
                            // Default to first value
                            $procurementTypeSelect.val(PO_PROCUREMENT_TYPE_VALUES[0]).trigger('change');

                        } else if (Array.isArray(poProcurementType)) {
                            // Multiple specific values
                            poProcurementType.forEach(function(value, idx) {
                                $procurementTypeSelect.append(
                                    $('<option>', {
                                        value: value,
                                        text: value,
                                        selected: idx === 0 // preselect first
                                    })
                                );
                            });
                            $procurementTypeSelect.trigger('change');

                        } else if (typeof poProcurementType === 'string' && poProcurementType !== '') {
                            // Single value
                            $procurementTypeSelect.append(
                                $('<option>', {
                                    value: poProcurementType,
                                    text: poProcurementType,
                                    selected: true
                                })
                            );
                            $procurementTypeSelect.trigger('change');
                        }

                    }
                } else {
                    $("#book_code").val('');
                }
            },
            error: function (xhr) {
                let errorResponse = xhr.responseJSON;
                $("#book_code").val('');
                Swal.fire({
                    title: 'Error!',
                    text: errorResponse?.message ? errorResponse?.message : 'Some internal error occurred. Please try again later.',
                    icon: 'error',
                });
            },
            complete: function () {
                document.getElementById('erp-overlay-loader').style.display = "none";
            }
        });
    });
$('#book_id').trigger('change'); // Trigger change on page load to set initial values
</script>
@endsection
