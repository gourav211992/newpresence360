function fetchLoanSeries(book_type_id, id) {
    $("#appli_no").prop('readonly', false);
    return $.ajax({
        url: getSeriesUrl,
        method: 'GET',
        data: {
            book_type_id: book_type_id
        },
        success: function(response) {
            if (response.success === 1) {
                $("#" + id).html(response.html);
            } else {
                alert(response.msg);
                $("#" + id).html(response.html);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            alert('An error occurred while fetching the data.');
        }
    });
}

function fetchSeriesBased(series_id, id, baseUrl) {
    var getVoucherUrl = baseUrl + '/' + series_id;
    $.ajax({
        url: getVoucherUrl,
        method: 'GET',
        success: function(response) {
            if (response.type=="Auto") {
                $("#" + id).attr("readonly", true);
                $("#" + id).val(response.voucher_no);
            } else {
                $("#" + id).attr("readonly", false);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            alert('An error occurred while fetching the data.');
        }
    });
}


function uploadMultipleDoc(files, list){
    var files = files;
    var $fileList = $('#' + list);
    $fileList.empty();

    $.each(files, function(index, file) {
        var fileSize = (file.size / 1024).toFixed(2) + ' KB';
        var fileName = file.name;
        var fileExtension = fileName.split('.').pop().toUpperCase();

        var $fileDiv = $('<div class="image-uplodasection mb-2"></div>');
        var $fileIcon = $('<i data-feather="file" class="fileuploadicon"></i>');
        var $fileName = $('<span class="file-name d-block"></span>').text(fileExtension + ' file').css('font-size', '10px'); // Display extension
        var $fileInfo = $('<span class="file-info d-block"></span>').text(fileSize).css('font-size', '10px'); // Display file size on the next line
        var $deleteDiv = $('<div class="delete-img text-danger"><i data-feather="x"></i></div>');

        $fileDiv.append($fileIcon).append($fileName).append($fileInfo).append($deleteDiv);
        $fileList.append($fileDiv);
        feather.replace();
    });
}

$(document).on('click', '.delete-img', function() {
    $(this).closest('.image-uplodasection').remove();
});


function fetchApproveRecord(id){
    var disbursal_id = $("#checkedData").val();
    if(disbursal_id){
        $.ajax({
            url: getDisburs,
            method: 'GET',
            data:{
                disbursal_id: disbursal_id
            },
            success: function(response) {
                if(response.success === 1){
                    let loan_disbursementData = response.loan_disbursement;
                    if(id === 2){
                        if(loan_disbursementData.status == 2){
                            $('#dis_appr_remark').val(loan_disbursementData.dis_appr_remark);
                            $("#fileList").html(response.html);
                        }else{
                            $('#dis_appr_remark').val('');
                            $("#fileList").html('');
                        }
                    }else{
                        if(loan_disbursementData.status == 3){
                            $('#dis_appr_remark_re').val(loan_disbursementData.dis_appr_remark);
                            $("#fileListRE").html(response.html);
                        }else{
                            $('#dis_appr_remark_re').val('');
                            $("#fileListRE").html('');
                        }
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('An error occurred while fetching the data.');
            }
        });
    }
}


function fetchREcApproveRecord(id){
    var recovery_id = $("#checkedData").val();
    if(recovery_id){
        $.ajax({
            url: getrec,
            method: 'GET',
            data:{
                recovery_id: recovery_id
            },
            success: function(response) {

                if(response.success === 1){
                    let loan_recoveryData = response.loan_recovery;
					console.log(loan_recoveryData.rec_appr_remark);
                    if(id === 1){
                        if(loan_recoveryData.rec_appr_status == 1){
                            $('#rc_appr_remark').val(loan_recoveryData.rec_appr_remark);
                            $("#fileList").html(response.html);
                        }else{
                            $('#rc_appr_remark').val('');
                            $("#fileList").html('');
                        }
                    }else{
                        if(loan_recoveryData.rec_appr_status == 2){
                            $('#re_appr_remark').val(loan_recoveryData.rec_appr_remark);
                            $("#fileListRE").html(response.html);
                        }else{
                            $('#re_appr_remark').val('');
                            $("#fileListRE").html('');
                        }
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('An error occurred while fetching the data.');
            }
        });
    }
}


function fetchSTcApproveRecord(id){
    var settle_id = $("#checkedData").val();
    if(settle_id){
        $.ajax({
            url: getset,
            method: 'GET',
            data:{
                settle_id: settle_id
            },
            success: function(response) {

                if(response.success === 1){
                    let loan_settleData = response.loan_settle;
                    if(id === 1){
                        if(loan_settleData.settle_appr_status == 1){
                            $('#st_appr_remark').val(loan_settleData.settle_appr_remark);
                            $("#fileList").html(response.html);
                        }else{
                            $('#st_appr_remark').val('');
                            $("#fileList").html('');
                        }
                    }else{
                        if(loan_settleData.settle_appr_status == 2){
                            $('#ste_appr_remark').val(loan_settleData.settle_appr_remark);
                            $("#fileListRE").html(response.html);
                        }else{
                            $('#ste_appr_remark').val('');
                            $("#fileListRE").html('');
                        }
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('An error occurred while fetching the data.');
            }
        });
    }
}

function validatePanGir(inputSelector, errorSelector, maxLength) {
    var inputElement = $(inputSelector);
    var inputValue = inputElement.val().trim();

    // Regex for PAN/GIR format
    var panGirRegex = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;

    // Check format validation
    if (!panGirRegex.test(inputValue)) {
        $(errorSelector).text('Invalid PAN/GIR NO format. It must be in the format XXXXX0000X');
        return false;
    }

    // Clear any previous errors
    $(errorSelector).text('');

    // Check for uniqueness among all inputs with the class 'validate-pan-name'
    var panNumbers = [];
    var isDuplicate = false;

    $('.validate-pan-name').each(function () {
        var panValue = $(this).val().trim();
        if (panValue) {
            if (panNumbers.includes(panValue)) {
                isDuplicate = true;
            }
            panNumbers.push(panValue);
        }
    });

    if (isDuplicate) {
        $(errorSelector).text('PAN/GIR NO must be unique across all fields.');
        return false;
    }

    return true;
}


function checkFileTypeandSize(event) {
    const file = event.target.files[0];

    if (file) {
        const maxSizeMB = 5;
        const fileSizeMB = file.size / (1024 * 1024);

        const videoExtensions = /(\.mp4|\.avi|\.mov|\.wmv|\.mkv)$/i;
        if (videoExtensions.exec(file.name)) {
            alert("Video files are not allowed.");
            event.target.value = "";
            return;
        }

        if (fileSizeMB > maxSizeMB) {
            alert("File size should not exceed 5MB.");
            event.target.value = "";
            return;
        }
    }
}
function showToast(icon, title) {
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        },
    });
    Toast.fire({ icon, title });
}

function validateAgeData(inputElement) {
    const dobValue = inputElement.value;
    const messageId = 'message-' + inputElement.id;
    const ageMessage = document.getElementById(messageId);

    if (!dobValue) {
        ageMessage.textContent = 'Date of Birth is required.';
        ageMessage.style.color = 'red';
        return;
    }

    const dob = new Date(dobValue);
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const monthDiff = today.getMonth() - dob.getMonth();

    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--;
    }

    if (age < 18) {
        ageMessage.textContent = 'You must be at least 18 years old.';
        ageMessage.style.color = 'red';
    } else {
        ageMessage.textContent = '';
    }
}

function formatNumberWithCommas(element) {
    // Determine whether element is an input field or a table cell
    let value = element.tagName === 'INPUT' ? element.value : element.textContent;

    // Remove any existing commas
    value = value ? value.toString().replace(/,/g, '') : '';

    // Only proceed if `value` is a valid number after removing commas
    if (!isNaN(value) && value !== '') {
        // Convert to number and format with commas
        const formattedValue = parseFloat(value).toLocaleString();

        // Set the formatted value based on element type
        if (element.tagName === 'INPUT') {
            element.value = formattedValue;
        } else {
            element.textContent = formattedValue;
        }
    }
}
function formatIndianNumber(number) {
    // Ensure the number is a float and round it to 2 decimal places
    number = parseFloat(number).toFixed(2);

    // Split the whole part and decimal part
    let parts = number.split('.');
    let wholePart = parts[0];
    let decimalPart = parts[1] || '00'; // Ensure decimal part exists

    // Remove any existing commas from the whole part
    wholePart = wholePart.replace(/,/g, '');

    // Regular expression to match the Indian format
    let lastThreeDigits = wholePart.slice(-3);
    let restOfTheNumber = wholePart.slice(0, -3);

    if (restOfTheNumber !== '') {
        restOfTheNumber = restOfTheNumber.replace(/\B(?=(\d{2})+(?!\d))/g, ',');
        wholePart = restOfTheNumber + ',' + lastThreeDigits;
    } else {
        wholePart = lastThreeDigits;
    }

    // Return the formatted number with two decimals
    return wholePart + '.' + decimalPart.padEnd(2, '0');
}


