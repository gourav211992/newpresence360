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
                            <h2 class="content-header-title float-start mb-0 border-0">Upload Bank Account Statement</h2>

                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <a href="{{ route('bank.ledgers.index') }}{{ request()->has('date') ? '?date=' . request()->get('date') : '' }}"
                            class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</a>
                        <a class="btn btn-primary btn-sm mb-50 mb-sm-0"
                            href="{{ asset('app-assets/sample/statement.csv') }}"><i data-feather="arrow-down-circle"></i>
                            Download Sample</a>
                        <a class="btn btn-success btn-sm mb-50 mb-sm-0"
                            href="{{ route('bank.statements.match-entries', ['id' => $id]) }}{{ request()->has('date') ? '?date=' . request()->get('date') : '' }}"><i
                                data-feather="check-circle"></i> Match
                            Statement</a>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <form class="form" role="post-data" method="POST"
                        action="{{ route('bank.statements.save', ['id' => $id]) }}" enctype="multipart/form-data"
                        autocomplete="off">
                        @csrf
                        <div class="row justify-content-center">
                            <div class="col-9">
                                <div class="upload-item-masstrerdata">
                                    <div class="drapdroparea upload-btn-wrapper text-center" id="file-input-container">
                                        <i class="uploadiconsvg" data-feather='upload'></i>
                                        <p>Upload the template file with updated data</p>
                                        <button class="btn btn-primary" id="upload-btn">DRAG AND DROP HERE OR CHOOSE
                                            FILE</button>
                                        <input type="file" id="file-input" name="bank_file" accept=".csv"
                                            class="form-control" />
                                    </div>

                                    <div id="file-name-container"
                                        class="drapdroparea drapdroparea-small upload-btn-wrapper text-center"
                                        style="display: none">
                                        <span id="selected-file-name"
                                            class="badge rounded-pill badge-light-warning fw-bold mb-1 badgeborder-radius d-flex align-items-center">No
                                            file selected</span>
                                        <button type="button" id="submit-upload" class="btn btn-primary"
                                            data-request="upload-file" data-target="[role=post-data]">Proceed
                                            to Upload</button>

                                        <div class="w-75 mt-3" id="progress-container" style="display: none">
                                            <div class="progress" style="height: 15px">
                                                <div class="progress-bar progress-bar-striped bg-success progress-bar-animated"
                                                    role="progressbar" aria-valuenow="75" aria-valuemin="0"
                                                    aria-valuemax="100" style="width: 75%">75%</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="drapdroparea drapdroparea-small upload-btn-wrapper text-center"
                                        id="error-container" style="display: none;"></div>

                                    <div class="drapdroparea drapdroparea-small upload-btn-wrapper text-center"
                                        id="success-container" style="display: none;"></div>

                                </div>
                            </div>

                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
    <!-- END: Content-->
@endsection

@section('scripts')
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>
    <script>
        $('#file-input').on('change', function() {
            const filename = this.files[0]?.name || "No file selected";
            $('#selected-file-name').text(filename);
            $('#file-name-container').show();
            $('#file-input-container').hide();
        });

        $(document).on('click', '#upload-again-btn', function() {
            $('#file-input-container').show(); // Show drag and drop area
            $('#file-name-container').hide(); // Hide file info area
            $('#error-container').hide(); // Hide error message
            $('#success-container').hide(); // Hide success message
            $('#file-input').val(null); // Optional: Reset file input
        });

        $(document).on("click", '[data-request="upload-file"]', function() {
            var $this = $(this);
            var $target = $this.data("target");
            var $url = $($target).attr("action");
            var $method = $($target).attr("method");
            var $redirect = $($target).attr("redirect");
            var $data = new FormData($($target)[0]);

            // Disable button and hide file name container on upload
            $this.prop('disabled', true);
            $('#file-name-container').hide();
            $('#error-container').hide();
            $('#success-container').hide();
            $('#progress-container').show();
            $('#progress-container .progress-bar').css('width', '0%').text('0%');

            $.ajax({
                url: $url,
                data: $data,
                type: $method,
                dataType: "JSON",
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#file-name-container').show();
                    $('#submit-upload').prop('disabled', true);
                },
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                            $('#progress-container .progress-bar')
                                .css('width', percentComplete + '%')
                                .text(percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function($response) {
                    $('#file-name-container').hide();
                    if ($response.status == 200) {
                        let successCount = $response.data.successfulRows || 0;
                        let failureCount = $response.data.failedRows || 0;
                        let failures = $response.data.failures || [];

                        let failureHtml = '';
                        if (failureCount > 0) {
                            let modalHtml = '<ul>';
                            failures.forEach(function(failure) {
                                if (failure.chqref_no) {
                                    modalHtml +=
                                        `<li><strong>Chq/Ref No ${failure.chqref_no}:</strong><ul>`;
                                } else {
                                    modalHtml += `<li><strong>Row ${failure.row}:</strong><ul>`;
                                }
                                failure.errors.forEach(function(error) {
                                    modalHtml += `<li>${error}</li>`;
                                });
                                modalHtml += '</ul></li>';
                            });
                            modalHtml += '</ul>';
                            $('#errorDetailsBody').html(modalHtml);
                        }

                        let successHtml = `
                            <i class="itemdatasuccesssmaster" data-feather='check-circle'></i>
                            <p>Your Statement has been uploaded successfully.<br>
                            Please proceed to Reconcile.</p>
                            <div class="d-flex flex-column align-items-start">
                                <span class="badge rounded-pill badge-light-success fw-bold mb-1">
                                    Transaction Succeeded: ${successCount}
                                </span>
                                ${failureCount > 0 ? 
                                    `<span class="badge rounded-pill badge-light-danger fw-bold mb-1">Failed Rows: ${failureCount}</span>` 
                                : ''}
                            </div>`;

                        $('#success-container').html(successHtml).show();
                        if (typeof feather !== 'undefined') {
                            feather.replace();
                        }

                        // Show SweetAlert and redirect on confirmation
                        Swal.fire({
                            title: 'Upload Successful!',
                            text: 'Your statement has been uploaded. Click OK to proceed to Reconciliation.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const currentUrl = window.location.href.split('?')[0];
                                window.location.href = currentUrl +
                                    '?type=success-statement&batch_uid=' +
                                    $response.data.batchId;
                            }
                        });

                        setTimeout(function() {
                            const currentUrl = window.location.href.split('?')[0];
                            window.location.href = currentUrl +
                                '?type=success-statement&batch_uid=' +
                                $response.data.batchId;
                        }, 3000);
                    }

                    $this.prop('disabled', false);
                },
                error: function($response) {
                    var errorMessage = $response.responseJSON && $response.responseJSON.message ?
                        $response.responseJSON.message :
                        'An unknown error occurred.';

                    let errorHtml = `
                    <i class="alertdropdatamaster" data-feather='alert-triangle'></i>
                    <p>${errorMessage}</p>
                    <div class="mt-2 downloadtemplate">
                        <button type="button" class="editbtnNew" id="upload-again-btn">
                            <i data-feather='upload'></i> Upload Again
                        </button>
                    </div>`;

                    $('#error-container').html(errorHtml).show();
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }

                    $('#file-name-container').hide();
                    $('#progress-container').hide();
                    $this.prop('disabled', false);
                }
            });

        });

        $(document).on('click', 'button[data-bs-toggle="modal"]', function(e) {
            e.preventDefault(); // stop form submission
        });
    </script>
@endsection
