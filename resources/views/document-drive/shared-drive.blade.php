@extends('layouts.app')
@section('scripts')
    <script src="{{ url('') }}/app-assets/js/scripts/pages/app-file-manager.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })

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
            Toast.fire({
                icon,
                title
            });
        }

        @if (session('success'))
            showToast("success", "{{ session('success') }}");
        @endif

        @if (session('error'))
            showToast("error", "{{ session('error') }}");
        @endif

        @if ($errors->any())
            showTOast('error',
                "@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach"
            );
        @endif




        $(function() {
            $("input[name='view-list-btn']").click(function() {
                if ($("#gridView").is(":checked")) {
                    $(".grid-data").show();
                    $(".list-data").hide();
                } else {
                    $(".grid-data").hide();
                    $(".list-data").show();
                }
            });
        });

        $(".select2").select2({
            multiple: true,
            placeholder: "Select",
        });

        const uploadRoute = "{{ route('document-drive.file.upload', ['parentId' => ':parentId']) }}";
        document.querySelector('#file-upload').addEventListener('change', function() {
            $('#upload-status').empty();
            var errorOccurred = false;
            const files = this.files;
            const parentId = "{{ $parent ?? null }}"; // Use the dynamic parent ID from the backend

            if (files.length > 0) {
                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('fileuplostatyus'));
                modal.show();

                document.getElementById('upload-count').textContent = files.length;

                let completedUploads = 0; // Track the number of completed uploads

                Array.from(files).forEach((file, index) => {
                    const formData = new FormData();
                    formData.append('files[]', file);
                    formData.append('_token', "{{ csrf_token() }}");

                    // Replace placeholder in route with actual parentId
                    const dynamicRoute = uploadRoute.replace(':parentId', parentId || '');

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', dynamicRoute, true);

                    // Add a new entry in the modal for this file
                    const statusElement = document.createElement('div');
                    statusElement.classList.add('file-uploadstatus');
                    statusElement.innerHTML = `
                <div class="d-flex align-items-center">
                    <i data-feather="${file.type.startsWith('image') ? 'image' : 'file-text'}"></i>
                    <span class="ms-50">${file.name}</span>
                </div>
                <div class="upload-progress">
                    <progress value="0" max="100"></progress>
                </div>
            `;
                    document.getElementById('upload-status').appendChild(statusElement);

                    // Update progress
                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            const percent = Math.round((e.loaded / e.total) * 100);
                            statusElement.querySelector('progress').value = percent;
                        }
                    });

                    // Handle success
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);

                            // Handle uploaded files
                            if (response.uploaded_files.length > 0) {
                                response.uploaded_files.forEach((uploadedFile) => {
                                    document.querySelectorAll('.file-uploadstatus').forEach((
                                        statusElement) => {
                                        const fileName = statusElement.querySelector(
                                            'span').textContent;
                                        if (fileName === uploadedFile) {
                                            statusElement.querySelector(
                                                '.upload-progress').innerHTML = `
                                        <i data-feather="check-circle" class="upload-filebtnact text-success"></i>
                                    `;
                                        }
                                    });
                                });
                            }

                            // Handle skipped files (files that already exist)
                            if (response.skipped_files.length > 0) {
                                response.skipped_files.forEach((skippedFile) => {
                                    document.querySelectorAll('.file-uploadstatus').forEach((
                                        statusElement) => {
                                        const fileName = statusElement.querySelector(
                                            'span').textContent;
                                        if (fileName === skippedFile) {
                                            statusElement.querySelector(
                                                '.upload-progress').innerHTML = `
                                        <i data-feather="x-circle" class="upload-filebtnact text-danger"></i>
                                        <span class="text-danger">File already exists</span>
                                    `;
                                        }
                                    });
                                });
                            }

                            // Handle validation errors
                            if (response.errors) {
                                response.errors.forEach((error) => {
                                    document.querySelectorAll('.file-uploadstatus').forEach((
                                        statusElement) => {
                                        const fileName = statusElement.querySelector(
                                            'span').textContent;
                                        if (fileName === error) {
                                            statusElement.querySelector(
                                                '.upload-progress').innerHTML = `
                                        <i data-feather="x-circle" class="upload-filebtnact text-danger"></i>
                                        <span class="text-danger">File Fomat not Supported!</span>
                                    `;
                                        }
                                    });
                                });
                            }
                            errorOccurred = true;

                            feather.replace(); // Refresh icons
                        } else {
                            alert('An error occurred while uploading files.');
                        }
                        errorOccurred = true;
                        checkCompletion(); // Check if all uploads are completed
                    };

                    // Handle errors
                    xhr.onerror = function() {
                        statusElement.querySelector('.upload-progress').innerHTML = `
                    <i data-feather="x-circle" class="upload-filebtnact text-danger"></i>
                    <span class="text-danger">Network error</span>
                `;
                        feather.replace(); // Refresh icons
                        errorOccurred = true;
                        checkCompletion(); // Check if all uploads are completed
                    };

                    // Send the request
                    xhr.send(formData);
                });

                // Function to check completion
                function checkCompletion() {
                    completedUploads++;
                    if (completedUploads === files.length) {
                        if (!errorOccurred) {
                            // Reload the page only if no errors occurred
                            window.location.reload();
                        }
                    }
                }
            }
        });
        const uploadRoute2 = "{{ route('document-drive.folder.upload', ['parentId' => ':parentId']) }}";
        document.querySelector('#folder-upload').addEventListener('change', function() {
            $('#upload-status').empty();
            var errorOccurred = false;
            const files = this.files;
            const parentId = "{{ $parent ?? null }}"; // Use the dynamic parent ID from the backend

            if (files.length > 0) {
                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('fileuplostatyus'));
                modal.show();

                document.getElementById('upload-count').textContent = files.length;

                let completedUploads = 0; // Track the number of completed uploads

                Array.from(files).forEach((file, index) => {
                    const formData = new FormData();
                    formData.append('files[]', file);
                    formData.append('_token', "{{ csrf_token() }}");

                    // Replace placeholder in route with actual parentId
                    const dynamicRoute = uploadRoute2.replace(':parentId', parentId || '');

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', dynamicRoute, true);

                    // Add a new entry in the modal for this file
                    const statusElement = document.createElement('div');
                    statusElement.classList.add('file-uploadstatus');
                    statusElement.innerHTML = `
                <div class="d-flex align-items-center">
                    <i data-feather="${file.type.startsWith('image') ? 'image' : 'file-text'}"></i>
                    <span class="ms-50">${file.name}</span>
                </div>
                <div class="upload-progress">
                    <progress value="0" max="100"></progress>
                </div>
            `;
                    document.getElementById('upload-status').appendChild(statusElement);

                    // Update progress
                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            const percent = Math.round((e.loaded / e.total) * 100);
                            statusElement.querySelector('progress').value = percent;
                        }
                    });

                    // Handle success
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);

                            // Handle uploaded files
                            if (response.uploaded_files.length > 0) {
                                response.uploaded_files.forEach((uploadedFile) => {
                                    document.querySelectorAll('.file-uploadstatus').forEach((
                                        statusElement) => {
                                        const fileName = statusElement.querySelector(
                                            'span').textContent;
                                        if (fileName === uploadedFile) {
                                            statusElement.querySelector(
                                                '.upload-progress').innerHTML = `
                                        <i data-feather="check-circle" class="upload-filebtnact text-success"></i>
                                    `;
                                        }
                                    });
                                });
                            }

                            // Handle skipped files (files that already exist)
                            if (response.skipped_files.length > 0) {
                                response.skipped_files.forEach((skippedFile) => {
                                    document.querySelectorAll('.file-uploadstatus').forEach((
                                        statusElement) => {
                                        const fileName = statusElement.querySelector(
                                            'span').textContent;
                                        if (fileName === skippedFile) {
                                            statusElement.querySelector(
                                                '.upload-progress').innerHTML = `
                                        <i data-feather="x-circle" class="upload-filebtnact text-danger"></i>
                                        <span class="text-danger">File already exists</span>
                                    `;
                                        }
                                    });
                                });
                            }

                            // Handle validation errors
                            if (response.errors) {
                                response.errors.forEach((error) => {
                                    document.querySelectorAll('.file-uploadstatus').forEach((
                                        statusElement) => {
                                        const fileName = statusElement.querySelector(
                                            'span').textContent;
                                        if (fileName === error) {
                                            statusElement.querySelector(
                                                '.upload-progress').innerHTML = `
                                        <i data-feather="x-circle" class="upload-filebtnact text-danger"></i>
                                        <span class="text-danger">File Fomat not Supported!</span>
                                    `;
                                        }
                                    });
                                });
                            }
                            errorOccurred = true;

                            feather.replace(); // Refresh icons
                        } else {
                            alert('An error occurred while uploading files.');
                        }
                        errorOccurred = true;
                        checkCompletion(); // Check if all uploads are completed
                    };

                    // Handle errors
                    xhr.onerror = function() {
                        statusElement.querySelector('.upload-progress').innerHTML = `
                    <i data-feather="x-circle" class="upload-filebtnact text-danger"></i>
                    <span class="text-danger">Network error</span>
                `;
                        feather.replace(); // Refresh icons
                        errorOccurred = true;
                        checkCompletion(); // Check if all uploads are completed
                    };

                    // Send the request
                    xhr.send(formData);
                });

                // Function to check completion
                function checkCompletion() {
                    completedUploads++;
                    if (completedUploads === files.length) {
                        if (!errorOccurred) {
                            // Reload the page only if no errors occurred
                            window.location.reload();
                        }
                    }
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const renameModal = document.getElementById('filerename');
            renameModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget; // Button that triggered the modal
                const Id = button.getAttribute('data-id');
                const Name = button.getAttribute('data-name');
                const Type = button.getAttribute('data-type');

                // Populate the modal fields
                const modalIdField = renameModal.querySelector('#rename-id');
                const modalNameField = renameModal.querySelector('#rename-name');
                const modalTypeField = renameModal.querySelector('#rename-type');

                if (Type === 'file') {
                    const nameWithoutExt = Name.split('.').slice(0, -1).join('.');
                    modalNameField.value = nameWithoutExt; // Set name without extension
                } else {
                    modalNameField.value = Name; // Set the folder name as is
                }

                modalIdField.value = Id;
                modalTypeField.value = Type;
            });

            const moveModal = document.getElementById('movetofolder');
            moveModal.addEventListener('show.bs.modal', function(event) {
                const rows = document.querySelectorAll('#movetofolder tbody tr');
                rows.forEach(row => {
                    row.style.display = ''; // Reset visibility to show all rows
                });
                const button = event.relatedTarget; // Button that triggered the modal
                const Id = button.getAttribute('data-id');
                const Type = button.getAttribute('data-type');

                // Populate the modal fields
                const modalIdField = moveModal.querySelector('#move-id');
                const modalTypeField = moveModal.querySelector('#move-type');
                modalIdField.value = Id;
                modalTypeField.value = Type;

                if (Type == "folder") {

                    $('#movetofolder tbody tr').each(function() {
                        if ($(this).find('input[name="destination_folder_id"]').val() === Id) {
                            $(this).css('display', 'none');
                        }
                    });
                }
            });

            const moveModalMulti = document.getElementById('movetofolderall');
            moveModalMulti.addEventListener('show.bs.modal', function(event) {
                // Iterate over all selected IDs
                const selectIds = [];
                const isGridVisible = $(".grid-data").is(":visible");
                const itemSelector = isGridVisible ? '.selected-item:checked' :
                    '.selected-item-list:checked';
                const rows = document.querySelectorAll('#movetofolderall tbody tr');
                rows.forEach(row => {
                    row.style.display = ''; // Reset visibility to show all rows
                });

                $(itemSelector).each(function() {
                    selectIds.push($(this).val()); // Each checkbox value in the format "type|id"
                });
                selectIds.forEach(function(selected) {
                    // Split the selected value into type and id
                    const [type, id] = selected.split('|'); // Destructure "type|id"

                    // Check if the type is "folder"
                    if (type === "folder") {
                        // Loop through all rows in the modal's table
                        $('#movetofolderall tbody tr').each(function() {
                            // Find the input field with the name "destination_folder_id" and get its value
                            const destinationId = $(this).find(
                                'input[name="destination_folder_id"]').val();

                            // If the destination ID matches the current folder ID, remove the row
                            if (destinationId === id) {
                                $(this).css('display', 'none');

                            }
                        });
                    }
                });
            });


            const shareModal = document.getElementById('shareuser');
            shareModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget; // Button that triggered the modal
                const Id = button.getAttribute('data-id');
                const Type = button.getAttribute('data-type');

                // Populate the modal fields
                const modalIdField = shareModal.querySelector('#share-id');
                const modalTypeField = shareModal.querySelector('#share-type');
                console.log(Id);
                modalIdField.value = Id;
                modalTypeField.value = Type;
            });
        });
        document.getElementById('search-folder').addEventListener('input', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#folder-table tr');

            rows.forEach(row => {
                const folderName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                if (folderName.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        $('.text-body').on('click', function() {
            location.reload(); // Reload the page
        });

        document.getElementById('downloadSelected').addEventListener('click', function() {
            // Collect selected items
            if ($(".grid-data").is(":visible")) {
                const selectedItems = Array.from(document.querySelectorAll('.selected-item:checked')).map(
                    checkbox => checkbox.value);

                if (selectedItems.length === 0) {
                    showToast('error', 'Please select at least one item.');
                    return;
                }
                console.log(selectedItems);

                // Send AJAX request
                fetch('{{ route('document-drive.download-zip') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            selected_items: selectedItems
                        })
                    })
                    .then(response => {
                        if (response.ok) return response.blob();
                        throw new Error('Failed to create ZIP file.');
                    })
                    .then(blob => {
                        // Create a downloadable link for the ZIP file
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'download-' + Date.now() + '.zip';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                    })
                    .catch(error => {
                        console.error(error);
                        alert('Something went wrong. Please try again.');
                    });
            } else {
                const selectedItems = Array.from(document.querySelectorAll('.selected-item-list:checked')).map(
                    checkbox => checkbox.value);

                if (selectedItems.length === 0) {
                    showToast('error', 'Please select at least one item.');
                    return;
                }
                console.log(selectedItems);

                // Send AJAX request
                fetch('{{ route('document-drive.download-zip') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            selected_items: selectedItems
                        })
                    })
                    .then(response => {
                        if (response.ok) return response.blob();
                        throw new Error('Failed to create ZIP file.');
                    })
                    .then(blob => {
                        // Create a downloadable link for the ZIP file
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'download-' + Date.now() + '.zip';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                    })
                    .catch(error => {
                        console.error(error);
                        alert('Something went wrong. Please try again.');
                    });


            }
        });


        $('#inlineCheckbox1').on('click', function() {
            // Get the checked state of the main checkbox
            const isChecked = $(this).is(':checked');

            // Set all checkboxes with the "selected-item" class to the same state
            $('.selected-item-list').prop('checked', isChecked);
        });

        $('#submitTags').on('click', function() {
            if ($(".grid-data").is(":visible")) {
                const selectedItems = [];
                $('.selected-item:checked').each(function() {
                    selectedItems.push($(this).val()); // Format: "type|id"
                });

                if (selectedItems.length === 0) {
                    showToast('error', 'Please select at least one item to tag.');
                    return;
                }
                const tags = $('#tag').val();
                const page = "index";
                if (tags === '') {
                    showToast('error', 'Tag Description is required.');
                    return;
                }
                $.ajax({
                    url: "{{ route('document-drive.tags') }}", // Replace with your endpoint
                    type: 'POST',
                    data: {
                        tags: tags,
                        page: page,
                        selected_items: selectedItems
                    },
                    success: function(response) {
                        showToast('success', 'Tags added successfully to selected items!');
                        $('#addtag').modal('hide');
                    },
                    error: function(error) {
                        showToast('error', 'Error adding tags to selected items!');
                        console.error(error);
                    }
                });
            } else {
                const selectedItems = [];
                $('.selected-item-list:checked').each(function() {
                    selectedItems.push($(this).val()); // Format: "type|id"
                });

                if (selectedItems.length === 0) {
                    showToast('error', 'Please select at least one item to tag.');
                    return;
                }
                const tags = $('#tag').val();
                const page = "index";
                if (tags === '') {
                    showToast('error', 'Tag Description is required.');
                    return;
                }
                $.ajax({
                    url: "{{ route('document-drive.tags') }}", // Replace with your endpoint
                    type: 'POST',
                    data: {
                        tags: tags,
                        page: page,
                        selected_items: selectedItems
                    },
                    success: function(response) {
                        showToast('success', 'Tags added successfully to selected items!');
                        $('#addtag').modal('hide');
                    },
                    error: function(error) {
                        showToast('error', 'Error adding tags to selected items!');
                        console.error(error);
                    }
                });

            }
        });
        $(document).on('click', '.delete-btn-all', function(e) {
            if ($(".grid-data").is(":visible")) {
                e.preventDefault();

                let $this = $(this);
                let url = $this.data('url'); // The delete endpoint
                let message = $this.data('message') || 'Are you sure you want to delete the selected items?';
                let redirectUrl = $this.data('redirect') || window.location.pathname;

                // Collect selected item IDs
                let selectedItems = [];
                $('.selected-item:checked').each(function() {
                    selectedItems.push($(this).val()); // Assumes checkbox value is the item ID
                });

                if (selectedItems.length === 0) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select at least one item to delete.',
                        icon: 'error'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Alert!',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete them!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            contentType: 'application/json',
                            data: JSON.stringify({
                                selected_items: selectedItems,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            }),
                            beforeSend: () => $('#loaderDiv').show(),
                            success: (res) => {
                                $('#loaderDiv').hide();
                                Swal.fire({
                                    title: 'Success!',
                                    text: res.message,
                                    icon: 'success'
                                });

                                // Remove deleted items from the UI
                                $('.selected-item-checkbox:checked').closest('tr').fadeOut(500,
                                    function() {
                                        $(this).remove();
                                    });

                                setTimeout(() => {
                                    if (redirectUrl) {
                                        window.location.replace(redirectUrl);
                                    } else {
                                        location.reload();
                                    }
                                }, 1500);
                            },
                            error: (error) => {
                                $('#loaderDiv').hide();
                                let res = error.responseJSON || {};
                                Swal.fire({
                                    title: 'Error!',
                                    text: res.message ||
                                        'An unexpected error occurred.',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            } else {
                e.preventDefault();

                let $this = $(this);
                let url = $this.data('url'); // The delete endpoint
                let message = $this.data('message') || 'Are you sure you want to delete the selected items?';
                let redirectUrl = $this.data('redirect') || window.location.pathname;

                // Collect selected item IDs
                let selectedItems = [];
                $('.selected-item-list:checked').each(function() {
                    selectedItems.push($(this).val()); // Assumes checkbox value is the item ID
                });

                if (selectedItems.length === 0) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select at least one item to delete.',
                        icon: 'error'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Alert!',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete them!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            contentType: 'application/json',
                            data: JSON.stringify({
                                selected_items: selectedItems,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            }),
                            beforeSend: () => $('#loaderDiv').show(),
                            success: (res) => {
                                $('#loaderDiv').hide();
                                Swal.fire({
                                    title: 'Success!',
                                    text: res.message,
                                    icon: 'success'
                                });

                                // Remove deleted items from the UI
                                $('.selected-item-checkbox:checked').closest('tr').fadeOut(500,
                                    function() {
                                        $(this).remove();
                                    });

                                setTimeout(() => {
                                    if (redirectUrl) {
                                        window.location.replace(redirectUrl);
                                    } else {
                                        location.reload();
                                    }
                                }, 1500);
                            },
                            error: (error) => {
                                $('#loaderDiv').hide();
                                let res = error.responseJSON || {};
                                Swal.fire({
                                    title: 'Error!',
                                    text: res.message ||
                                        'An unexpected error occurred.',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            }
        });
        $(document).on('click', '#submitShare', function() {
            // Collect selected user IDs from the modal
            const selectedUsers = $('#shareUsers').val();

            // Determine which checkboxes to target based on visibility
            const isGridVisible = $(".grid-data").is(":visible");
            const itemSelector = isGridVisible ? '.selected-item:checked' : '.selected-item-list:checked';

            // Collect selected items (files and folders) from checkboxes
            const selectedItems = [];
            $(itemSelector).each(function() {
                selectedItems.push($(this).val()); // Format: "type|id"
            });

            // Validate inputs
            if (!selectedUsers || selectedUsers.length === 0) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select at least one user to share with.',
                    icon: 'error',
                });
                return;
            }

            if (selectedItems.length === 0) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select at least one item to share.',
                    icon: 'error',
                });
                return;
            }

            // AJAX request to share items with selected users
            $.ajax({
                url: '{{ route('document-drive.share.all') }}', // Replace with your endpoint
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    selected_users: selectedUsers,
                    selected_items: selectedItems,
                    _token: $('meta[name="csrf-token"]').attr('content'),
                }),
                beforeSend: () => $('#loaderDiv').show(),
                success: (response) => {
                    $('#loaderDiv').hide();
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                    });
                    $('#shareuserall').modal('hide');
                },
                error: (error) => {
                    $('#loaderDiv').hide();
                    Swal.fire({
                        title: 'Error!',
                        text: error.responseJSON?.message || 'An unexpected error occurred.',
                        icon: 'error',
                    });
                },
            });
        });

        $(document).on('click', '#moveSubmit', function() {
            // Collect selected items based on the visible view
            const selectedItems = [];
            const isGridVisible = $(".grid-data").is(":visible");
            const itemSelector = isGridVisible ? '.selected-item:checked' :
                '.selected-item-list:checked';
            $(itemSelector).each(function() {
                selectedItems.push($(this).val()); // Format: "type|id"
            });


            // Get the selected destination folder ID
            const destinationFolderId = $('#destinationFolder').val();

            // Validate inputs
            if (selectedItems.length === 0) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select at least one item to move.',
                    icon: 'error',
                });
                return;
            }

            if (!destinationFolderId) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select a destination folder.',
                    icon: 'error',
                });
                return;
            }

            // AJAX request to move items to the selected folder
            $.ajax({
                url: '{{ route('document-drive.movetofolder.multiple') }}', // Adjust with your route
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    selected_items: selectedItems, // Array of "type|id" values
                    destination_folder_id: destinationFolderId, // ID of destination folder
                    _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                }),
                beforeSend: () => $('#loaderDiv').show(),
                success: (response) => {
                    $('#loaderDiv').hide();
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                    });
                    $('#moveModal').modal('hide');
                    // Close the modal
                    window.location.reload();

                    // Optionally, refresh the folder/file list here
                },
                error: (error) => {
                    $('#loaderDiv').hide();
                    Swal.fire({
                        title: 'Error!',
                        text: error.responseJSON?.message || 'An unexpected error occurred.',
                        icon: 'error',
                    });
                },
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-folder-all'); // Search input element
            const rows = document.querySelectorAll('.list-data  .table tbody tr'); // Table rows to be filtered
            const folderItems = document.querySelectorAll('.mngfolder');
            const fileItems = document.querySelectorAll('.mngfile');

            // Add event listener to the search input field
            searchInput.addEventListener('input', function() {
                const searchTerm = searchInput.value.toLowerCase(); // Get the search term (in lowercase)

                // Loop through each row and check if the row should be visible or hidden
                rows.forEach(row => {
                    const nameCell = row.querySelector(
                        'td:nth-child(2)'); // Select the 'Name' column (2nd column)
                    const nameText = nameCell ? nameCell.textContent.toLowerCase() :
                        ''; // Get the name text in lowercase

                    // Check if the name contains the search term
                    if (nameText.includes(searchTerm)) {
                        row.style.display = ''; // Show row if it matches the search term
                    } else {
                        row.style.display = 'none'; // Hide row if it doesn't match
                    }
                });

                folderItems.forEach(function(folder) {
                const folderName = folder.querySelector('h6').textContent.toLowerCase();
                if (folderName.includes(searchTerm)) {
                    folder.style.display = 'block';
                } else {
                    folder.style.display = 'none';
                }
            });

            fileItems.forEach(function(file) {
                const fileName = file.querySelector('h6').textContent.toLowerCase();
                if (fileName.includes(searchTerm)) {
                    file.style.display = 'block';
                } else {
                    file.style.display = 'none';
                }
            });
            });
        });
    </script>
@endsection
@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ url('') }}/app-assets/css/pages/app-file-manager.css">
    <style>
        .responsive-preview {
            width: 100%;
            height: auto;
            max-height: 500px;
            object-fit: contain;
            border: none;
            display: block;
            margin: 0 auto;
        }

        .zip-preview {
            text-align: center;
            margin: 20px 0;
        }

        .iframe-container {
            position: relative;
            width: 100%;
            height: 0;
            padding-bottom: 56.25%;
            /* This maintains a 16:9 aspect ratio (adjust as needed) */
            overflow: hidden;
        }

        .iframe-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
@endsection
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content file-manager-application">
        @if ($errors->any())
            <div class="alert alert-danger mt-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-header row">
            <div class="content-header-left col-md-5 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-start mb-0">Shared Drive</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('document-drive.shared-drive') }}">Home</a>
                                </li>
                                @isset($parentFolders)
                                    @foreach ($parentFolders as $parentFolder)
                                        <li class="breadcrumb-item">
                                            <a
                                                href="{{ route('document-drive.folder.show', $parentFolder->id) }}">{{ $parentFolder->name }}</a>
                                        </li>
                                    @endforeach
                                @endisset
                                @if (isset($parent_folder))
                                    <li class="breadcrumb-item active">{{ $parent_folder->name }}</li>
                                @else
                                    <li class="breadcrumb-item active">Drive List</li>
                                @endif

                            </ol>

                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                <div class="form-group breadcrumb-right d-flex flex-wrap align-items-end justify-content-sm-end">
                    <button class="btn btn-warning btn-sm mb-50 mb-sm-0 me-50" data-bs-target="#filter"
                        data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                    <div class="dropdown me-50 mb-50 mb-sm-0">
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" data-bs-toggle="modal" href="#addaccess">
                                <i data-feather="file" class="me-50"></i>
                                <span>Create Folder</span>
                            </a>
                            <div class="image-uploadhide">
                                <a href="#" class="dropdown-item">
                                    <i data-feather="upload" class="me-50"></i>
                                    <span>File Upload</span>
                                </a>
                                <input type="file" id="file-upload" multiple />

                            </div>

                            <div class="image-uploadhide">
                                <a href="#" class="dropdown-item">
                                    <i data-feather="upload-cloud" class="me-50"></i>
                                    <span>Folder Upload</span>
                                </a>
                                <input type="file" id="folder-upload" multiple />
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-area-wrapper container-xxl p-0">
            <div class="content-right w-100">
                <div class="content-wrapper container-xxl p-0">
                    <div class="content-header row">
                    </div>
                    <div class="content-body">
                        <!-- file manager app content starts -->
                        <div class="file-manager-main-content">
                            <!-- search area start -->
                            <div class="file-manager-content-header d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="input-group input-group-merge shadow-none m-0 flex-grow-1">
                                        <span class="input-group-text border-0">
                                            <i data-feather="search"></i>
                                        </span>
                                        <input type="text" id="search-folder-all" class="form-control files-filter border-0 bg-transparent"
                                            placeholder="Search" />
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="file-actions d-block">
                                        <span data-bs-toggle="tooltip" data-bs-placement="bottom" title="Share"><i
                                                data-bs-toggle="modal" href="#shareuserall" data-feather="user-plus"
                                                class="font-medium-2 cursor-pointer me-50"></i></span>
                                        <span data-bs-toggle="tooltip" data-bs-placement="bottom" id="downloadSelected"
                                            title="Download"><i data-feather="download"
                                                class="font-medium-2 cursor-pointer me-50"></i></span>
                                        <span data-bs-toggle="tooltip" data-bs-placement="bottom" class="delete-btn-all"
                                            title="Delete" data-url="{{ route('document-drive.delete') }}"
                                            data-redirect="{{ route('document-drive.shared-drive', [$parent ?? '']) }}"
                                            data-message="Are you sure you want to delete these files\folders?">
                                            <i data-feather="trash" class="font-medium-2 cursor-pointer me-50"></i></span>
                                        <span data-bs-toggle="tooltip" data-bs-placement="bottom" title="Move to Folder"><i
                                                data-bs-toggle="modal" href="#movetofolderall" data-feather="folder-minus"
                                                class="font-medium-2 cursor-pointer me-50"></i></span>
                                        <span data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Tag"><i
                                                data-bs-toggle="modal" href="#addtag" data-feather="tag"
                                                class="font-medium-2 cursor-pointer me-50"></i></span>
                                    </div>
                                    <div class="btn-group view-toggle ms-50" role="group">
                                        <input type="radio" class="btn-check" name="view-list-btn" id="gridView"
                                            checked autocomplete="off" />
                                        <label class="btn btn-outline-primary p-50 btn-sm" for="gridView">
                                            <i data-feather="grid"></i>
                                        </label>
                                        <input type="radio" class="btn-check" name="view-list-btn" id="listView"
                                            autocomplete="off" />
                                        <label class="btn btn-outline-primary p-50 btn-sm" for="listView">
                                            <i data-feather="list"></i>
                                        </label>
                                    </div>
                                </div>
                            </div>


                            <div class="file-manager-content-body grid-data">

                                <div class="">
                                    <h6 class="files-section-title mt-25 mb-75">Folders</h6>


                                    <div class="row">
                                        @forelse ($folders as $folder)
                                            <div class="col-md-3 mngfolder">
                                                <div class="doc-drivebocfolder">

                                                    <div class="form-check form-check-inline me-50">
                                                        <input class="form-check-input selected-item" type="checkbox"
                                                            id="inlineCheckbox{{ $folder->id }}"
                                                            value="folder|{{ $folder->id }}">
                                                    </div>
                                                    <h6
                                                        onclick="window.location.href='{{ route('document-drive.folder.show', $folder->id) }}'">
                                                        {{ $folder->name }}</h6>
                                                    <div class="tableactionnew ms-auto">
                                                        <div class="dropdown">
                                                            <button type="button"
                                                                class="btn btn-sm dropdown-toggle hide-arrow py-0"
                                                                data-bs-toggle="dropdown">
                                                                <i data-feather="more-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item" href="#shareuser"
                                                                    data-id="{{ $folder->id }}" data-type="folder"
                                                                    data-bs-toggle="modal">
                                                                    <i data-feather="user-plus" class="me-50"></i>
                                                                    <span>Share</span>
                                                                </a>
                                                                <a class="dropdown-item"
                                                                    href="{{ route('document-drive.folders.download', [$folder->id]) }}">
                                                                    <i data-feather="download" class="me-50"></i>
                                                                    <span>Download</span>
                                                                </a>
                                                                <a class="dropdown-item" href="#"
                                                                    data-bs-toggle="modal" data-bs-target="#filerename"
                                                                    data-id="{{ $folder->id }}"
                                                                    data-name="{{ $folder->name }}" data-type="folder">
                                                                    <i data-feather="file-text" class="me-50"></i>
                                                                    <span>Rename</span>
                                                                </a>
                                                                <a class="dropdown-item" href="#movetofolder"
                                                                    data-bs-toggle="modal" data-id="{{ $folder->id }}"
                                                                    data-type="folder">
                                                                    <i data-feather="folder-minus" class="me-50"></i>
                                                                    <span>Move to Folder</span>
                                                                </a>
                                                                <a class="dropdown-item delete-btn" href="#"
                                                                    data-url="{{ route('document-drive.folder.delete', $folder->id) }}"
                                                                    data-redirect="{{ route('document-drive.shared-drive', [$parent ?? '']) }}"
                                                                    data-message="Are you sure you want to delete this Folder?">
                                                                    <i data-feather="trash-2" class="me-50"></i>
                                                                    <span>Delete</span>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-md-3">
                                                <p>No folders available.</p>
                                            </div>
                                        @endforelse
                                    </div>






                                    <div class="d-none flex-grow-1 align-items-center no-result mb-3">
                                        <i data-feather="alert-circle" class="me-50"></i>
                                        No Results
                                    </div>
                                </div>
                                <!-- /Folders Container Ends -->

                                <!-- Files Container Starts -->
                                <div class="">
                                    <h6 class="files-section-title mt-2 mb-75">Files</h6>


                                    <div class="row">

                                        @forelse ($files as $file)
                                            <div class="col-md-3 mngfile">
                                                <div class="docu-managedriveboxfile">
                                                    <div class="doc-drivebocfolder">
                                                        <div class="form-check form-check-inline me-50">
                                                            <input class="form-check-input selected-item" type="checkbox"
                                                                id="checkbox-{{ $file->id }}"
                                                                value="file|{{ $file->id }}">
                                                        </div>
                                                        <h6><a href="{{ route('document-drive.file.show', $file->id) }}"
                                                                target="_blank">{{ $file->name }}</a></h6>
                                                        <div class="tableactionnew ms-auto">
                                                            <div class="dropdown">
                                                                <button type="button"
                                                                    class="btn btn-sm dropdown-toggle hide-arrow py-0"
                                                                    data-bs-toggle="dropdown">
                                                                    <i data-feather="more-vertical"></i>
                                                                </button>
                                                                <div class="dropdown-menu dropdown-menu-end">
                                                                    <a class="dropdown-item" href="#shareuser"
                                                                        data-id="{{ $file->id }}" data-type="file"
                                                                        data-bs-toggle="modal">
                                                                        <i data-feather="user-plus" class="me-50"></i>
                                                                        <span>Share</span>
                                                                    </a>
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('document-drive.files.download', $file->id) }}">
                                                                        <i data-feather="download" class="me-50"></i>
                                                                        <span>Download</span>
                                                                    </a>
                                                                    <a class="dropdown-item" href="#"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#filerename"
                                                                        data-id="{{ $file->id }}"
                                                                        data-name="{{ $file->name }}" data-type="file">
                                                                        <i data-feather="file-text" class="me-50"></i>
                                                                        <span>Rename</span>
                                                                    </a>
                                                                    <a class="dropdown-item" href="#movetofolder"
                                                                        data-bs-toggle="modal"
                                                                        data-id="{{ $file->id }}" data-type="file">
                                                                        <i data-feather="folder-minus" class="me-50"></i>
                                                                        <span>Move to Folder</span>
                                                                    </a>
                                                                    <a class="dropdown-item delete-btn"
                                                                        data-url="{{ route('document-drive.file.delete', $file->id) }}"
                                                                        data-redirect="{{ route('document-drive.shared-drive', [$parent ?? '']) }}"
                                                                        data-message="Are you sure you want to delete this File?">
                                                                        <i data-feather="trash-2" class="me-50"></i>
                                                                        <span>Delete</span>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <a href="{{ route('document-drive.file.show', $file->id) }}"
                                                        target="_blank">
                                                        <div class="iframe-container">
                                                            @php
                                                                $extension = strtolower(
                                                                    pathinfo($file->name, PATHINFO_EXTENSION),
                                                                ); // Get the file extension
                                                                $imageExtensions = [
                                                                    'jpg',
                                                                    'jpeg',
                                                                    'png',
                                                                    'gif',
                                                                    'webp',
                                                                ]; // Define valid image extensions
                                                            @endphp

                                                            @if (in_array($extension, $imageExtensions))
                                                                <img src="{{ route('document-drive.file.show', $file->id) }}"
                                                                    alt="{{ $file->name }}" class="responsive-preview">
                                                            @elseif (in_array($extension, ['zip', 'rar', '7z']))
                                                                <!-- Display a ZIP icon with a download or view option -->
                                                                <div class="zip-preview">
                                                                    <img src="{{ url('') }}/img/zip.png"
                                                                        style="width:50%;height:50%" alt="ZIP Icon"
                                                                        class="responsive-preview">
                                                                </div>
                                                            @else
                                                                <iframe id=""
                                                                    src="{{ route('document-drive.file.show', $file->id) }}"
                                                                    alt="{{ $file->name }}" scrolling="no"></iframe>
                                                            @endif

                                                        </div>
                                                    </a>

                                                </div>
                                            </div>

                                        @empty
                                            <div class="col-md-3">
                                                <p>No files available.</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                                <!-- /Files Container Ends -->
                            </div>

                            <div class="file-manager-content-body list-data px-0">
                                <table
                                    class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                    <thead>
                                        <tr>
                                            <th class="pe-0 ps-50">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="inlineCheckbox1">
                                                </div>
                                            </th>
                                            <th>Name</th>
                                            <th>Document Type</th>
                                            <th>Owner</th>
                                            <th>Last Modified</th>
                                            <th>File Size</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($items as $item)
                                            <tr class="cursor-pointer">
                                                <!-- Checkbox Column -->
                                                <td class="pe-0">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input selected-item-list" type="checkbox"
                                                            id="checkbox-{{ $item->id }}"
                                                            value="{{ $item->type }}|{{ $item->id }}">
                                                    </div>
                                                </td>

                                                <!-- Name Column -->
                                                <td class="fw-bolder text-dark">
                                                    <div class="d-flex align-items-center">
                                                        @php
                                                            $pathInfo = pathinfo($item->name);
                                                            $extension = isset($pathInfo['extension'])
                                                                ? strtolower($pathInfo['extension'])
                                                                : null;
                                                        @endphp
                                                        @if ($item->type === 'folder')
                                                            <i data-feather="folder"></i>
                                                        @else
                                                            @if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                                <i data-feather="image"></i>
                                                            @elseif ($extension === 'pdf')
                                                                <i data-feather="file-text"></i>
                                                            @elseif (in_array($extension, ['xls', 'xlsx', 'csv']))
                                                                <i data-feather="file"></i>
                                                            @else
                                                                <i data-feather="file"></i>
                                                            @endif
                                                        @endif
                                                        <span
                                                            onclick="window.location.href='{{ $item->type === 'folder' ? route('document-drive.folder.show', $item->id) : route('document-drive.file.show', $item->id) }}'"
                                                            class="ms-50">{{ $item->name }}</span>
                                                    </div>
                                                </td>

                                                <!-- Extension Column -->
                                                <td>{{ $item->type === 'folder' ? 'Folder' : $extension }}</td>

                                                <!-- Owner Column -->
                                                <td>
                                                    <div class="d-flex flex-row doc-driownername">
                                                        <div class="avatar me-75">
                                                            <img src="{{ $item->owner->avatar ?? asset('img/default.jpg') }}"
                                                                width="25" height="25" alt="Avatar">
                                                        </div>
                                                        <div class="my-auto">
                                                            <h6>{{ $item->owner->name ?? 'Unknown' }}</h6>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Date Column -->
                                                <td>{{ $item->created_at->format('d-m-Y') }}</td>

                                                <!-- Size Column -->
                                                <td>{{ $item->type === 'file' ? number_format($item->size / 1024, 2) . ' MB' : '-' }}
                                                </td>

                                                <!-- Actions Column -->
                                                <td class="tableactionnew">
                                                    <div class="dropdown">
                                                        <button type="button"
                                                            class="btn btn-sm dropdown-toggle hide-arrow py-0"
                                                            data-bs-toggle="dropdown">
                                                            <i data-feather="more-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <a class="dropdown-item" href="#shareuser"
                                                                data-id="{{ $item->id }}"
                                                                data-type="{{ $item->type }}" data-bs-toggle="modal">
                                                                <i data-feather="user-plus" class="me-50"></i>
                                                                <span>Share</span>
                                                            </a>
                                                            <a class="dropdown-item"
                                                                href="{{ $item->type === 'folder'
                                                                    ? route('document-drive.folders.download', $item->id)
                                                                    : route('document-drive.files.download', $item->id) }}">
                                                                <i data-feather="download" class="me-50"></i>
                                                                <span>Download</span>
                                                            </a>

                                                            <a class="dropdown-item" href="#"
                                                                data-bs-toggle="modal" data-bs-target="#filerename"
                                                                data-id="{{ $item->id }}"
                                                                data-name="{{ $item->name }}"
                                                                data-type="{{ $item->type }}">
                                                                <i data-feather="file-text" class="me-50"></i>
                                                                <span>Rename</span>
                                                            </a>
                                                            <a class="dropdown-item" href="#movetofolder"
                                                                data-bs-toggle="modal" data-id="{{ $item->id }}"
                                                                data-type="{{ $item->type }}">
                                                                <i data-feather="folder-minus" class="me-50"></i>
                                                                <span>Move to Folder</span>
                                                            </a>
                                                            <a class="dropdown-item delete-btn"
                                                                data-url="{{ $item->type === 'file' ? route('document-drive.file.delete', $item->id) : route('document-drive.folder.delete', $item->id) }}"
                                                                data-redirect="{{ route('document-drive.shared-drive', [$parent ?? '']) }}"
                                                                data-message="Are you sure you want to delete this Folder?">
                                                                <i data-feather="trash-2" class="me-50"></i>
                                                                <span>Delete</span>
                                                            </a>

                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>



                                </table>
                            </div>


                        </div>

                        <div class="dropdown-menu dropdown-menu-end file-dropdown">
                            <a class="dropdown-item" href="#">
                                <i data-feather="edit-3" class="align-middle me-50"></i>
                                <span class="align-middle">Edit</span>
                            </a>
                            <a class="dropdown-item" href="#">
                                <i data-feather="trash-2" class="align-middle me-50"></i>
                                <span class="align-middle">Delete</span>
                            </a>
                            <a class="dropdown-item" href="#">
                                <i data-feather="download" class="align-middle me-50"></i>
                                <span class="align-middle">Download</span>
                            </a>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Content-->

    <div class="modal modal-sticky" id="fileuplostatyus" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-sm">
            <div class="modal-content p-0">
                <div class="modal-header">
                    <h5 class="modal-title">Uploading <span id="upload-count">0</span> Items</h5>
                    <div class="modal-actions">
                        <a class="text-body" href="#" data-bs-dismiss="modal" aria-label="Close"><i
                                data-feather="x"></i></a>
                    </div>
                </div>
                <div class="modal-body p-0" id="upload-status">
                    <!-- Upload status will be appended here -->
                </div>
            </div>
        </div>
    </div>



    <!-- Modal -->
    <div class="modal fade" id="addaccess" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-4 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Add/Edit Folder</h1>
                    <p class="text-center">Enter the details below.</p>

                    <form
                        action="{{ isset($parent) ? route('document-drive.folder.store', [$parent]) : route('document-drive.folder.store') }}"
                        method="POST">
                        @csrf
                        <div class="row mt-2">
                            <div class="col-md-12 mb-1">
                                <label class="form-label">Folder Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name"
                                    placeholder="Enter Folder Name" required />
                            </div>

                            <div class="col-md-12 mb-1">
                                <label class="form-label">Status</label>
                                <div class="demo-inline-spacing">
                                    <div class="form-check form-check-primary mt-25">
                                        <input type="radio" id="active" name="status" class="form-check-input"
                                            value="active" checked>
                                        <label class="form-check-label fw-bolder" for="active">Active</label>
                                    </div>
                                    <div class="form-check form-check-primary mt-25">
                                        <input type="radio" id="inactive" name="status" class="form-check-input"
                                            value="inactive">
                                        <label class="form-check-label fw-bolder" for="inactive">Inactive</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-outline-secondary me-1"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="addtag" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-4 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Add/Edit Tag</h1>
                    <p class="text-center">Enter the details below.</p>

                    <div class="row mt-2">

                        <div class="col-md-12 mb-1">
                            <label class="form-label">Tag/Description <span class="text-danger">*</span></label>
                            <input type="text" name="tags" id="tag" class="form-control"
                                placeholder="Enter Detail" />
                        </div>


                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button id="submitTags" type="button" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="filerename" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('document-drive.rename', [$parent ?? '']) }}" method="POST">
                    @csrf
                    <div class="modal-body px-sm-4 mx-50 pb-2">
                        <h1 class="text-center mb-1" id="shareProjectTitle">Edit File/Folder Name</h1>
                        <p class="text-center">Enter the details below.</p>
                        <div class="row mt-2">
                            <div class="col-md-12 mb-1">
                                <label class="form-label">File/Folder Name <span class="text-danger">*</span></label>
                                <input type="hidden" name="id" id="rename-id" value="">
                                <input type="hidden" name="type" id="rename-type" value="">
                                <input type="text" class="form-control" name="name" id="rename-name"
                                    placeholder="Enter File/Folder Name" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-secondary me-1"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="movetofolderall" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-4 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Move to Folder</h1>
                    <p class="text-center">Select a folder to move the item.</p>

                    <div class="row mt-2">
                        <div class="col-md-12 mb-1">
                            <label class="form-label">Search Folder <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Search Folder" id="search-folder" />
                        </div>

                        <div class="col-md-12">

                            <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Folder Name</th>
                                        <th>Last Modified</th>
                                        <th>Files Size</th>
                                    </tr>
                                </thead>
                                @forelse($all_folders as $fd)
                                    <tbody id="folder-table">
                                        <!-- Example Folder 1 -->
                                        <tr class="cursor-pointer" data-folder-id="1" data-type="folder">
                                            <td class="pe-0">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" id="destinationFolder" type="radio"
                                                        name="destination_folder_id" value="{{ $fd->id }}"
                                                        required>
                                                </div>
                                            </td>
                                            <td class="fw-bolder text-dark">
                                                @php
                                                    $currentFolder = $fd;
                                                    $hierarchy = [];
                                                    while ($currentFolder) {
                                                        $hierarchy[] = $currentFolder->name;
                                                        $currentFolder = $currentFolder->parent; // Assuming a `parent` relationship exists in the model
                                                    }
                                                    echo implode(' > ', array_reverse($hierarchy));
                                                @endphp
                                            </td>
                                            <td>{{ $fd->created_at->format('d-m-Y') }}</td>
                                            <td>-</td>
                                        </tr>
                                    </tbody>
                                @empty
                                    <div class="col-md-3">
                                        <p>No folders available.</p>
                                    </div>
                                @endforelse
                            </table>
                            <div class="modal-footer justify-content-center">
                                <button type="reset" class="btn btn-outline-secondary me-1"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="button" id="moveSubmit" class="btn btn-primary">Move</button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="movetofolder" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-4 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Move to Folder</h1>
                    <p class="text-center">Select a folder to move the item.</p>

                    <div class="row mt-2">
                        <div class="col-md-12 mb-1">
                            <label class="form-label">Search Folder <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Search Folder" id="search-folder" />
                        </div>

                        <div class="col-md-12">
                            <!-- Form to handle the move operation -->
                            <form method="POST" action="{{ route('document-drive.movetofolder') }}">
                                @csrf
                                <!-- Hidden Fields to hold source folder/file ID and type -->
                                <input type="hidden" name="source_id" id="move-id">
                                <input type="hidden" name="source_type" id="move-type">

                                <table
                                    class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Folder Name</th>
                                            <th>Last Modified</th>
                                            <th>Files Size</th>
                                        </tr>
                                    </thead>
                                    @forelse($all_folders as $fd)
                                        <tbody id="folder-table">
                                            <!-- Example Folder 1 -->
                                            <tr class="cursor-pointer" data-folder-id="1" data-type="folder">
                                                <td class="pe-0">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio"
                                                            name="destination_folder_id" value="{{ $fd->id }}"
                                                            required>
                                                    </div>
                                                </td>
                                                <td class="fw-bolder text-dark">
                                                    @php
                                                        $currentFolder = $fd;
                                                        $hierarchy = [];
                                                        while ($currentFolder) {
                                                            $hierarchy[] = $currentFolder->name;
                                                            $currentFolder = $currentFolder->parent; // Assuming a `parent` relationship exists in the model
                                                        }
                                                        echo implode(' > ', array_reverse($hierarchy));
                                                    @endphp
                                                </td>
                                                <td>{{ $fd->created_at->format('d-m-Y') }}</td>
                                                <td>-</td>
                                            </tr>
                                        </tbody>
                                    @empty
                                        <div class="col-md-3">
                                            <p>No folders available.</p>
                                        </div>
                                    @endforelse
                                </table>
                                <div class="modal-footer justify-content-center">
                                    <button type="reset" class="btn btn-outline-secondary me-1"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Move</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="shareuser" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('document-drive.share') }}" method="POST">
                    @csrf
                    <div class="modal-header p-0 bg-transparent">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-sm-4 mx-50 pb-2">
                        <h1 class="text-center mb-1" id="shareProjectTitle">Share with User</h1>
                        <p class="text-center">Enter the details below.</p>
                        <input type="hidden" id="share-id" value="" name="share_id">
                        <input type="hidden" id="share-type" value="" name="share_type">

                        <div class="row mt-2">
                            <div class="col-md-12 mb-1">
                                <label class="form-label">Select User <span class="text-danger">*</span></label>
                                <select class="form-select select2" name="users[]" multiple>
                                    <option value="">Select</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                        <button type="submit" class="btn btn-primary">Share</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal fade" id="shareuserall" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-4 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Share with User</h1>
                    <p class="text-center">Enter the details below.</p>
                    <div class="row mt-2">
                        <div class="col-md-12 mb-1">
                            <label class="form-label">Select User <span class="text-danger">*</span></label>
                            <select class="form-select select2" name="users[]" multiple id="shareUsers">
                                <option value="">Select</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submitShare">Share</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
        <div class="modal-dialog sidebar-sm">

            <form class="add-new-record modal-content pt-0" method="GET"
                action="{{ route('document-drive.shared-drive', [$parent ?? '']) }}">
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                        <label class="form-label" for="fp-range">Select Date</label>
                        <input type="text" id="fp-range" name="date_range"
                            class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD"
                            value="{{ request('date_range') }}" />
                    </div>
                    <div class="mb-1">
                        <label class="form-label">File Name</label>
                        <input type="text" name="file_name" placeholder="Enter File Name" class="form-control"
                            value="{{ request('file_name') }}" />
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Document Type</label>
                        <select name="document_type" class="form-select">
                            <option value="">Select</option>
                            <!-- Categories -->
                            <option value="Image" {{ request('document_type') == 'Image' ? 'selected' : '' }}>Image
                            </option>
                            <option value="Video" {{ request('document_type') == 'Video' ? 'selected' : '' }}>Video
                            </option>
                            <option value="Zip" {{ request('document_type') == 'Zip' ? 'selected' : '' }}>Zip</option>

                            <!-- Specific Document Types -->
                            <option value="MS Word" {{ request('document_type') == 'MS Word' ? 'selected' : '' }}>MS Word
                            </option>
                            <option value="MS Excel" {{ request('document_type') == 'MS Excel' ? 'selected' : '' }}>MS
                                Excel</option>
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Owner</label>
                        <select name="owner" class="form-select">
                            <option value="">Select</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ request('owner') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Folder</label>
                        <select name="folder" class="form-select">
                            <option value="">Select</option>
                            @foreach ($folders as $folder)
                                <option value="{{ $folder->id }}"
                                    {{ request('folder') == $folder->id ? 'selected' : '' }}>
                                    {{ $folder->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Tag/Description</label>
                        <input type="text" name="tag_description" placeholder="Enter Tag/Description"
                            class="form-control" value="{{ request('tag_description') }}" />
                    </div>
                </div>
                <div class="modal-footer justify-content-start">
                    <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>

        </div>
    </div>
@endsection
