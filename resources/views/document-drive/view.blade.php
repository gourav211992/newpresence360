@extends('layouts.app')
@section('scripts')
    <script src="../../../app-assets/js/scripts/pages/app-file-manager.js"></script>
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

        $(window).on('load', function() {
            //  $('#fileuplostatyus').modal('show');
        });
    </script>
@endsection
@section('styles')
    <link rel="stylesheet" type="text/css" href="../../../app-assets/css/pages/app-file-manager.css">
@endsection
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content file-manager-application">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-header row">
            <div class="content-header-left col-md-5 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-start mb-0">My Drive</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('document-drive.index') }}">Home</a></li>

                                @foreach ($parentFolders as $parentFolder)
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('document-drive.folder.show', $parentFolder->id) }}">{{ $parentFolder->name }}</a>
                                    </li>
                                @endforeach

                                <li class="breadcrumb-item active">{{ $parent_folder->name }}</li>
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
                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle exportcustomdrop"
                            data-bs-toggle="dropdown">
                            <i data-feather="plus-circle"></i> Add New
                        </button>
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
                                <input type="file" class="" multiple />
                            </div>

                            <div class="image-uploadhide">
                                <a href="#" class="dropdown-item">
                                    <i data-feather="upload-cloud" class="me-50"></i>
                                    <span>Folder Upload</span>
                                </a>
                                <input type="file" class="" multiple />
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
                                        <input type="text" class="form-control files-filter border-0 bg-transparent"
                                            placeholder="Search" />
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="file-actions d-block">
                                        <span data-bs-toggle="tooltip" data-bs-placement="bottom" title="Share"><i
                                                data-bs-toggle="modal" href="#shareuser" data-feather="user-plus"
                                                class="font-medium-2 cursor-pointer me-50"></i></span>
                                        <span data-bs-toggle="tooltip" data-bs-placement="bottom" title="Download"><i
                                                data-feather="download"
                                                class="font-medium-2 cursor-pointer me-50"></i></span>
                                        <span data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete"><i
                                                data-feather="trash" class="font-medium-2 cursor-pointer me-50"></i></span>
                                        <span data-bs-toggle="tooltip" data-bs-placement="bottom" title="Move to Folder"><i
                                                data-bs-toggle="modal" href="#movetofolder" data-feather="folder-minus"
                                                class="font-medium-2 cursor-pointer me-50"></i></span>
                                        <span data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Tag"><i
                                                data-bs-toggle="modal" href="#addtag" data-feather="tag"
                                                class="font-medium-2 cursor-pointer me-50"></i></span>
                                    </div>
                                    <div class="btn-group view-toggle ms-50" role="group">
                                        <input type="radio" class="btn-check" name="view-list-btn" id="gridView" checked
                                            autocomplete="off" />
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
                                            <div class="col-md-3">
                                                <div class="doc-drivebocfolder"
                                                    onclick="window.location.href='{{ route('document-drive.folder.show', $folder->id) }}'">
                                                    <div class="form-check form-check-inline me-50">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="inlineCheckbox{{ $folder->id }}">
                                                    </div>
                                                    <h6>{{ $folder->name }}</h6>
                                                    <div class="tableactionnew ms-auto">
                                                        <div class="dropdown">
                                                            <button type="button"
                                                                class="btn btn-sm dropdown-toggle hide-arrow py-0"
                                                                data-bs-toggle="dropdown">
                                                                <i data-feather="more-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item" href="#shareuser"
                                                                    data-bs-toggle="modal">
                                                                    <i data-feather="user-plus" class="me-50"></i>
                                                                    <span>Share</span>
                                                                </a>
                                                                <a class="dropdown-item" href="#">
                                                                    <i data-feather="download" class="me-50"></i>
                                                                    <span>Download</span>
                                                                </a>
                                                                <a class="dropdown-item" href="#filerename"
                                                                    data-bs-toggle="modal">
                                                                    <i data-feather="file-text" class="me-50"></i>
                                                                    <span>Rename</span>
                                                                </a>
                                                                <a class="dropdown-item" href="#">
                                                                    <i data-feather="folder-minus" class="me-50"></i>
                                                                    <span>Move to Folder</span>
                                                                </a>
                                                                <a class="dropdown-item"
                                                                    data-url="{{ route('document-drive.folder.delete', $folder->id) }}"
                                                                    data-redirect="{{ route('document-drive.index') }}"
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
                                            <div class="col-md-3">
                                                <div class="docu-managedriveboxfile">
                                                    <div class="doc-drivebocfolder">
                                                        <div class="form-check form-check-inline me-50">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="checkbox-{{ $file->id }}">
                                                        </div>
                                                        <h6>{{ $file->name }}</h6>
                                                        <div class="tableactionnew ms-auto">
                                                            <div class="dropdown">
                                                                <button type="button"
                                                                    class="btn btn-sm dropdown-toggle hide-arrow py-0"
                                                                    data-bs-toggle="dropdown">
                                                                    <i data-feather="more-vertical"></i>
                                                                </button>
                                                                <div class="dropdown-menu dropdown-menu-end">
                                                                    <a class="dropdown-item" href="#shareuser"
                                                                        data-bs-toggle="modal">
                                                                        <i data-feather="user-plus" class="me-50"></i>
                                                                        <span>Share</span>
                                                                    </a>
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('files.download', $file->id) }}">
                                                                        <i data-feather="download" class="me-50"></i>
                                                                        <span>Download</span>
                                                                    </a>
                                                                    <a class="dropdown-item" href="#filerename"
                                                                        data-bs-toggle="modal">
                                                                        <i data-feather="file-text" class="me-50"></i>
                                                                        <span>Rename</span>
                                                                    </a>
                                                                    <a class="dropdown-item" href="#movetofolder"
                                                                        data-bs-toggle="modal">
                                                                        <i data-feather="folder-minus" class="me-50"></i>
                                                                        <span>Move to Folder</span>
                                                                    </a>
                                                                    <a class="dropdown-item"
                                                                        data-url="{{ route('document-drive.file.delete', $file->id) }}"
                                                                        data-redirect="{{ route('document-drive.index') }}"
                                                                        data-message="Are you sure you want to delete this File?">
                                                                        <i data-feather="trash-2" class="me-50"></i>
                                                                        <span>Delete</span>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <a href="{{ asset('storage/' . $file->path) }}" target="_blank">
                                                        <img src="{{ asset('img/file-icon.png') }}"
                                                            alt="{{ $file->name }}" />
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
                                        <tr class="cursor-pointer">
                                            <td class="pe-0">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="inlineCheckbox1">
                                                </div>
                                            </td>
                                            <td class="fw-bolder text-dark">
                                                <div class="d-flex align-items-center">
                                                    <i data-feather="folder"></i>
                                                    <span class="ms-50">UI/UX</span>
                                                </div>
                                            </td>
                                            <td></td>
                                            <td>
                                                <div class="d-flex flex-row doc-driownername">
                                                    <div class="avatar me-75">
                                                        <img src="../../../app-assets/images/portrait/small/avatar-s-8.jpg"
                                                            width="25" height="25" alt="Avatar">
                                                    </div>
                                                    <div class="my-auto">
                                                        <h6>Mahesh Kumar</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>19-11-2024</td>
                                            <td>200 MB</td>
                                            <td class="tableactionnew">
                                                <div class="dropdown">
                                                    <button type="button"
                                                        class="btn btn-sm dropdown-toggle hide-arrow py-0"
                                                        data-bs-toggle="dropdown">
                                                        <i data-feather="more-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#shareuser"
                                                            data-bs-toggle="modal">
                                                            <i data-feather="user-plus" class="me-50"></i>
                                                            <span>Share</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#">
                                                            <i data-feather="download" class="me-50"></i>
                                                            <span>Download</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#filerename"
                                                            data-bs-toggle="modal">
                                                            <i data-feather="file-text" class="me-50"></i>
                                                            <span>Rename</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#">
                                                            <i data-feather="folder-minus" class="me-50"></i>
                                                            <span>Move to Folder</span>
                                                        </a>
                                                        <a class="dropdown-item">
                                                            <i data-feather="trash-2" class="me-50"></i>
                                                            <span>Delete</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="cursor-pointer">
                                            <td class="pe-0">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="inlineCheckbox1">
                                                </div>
                                            </td>
                                            <td class="fw-bolder text-dark">
                                                <div class="d-flex align-items-center">
                                                    <i data-feather="folder"></i>
                                                    <span class="ms-50">MIDC</span>
                                                </div>
                                            </td>
                                            <td></td>
                                            <td>
                                                <div class="d-flex flex-row doc-driownername">
                                                    <div class="avatar me-75">
                                                        <img src="../../../app-assets/images/portrait/small/avatar-s-8.jpg"
                                                            width="25" height="25" alt="Avatar">
                                                    </div>
                                                    <div class="my-auto">
                                                        <h6>Nishu Garg</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>19-11-2024</td>
                                            <td>200 MB</td>
                                            <td class="tableactionnew">
                                                <div class="dropdown">
                                                    <button type="button"
                                                        class="btn btn-sm dropdown-toggle hide-arrow py-0"
                                                        data-bs-toggle="dropdown">
                                                        <i data-feather="more-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#shareuser"
                                                            data-bs-toggle="modal">
                                                            <i data-feather="user-plus" class="me-50"></i>
                                                            <span>Share</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#">
                                                            <i data-feather="download" class="me-50"></i>
                                                            <span>Download</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#filerename"
                                                            data-bs-toggle="modal">
                                                            <i data-feather="file-text" class="me-50"></i>
                                                            <span>Rename</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#">
                                                            <i data-feather="folder-minus" class="me-50"></i>
                                                            <span>Move to Folder</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#">
                                                            <i data-feather="trash-2" class="me-50"></i>
                                                            <span>Delete</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="cursor-pointer">
                                            <td class="pe-0">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="inlineCheckbox1">
                                                </div>
                                            </td>
                                            <td class="fw-bolder text-dark">
                                                <div class="d-flex align-items-center">
                                                    <i data-feather="folder"></i>
                                                    <span class="ms-50">Carestyle</span>
                                                </div>
                                            </td>
                                            <td></td>
                                            <td>
                                                <div class="d-flex flex-row doc-driownername">
                                                    <div class="avatar me-75">
                                                        <img src="../../../app-assets/images/portrait/small/avatar-s-8.jpg"
                                                            width="25" height="25" alt="Avatar">
                                                    </div>
                                                    <div class="my-auto">
                                                        <h6>Roshan Kumar</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>19-11-2024</td>
                                            <td>200 MB</td>
                                            <td class="tableactionnew">
                                                <div class="dropdown">
                                                    <button type="button"
                                                        class="btn btn-sm dropdown-toggle hide-arrow py-0"
                                                        data-bs-toggle="dropdown">
                                                        <i data-feather="more-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#shareuser"
                                                            data-bs-toggle="modal">
                                                            <i data-feather="user-plus" class="me-50"></i>
                                                            <span>Share</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#">
                                                            <i data-feather="download" class="me-50"></i>
                                                            <span>Download</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#filerename"
                                                            data-bs-toggle="modal">
                                                            <i data-feather="file-text" class="me-50"></i>
                                                            <span>Rename</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#">
                                                            <i data-feather="folder-minus" class="me-50"></i>
                                                            <span>Move to Folder</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#">
                                                            <i data-feather="trash-2" class="me-50"></i>
                                                            <span>Delete</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="cursor-pointer">
                                            <td class="pe-0">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="inlineCheckbox1">
                                                </div>
                                            </td>
                                            <td class="fw-bolder text-dark">
                                                <div class="d-flex align-items-center">
                                                    <i data-feather="image"></i>
                                                    <span class="ms-50">design_midc.jpg</span>
                                                </div>
                                            </td>
                                            <td>jpg</td>
                                            <td>
                                                <div class="d-flex flex-row doc-driownername">
                                                    <div class="avatar me-75">
                                                        <img src="../../../app-assets/images/portrait/small/avatar-s-8.jpg"
                                                            width="25" height="25" alt="Avatar">
                                                    </div>
                                                    <div class="my-auto">
                                                        <h6>Mahesh Kumar</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>19-11-2024</td>
                                            <td>200 MB</td>
                                            <td class="tableactionnew">
                                                <div class="dropdown">
                                                    <button type="button"
                                                        class="btn btn-sm dropdown-toggle hide-arrow py-0"
                                                        data-bs-toggle="dropdown">
                                                        <i data-feather="more-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#shareuser"
                                                            data-bs-toggle="modal">
                                                            <i data-feather="user-plus" class="me-50"></i>
                                                            <span>Share</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#">
                                                            <i data-feather="download" class="me-50"></i>
                                                            <span>Download</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#filerename"
                                                            data-bs-toggle="modal">
                                                            <i data-feather="file-text" class="me-50"></i>
                                                            <span>Rename</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#">
                                                            <i data-feather="folder-minus" class="me-50"></i>
                                                            <span>Move to Folder</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#">
                                                            <i data-feather="trash-2" class="me-50"></i>
                                                            <span>Delete</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="cursor-pointer">
                                            <td class="pe-0">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="inlineCheckbox1">
                                                </div>
                                            </td>
                                            <td class="fw-bolder text-dark">
                                                <div class="d-flex align-items-center">
                                                    <i data-feather="file-text"></i>
                                                    <span class="ms-50">structure.excel</span>
                                                </div>
                                            </td>
                                            <td>Excel</td>
                                            <td>
                                                <div class="d-flex flex-row doc-driownername">
                                                    <div class="avatar me-75">
                                                        <img src="../../../app-assets/images/portrait/small/avatar-s-8.jpg"
                                                            width="25" height="25" alt="Avatar">
                                                    </div>
                                                    <div class="my-auto">
                                                        <h6>Nishu Garg</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>19-11-2024</td>
                                            <td>200 MB</td>
                                            <td class="tableactionnew">
                                                <div class="dropdown">
                                                    <button type="button"
                                                        class="btn btn-sm dropdown-toggle hide-arrow py-0"
                                                        data-bs-toggle="dropdown">
                                                        <i data-feather="more-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#shareuser"
                                                            data-bs-toggle="modal">
                                                            <i data-feather="user-plus" class="me-50"></i>
                                                            <span>Share</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#">
                                                            <i data-feather="download" class="me-50"></i>
                                                            <span>Download</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#filerename"
                                                            data-bs-toggle="modal">
                                                            <i data-feather="file-text" class="me-50"></i>
                                                            <span>Rename</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#">
                                                            <i data-feather="folder-minus" class="me-50"></i>
                                                            <span>Move to Folder</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#">
                                                            <i data-feather="trash-2" class="me-50"></i>
                                                            <span>Delete</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="cursor-pointer">
                                            <td class="pe-0">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="inlineCheckbox1">
                                                </div>
                                            </td>
                                            <td class="fw-bolder text-dark">
                                                <div class="d-flex align-items-center">
                                                    <i data-feather="image"></i>
                                                    <span class="ms-50">Carestylefile.png</span>
                                                </div>
                                            </td>
                                            <td>PNG</td>
                                            <td>
                                                <div class="d-flex flex-row doc-driownername">
                                                    <div class="avatar me-75">
                                                        <img src="../../../app-assets/images/portrait/small/avatar-s-8.jpg"
                                                            width="25" height="25" alt="Avatar">
                                                    </div>
                                                    <div class="my-auto">
                                                        <h6>Roshan Kumar</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>19-11-2024</td>
                                            <td>200 MB</td>
                                            <td class="tableactionnew">
                                                <div class="dropdown">
                                                    <button type="button"
                                                        class="btn btn-sm dropdown-toggle hide-arrow py-0"
                                                        data-bs-toggle="dropdown">
                                                        <i data-feather="more-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#shareuser"
                                                            data-bs-toggle="modal">
                                                            <i data-feather="user-plus" class="me-50"></i>
                                                            <span>Share</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#">
                                                            <i data-feather="download" class="me-50"></i>
                                                            <span>Download</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#filerename"
                                                            data-bs-toggle="modal">
                                                            <i data-feather="file-text" class="me-50"></i>
                                                            <span>Rename</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#">
                                                            <i data-feather="folder-minus" class="me-50"></i>
                                                            <span>Move to Folder</span>
                                                        </a>
                                                        <a class="dropdown-item" href="#">
                                                            <i data-feather="trash-2" class="me-50"></i>
                                                            <span>Delete</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>






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

    <div class="modal modal-sticky" id="fileuplostatyus">
        <div class="modal-dialog modal-dialog-scrollable modal-sm">
            <div class="modal-content p-0">
                <div class="modal-header">
                    <h5 class="modal-title">Uploading 1 Items</h5>
                    <div class="modal-actions">
                        <a class="text-body" href="#" data-bs-dismiss="modal" aria-label="Close"><i
                                data-feather="x"></i></a>
                    </div>
                </div>
                <div class="modal-body p-0">
                    <div class="file-uploadstatus ">
                        <div class="d-flex align-items-center fileuploadstat-pending">
                            <i data-feather="file-text"></i>
                            <span class="ms-50">structure.excel</span>
                        </div>
                        <div class=""><i data-feather="x-circle" class="upload-filebtnact text-dark"></i></div>
                    </div>

                    <div class="file-uploadstatus ">
                        <div class="d-flex align-items-center">
                            <i data-feather="image"></i>
                            <span class="ms-50">design_midc.jpg</span>
                        </div>
                        <div class=""><i data-feather="check-circle" class="upload-filebtnact text-success"></i>
                        </div>
                    </div>


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

                    <form action="{{ route('document-drive.folder.store', [$parent]) }}" method="POST">
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
                            <input type="text" class="form-control" placeholder="Enter Detail" />
                        </div>


                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="reset" class="btn btn-primary">Submit</button>
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
                <div class="modal-body px-sm-4 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Edit File/Folder Name</h1>
                    <p class="text-center">Enter the details below.</p>

                    <div class="row mt-2">

                        <div class="col-md-12 mb-1">
                            <label class="form-label">File/Folder Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" value="midc-stat.png"
                                placeholder="Enter File/Folder Name" />
                        </div>


                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="reset" class="btn btn-primary">Submit</button>
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
                    <p class="text-center">Enter the details below.</p>

                    <div class="row mt-2">

                        <div class="col-md-12 mb-1">
                            <label class="form-label">Search Folder <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter Folder Name" />
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
                                <tbody>
                                    <tr class="cursor-pointer">
                                        <td class="pe-0">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="selectfolder"
                                                    id="inlineCheckbox1">
                                            </div>
                                        </td>
                                        <td class="fw-bolder text-dark">
                                            UI/UX
                                        </td>
                                        <td>19-11-2024</td>
                                        <td>200 MB</td>
                                    </tr>
                                    <tr class="cursor-pointer">
                                        <td class="pe-0">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="selectfolder"
                                                    id="inlineCheckbox2">
                                            </div>
                                        </td>
                                        <td class="fw-bolder text-dark">
                                            MIDC
                                        </td>
                                        <td>19-11-2024</td>
                                        <td>200 MB</td>
                                    </tr>
                                    <tr class="cursor-pointer">
                                        <td class="pe-0">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="selectfolder"
                                                    id="inlineCheckbox3">
                                            </div>
                                        </td>
                                        <td class="fw-bolder text-dark">
                                            Carestyle
                                        </td>
                                        <td>19-11-2024</td>
                                        <td>200 MB</td>

                                    </tr>
                                </tbody>


                            </table>
                        </div>


                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="reset" class="btn btn-primary">Move</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="shareuser" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
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
                            <select class="form-select select2" multiple>
                                <option>Select</option>
                                <option>Deepak Kumar</option>
                                <option>Mahesh Kumar</option>
                                <option>Kundan Kumar</option>
                                <option>Aniket Singh</option>
                                <option>Abhishek</option>

                            </select>
                        </div>


                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="reset" class="btn btn-primary">Share</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
        <div class="modal-dialog sidebar-sm">
            <form class="add-new-record modal-content pt-0">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                        <label class="form-label" for="fp-range">Select Date</label>
                        <!--                        <input type="text" id="fp-default" class="form-control flatpickr-basic" placeholder="YYYY-MM-DD" />-->
                        <input type="text" id="fp-range" class="form-control flatpickr-range bg-white"
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                    </div>

                    <div class="mb-1">
                        <label class="form-label">File Name</label>
                        <input type="text" placeholder="Enter File Name" class="form-control" />
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Document Type</label>
                        <select class="form-select">
                            <option>Select</option>
                            <option>PNG</option>
                            <option>JPEG</option>
                            <option>GIF</option>
                            <option>MS Word</option>
                            <option>MS Excel</option>
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Owner</label>
                        <select class="form-select">
                            <option>Select</option>
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Folder</label>
                        <select class="form-select">
                            <option>Select</option>
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Tag/Description</label>
                        <input type="text" placeholder="Enter Tag/Description" class="form-control" />
                    </div>

                </div>
                <div class="modal-footer justify-content-start">
                    <button type="button" class="btn btn-primary data-submit mr-1">Apply</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection
