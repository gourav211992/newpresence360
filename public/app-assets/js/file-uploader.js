    let selectedAttachmentsMain = [];

    let allowMoreFile = true;

    const ALLOWED_EXTENSIONS = [
        'doc', 'docx', 'odt', 'rtf', 'txt', 'xls', 'xlsx', 'ods', 'csv',
        'ppt', 'pptx', 'odp', 'pdf', 'jpg', 'jpeg', 'png', 'gif', 
        'bmp', 'tiff', 'tif', 'svg', 'ico', 'webp'
    ];
    const ALLOWED_MIME_TYPES = [
        'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
        'application/pdf', 
        'image/jpeg', 
        'image/png', 
        'image/gif', 
        'application/vnd.ms-excel', // For .xls files
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // For .xlsx files
        'application/vnd.oasis.opendocument.spreadsheet', // For .ods files
        'text/csv', // For .csv files
        'application/kset' // some special case for window excel file
    ];

    const MAX_FILE_SIZE = 5120; //In KBs

    function appendFilePreviews(fileUrl, previewElementId, index, rowId = null, disabled = false)
    {
        const tempDiv = document.createElement('div');
        tempDiv.className = "col-md-1 file-upload-preview";
        tempDiv.style.cursor = "pointer";
        var removeFileOption = ``;
        if (disabled == false) {
            removeFileOption = `
            <div class="delete-img text-danger" data-index = '${index}' data-id = "${rowId || ''}" data-edit-flag="true" >
                    <i data-feather="x"></i>
                </div>
            `;
        }
        var htmlData = `
            <div class="image-uplodasection expenseadd-sign">
                <i onclick = "previewFile(this);" file-url = '${fileUrl}' data-feather="file-text"></i>
                ${removeFileOption}
            </div>
            `;
        tempDiv.innerHTML = htmlData;
        const element = document.getElementById(previewElementId);
        element.appendChild(tempDiv);
    }

    // Global object to store files for each input
    let fileInputData = {};

    function addFiles(element, previewElementId) {
        const input = element;
        const allowedMaxFilesCount = Number(element.getAttribute('max_file_count') ? element.getAttribute('max_file_count') : 1);
        const files = Array.from(input.files); // Convert new FileList to array
        const dt = new DataTransfer();
        const inputId = input.name.replace('[]','');
        // Initialize storage for this input if not already initialized
        if (!fileInputData[inputId]) {
            fileInputData[inputId] = [];
            addedFilesCount = 0;
        } else {
            addedFilesCount = fileInputData[inputId].length;
        }

        if ((files.length + fileInputData[inputId].length) > allowedMaxFilesCount) 
        {
            Swal.fire({
                title: 'Error!',
                text: "Maximum " + allowedMaxFilesCount + " files are allowed",
                icon: 'error',
            });
            let prevAllFiles = fileInputData[inputId] ? fileInputData[inputId] : [];
            let tempDt = new DataTransfer();
            prevAllFiles.forEach((fileElement) => {
                tempDt.items.add(fileElement);
            });
            input.files = tempDt.files;
            return;
        }

        // Combine old and new files
        let allFiles = [...fileInputData[inputId], ...files];
        var invalidFile = {};

        // Validate files
        for (let i = 0; i < allFiles.length; i++) {
            const file = allFiles[i];
            const fileExtension = file.name.split('.').pop().toLowerCase();

            // if (!ALLOWED_EXTENSIONS.includes(fileExtension) || !ALLOWED_MIME_TYPES.includes(file.type)) {
            //     invalidFile.message = 'Please select valid files';
            //     break;
            // }
            const mimeIsInvalid = file.type && !ALLOWED_MIME_TYPES.includes(file.type);
            const extensionIsInvalid = !ALLOWED_EXTENSIONS.includes(fileExtension);
            console.log(`File: ${file.name}, Extension: ${fileExtension}, MIME Type: ${file.type}`);
            if (!file.type) {
                console.warn(`MIME type missing for file: ${file.name}`);
            }
            if (extensionIsInvalid || mimeIsInvalid) {
                invalidFile = {
                    message: 'Please select valid files'
                };
                break;
            }

            const fileSize = (file.size / 1024).toFixed(2);
            if (fileSize > MAX_FILE_SIZE) {
                invalidFile.message = 'Please select files with size not more than 5MB';
                break;
            }
        }

        // Stop if there's an invalid file
        if (invalidFile && invalidFile.message) {
            Swal.fire({
                title: 'Error!',
                text: invalidFile.message,
                icon: 'error',
            });
            element.value = ''; // Reset file input
            return;
        } else {
            // Add all files to DataTransfer and rebuild the preview
            allFiles.forEach((file, i) => {
                dt.items.add(file);
                if (!fileInputData[inputId].some(f => f.name === file.name && f.size === file.size)) {
                    const fileUrl = URL.createObjectURL(file);
                    appendFilePreviews(fileUrl, previewElementId, i);
                }
            });

            // Update the global object for this input
            fileInputData[inputId] = allFiles.reduce((unique, file) => {
                if (!unique.some(f => f.name === file.name && f.size === file.size)) {
                    unique.push(file);
                }
                return unique;
            }, []);

            // Update the file input's FileList
            input.files = dt.files;

            // Reset and re-render SVG icons (if applicable)
            feather.replace({
                width: 20,
                height: 20,
            });
        }
    }


    function previewFile(element)
    {
        if (element && element.getAttribute('file-url')) {
            const fileUrl = element.getAttribute('file-url');
            window.open(fileUrl, '_blank');
        }
    }

    function removeFile(element, editFlag = false)
    {
        // const fileIndex = element.getAttribute('data-index');
        // const dt = new DataTransfer();
        // if (fileIndex !== null && fileIndex !== undefined)
        // {
        //     //Only remove in create mode
        //     if (editFlag === false) {
        //         selectedAttachmentsMain.splice(fileIndex, 1);
        //     } else {
        //         var deletedAttachmentIds = JSON.parse(localStorage.getItem('deletedAttachmentIds')) || [];
        //         if(element.getAttribute('data-id')) {
        //             deletedAttachmentIds.push(element.getAttribute('data-id'));
        //             localStorage.setItem('deletedAttachmentIds', JSON.stringify(deletedAttachmentIds));
        //         }
        //     }
        //     const parentElement = element.closest('.file-upload-preview');
        //     if (parentElement) {
        //         parentElement.remove();
        //     }

        //     selectedAttachmentsMain.forEach(file => dt.items.add(file));

        //     const mainOrderFilePreview = $(element).closest('.row');

        //     //Rebuild UI
        //     // document.getElementById(previewElementId).innerHTML = '';
        //     // selectedAttachmentsMain.forEach((file, fileIndex) => {
        //     //     const fileUrl = URL.createObjectURL(file);
        //     //     appendFilePreviews(fileUrl, previewElementId, fileIndex);
        //     // });
        // }
    }

