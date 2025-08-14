@extends('layouts.app')
@section('css')
<style type="text/css">
    /* Loader Container */


.loader {
    display: none; /* Hidden by default */
    position: absolute; /* Positioned relative to parent */
    top: 50%; /* Center the loader vertically */
    left: 50%; /* Center the loader horizontally */
    transform: translate(-50%, -50%); /* Center precisely */
    z-index: 9999; /* Ensure it's on top of other content */
}

/* Loader Animation */
.loader div {
    border: 4px solid rgba(0, 0, 0, 0.1); /* Light border */
    border-radius: 50%; /* Circular border */
    border-top: 4px solid #3498db; /* Color of the spinner */
    width: 40px; /* Size of the spinner */
    height: 40px; /* Size of the spinner */
    animation: spin 1s linear infinite; /* Rotation animation */
}

/* Spinner Rotation Animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 0.875em;
}


</style>
@endsection
@section('content')

        <!-- BEGIN: Content-->
     <div class="app-content content email-application">
        @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-header pocreate-sticky">
            <div class="row">
                <div class="content-header-left col-md-6 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Reply & View</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{route('legal')}}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">View</li>


                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-6 mb-2 mb-sm-0">
                    <div style="text-transform: uppercase;" class="form-group breadcrumb-right">

                                                        @if($data->status == 'Close')
                                                            <span class="badge rounded-pill badge-light-secondary forminnerstatus mb-50 mb-sm-0">Status : <span class="text-success">{{$data->status}}</span></span>
                                                        @elseif (!empty($data->emails) && count($data->emails) > 0)
                                                            <span class="badge rounded-pill badge-light-secondary forminnerstatus mb-50 mb-sm-0">Status : <span class="text-info">Waiting</span></span>
                                                        @elseif(!empty($data->teams) && count($data->teams) > 0)
                                                            <span class="badge rounded-pill badge-light-secondary forminnerstatus mb-50 mb-sm-0">Status : <span class="text-warning">Assigned</span></span>
                                                        @elseif($data->status == 'draft')
                                                            <span class="badge rounded-pill badge-light-secondary forminnerstatus mb-50 mb-sm-0">Status : <span class="text-info">{{$data->status}}</span></span>
                                                        @elseif($data->status == 'approved')
                                                            <span class="badge rounded-pill badge-light-secondary forminnerstatus mb-50 mb-sm-0">Status : <span class="text-success">{{$data->status}}</span>
                                                            </span>

                                                        @elseif($data->status == 'rejected')
                                                            <span class="badge rounded-pill badge-light-secondary forminnerstatus mb-50 mb-sm-0">Status : <span class="text-danger">{{$data->status}}</span>
                                                            </span>
                                                        @elseif($data->status == 'submitted')
                                                            <span class="badge rounded-pill badge-light-secondary forminnerstatus mb-50 mb-sm-0">Status : <span class="text-success">{{$data->status}}</span>
                                                            </span>
                                                        @else
                                                            <span class="badge rounded-pill badge-light-secondary forminnerstatus mb-50 mb-sm-0">Status : <span class="text-success">{{$data->status}}</span></span>
                                                        @endif


                        <a href="{{route('legal')}}"><button class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</button></a>

                    @if ($buttons['reject'])
                        <a class="btn btn-danger btn-sm" data-bs-target="#reject" data-bs-toggle="modal"><i data-feather="x-circle"></i> Reject</a>
                    @endif
                    @if($buttons['approve'])
                    <a data-bs-toggle="modal" data-bs-target="#approved" class="btn btn-success btn-sm"><i data-feather="check-circle"></i> Approve</a>
                    @endif

                    @if($buttons['close'])
                    <a data-bs-toggle="modal" data-bs-target="#Closed" class="btn btn-danger btn-sm"><i data-feather="check-circle"></i> Close </a>
                    @endif

                   
                    </>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-7">
                <div class="content-area-wrapper container-xxl p-0">
                        <div class="content-right w-100">
                            <div class="content-wrapper container-xxl p-0">
                                <div class="content-body">
                                    <div class="email-app-list">
                                        @if(!empty($message) && count($message) > 0)
                                        <div class="app-fixed-search d-flex align-items-center">
                                            <div class="d-flex align-content-center justify-content-between w-100">
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i data-feather="search" class="text-muted"></i></span>
                                                     <input type="text" class="form-control mail-search" id="mail-search" placeholder="Search Chat" aria-label="Search..." aria-describedby="mail-search" />
                                                </div>
                                            </div>
                                        </div>
                                        @endif


                                        <div class="email-user-list">

                                            <ul class="email-media-list" id="email-user-list">
                                                @foreach($message as $msg)
                                                <li class="d-flex align-items-center user-mail mail-read p-1"  onclick='showData({{ json_encode($msg) }}, {{ json_encode($msg->replies) }}, "{{ $msg->id }}")'>
                                                    <div class="mail-left pe-50">
                                                        <div class="avatar mb-0">
                                                            <img src="{{ url('img/user.png') }}" alt="avatar img holder" />
                                                        </div>
                                                    </div>
                                                    <div class="mail-body">
                                                        <div class="mail-details mb-0">
                                                            <div class="mail-items">
                                                                <h5 class="mb-0">{{ $msg->body }}</h5>
                                                                <p class="mt-25 mb-0">By: @if(($msg->user_type == 'user') &&!empty($msg->user)) {{ $msg->user->name }} @elseif(($msg->user_type == 'employee') &&!empty($msg->employee)) {{ $msg->employee->name }} @endif @if(count($msg->attachments) > 0) | View Replies <i data-feather="paperclip"></i>@endif</p>
                                                            </div>
                                                            <div class="mail-meta-item">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                                <span class="mail-date"  data-utc="{{ \Carbon\Carbon::parse($msg->created_at)->toIso8601String() }}">{{ \Carbon\Carbon::parse($msg->created_at)->format('Y-m-d H:i:s') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>

                                                @endforeach
                                            </ul>

                                            <div  @if(!empty($message) && count($message) > 0) class="no-results" @else class="no-results show" @endif>
                                                <h5 class="show">No Items Found</h5>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="email-app-details" id="email-app-detailshow">

                                    </div>

                                </div>
                            </div>
                        </div>
                </div>

            </div>

            <div class="col-md-5">
                <div class="card h-100 mb-0">
                     <div class="card-body customernewsection-form">


                                <div class="border-bottom mb-2 pb-25">
                                         <div class="row">
                                            <div class="col-6">
                                                <div class="newheader ">
                                                    <h4 class="card-title text-theme">Basic Information</h4>
                                                    <p class="card-text">View the details</p>
                                                </div>
                                            </div>
                                            <div class="col-6 text-end">
                                                @if($buttons['email'])
                                                <button type="button" class="compose-email btn btn-primary btn-sm modalcompose">
                                                    Send Message
                                                </button>
                                                @endif
                                            </div>

                                        </div>

                                 </div>


                                <div class="row">
                                     <div class="col-md-12">

                                         <div class="row loandetailview">
                                           <div class="col-md-4 mb-1">
                                                <label class="form-label">Date</label>
                                                <h6 class="fw-bolder text-dark">{{date('d-m-Y',strtotime($data->created_at))}}</h6>
                                            </div>
                                            <div class="col-md-4 mb-1">
                                                <label class="form-label">Issue Type</label>
                                                <h6 class="fw-bolder text-dark">{{$data->issues_detail ? $data->issues_detail->name : '-'}}</h6>
                                            </div>
                                             <div class="col-md-4 mb-1">
                                                <label class="form-label">Request No.</label>
                                                <h6 class="fw-bolder text-dark">{{$data->requestno}}</h6>
                                            </div>
                                             <div class="col-md-4 mb-1">
                                                <label class="form-label">Subject</label>
                                                <h6 class="fw-bolder text-dark">{{$data->subject}}</h6>
                                            </div>
                                            <div class="col-md-8 mb-1">
                                                <label class="form-label">Assigned Team</label>
                                                <h6 class="fw-bolder text-dark">
                                                    <div class="avatar-group">
                                                        @foreach($data->teams as $team)
                                                        @if(!empty($team->user))
                                                          <div data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                                                     @if (!empty($team->user)) title="{{ $team->user->name ?? $team->name ?? "N/A" }}" data-bs-original-title="{{ $team->user->name ?? $team->name ?? "N/A" }}" @endif
                                                                     class="avatar pull-up">

                                                                    @if (!empty($team->user->imagebase64code))
                                                                        <img src="data:image/png;base64,{{ $team->user->imagebase64code }}" alt="Avatar" height="32" width="32">
                                                                   @else
                                                                    @php
                                                                        echo \App\Helpers\Helper::getInitials($team->user->name ?? $team->name ?? "N/A");
                                                                    @endphp
                                                                @endif

                                                            </div>
                                                        @endif
                                                        @endforeach

                                                    </div>
                                                </h6>
                                            </div>
                                             <div class="col-md-12 mb-1">
                                                <label class="form-label">Remarks </label>
                                                <h6 class="fw-bolder text-dark">{{$data->remark}}</h6>
                                            </div>
                                            @if($data->status == 'Close' && !empty($data->close_remark))
                                            <div class="col-md-12 mb-1">
                                                <label class="form-label">Close Remarks </label>
                                                <h6 class="fw-bolder text-dark">{{$data->close_remark}}</h6>
                                            </div>
                                            @endif
                                            <div class="col-md-12">
                                                <div class="input-group input-group-merge docreplchatsearch border-bottom mb-25">
                                                    <span class="input-group-text border-0 ps-0">
                                                        <i data-feather="search"></i>
                                                    </span>
                                                    <input type="text" class="form-control border-0" id="email-search" placeholder="Search Doc" aria-label="Search..." aria-describedby="email-search">
                                                </div>
                                                <div class="table-responsive"  style="max-height: 150px;">
                                                    <table class="m-0 table myrequesttablecbox table-striped po-order-detail loanapplicationlist">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Date</th>
                                                                    <th>Doc Name</th>
                                                                    <th>Download</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody id="legal-docs-tbody">
                                                                    @php
                                                                    $i = 1;
                                                                    if(!empty($data->file_path))
                                                                    {
                                                                        $files = explode(',',$data->file_path);
                                                                    }
                                                                    else
                                                                    {
                                                                        $files =[];
                                                                    }

                                                                    @endphp

                                                                    @foreach($files as $key => $file)
                                                                    <tr>
                                                                        <td>{{$i++}}</td>
                                                                        <td>{{date('d-m-Y',strtotime($data->created_at))}}</td>
                                                                        <td>{{$file}}</td>
                                                                        <td><a href="{{url('uploads/legal/'.$file)}}" target="_blank"><i data-feather='download'></i></a></td>
                                                                    </tr>
                                                                    @endforeach
                                                                    @foreach($data->emails as $email)
                                                                    @foreach($email->attachments as $attach)
                                                                    <tr>
                                                                        <td>{{$i++}}</td>
                                                                        <td>{{date('d-m-Y',strtotime($attach->created_at))}}</td>
                                                                        <td>{{$attach->file_name}}</td>
                                                                        <td><a href="{{url('attachments/'.$attach->file_path)}}" target="_blank"><i data-feather='download'></i></a></td>
                                                                    </tr>
                                                                    @endforeach
                                                                    @endforeach

                                                                    <!-- <tr>
                                                                        <td>2</td>
                                                                        <td>04-05-2024</td>
                                                                        <td>PAN/GIR No.</td>
                                                                        <td><a href="#"><i data-feather='download'></i></a></td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td>3</td>
                                                                        <>04-05-2024</<td>
                                                                        <td>Plot Document</td>
                                                                        <td><a href="#"><i data-feather='download'></i></a> <a href="#"><i data-feather='download'></i></a></td>
                                                                    </tr> -->

                                                            </tbody>


                                                        </table>
                                                    </div>

                                            </div>
                                        </div>
                                    </div>
                                 </div>


                    </div>
                </div>
            </div>


        </div>
    </div>


@endsection

<div class="modal fade" id="Closed" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{route('legal.close')}}">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Close Application</h4>
                    <!-- <p class="mb-0 fw-bold voucehrinvocetxt mt-0">Nishu Garg | 20 Lkh | 29-07-2024</p> -->
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pb-2">
                <input type="hidden" id="selected_ids" name="id" value="{{$data->id}}">
                <div class="row mt-1">
                    <div class="col-md-12">
                        <div class="mb-1">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remark"></textarea>
                        </div>
                    </div>
                </div>
            </div>


            <div class="modal-footer justify-content-center">
                <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                <button type="submit" id="submit-approval" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </form>
    </div>
</div>
<div class="modal fade" id="approved" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">

                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Approve Legal Application</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('legal.appr_rej') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body pb-2">
                    <div class="row mt-1">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        @if (isset($data->id))
                                            <input type="hidden" name="appr_rej_status" value="approve">
                                            <input type="hidden" name="appr_rej_land_id" value="{{ $data->id }}">
                                        @endif
                                    </div>
                                </div>


                            </div>

                            <div class="mb-1">
                                <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                @if (isset($data) && $data->status == 2)
                                    <textarea class="form-control" name="appr_rej_remarks">{{ $data->appr_rej_recom_remark ?? '' }}</textarea>
                                @else
                                    <textarea class="form-control" name="appr_rej_remarks"></textarea>
                                @endif
                            </div>

                            <div class="mb-1">
                                @if (isset($data) && $data->status == 2)
                                    @if (isset($data->id))
                                        <input type="hidden" name="stored_appr_rej_doc" value="{{ $data->appr_rej_doc ?? '' }}">
                                    @endif
                                    <label class="form-label">Upload Document</label>
                                    <input type="file" name="appr_rej_doc" class="form-control" />
                                    @if (isset($data) && !empty($data->appr_rej_doc))
                                        <div class="col-md-3 mt-1">
                                            <p><i data-feather='folder' class="me-50"></i><a href="{{ asset('storage/' . $data->appr_rej_doc) }}" style="color:green; font-size:12px;" target="_blank" download>Approved Doc</a></p>
                                        </div>
                                    @endif
                                @else
                                    <label class="form-label">Upload Document</label>
                                    <input type="file" name="appr_rej_doc" class="form-control" />
                                @endif
                            </div>

                            @php
                                $selectedValues = isset($data) && $data->appr_rej_behalf_of ? json_decode($data->appr_rej_behalf_of, true) : [];
                            @endphp
                            <div class="mb-1">
                                <label class="form-label">On Behalf of <span class="text-danger">*</span></label>
                                @if (isset($data) && $data->status == 2)
                                    <select class="form-select select2" multiple name="appr_rej_behalf_of[]">
                                        <option value="">Select</option>
                                        @foreach($users as $user)
                                        <option value="{{$user->id}}">{{$user->name}}</option>
                                   @endforeach
                                    </select>
                                @else
                                    <select class="form-select select2" multiple name="appr_rej_behalf_of[]">
                                        <option value="">Select</option>
                                        @foreach($users as $user)
                                        <option value="{{$user->id}}">{{$user->name}}</option>
                                   @endforeach
                                    </select>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary me-1 cancelButton" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- END: Content-->
<div class="modal fade" id="reject" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">
                        Reject Legal Application
                    </h4>
                    <p class="mb-0 fw-bold voucehrinvocetxt mt-0">{{ $data->name ?? '' }} | {{ $data->plot_area ?? '' }} | {{ $data->handoverdate ?? '' }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('legal.appr_rej') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body pb-2">
                    <div class="row mt-1">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        @if (isset($data->id))
                                            <input type="hidden" name="appr_rej_status" value="reject">
                                            <input type="hidden" name="appr_rej_land_id" value="{{ $data->id }}">
                                        @endif
                                    </div>
                                </div>

                                </div>

                            <div class="mb-1">
                                <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                @if (isset($data) && $data->status == 3)
                                    <textarea class="form-control" name="appr_rej_remarks">{{ $data->appr_rej_recom_remark ?? '' }}</textarea>
                                @else
                                    <textarea class="form-control" name="appr_rej_remarks"></textarea>
                                @endif
                            </div>

                            <div class="mb-1">
                                @if (isset($data) && $data->status == 3)
                                    @if (isset($data->id))
                                        <input type="hidden" name="stored_appr_rej_doc" value="{{ $data->appr_rej_doc ?? '' }}">
                                    @endif
                                    <label class="form-label">Upload Document</label>
                                    <input type="file" name="appr_rej_doc" class="form-control" />
                                    @if (isset($data) && !empty($data->appr_rej_doc))
                                        <div class="col-md-3 mt-1">
                                            <p><i data-feather='folder' class="me-50"></i><a href="{{ asset('storage/' . $data->appr_rej_doc) }}" style="color:green; font-size:12px;" target="_blank" download>Approved Doc</a></p>
                                        </div>
                                    @endif
                                @else
                                    <label class="form-label">Upload Document</label>
                                    <input type="file" name="appr_rej_doc" class="form-control" />
                                @endif
                            </div>

                            @php
                                $selectedValues = isset($data) && $data->appr_rej_behalf_of ? json_decode($data->appr_rej_behalf_of, true) : [];
                            @endphp
                            <div class="mb-1">
                                <label class="form-label">On Behalf of <span class="text-danger">*</span></label>
                                @if (isset($data) && $data->status == 3)
                                    <select class="form-select select2" multiple name="appr_rej_behalf_of[]">
                                        <option value="">Select</option>
                                        <option value="nishu" {{ in_array('nishu', $selectedValues) ? 'selected' : '' }}>Nishu Garg</option>
                                        <option value="mahesh" {{ in_array('mahesh', $selectedValues) ? 'selected' : '' }}>Mahesh Bhatt</option>
                                        <option value="inder" {{ in_array('inder', $selectedValues) ? 'selected' : '' }}>Inder Singh</option>
                                        <option value="shivangi" {{ in_array('shivangi', $selectedValues) ? 'selected' : '' }}>Shivangi</option>
                                    </select>
                                @else
                                    <select class="form-select select2" multiple name="appr_rej_behalf_of[]">
                                        <option value="">Select</option>
                                        <option value="nishu">Nishu Garg</option>
                                        <option value="mahesh">Mahesh Bhatt</option>
                                        <option value="inder">Inder Singh</option>
                                        <option value="shivangi">Shivangi</option>
                                    </select>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary me-1 cancelButton">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal modal-sticky" id="compose-mail">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content p-0">
            <div class="modal-header">
                <h5 class="modal-title">Send Message or Reply</h5>
                <div class="modal-actions">
                    <a class="text-body" href="#" data-bs-dismiss="modal" aria-label="Close"><i data-feather="x"></i></a>
                </div>
            </div>

            <div class="modal-body flex-grow-1 p-0">
            <form id="legal-message-form" class="compose-form" method="POST" action="{{ route('legal.send_message') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="legal_id" id="legalid">
            <input type="hidden" name="parent_id" id="parent_id">
            <div id="message-editor">
                <div class="px-1">
                    <div class="row align-items-center border-bottom sendmessagepopuplegal">
                        <div class="col-1 pe-0" id="totag"><label class="form-label ms-1">To<span class="text-danger">*</span></label></div>
                        <div class="col-11" id="toselect">
                            <select class="form-select select2 border-1" name="to[]" multiple required>
                                <option disabled>Select</option>
                                @foreach($data->teams as $team)
                                @isset($team->user)
                                @if($type == 'user')
                                <option value="{{$team->user->id}}">{{ $team->user->name ?? $team->name ?? "N/A" }}</option>
                                @elseif($team->user->id != $user_id)
                                <option value="{{$team->user->id}}">{{ $team->user->name ?? $team->name ?? "N/A" }}</option>
                                @endif
                                @endisset
                                @endforeach
                            </select>
                        </div>
                         <div class="col-12" id="toSubject">
                            <input class="form-control" placeholder="Subject*" name="subject" id="toSubjectinput" required maxlength="100" />
                        </div>
                        <div class="col-12">
                            <textarea class="form-control border-0 ql-editor" placeholder="Enter Message*" required rows="5" name="body"></textarea>
                        </div>
                        <div class="col-md-12">
                            <table class="table mb-1 myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Document Name</th>
                                        <th>Upload</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    <tr>
                                        <td>1</td>
                                        <td><input type="text" class="form-control mw-100" placeholder="Document Name" name="names[]"></td>
                                        <td>
                                            <input type="file" class="form-control mw-100 attachment-file" name="attachments[]" id="attachment-file">
                                        </td>

                                        <td><a href="#" id="addRow" class="text-primary">
                                            <i data-feather="plus-square"></i>
                                        </a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
           <div class="compose-footer-wrapper">
                <div class="btn-wrapper d-flex align-items-center">
                    <div class="btn-group dropup me-1">
                        <button type="submit" class="btn btn-primary" id="send-button">Send</button>
                    </div>
                </div>
            </div>

            <!-- Loader HTML -->
            <div class="loader" id="email-loader">
                <div></div>
            </div>


        </form>
            </div>

        </div>
    </div>
</div>
    <!-- END: Content-->
@section('scripts')

    <script>
       document.addEventListener('DOMContentLoaded', function () {
    showloctime();
});

    function showloctime() {
    document.querySelectorAll('.mail-date').forEach(function (element) {
        var utcDate = element.getAttribute('data-utc');

        // Log the raw UTC date value

        // Create a new Date object and check if it's valid
        var dateObj = new Date(utcDate);

        // Check if the date is valid
        if (isNaN(dateObj.getTime())) {
            console.error('Invalid Date:', utcDate);
            return;
        }

        // Get today's and yesterday's dates
        var today = new Date();
        var yesterday = new Date();
        yesterday.setDate(today.getDate() - 1);

        var localDate;

        // Check if the date is today
        if (dateObj.toDateString() === today.toDateString()) {
            localDate = dateObj.toLocaleString('en-IN', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
        }
        // Check if the date is yesterday
        else if (dateObj.toDateString() === yesterday.toDateString()) {
            localDate = 'Yesterday';
        }
        // For other dates
        else {
            localDate = dateObj.toLocaleString('en-IN', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
        }


        // Update the text content
        element.textContent = localDate;
    });
}





        $(function() {
           $("input[name='loanassesment']").click(function() {
             if ($("#Disbursement1").is(":checked")) {
               $(".selectdisbusement").show();
               $(".cibil-score").hide();
             } else {
               $(".selectdisbusement").hide();
               $(".cibil-score").show();
             }
           });
         });

        $(document).ready(function()
        {
          $("#viewrepliescharhist").click(function(){
           $(".replies-sections").slideToggle("slow");
          });
        });



        $("#addRow").click(function(){
                var rowCount = $("#tableBody").find('tr').length + 1; // Counter for row numbering, starting at 1

                var newRow = `
                <tr>
                    <td>${rowCount}</td>
                    <td><input type="text" name=names[] class="form-control mw-100"></td>
                    <td><input type="file" name="attachments[]" class="form-control mw-100 attachment-file"></td>
                    <td><a href="#" class="trash text-danger">
                        <i data-feather="trash-2"></i>
                    </a></td>
                </tr>`;

                $("#tableBody").append(newRow);
                feather.replace();

            });

            $("#tableBody").on("click", ".trash", function(event) {
                event.preventDefault(); // Prevent default action for <a> tag
                $(this).closest('tr').remove(); // Remove the closest <tr> element
            });

            function showData(msg, replies, parent_id) {
    // Clear existing content
    const detailsDiv = document.getElementById('email-app-detailshow');
    if (detailsDiv) {
        detailsDiv.innerHTML = '';

        let status = '{{ $data->status }}';

        // Define your HTML structure for the email details
        let emailDetailsHTML = `
            <div class="email-detail-header">
                <div class="email-header-left d-flex align-items-center">
                    <span class="go-back me-1"><i data-feather="chevron-left" class="font-medium-4"></i></span>
                    <h6 class="email-subject mb-0">${msg.subject}</h6>
                </div>
            </div>
            <div class="email-scroll-area mt-1">
                <div class="text-sm-end">
                ${status !== 'Close' ?
                    `<h5 class="mb-1">
                        Click here to <a href="javascript:void(0)" onclick='showModal(${parent_id})'><i data-feather="corner-up-right"></i>Reply</a>
                    </h5>`
                : ''
                }
                </div>
                <span id="parentdata"></span>
        `;

        // Loop through replies and create the HTML structure
        replies.forEach(reply => {


             const utcDate1 = reply.created_at; // Get the UTC date string
             const dateObj1 = new Date(utcDate1);

                if (isNaN(dateObj1.getTime())) {
                    console.error('Invalid Date:', utcDate1);
                    // Handle invalid date if necessary
                    return;
                }

                // Get today's and yesterday's dates
                const today = new Date();
                const yesterday = new Date();
                yesterday.setDate(today.getDate() - 1);

                let localDate1;

                // Check if the date is today
                if (dateObj1.toDateString() === today.toDateString()) {
                    localDate1 = dateObj1.toLocaleString('en-IN', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: true
                    });
                }
                // Check if the date is yesterday
                else if (dateObj1.toDateString() === yesterday.toDateString()) {
                    localDate1 = 'Yesterday';
                }
                // For other dates
                else {
                    localDate1 = dateObj1.toLocaleString('en-IN', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit'
                    });
                }

                let userName = '';
                let useremail = '';
                if (reply.user_type == 'user' && reply.user) {
                    userName = reply.user.name;
                     useremail = reply.user.email;
                } else if (reply.user_type == 'employee' && reply.employee) {
                    userName = reply.employee.name;
                    useremail = reply.employee.email;
                }


            emailDetailsHTML += `
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header email-detail-head p-1">
                                <div class="user-details d-flex justify-content-between align-items-center flex-wrap">
                                    <div class="avatar me-75">
                                        <img src="{{url('/app-assets/images/portrait/small/avatar-s-5.jpg')}}" alt="avatar img holder" width="38" height="38" />
                                    </div>
                                    <div class="mail-items">
                                        <h5 class="mb-0 mt-50">${userName}</h5>
                                        <p class="mb-0">${useremail}</p>
                                    </div>
                                </div>
                                <div class="mail-meta-item d-flex align-items-center">
                                    <small class="mail-date-time text-muted">${localDate1}</small>
                                </div>
                            </div>
                            <div class="card-body mail-message-wrapper p-1">
                                <div class="mail-message">
                                    <p class="card-text">${reply.body}</p>
                                    ${reply.attachments.map(att => `
                                        <a href="{{ url('attachments/${att.file_path}') }}" target="_blank">
                                            <img src="{{url('/app-assets/images/icons/jpg.png')}}" class="me-25" alt="attachment" height="18" />
                                            <small class="text-muted fw-bolder">${att.file_name}</small>
                                        </a>
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

         const utcDate1 = msg.created_at; // Get the UTC date string
                const dateObj1 = new Date(utcDate1);

                if (isNaN(dateObj1.getTime())) {
                    console.error('Invalid Date:', utcDate1);
                    // Handle invalid date if necessary
                    return;
                }

                // Get today's and yesterday's dates
                const today = new Date();
                const yesterday = new Date();
                yesterday.setDate(today.getDate() - 1);

                let localDate1;

                // Check if the date is today
                if (dateObj1.toDateString() === today.toDateString()) {
                    localDate1 = dateObj1.toLocaleString('en-IN', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: true
                    });
                }
                // Check if the date is yesterday
                else if (dateObj1.toDateString() === yesterday.toDateString()) {
                    localDate1 = 'Yesterday';
                }
                // For other dates
                else {
                    localDate1 = dateObj1.toLocaleString('en-IN', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit'
                    });
                }

                let userName = '';
                let useremail = '';
                if (msg.user_type == 'user' && msg.user) {
                    userName = msg.user.name;
                     useremail = msg.user.email;
                } else if (msg.user_type == 'employee' && msg.employee) {
                    userName = msg.employee.name;
                    useremail = msg.employee.email;
                }

        emailDetailsHTML += `
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header email-detail-head p-1">
                                <div class="user-details d-flex justify-content-between align-items-center flex-wrap">
                                    <div class="avatar me-75">
                                        <img src="{{url('/app-assets/images/portrait/small/avatar-s-5.jpg')}}" alt="avatar img holder" width="38" height="38" />
                                    </div>
                                    <div class="mail-items">
                                        <h5 class="mb-0 mt-50">${userName}</h5>
                                        <p class="mb-0">${useremail}</p>
                                    </div>
                                </div>
                                <div class="mail-meta-item d-flex align-items-center">
                                    <small class="mail-date-time text-muted">${localDate1}</small>
                                </div>
                            </div>
                            <div class="card-body mail-message-wrapper p-1">
                                <div class="mail-message">
                                    <p class="card-text">${msg.body}</p>
                                    ${msg.attachments.map(att => `
                                        <a href="{{ url('attachments/${att.file_path}') }}" target="_blank">
                                            <img src="{{url('/app-assets/images/icons/jpg.png')}}" class="me-25" alt="attachment" height="18" />
                                            <small class="text-muted fw-bolder">${att.file_name}</small>
                                        </a>
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Set the innerHTML of the email details div
        detailsDiv.innerHTML = emailDetailsHTML;

        // Ensure 'show' class is added to make the section visible
        detailsDiv.classList.add('show');
        $("#email-app-detailshow").addClass('show');
        $(".email-app-details").addClass('show');

         var emailScrollArea = $('.email-scroll-area');
         if ($(emailScrollArea).length > 0) {
              var users_list = new PerfectScrollbar(emailScrollArea[0]);
            }

        // Replace feather icons if necessary
        feather.replace();
    }
}




        $(document).on('click', '.modalcompose', function()
{
    var token = "{{ csrf_token() }}";
    $("#token").val(token);
    $("#legalid").val("{{ $data->id }}");
    $('#legal-message-form')[0].reset();
    $('.select2').val(null).trigger('change');
    $('.select2').val([]).trigger('change');
    $("#parent_id").val("");
    $("#totag").css('display','block');
    $("#toselect").css('display','block');
    $("#toSubject").css('display','block');
    $('.form-select.select2').attr('required', 'required');
    $('#toSubjectinput').attr('required', 'required');

    // Show the modal
    $("#compose-mail").modal('show');
});

    $(document).on('click', '.go-back', function() {
      $("#email-app-detailshow").removeClass('show');;
    });

        function showModal(parent_id)
        {
            var token = "{{ csrf_token() }}";
            $("#token").val(token);
            $("#legalid").val("{{ $data->id }}");
            $("#parent_id").val(parent_id);

            // Show the modal
            $('#legal-message-form')[0].reset();
            $('.select2').val(null).trigger('change');
            $('.select2').val([]).trigger('change');
            $("#totag").css('display','none');
            $("#toselect").css('display','none');
            $("#toSubject").css('display','none');
            $('.form-select.select2').removeAttr('required');
            $('#toSubjectinput').removeAttr('required');
            $("#compose-mail").modal('show');
        }


    $(document).on('input', '.mail-search', function() {
    var query = $(this).val();

    $.ajax({
        url: '{{ route('search.messages') }}',
        method: 'GET',
        data: { query: query,id: "{{$data->id}}"},
        success: function(response) {
            var html = '';
            if (response.length > 0) {
                response.forEach(function(msg)
                {

                const utcDate1 = msg.created_at; // Get the UTC date string
                const dateObj1 = new Date(utcDate1);

                if (isNaN(dateObj1.getTime())) {
                    console.error('Invalid Date:', utcDate1);
                    // Handle invalid date if necessary
                    return;
                }

                // Get today's and yesterday's dates
                const today = new Date();
                const yesterday = new Date();
                yesterday.setDate(today.getDate() - 1);

                let localDate1;

                // Check if the date is today
                if (dateObj1.toDateString() === today.toDateString()) {
                    localDate1 = dateObj1.toLocaleString('en-IN', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: true
                    });
                }
                // Check if the date is yesterday
                else if (dateObj1.toDateString() === yesterday.toDateString()) {
                    localDate1 = 'Yesterday';
                }
                // For other dates
                else {
                    localDate1 = dateObj1.toLocaleString('en-IN', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit'
                    });
                }

                    html += `
                        <li class="d-flex align-items-center user-mail mail-read p-1" onclick='showData(${JSON.stringify(msg)},${JSON.stringify(msg.replies)}, ${msg.id})'>
                            <div class="mail-left pe-50">
                                <div class="avatar mb-0">
                                    <img src="{{ url('img/user.png') }}" alt="avatar img holder" />
                                </div>
                            </div>
                            <div class="mail-body">
                                <div class="mail-details mb-0">
                                    <div class="mail-items">
                                        <h5 class="mb-0">${msg.body}</h5>
                                        <p class="mt-25 mb-0">By: ${msg.user.name} ${msg.attachments.length > 0 ? '| View Replies <i data-feather="paperclip"></i>' : ''}</p>
                                    </div>
                                    <div class="mail-meta-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        <span class="mail-date">${localDate1}</span>
                                    </div>
                                </div>
                            </div>
                        </li>
                    `;
                });
            } else {
                html = '<div class="no-results show"><h5 class="show">No Items Found</h5></div>';
            }

            $("#email-user-list").html('');
            $('#email-user-list').html(html);
            feather.replace(); // Re-initialize Feather Icons
        }
    });
});

            $('#legal-message-form').on('submit', function (e) {
    e.preventDefault(); // Prevent the default form submission

    // Clear previous error states
    $('.form-control').removeClass('is-invalid');

    // Get form inputs
    var recipients = $('select[name="to[]"]').val();
    var messageBody = $('textarea[name="body"]').val().trim();
    var attachments = $('input[name="attachments[]"]')[0].files;

    // Perform client-side validation
    var isValid = true;

    if ($("#parent_id").val() == '' && (!recipients || recipients.length === 0)) {
        alert('Please specify at least one recipient.');
        $('select[name="to[]"]').addClass('is-invalid');
        isValid = false;
    }

    if (!messageBody) {
        alert('Please enter the message.');
        $('textarea[name="body"]').addClass('is-invalid');
        isValid = false;
    }

    // Check if any file is uploaded and validate document names if so
    var filesUploaded = false;
    $('#tableBody input[name="attachments[]"]').each(function() {
        if (this.files.length > 0) {
            filesUploaded = true;
        }
    });

    if (filesUploaded) {
        $('#tableBody input[name="names[]"]').each(function() {
            if (!$(this).val().trim()) {
                alert('Please enter the document name for each uploaded file.');
                $(this).addClass('is-invalid');
                isValid = false;
            }
        });
    }

    // Check file size and extension
    var maxSize = 5 * 1024 * 1024; // 5 MB
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.pdf)$/i; // Allowed extensions
    $.each(attachments, function (index, file) {
        if (file.size > maxSize) {
            alert('File ' + file.name + ' is too large. Maximum size is 5 MB.');
            $('input[name="attachments[]"]').addClass('is-invalid');
            isValid = false;
        }

        if (!allowedExtensions.exec(file.name)) {
            alert('Invalid file type for ' + file.name + '. Only jpg, jpeg, png, and pdf files are allowed.');
            $('input[name="attachments[]"]').addClass('is-invalid');
            isValid = false;
        }
    });

    if (!isValid) {
        return; // Stop the form from being submitted if validation fails
    }

    // Show the loader and disable the Send button
    $('#email-loader').show();
    $('#send-button').prop('disabled', true);

    var formData = new FormData(this); // Create a FormData object for the form
    formData.append('_token', '{{ csrf_token() }}'); // Add the CSRF token to the FormData

    $.ajax({
        url: $(this).attr('action'), // Use the form's action attribute for the URL
        type: $(this).attr('method'), // Use the form's method attribute (POST)
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            // Existing success handling code
            // ...

            feather.replace();
            // Hide the loader and enable the Send button
            $('#email-loader').hide();
            $('#send-button').prop('disabled', false);

            $("#compose-mail").modal('hide');

            // Optionally, you can reset the form here:
            $('#legal-message-form')[0].reset();
            $('.select2').val(null).trigger('change');
            alert('Email message sent successfully.');
            window.location.reload();
           
        },
        error: function (xhr, status, error) {
            // Handle error (e.g., show an error message)
            alert('An error occurred: ' + xhr.responseText);
            // Hide the loader and enable the Send button
            $('#email-loader').hide();
            $('#send-button').prop('disabled', false);
        }
    });
});






$(document).ready(function(){
    $('#email-search').on('keyup', function() {
        let query = $(this).val();
        $.ajax({
            url: "{{ route('legal.search.docs') }}",
            type: "GET",
            data: { query: query,id: "{{$data->id}}" },
            success: function(data) {
                $('#legal-docs-tbody').html(data);
                feather.replace(); // Re-initialize Feather icons after AJAX load
            }
        });
    });
});

   document.querySelector('.attachment-file').addEventListener('change', function() {
    var file = this.files[0]; // Get the selected file
    var maxSize = 5 * 1024 * 1024; // 5 MB
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.pdf)$/i; // Allowed extensions

    if (file) {
        // Check file size
        if (file.size > maxSize) {
            alert('File is too large. Maximum size is 5 MB.');
            this.value = ''; // Clear the file input
            return; // Exit function
        }

        // Check file extension
        if (!allowedExtensions.exec(file.name)) {
            alert('Invalid file type. Only jpg, jpeg, png, and pdf files are allowed.');
            this.value = ''; // Clear the file input
            return; // Exit function
        }
    }
});




    </script>


@endsection
