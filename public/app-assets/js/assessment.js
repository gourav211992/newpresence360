$(document).ready(function () {
    initSelect2WithIcons('#select2-icons0');
    feather.replace();
});


function addQuestionBox() {
    let qIndex = questionCounter - 1;

    let html = `<div class="question-box" data-question-index="${qIndex}">
        <h4>Question ${questionCounter}
            <a href="#" onclick="removeQuestionBox(this)">
                <span data-bs-toggle="tooltip" data-popup="tooltip-custom"
                    data-bs-placement="top" title="Delete"
                    class="float-end text-danger ms-1">
                    <i data-feather="trash-2"></i>
                </span>
            </a>
            <a href="#" onclick="addQuestionBox()">
                <span data-bs-toggle="tooltip" data-popup="tooltip-custom"
                    data-bs-placement="top" title="Duplicate"
                    class="float-end text-dark ms-1">
                    <i data-feather='copy'></i>
                </span>
            </a>
            <span data-bs-toggle="tooltip" data-popup="tooltip-custom"
                data-bs-placement="top" title="Add Image"
                class="upload-btn-wrapper float-end">
                <button type="button" class="uploadBtnnew">
                    <i data-feather='image'></i>
                </button>
                <input type="file" name="questions[${qIndex}][attachment]">
            </span>
        </h4>
        <div class="row">
            <div class="col-md-8 mb-sm-0 mb-1">
                <input type="text" class="form-control" placeholder="Title" name="questions[${qIndex}][title]">
            </div>
            <div class="col-md-4 question-select mb-1">
                <select data-placeholder="Select Question Type"
                    class="select2-icons form-select" id="select2-icons${qIndex}" name="questions[${qIndex}][type]" onchange="handleQuestionTypeChange(this)">
                    <option value="single choice" data-icon="circle" selected>
                        Single
                        Choice</option>
                    <option value="multiple choice" data-icon="stop-circle">
                        Multiple Choice</option>
                    <option value="dropdown" data-icon="chevron-down">
                        Dropdown
                    </option>
                    <option value="file upload" data-icon="upload">File
                        Upload
                    </option>
                    <option value="image" data-icon="image">Image Upload</option>
                    <option value="short answer" data-icon="align-left">
                        Short
                        Answer</option>
                    <option value="rating" data-icon="star">Rating
                    </option>
                </select>
            </div>
        </div>
        <div class="innergroupanser option-preview-section" data-question-index="${qIndex}">
        </div>
        <div class="option-section" data-question-index="${qIndex}">
        </div>
        <div>
            <div class="d-flex align-items-center">
                <div class="form-check form-check-primary form-switch">
                    <input type="checkbox" checked class="form-check-input"
                        id="required${qIndex}" name="questions[${qIndex}][is_required]" value="1"/>
                </div>
                <label class="form-check-label"
                    for="required${qIndex}">Required</label>
            </div>
        </div>
        <div class="d-flex align-items-center mt-1 show-dropdown-toggle">
            <div class="form-check form-check-primary form-switch">
                <input type="checkbox" class="form-check-input" id="dropdown${qIndex}" name="questions[${qIndex}][is_dropdown]" value="1"/>
            </div>
            <label class="form-check-label" for="dropdown${qIndex}">Show as
                dropdown</label>
        </div>
    </div>`;

    $('#question-section').append(html);
    initSelect2WithIcons(`#question-section .question-box[data-question-index="${qIndex}"] select.select2-icons`);

    feather.replace();
    questionCounter++;
    let newSelect = document.querySelector(`#select2-icons${qIndex}`);
    handleQuestionTypeChange(newSelect);

}

function handleQuestionTypeChange(el) {
    const selectedType = el.value.toLowerCase();
    const questionBox = el.closest('.question-box');
    const qIndex = questionBox.getAttribute('data-question-index');
    const typeFieldContainer = questionBox.querySelector('.option-section');
    const showDropdownToggle = questionBox.querySelector('.show-dropdown-toggle');
    const optionPreviewSection = questionBox.querySelector('.option-preview-section');
    if (!typeFieldContainer) {
        return;
    }

    optionPreviewSection.innerHTML = '';

    if (selectedType === 'single choice' || selectedType === 'multiple choice') {
        showDropdownToggle.style.display = 'flex'; // show the toggle
    } else {
        showDropdownToggle.style.setProperty('display', 'none', 'important');
    }

    // Clear previous content
    typeFieldContainer.innerHTML = '';

    if (selectedType === 'single choice' || selectedType === 'multiple choice' || selectedType === 'dropdown') {
        typeFieldContainer.innerHTML = addOptionHtml(qIndex);
    } else if (selectedType === 'rating') {
        typeFieldContainer.innerHTML = ratingFields(qIndex);
    } else if (selectedType === 'image') {  // <--- here handle image upload options
        typeFieldContainer.innerHTML = addImageOptionHtml(qIndex);
    }

    feather.replace();
}


