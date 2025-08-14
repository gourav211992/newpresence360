/*Open import item modal*/
function openImportItemModal(type, mrnId=NULL) {
    if(!checkVendorFilledDetail()) {
        Swal.fire({
            title: 'Error!',
            text: 'Please fill vendor detail first',
            icon: 'error',
        });
        return false;
    }
    $("#importItemModal").modal('show');
    const storeId = $('.header_store_id').val(); // Replace with dynamic store ID if needed
    $('#importItemModal').find('input[name="store_id"]').remove(); // Remove existing hidden input if any
    $('#importItemModal').find('form').append(`<input type="hidden" name="store_id" value="${storeId}">`);
    $('#importItemModal').find('form').append(`<input type="hidden" name="type" value="${type}">`);
    $('#importItemModal').find('form').append(`<input type="hidden" name="mrn_header_id" value="${mrnId}">`);
}

feather.replace();
    var fileInput = $("#fileUpload");
    var backBtn = $(".btn-secondary"); 
    console.log("backBtn", backBtn, fileInput);
    
    let simulateProgress;
    const ALLOWED_EXTENSIONS = [
        'xls', 'xlsx'
    ];
    const ALLOWED_MIME_TYPES = [
        'application/vnd.ms-excel', 
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

    $(document).on('submit', '.importForm', function (e) {
        e.preventDefault(); 
        const currentForm = this;
        var submitButton = (e.originalEvent && e.originalEvent.submitter) || $(this).find(':submit');
        var submitButtonHtml = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>'; 
        submitButton.disabled = true;
        var method = $(this).attr('method');
        var url = $(this).attr('action');
        console.log("submitButton", submitButton, method, url);
        
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
                console.log('res', res);
                
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
                if (res.successful_items.length > 0) {
                    $('.processImportedBtn').show();
                } else {
                    $('.processImportedBtn').hide();
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
                        window.location.href = `/material-receipts/export-successful-items`; 
                    } else if (activeTab === '#Failed') {
                        window.location.href = `/material-receipts/export-failed-items`; 
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
                    <td>${item?.item_name}</td>
                    <td>${item?.uom_code}</td>
                    <td>${item?.hsn_code}</td>
                    <td>${item?.store_code}</td>
                    <td>${Number(item?.order_qty || 0).toFixed(2)}</td>
                    <td>${Number(item?.rate || 0).toFixed(2)}</td>
                    <td class="${item.status === 'success' ? 'text-success' : 'text-danger'}">
                        ${item.status === 'success' ? 'Success' : item.reason}
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

$(document).ready(function() {
    
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

function updateImportItemData(status)
{
    $.ajax({
        url: "/material-receipts/import-items-data/update",
        method: 'GET',
        dataType: 'json',
        data: {
            status : status,
        },
        success: function(data) {
            if((data.status == 200) && data.data.length) {
                
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: 'No data found',
                    icon: 'error',
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                title: 'Error!',
                text: xhr?.responseJSON?.message,
                icon: 'error',
            });
        }
    });
}
