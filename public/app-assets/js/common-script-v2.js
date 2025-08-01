$(document).ready(function () {
    $(document).on('keypress', '.numberonly-v2', function (e) {
        var charCode = (e.which) ? e.which : event.keyCode;

        if (String.fromCharCode(charCode).match(/[^0-9]/g)) {
            return false;
        }
    });

    $(document).on('keypress', '.decimalNumberonly', function (e) {
        var charCode = (e.which) ? e.which : event.keyCode;

        // Allow only numbers and one decimal point
        if (charCode !== 46 && (charCode < 48 || charCode > 57)) {
            return false; // Not a number or decimal point
        }

        // Check if the decimal point is already present
        if (charCode === 46 && $(this).val().indexOf('.') !== -1) {
            return false; // Prevent multiple decimal points
        }
    });

    if ($.fn.summernote && $(".summernote").length > 0) {
        $(".summernote").summernote({
            toolbar: [
                ["style", ["bold", "italic", "underline", "clear"]]
            ],
            height: 100,
            placeholder: 'Write your description here...',
            // tabsize: 2,
            minHeight: null,
            maxHeight: null,
            focus: true,
        });
    }

    /*-----ADD & UPDATE DATA--------*/
    $(document).on("click", '[data-request="ajax-submit"]', function () {
        /*REMOVING PREVIOUS ALERT AND ERROR CLASS*/
        $(".is-invalid").removeClass("is-invalid");
        $(".help-block").remove();
        var $this = $(this);
        var $target = $this.data("target");
        var $url = $(this).data("action") ? $(this).data("action") : $($target).attr("action");
        var $method = $(this).data("action") ? "POST" : $($target).attr("method");
        var $redirect = $($target).attr("redirect");
        var $reload = $($target).attr("reload");
        var $callback = $($target).attr("callback");
        // console.log($callback);
        var $data = new FormData($($target)[0]);
        if (!$method) {
            $method = "get";
        }
        $this.prop('disabled', true);

        $.ajax({
            url: $url,
            data: $data,
            cache: false,
            type: $method,
            dataType: "JSON",
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#loaderDiv').show();
            },
            success: function ($response) {
                $('#loaderDiv').hide();
                if ($response.status === 200) {
                    $($target).trigger("reset");
                    $this.prop('disabled', false);

                    Swal.fire("Success!", $response.message, "success");
                    console.log($callback);
                    if ($callback) {
                        console.log('call');
                        data = $response.data;
                        eval($callback);
                    }
                    setTimeout(function () {
                        if ($redirect) {
                            window.location.href = $redirect;
                        } else if ($reload) {
                            console.log($reload);
                            location.reload();
                        }
                    }, 2200);
                }
            },
            error: function ($response) {
                $('#loaderDiv').hide();
                $this.prop('disabled', false);
                if ($response.status === 422) {
                    if (
                        Object.size($response.responseJSON) > 0 &&
                        Object.size($response.responseJSON.errors) > 0
                    ) {
                        show_validation_error($response.responseJSON.errors);
                    }
                } else {
                    Swal.fire(
                        "Info!",
                        $response.responseJSON.message,
                        "warning"
                    );
                    setTimeout(function () { }, 1200);
                }
            },
        });
    });

    /*-----DELETE DATA--------*/
    $(document).on("click", '[data-request="remove"]', function () {
        var $this = $(this);
        var $message = $this.attr("data-message");
        var $url = $this.attr("data-url");
        var $reload = $this.attr("data-reload");
        var $callback = $this.attr("data-callback");
        Swal.fire({
            title: "Alert! ",
            text: $message ? $message : "Are you sure you want to delete ?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, please!",
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: $url,
                    type: "DELETE",
                    beforeSend: function () {
                        $('#loaderDiv').show();
                    },
                    success: function (data) {
                        $('#loaderDiv').hide();
                        Swal.fire("Success!", data.message, "success");

                        $this.closest('tr')
                            .children('td')
                            .animate({
                                padding: 0
                            })
                            .wrapInner('<div/>')
                            .children()
                            .slideToggle(function () {
                                $(this).closest('tr').remove();
                            });

                        setTimeout(function () {
                            // location.reload();
                            if ($reload === 'false') {
                                if ($callback) {
                                    eval($callback);
                                }
                            } else {
                                window.location.replace(window.location.pathname);
                            }
                        }, 1000);
                    },
                    error: function (data) {
                        $('#loaderDiv').hide();
                        Swal.fire(
                            "Info!",
                            data.responseJSON.message,
                            "warning"
                        );
                        setTimeout(function () { }, 1200);
                    },
                });
            }
        });
    });

    /*-----ADD SUPPLY SPLIT DATA--------*/
    $(document).on("click", '[data-request="supply-split"]', function () {
        /*REMOVING PREVIOUS ALERT AND ERROR CLASS*/
        $(".is-invalid").removeClass("is-invalid");
        $(".help-block").remove();
        var $this = $(this);
        var $target = $this.data("target");
        var $url = $(this).data("action") ? $(this).data("action") : $($target).attr("action");
        var $method = $($target).attr("method") ? $($target).attr("method") : "POST";
        var $data = new FormData($($target)[0]);
        $this.prop('disabled', true);
        $.ajax({
            url: $url,
            data: $data,
            cache: false,
            type: $method,
            dataType: "JSON",
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#loaderDiv').show();
            },
            success: function ($response) {
                $('#loaderDiv').hide();
                if ($response.status === 200) {
                    $($target)[0].reset();
                    $($target).find('select').each(function () {
                        $(this).val($(this).find('option:first').val()).trigger('change');
                    });
                    $this.prop('disabled', false);
                    $('#render-split-data').html($response.data);
                }
            },
            error: function ($response) {
                $('#loaderDiv').hide();
                $this.prop('disabled', false);
                if ($response.status === 422) {
                    console.log($response.responseJSON.errors);
                    if (
                        Object.size($response.responseJSON) > 0 &&
                        Object.size($response.responseJSON.errors) > 0
                    ) {
                        show_validation_error($response.responseJSON.errors);
                    }
                } else {
                    Swal.fire(
                        "Info!",
                        $response.responseJSON.message,
                        "warning"
                    );
                    setTimeout(function () { }, 1200);
                }
            },
        });
    });

    // Ajax Save & Update with Confirmation
    $(document).on("click", '[data-request="confirm-and-save"]', function () {
        $(".is-invalid").removeClass("is-invalid");
        $(".help-block").remove();
        var $this = $(this);
        var $target = $this.data("target");
        var $url = $(this).data("action") ? $(this).data("action") : $($target).attr("action");
        var $method = $(this).data("action") ? "POST" : $($target).attr("method");
        var $redirect = $($target).attr("redirect");
        var $reload = $($target).attr("reload");
        var $callback = $($target).attr("callback");
        var $message = $($target).attr("data-message");
        var $data = new FormData($($target)[0]);
        if (!$method) {
            $method = "get";
        }
        $this.prop('disabled', true);

        Swal.fire({
            title: "Alert! ",
            text: $message ? $message : "Are you sure ?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, please!",
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: $url,
                    data: $data,
                    cache: false,
                    type: $method,
                    dataType: "JSON",
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $('#loaderDiv').show();
                    },
                    success: function ($response) {
                        $('#loaderDiv').hide();
                        if ($response.status === 200) {
                            $($target).trigger("reset");
                            $this.prop('disabled', false);

                            Swal.fire("Success!", $response.message, "success");
                            if ($callback) {
                                console.log('call');
                                data = $response.data;
                                eval($callback);
                            }
                            setTimeout(function () {
                                if ($redirect) {
                                    window.location.href = $redirect;
                                } else if ($reload) {
                                    console.log($reload);
                                    location.reload();
                                }
                            }, 2200);
                        }
                    },
                    error: function ($response) {
                        $('#loaderDiv').hide();
                        $this.prop('disabled', false);
                        if ($response.status === 422) {
                            if (
                                Object.size($response.responseJSON) > 0 &&
                                Object.size($response.responseJSON.errors) > 0
                            ) {
                                show_validation_error($response.responseJSON.errors);
                            }
                        } else {
                            Swal.fire(
                                "Info!",
                                $response.responseJSON.message,
                                "warning"
                            );
                            setTimeout(function () { }, 1200);
                        }
                    },
                });
            }
        });
    });

});