function addOptions(el) {
    const questionBox = el.closest('.question-box');
    const qIndex = questionBox.getAttribute('data-question-index');
    const type = questionBox.querySelector(`select[name="questions[${qIndex}][type]"]`).value.toLowerCase();

    if (type === 'image') {
        handleImageOptionAdd(el, qIndex);
        return;
    }

    const inputField = questionBox.querySelector(`input[name="questions[${qIndex}][new_option_label]"]`);
    const optionText = inputField.value.trim();

    if (!optionText) {
        Swal.fire(
            "Info!",
            "Please enter an option before adding.",
            "warning"
        );
        return;
    }

    const optionSection = questionBox.querySelector('.option-preview-section');
    let optionCount = optionSection.querySelectorAll('.row').length;

    const inputType = type === 'multiple choice' ? 'checkbox' : 'radio';
    const correctName = type === 'multiple choice' ? `questions[${qIndex}][correct_options][]` : `questions[${qIndex}][correct_option]`;

    let html = `<div class="row">
            <div class="col-md-6">
                <div class="form-check ansercheckbox">
                    <input class="form-check-input" type="${inputType}" name="${correctName}]" value="${optionCount}" id="answer${qIndex}_${optionCount}" />
                    <input type="hidden" name="questions[${qIndex}][options][${optionCount}]" value="${optionText}" />
                    <label class="form-check-label" for="answer${qIndex}_${optionCount}">${optionText}</label>
                </div>
            </div>
            <div class="col-md-2">
                <span class="text-danger cursor-pointer add-removetxt remove-option">
                    <i data-feather="x-circle"></i> Remove
                </span>
            </div>
        </div>`;

    optionSection.insertAdjacentHTML('beforeend', html);
    inputField.value = '';
    feather.replace();
}

function handleImageOptionAdd(el, qIndex) {
    const questionBox = el.closest('.question-box');
    const optionSection = questionBox.querySelector('.option-preview-section');
    const currentOptionBox = el.closest('.addoption-box');
    if (!currentOptionBox) return;

    const fileInput = currentOptionBox.querySelector('input[type="file"]');
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        Swal.fire("Info!", "Please select an image before adding.", "warning");
        return;
    }

    const file = fileInput.files[0];
    const allowedTypes = ['jpg', 'jpeg', 'png'];
    const maxTotalSizeMB = 10;

    // Check extension
    const extension = file.name.split('.').pop().toLowerCase();
    if (!allowedTypes.includes(extension)) {
        fileInput.value = '';
        Swal.fire(
            'Info',
            "File format not supported (only jpg, jpeg, png allowed). Kindly select again.",
            "warning"
        );
        return;
    }

    // Calculate total size of all files already uploaded + current file
    let totalSizeMB = 0;
    optionSection.querySelectorAll('input[type="file"]').forEach(input => {
        if (input.files.length > 0) {
            totalSizeMB += input.files[0].size / 1024 / 1024;
        }
    });
    totalSizeMB += file.size / 1024 / 1024;

    if (totalSizeMB > maxTotalSizeMB) {
        fileInput.value = '';
        Swal.fire(
            'Info',
            "You can upload a maximum of 10 MB files in total. Kindly select again.",
            "warning"
        );
        return;
    }

    // Create preview
    const imageUrl = URL.createObjectURL(file);
    const optionCount = optionSection.querySelectorAll('.image-option-preview').length;

    const previewHtml = `
    <div class="row align-items-center mb-2 image-option-preview" data-option-index="${optionCount}">
        <div class="col-md-6 d-flex align-items-center gap-2">
            <input class="form-check-input" type="radio" name="questions[${qIndex}][correct_option]" value="${optionCount}" id="answer${qIndex}_${optionCount}" />
            <img src="${imageUrl}" alt="Option Image" style="max-height: 80px; border: 1px solid #ccc; padding: 2px;" />
        </div>
        <div class="col-md-2">
            <span class="text-danger cursor-pointer add-removetxt remove-option">
                <i data-feather="x-circle"></i> Remove
            </span>
        </div>
    </div>
    `;
    optionSection.insertAdjacentHTML('beforeend', previewHtml);

    // Replace hidden input with the actual current file input so the file gets submitted
    const lastPreview = optionSection.querySelector('.image-option-preview:last-child .col-md-6');
    const clonedInput = fileInput.cloneNode();
    clonedInput.style.display = 'none';
    lastPreview.appendChild(clonedInput);
    fileInput.value = '';
    currentOptionBox.innerHTML = addImageOptionHtml(qIndex);
    feather.replace();
}


