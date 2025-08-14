@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-5 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Upload Master Data</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>  
                                    <li class="breadcrumb-item active">Item Master</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <a href="{{ route('item.index') }}" class="btn btn-secondary btn-sm mb-50 mb-sm-0">
                            <i data-feather="arrow-left-circle"></i> Back
                        </a>
                        <a href="{{ asset('templates/Item_sample_template.xlsx') }}" class="btn btn-secondary btn-sm" id="download-template-btn" download>
                            <i data-feather="download"></i> Download Template
                        </a> 
                    </div>
                </div>
                
            </div>
            <div class="content-body">
				<section id="basic-datatable">
                    <div class="row justify-content-center">
                        <div class="col-9">
                        <form class="importForm" method="POST" action="{{ route('items.import') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="upload-item-masstrerdata">
                                <!-- File Upload Section -->
                                <div class="drapdroparea upload-btn-wrapper text-center default-dragdrop-area-unique">
                                    <i class="uploadiconsvg" data-feather='upload'></i>
                                    <p>Upload the template file with updated data</p>
                                    <button class="btn btn-primary">DRAG AND DROP HERE OR CHOOSE FILE</button>  
                                    <input type="file" name="file" accept=".xlsx, .xls, .csv" class="form-control" id="fileUpload"/>
                                </div>
                
                                <div class="drapdroparea drapdroparea-small upload-btn-wrapper text-center" id="fileNameDisplay" style="display: none;">
                                    <div class="badge rounded-pill badge-light-warning fw-bold mb-1 badgeborder-radius d-flex align-items-center"> <span id="selectedFileName"></span> <i data-feather='x-circle' id="cancelBtn" class="ms-75"></i></div>
                                    <button type="submit" class="btn btn-primary">Proceed to Upload</button>
                                </div>

                                <div class="drapdroparea drapdroparea-small upload-btn-wrapper text-center" id="uploadProgress" style="display: none;">
                                    <span class="badge rounded-pill badge-light-warning fw-bold mb-1 badgeborder-radius d-flex align-items-center" id="progressFileName">
                                        <span id="selectedFileName"></span>
                                    </span>
                                    <button class="btn btn-primary" disabled>Proceed to Upload</button>
                                    <div class="w-75 mt-3">
                                        <div class="progress" style="height: 15px">
                                            <div id="uploadProgressBar" class="progress-bar progress-bar-striped bg-success progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">0%</div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Error Section -->
                                <div class="drapdroparea drapdroparea-small upload-btn-wrapper text-center" id="uploadError" style="display: none;">
                                    <i class="alertdropdatamaster" data-feather='alert-triangle'></i><br>
                                    <div class="alert alert-danger" id="upload-error" style="display: none;"></div>
                                    <div class="mt-2 downloadtemplate"> 
                                        <button class="editbtnNew">
                                            <i data-feather='upload'></i> Upload Again
                                        </button> 									
                                    </div> 	
                                </div>

                                <!-- Success Section -->
                                <div class="drapdroparea drapdroparea-small upload-btn-wrapper text-center" id="uploadSuccess" style="display: none;">
                                    <i class="itemdatasuccesssmaster" data-feather='check-circle'></i>
                                    <p>All records have been uploaded successfully.<br>
                                    Please proceed to process sales.</p>
                                    <div class="d-flex">
                                        <span class="badge rounded-pill badge-light-success fw-bold me-1 font-small-2 badgeborder-radius" id="success-count-badge"></span>
                                    </div>
                                </div>
                            </div>
                        </form>
                        </div>
                        
                        <div class="col-md-11 mt-3 col-12 hide-this-section" style="display:none">
                           <div class="card  new-cardbox"> 
                                <ul class="nav nav-tabs border-bottom" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#Succeded">Records Succeeded &nbsp;<span id="success-count">(0)</span></a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#Failed">Records Failed &nbsp;<span id="failed-count">(0)</span></a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane active" id="Succeded">
                                        <div class="text-end my-1">
                                           <button type="button" class="btn btn-warning btn-sm mb-50 mb-sm-0 me-50 exportBtn"><i data-feather="download"></i>Export to Excel</button>
                                        </div>
                                        <div class="table-responsive candidates-tables">
                                            <table class="datatables-basic datatables-success table table-striped myrequesttablecbox loanapplicationlist">
                                                <thead>
                                                    <tr>
                                                        <th>S.No</th>
                                                        <th>Item Code</th>
                                                        <th>Item Name</th>
                                                        <th>Inventory Unit</th>
                                                        <th>HSN</th>
                                                        <th>Type</th>
                                                        <th>Sub-Type</th>
                                                        <th>Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="success-table-body">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="Failed">
                                        <div class="text-end my-1">
                                           <button type="button" class="btn btn-warning btn-sm mb-50 mb-sm-0 me-50 editbtnNew"><i data-feather='upload'></i>Upload Again</button>
                                        </div>
                                        <div class="text-end my-1">
                                           <button type="button" class="btn btn-warning btn-sm mb-50 mb-sm-0 me-50 exportBtn"><i data-feather="download"></i>Export to Excel</button>
                                        </div>
                                        <div class="table-responsive candidates-tables">
                                            <table class="datatables-basic datatables-failed table table-striped myrequesttablecbox loanapplicationlist">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Item Code</th>
                                                        <th>Item Name</th>
                                                        <th>Inventory Unit</th>
                                                        <th>HSN</th>
                                                        <th>Type</th>
                                                        <th>Sub-Type</th>
                                                        <th>Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="failed-table-body">
                                                </tbody>
                                            </table>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    $(document).ready(function() {
    feather.replace();
    var fileInput = $("#fileUpload");
    var backBtn = $(".btn-secondary"); 
    const IMPORT_TIMEOUT = 5000; 
    let timeoutWarningShown = false;
    let simulateProgress;
    const ALLOWED_EXTENSIONS = [
        'xls', 'xlsx'
    ];

    const ALLOWED_MIME_TYPES = [
        'application/vnd.ms-excel', 
        'application/excel',
        'application/x-excel', 
        'application/x-msexcel', 
        'application/vnd.ms-office', 
        'application/kset',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
    ];
    
    const MAX_FILE_SIZE = 30 * 1024 * 1024;
    const MAX_ROW_COUNT = 10000; 
    $(".drapdroparea").on("dragover", function(event) {
        event.preventDefault();
        $(this).addClass("dragging");
    });

    $(".drapdroparea").on("dragleave", function(event) {
        event.preventDefault();
        $(this).removeClass("dragging");
    });

    $(".drapdroparea").on("drop", function(event) {
        event.preventDefault();
        $(this).removeClass("dragging");

        var files = event.originalEvent.dataTransfer.files;
        if (files.length) {
            fileInput[0].files = files;
            handleFileSelected(files[0]);
        }
    });

    fileInput.on('change', function(e) {
        var file = e.target.files[0];
        if (!file) {
            console.warn("No file selected.");
            return;
        }

        handleFileSelected(file); 
    });
    function handleFileSelected(file) {
        var fileName = file.name;
        const fileSize = file.size;
        const fileExtension = fileName.split('.').pop().toLowerCase();
        $('#upload-error').hide().html('');

        if (!ALLOWED_EXTENSIONS.includes(fileExtension)) {
            displayError(`Invalid file type. Allowed types are: ${ALLOWED_EXTENSIONS.join(', ')}`);
            fileInput.val(''); 
            return;
        }

        if (!ALLOWED_MIME_TYPES.includes(file.type)) {
            displayError(`Invalid MIME type for the selected file.`);
            fileInput.val(''); 
            return;
        }

        if (fileSize > MAX_FILE_SIZE) {
            displayError(`File size exceeds ${MAX_FILE_SIZE / 1024 / 1024} MB (30 MB). Please upload a smaller file.`);
            fileInput.val('');
            return;
        }

        if (fileExtension === 'xlsx' || fileExtension === 'xls') {
            const reader = new FileReader();
            reader.onload = function (event) {
                const data = new Uint8Array(event.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const sheetName = workbook.SheetNames[0];
                const sheet = workbook.Sheets[sheetName];
                const range = XLSX.utils.decode_range(sheet['!ref']);
                const rowCount = range.e.r + 1; 
                if (rowCount > MAX_ROW_COUNT) {
                    displayError(`File contains more than ${MAX_ROW_COUNT} rows. Please upload a smaller file.`);
                    fileInput.val(''); 
                    return;
                }
                $('#selectedFileName').text(fileName);
                $('#fileNameDisplay').show();
                $(".default-dragdrop-area-unique").hide();
                $('#proceedBtn').show();
            };
            reader.readAsArrayBuffer(file); 
        }
    }
    function displayError(message) {
        $(".default-dragdrop-area-unique").hide();
        $('#fileNameDisplay').hide(); 
        $('#uploadError').show()
        $('#upload-error').html(message).show(); 
    }

    $('#cancelBtn').on('click', function() {
        clearInterval(simulateProgress); 
        $('#fileNameDisplay').hide();
        $(".default-dragdrop-area-unique").show();
        $("#fileUpload").val('');
        $('#selectedFileName').text('');  
        $('#proceedBtn').hide();
        $('#uploadProgress').hide();
        $('#uploadSuccess').hide();
        $('#uploadError').hide();
    });
    function showTimeoutMessage() {
        Swal.fire({
            title: 'Processing Your Import',
            html: 'Your file is being processed. This may take some time depending on the file size.<br><br>' +
                    'You will be notified by email once the import is complete.',
            icon: 'info',
            showConfirmButton: true,
            timer: 10000
        });
    }
    $(document).on('submit', '.importForm', function (e) {
        e.preventDefault(); 
        const currentForm = this;
        var submitButton = (e.originalEvent && e.originalEvent.submitter) || $(this).find(':submit');
        var submitButtonHtml = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>'; 
        submitButton.disabled = true;
        var method = $(this).attr('method');
        var url = $(this).attr('action');
        var data = new FormData($(this)[0]); 
        $('#fileNameDisplay').hide();
        $('#uploadProgress').show();
        $('.default-dragdrop-area-unique').hide();
        const uploadProgressBar = $('#uploadProgressBar');
        uploadProgressBar.css('width', '0%').text('0%').attr('aria-valuenow', 0);
        const fileName = $('#fileUpload').val().split('\\').pop();
        $('#selectedFileName').text(fileName);
        let simulatedProgress = 0;
        const simulateProgress = setInterval(() => {
            if (simulatedProgress < 100) {
                simulatedProgress += 10; 
                uploadProgressBar.css('width', simulatedProgress + '%')
                    .text(simulatedProgress + '%')
                    .attr('aria-valuenow', simulatedProgress);
                $('#uploadPercentage').text(`${simulatedProgress}% uploaded`);
            } else {
                clearInterval(simulateProgress);
            }
        }, 200);
        $.ajax({
            url: url,
            type: method,
            data: data,
            contentType: false,
            processData: false,
            beforeSend: function () {
                setTimeout(function () {
                    if (!timeoutWarningShown) {
                        showTimeoutMessage();
                        timeoutWarningShown = true;
                    }
                }, IMPORT_TIMEOUT);
            },
            xhr: function() {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    uploadProgressBar.css('width', percent + '%')
                        .text(percent + '%')
                        .attr('aria-valuenow', percent);
                    $('#uploadPercentage').text(`${percent}% uploaded`);
                } else {
                    console.log("lengthComputable is false");
                    uploadProgressBar.css('width', '0%').text('0%').attr('aria-valuenow', 0);
                    $('#uploadPercentage').text('Uploading...');
                }
            }, false);
            return xhr;
          },
            success: function (res) {
                clearInterval(simulateProgress); 
                submitButton.disabled = false;
                submitButton.innerHTML = submitButtonHtml;
                $('.ajax-validation-error-span').remove();
                $(".is-invalid").removeClass("is-invalid");
                $(".help-block").remove();
                $(".waves-ripple").remove();
                if (res.status === 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: res.message,
                        icon: 'success',  
                    });
                } else if (res.status === 'failure') {
                    Swal.fire({
                        title: 'Failure!',
                        text: res.message,
                        icon: 'error', 
                    });
                }
                populateTable('#success-table-body', res.successful_items);
                populateTable('#failed-table-body', res.failed_items);
                $('#success-count-badge').text(`Records Succeeded: ${res.successful_items.length}`);
                $('#success-count').text(`(${res.successful_items.length})`);
                $('#failed-count').text(`(${res.failed_items.length})`);
                $('#uploadProgress').hide();
                $('.hide-this-section').show();
                if (res.failed_items.length > 0) {
                    $('.editbtnNew').show();  
                } else {
                    $('.editbtnNew').hide(); 
                }
                $('.exportBtn').on('click', function() {
                    var activeTab = $('.nav-link.active').attr('href'); 
                    if (activeTab === '#Succeded') {
                        window.location.href = `/items/export-successful-items`; 
                    } else if (activeTab === '#Failed') {
                        window.location.href = `/items/export-failed-items`; 
                    }
                });
            },
            error: function (xhr, status, error) {
                $('#fileNameDisplay').hide(); 
                clearInterval(simulateProgress);
                submitButton.disabled = false;
                submitButton.innerHTML = submitButtonHtml;
                $('.ajax-validation-error-span').remove();
                $(".is-invalid").removeClass("is-invalid");
                $(".help-block").remove();
                $(".waves-ripple").remove();
                $('#uploadProgress').hide();

                $('#upload-error').html(''); 
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                  
                        let errorMessage = ''; 
                        $.each(xhr.responseJSON.errors, function (key, value) {
                            $.each(value, function (index, errorMsg) {
                                errorMessage += `${errorMsg}<br>`; 
                            });
                        });
                        $('#upload-error').html(errorMessage).show();
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        $('#upload-error').html(xhr.responseJSON.message).show();
                } else {
                    $('#upload-error').html('An error occurred while processing the file. Please try again.').show();
                }
                $("#uploadError").show();
            }
        });
    });

    function populateTable(tableBodySelector, items) {
            const tableBody = $(tableBodySelector);
            tableBody.empty(); 
            if (items.length > 0) {
            items.forEach((item, index) => {
                const row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td class="fw-bolder text-dark">${item.item_code}</td>
                        <td>${item.item_name}</td>
                        <td>${item.uom}</td>
                        <td>${item.hsn}</td>
                        <td>${item.type}</td>
                        <td><span class="badge rounded-pill badge-light-secondary">${item.sub_type}</span></td>
                        <td class="${item.status === 'success' ? 'text-success' : 'text-danger'}">
                            ${item.status === 'success' ? 'Success' : item.remarks}
                        </td>
                    </tr>
                `;
                tableBody.append(row);
            });
            } else {
                const noDataRow = `<tr><td colspan="8" class="text-center">No records found</td></tr>`;
                tableBody.append(noDataRow); 
            }
        }
    });
    $(document).on('click', '.editbtnNew', function(e) {
        e.preventDefault();
        feather.replace();
        $('#uploadError').hide();
        $('#upload-error').hide(); 
        $('#uploadProgress').hide();
        $('.default-dragdrop-area-unique').show();
        $('#fileUpload').val('');
        $('#fileNameDisplay, #uploadProgress').hide();
        $('.hide-this-section').hide();
    });
</script>

@endsection