$(document).on('click', '.delete-img', (e) => {
    const editFlag = $(e.target).closest('.delete-img').attr('data-edit-flag') == 'true' ? true : false;
    const dataIndex = $(e.target).closest('.delete-img').attr('data-index');
    const dataId = $(e.target).closest('.delete-img').attr('data-id');

    const fileIndex = dataIndex;
    const dt = new DataTransfer();
    if (fileIndex !== null && fileIndex !== undefined)
    {
        //Only remove in create mode
        if (editFlag === false) {
            selectedAttachmentsMain.splice(fileIndex, 1);
        } else {
            var deletedAttachmentIds = JSON.parse(localStorage.getItem('deletedAttachmentIds')) || [];
            if(dataId) {
                deletedAttachmentIds.push(dataId);
                localStorage.setItem('deletedAttachmentIds', JSON.stringify(deletedAttachmentIds));
            }
        }

        let inputFile = null;
        let currentElement = e.target;

        while (currentElement && !inputFile) {
            inputFile = $(currentElement).find("input[type='file']").get(0);
            currentElement = currentElement.parentElement;
        }

        const parentElement = e.target.closest('.file-upload-preview');
        const mainFileContainer = $(parentElement).parent('div');
        if (parentElement) {
            parentElement.remove();
        }

        const dataKeyId = inputFile.name.replace('[]','');
        const files = fileInputData[dataKeyId];

        if(files != undefined && files.length) {
            const updatedFiles = files.filter((file, index) => index !== parseInt(fileIndex, 10));
            const dt = new DataTransfer();
            updatedFiles.forEach(file => dt.items.add(file));
            inputFile.files = dt.files;
            fileInputData[dataKeyId] = updatedFiles;
        } else {
            fileInputData[dataKeyId] = [];
        }

        if(mainFileContainer.find('.file-upload-preview').length) {
            mainFileContainer.find('.file-upload-preview').each( function (index,item) {
                $(item).find(".delete-img").attr('data-index',index);
            });
        }
        //Rebuild UI
        // document.getElementById(previewElementId).innerHTML = '';
        // selectedAttachmentsMain.forEach((file, fileIndex) => {
        //     const fileUrl = URL.createObjectURL(file);
        //     appendFilePreviews(fileUrl, previewElementId, fileIndex);
        // });
    }
});

/*When page refreh localstorage clear data*/
setTimeout(() => {
    localStorage.removeItem('deletedAttachmentIds');
},0);