// Remove option (using event delegation)
document.addEventListener('click', function (e) {
    if (e.target.closest('.remove-option')) {
        const optionRow = e.target.closest('.row');
        if (optionRow) optionRow.remove();
    }
});

function ratingFields(qIndex) {
    let html = `
    <div class="row">
        <div class="col-md-9">
            <div class="row mb-1 align-items-center">
                <div class="col-md-2">
                    <label class="form-label">Score from</label>
                </div>
                <div class="col-md-3 pe-sm-0">
                    <input type="number" class="form-control" name="questions[${qIndex}][score_from]" placeholder="0">
                </div>
                <div class="col-md-1 text-center">
                    <p class="mb-0">to</p>
                </div>
                <div class="col-md-3 ps-sm-0">
                    <input type="number" class="form-control" name="questions[${qIndex}][score_to]" placeholder="10">
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-1">
                <label class="form-label">Low score label</label>
                <input type="number" class="form-control" name="questions[${qIndex}][low_score]" placeholder="Enter">
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-1">
                <label class="form-label">High score label</label>
                <input type="number" class="form-control" name="questions[${qIndex}][high_score]" placeholder="Enter">
            </div>
        </div>
    </div>`;

    return html;
}

function shortAnswerField(qIndex) {
    let html = `
    <div class="mb-1">
        <div class="row innergroupanser"><div class="col-md-6"><input type="text" class="form-control" name="questions[${qIndex}][short_answer]" placeholder="Enter your answer"></div></div>
    </div>
    `;

    return html;
}

function fileUploadField(qIndex) {
    let html = `
    <div class="mb-1">
        <div class="row innergroupanser"><div class="col-md-6"><input type="file" class="form-control" name="questions[${qIndex}][file_upload]" /></div></div>
    </div>
    `;

    return html;
}

function dropdownField(qIndex) {
    let html = `
    <div class="mb-1">
        <div class="row">
            <div class="col-md-6">
                <select class="form-select" name="questions[${qIndex}][preview_dropdown]">
                </select>
            </div>
        </div>
    </div>
    `;

    return html;
}

function addOptionHtml(qIndex) {
    let html = `
    <div class="row innergroupanser mb-2 mt-1 addoption-box">
        <div class="col-md-10">
            <input type="text" class="form-control"
                placeholder="Enter Value" name="questions[${qIndex}][new_option_label]">
        </div>
        <div class="col-md-2 mt-1  text-sm-end">
            <span class="text-theme cursor-pointer add-removetxt" onclick="addOptions(this)">
                <i data-feather="plus-square"></i> Add Option
            </span>
        </div>
    </div>
    `;

    return html;
}

function addImageOptionHtml(qIndex) {
    let html = `
    <div class="row innergroupanser mb-2 mt-1 addoption-box">
        <div class="col-md-10">
            <input type="file" class="form-control"
                accept="image/*"
                name="questions[${qIndex}][options_images][]" />
        </div>
        <div class="col-md-2 mt-1 text-sm-end">
            <span class="text-theme cursor-pointer add-removetxt" onclick="addOptions(this)">
                <i data-feather="plus-square"></i> Add Option
            </span>
        </div>
    </div>`;
    return html;
}


function removeQuestionBox(el) {
    const questionBox = el.closest('.question-box');
    if (questionBox) {
        questionBox.remove();
    }

    // Optional: renumber the visible "Question X" labels after removal
    renumberVisibleQuestions();
}

function renumberVisibleQuestions() {
    const allBoxes = document.querySelectorAll('.question-box');
    allBoxes.forEach((box, i) => {
        const h4 = box.querySelector('h4');
        if (h4) {
            h4.childNodes[0].nodeValue = `Question ${i + 1} `;
        }
    });
}

function initSelect2WithIcons(selector) {
    $(selector).select2({
        templateResult: formatIcon,
        templateSelection: formatIcon,
        minimumResultsForSearch: -1
    }).on('select2:open', function () {
        // Delay slightly so dropdown DOM is rendered
        setTimeout(() => {
            feather.replace();
        }, 0);
    });

    function formatIcon(state) {
        if (!state.id) {
            return state.text; // placeholder or search box text
        }
        var icon = $(state.element).data('icon');
        if (icon) {
            // Return a jQuery object with icon + text for both dropdown and selected option
            return $(`<span><i data-feather="${icon}"></i> ${state.text}</span>`);
        }
        return state.text;
    }

    // Replace icons once initially
    feather.replace();
}