$(window).on('load', function () {
    if (feather) {
        feather.replace({
            width: 14,
            height: 14
        });
    }
})

function show_validation_error(msg) {
    if ($.isPlainObject(msg)) {
        $data = msg;
    } else {
        $data = $.parseJSON(msg);
    }

    $.each($data, function (index, value) {
        var name = index.replace(/\./g, "][");

        if (index.indexOf(".") !== -1) {
            name = name + "]";
            name = name.replace("]", "");
        }

        if (index === 'job_request') {
            $('#request-error-placeholder').html('<span class="help-block text-danger fw-bolder">' + value + '</span>');
            return; // Skip default logic
        }

        if (index === 'resume') {
            $('form [name="' + name + '"]').after('<span class="help-block text-danger fw-bolder">' + value + "</span>");
            return; // Skip default logic
        }

        if (index === 'bank_date') {
            $('#bank-date-error').html('<span class="help-block text-danger fw-bolder">' + value + '</span>');
            return; // Skip default logic
        }

        if (index === 'before_kaizen') {
            $('#kaizen-before-error').html('<span class="help-block text-danger fw-bolder">' + value + '</span>');
            return;
        }

        if (index === 'after_kaizen') {
            $('#kaizen-after-error').html('<span class="help-block text-danger fw-bolder">' + value + '</span>');
            return;
        }

        if (index.match(/^questions\.\d+\.options$/)) {
            let match = index.match(/^questions\.(\d+)\.options$/);
            if (match) {
                let qIndex = match[1];
                let customName = `questions[${qIndex}][new_option_label]`;
                let $input = $(`form [name="${customName}"]`);
                if ($input.length) {
                    $input.addClass("is-invalid");
                    $input.after('<span class="help-block text-danger fw-bolder">' + value[0] + '</span>');
                    return; // Skip rest of loop
                }
            }
        }

        if (index.match(/^questions\.\d+\.correct_option$/)) {
            let match = index.match(/^questions\.(\d+)\.correct_option$/);
            if (match) {
                let qIndex = match[1];
                let $targetDiv = $(`.innergroupanser.option-preview-section[data-question-index="${qIndex}"]`);

                if ($targetDiv.length) {
                    $targetDiv.after(
                        `<span class="help-block text-danger fw-bolder">${value[0]}</span>`
                    );
                }
                return; // Prevent default error appending
            }
        }

        if (index.match(/^questions\.\d+\.correct_options$/)) {
            let match = index.match(/^questions\.(\d+)\.correct_options$/);
            if (match) {
                let qIndex = match[1];
                let $targetDiv = $(`.innergroupanser.option-preview-section[data-question-index="${qIndex}"]`);

                if ($targetDiv.length) {
                    $targetDiv.after(
                        `<span class="help-block text-danger fw-bolder">${value[0]}</span>`
                    );
                }
                return; // Prevent default error appending
            }
        }

        if (name.indexOf("[]") !== -1) {
            // $('form [name="' + name + '"]').last().closest("").addClass("is-invalid error");
            $('form [name="' + name + '"]').last().find("").append('<span class="help-block text-danger fw-bolder">' + value + "</span>");
        } else if ($('form [name="' + name + '[]"]').length > 0) {
            if ($('form [name="' + name + '[]"]').hasClass("kaizen_team")) {
                $('form [name="' + name + '[]"]').next('.select2').after('<span class="help-block text-danger fw-bolder">' + value + "</span>");
            } else {
                $('form [name="' + name + '[]"]').addClass("is-invalid error");
                $('form [name="' + name + '[]"]').parent().after('<span class="help-block text-danger fw-bolder">' + value + "</span>");
            }
        } else {
            if ($('form [name="' + name + '"]').attr("type") == "checkbox" || $('form [name="' + name + '"]').attr("type") == "radio") {
                if ($('form [name="' + name + '"]').attr("type") == "checkbox") {
                    // $('form [name="' + name + '"]').addClass("is-invalid error");
                    $('form [name="' + name + '"]').parent().after('<span class="help-block text-danger fw-bolder">' + value + "</span>");
                } else {
                    // $('form [name="' + name + '"]').addClass("is-invalid error");
                    $('form [name="' + name + '"]').parent().parent().append('<span class="help-block text-danger fw-bolder">' + value + "</span>");
                }
            } else if ($('form [name="' + name + '"]').get(0)) {
                if ($('form [name="' + name + '"]').get(0).tagName == "SELECT") {
                    // $('form [name="' + name + '"]').addClass("is-invalid error");
                    $('form [name="' + name + '"]').parent().after('<span class="help-block text-danger fw-bolder">' + value + "</span>");
                } else if ($('form [name="' + name + '"]').attr("type") == "password" && $('form [name="' + name + '"]').hasClass("hideShowPassword-field")) {
                    // $('form [name="' + name + '"]').addClass("is-invalid error");
                    $('form [name="' + name + '"]').parent().after('<span class="help-block text-danger fw-bolder">' + value + "</span>");
                } else if ($('form [name="' + name + '"]').attr("type") == "file") {
                    // $('form [name="' + name + '"]').addClass("is-invalid error");
                    $('form [name="' + name + '"]').closest('.attachment-container').find('#preview')
                        .before('<span class="help-block text-danger fw-bolder">' + value + '</span><br>');
                    $('form [name="' + name + '"]').parent().after('<span class="help-block text-danger fw-bolder">' + value + "</span>");
                } else if ($('form [name="' + name + '"]').hasClass("summernote")) {
                    $('form [name="' + name + '"]').next('.note-editor.note-frame').after('<span class="help-block text-danger fw-bolder">' + value + "</span>");
                } else {
                    // $('form [name="' + name + '"]').addClass("is-invalid error");
                    $('form [name="' + name + '"]').after('<span class="help-block text-danger fw-bolder" role="alert">' + value + "</span>");
                }
            } else {
                // $('form [name="' + name + '"]').addClass("is-invalid error");
                $('form [name="' + name + '"]').after('<span class="help-block text-danger fw-bolder">' + value + "</span>");
            }
        }
    });

    /*SCROLLING TO THE INPUT BOX*/
    scroll();
}

Object.size = function (obj) {
    var size = 0,
        key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

function dropdown(url, selected_id, selected_value) {
    $.ajax({
        beforeSend: function (xhr) { },
        url: url,
        method: "GET",
        dataType: "json",
        success: function (response) {
            $("#" + selected_id + "").empty("");
            var options = '<option value="">select</option>';
            if (response.status === 200) {
                var option_list = response.data;
                $.each(option_list, function (index, value) {
                    // let selected = parseInt(selected_value) === value.id ? "selected" : "";
                    // let selected = selected_value.includes(value.id.toString()) ? "selected" : "";
                    let selected = value.id.toString() === selected_value?.toString() ? "selected" : "";
                    let name = value.name;
                    options += '<option value="' + value.id + '" ' + selected + ">" + name + "</option>";
                });
                $("#" + selected_id + "").append(options);

            }
        },
    });
}