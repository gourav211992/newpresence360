@extends('layouts.app')

@section('content')

    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Settlement</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">View Details</li>


                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        if ($data->homeLoan->type == 1) {
                            $view_route = 'loan.view_all_detail';
                            $edit_route = 'loan.home-loan-edit';
                            $delete_route = 'loan.home-loan-delete';
                        } elseif ($data->homeLoan->type == 2) {
                            $view_route = 'loan.view_vehicle_detail';
                            $edit_route = 'loan.edit_vehicle_detail';
                            $delete_route = 'loan.delete_vehicle_detail';
                        } else {
                            $view_route = 'loan.view_term_detail';
                            $edit_route = 'loan.term-loan-edit';
                            $delete_route = 'loan.term-loan-delete';
                        }
                    @endphp
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="arrow-left-circle"></i> Back</button>

                                    @if ($buttons['reject'])
                                    <button class="btn btn-danger btn-sm mb-50 mb-sm-0" data-bs-target="#reject"
                                    data-bs-toggle="modal"><i data-feather="x-circle"></i> Reject</button>
                                    @endif
                                    @if ($buttons['approve'])
                                    <button class="btn btn-success btn-sm mb-50 mb-sm-0" data-bs-target="#approved"
                                    data-bs-toggle="modal"><i data-feather="check-circle"></i> Approve</button>
                                    @endif
                                    @if($buttons['voucher'])
                                    <button type="button" onclick="onPostVoucherOpen('posted');" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg> Voucher</button>
                                @endif
                                    @if ($buttons['post'])
                                <button onclick = "onPostVoucherOpen();" type = "button"
                                    class="btn btn-warning btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg
                                        xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="feather feather-check-circle">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg> Post</button>
                            @endif


                            <a class="btn btn-primary btn-sm mb-50 mb-sm-0"
                                href="{{ route($view_route, ['id' => $data->home_loan_id]) }}"><i data-feather="check-circle"></i>
                                View
                                Application</a>
                        </div>
                    </div>
             </div>
            </div>
            <div class="content-body">

                <div id="settle-add-update">
                    @csrf
                    @if ($errors->any())
                    <div class="alert alert-danger mt-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                    <section id="basic-datatable">

                        <div class="row">
                            <div class="col-12">

                                <div class="card">
                                    <div class="card-body customernewsection-form">


                                        <div class="border-bottom mb-2 pb-25">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader ">
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>


                                            </div>

                                        </div>


                                        <div class="row">

                                            <div class="col-md-8">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Series <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-select" name="book_id" id="book_id" required>
                                                            <option value="">Select</option>
                                                            @if (isset($book_type))
                                                                @foreach ($book_type as $key => $val)
                                                                    <option value="{{ $val->id }}" {{ isset($data->book_id) && $data->book_id == $val->id ? 'selected' : '' }}>
                                                                        {{ $val->book_name }}
                                                                    </option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                         </div>

                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Document No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="settle_document_no"
                                                            id="settle_document_no" value="{{$data->settle_document_no}}" required>
                                                        <span id="settle_document_no_error_message"
                                                            class="text-danger"></span>
                                                        <span id="settle_document_no_span"></span>
                                                        @error('settle_document_no')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Application No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-select" name="settle_application_no"
                                                            id="settle_application_no" required>
                                                            <option value="">Select</option>
                                                            @if (isset($applicants))
                                                            @foreach ($applicants as $key => $val)
                                                                <option value="{{ $val->id }}"
                                                                    {{ isset($data->home_loan_id) && $data->home_loan_id == $val->id ? 'selected' : '' }}>
                                                                    {{ $val->appli_no }}</option>
                                                            @endforeach
                                                        @endif
                                                     </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Customer <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" readonly id="settle_customer"
                                                            name="settle_customer" class="form-control" value="">
                                                        @error('settle_customer')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Loan Type <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" readonly id="settle_loan_type"
                                                            name="settle_loan_type" value="" class="form-control">
                                                        @error('settle_loan_type')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-4" hidden>

                                                <div
                                                    class="step-custhomapp bg-light p-1 customerapptimelines customerapptimelinesapprovalpo">
                                                    <h5 class="mb-2 text-dark border-bottom pb-50">
                                                        <strong><i data-feather="arrow-right-circle"></i> Approval
                                                            History</strong>
                                                    </h5>
                                                    <ul class="timeline ms-50 newdashtimline ">
                                                        <li class="timeline-item">
                                                            <span class="timeline-point timeline-point-indicator"></span>
                                                            <div class="timeline-event">
                                                                <div
                                                                    class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                    <h6>Aniket Singh</h6>
                                                                    <span
                                                                        class="badge rounded-pill badge-light-danger">Rejected</span>
                                                                </div>
                                                                <h5>(2 min ago)</h5>
                                                                <p>Description will come here</p>
                                                            </div>
                                                        </li>
                                                        <li class="timeline-item">
                                                            <span
                                                                class="timeline-point timeline-point-warning timeline-point-indicator"></span>
                                                            <div class="timeline-event">
                                                                <div
                                                                    class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                    <h6>Deewan Singh</h6>
                                                                    <span
                                                                        class="badge rounded-pill badge-light-warning">Pending</span>
                                                                </div>
                                                                <h5>(5 min ago)</h5>
                                                                <p>Description will come here</p>
                                                            </div>
                                                        </li>
                                                        <li class="timeline-item">
                                                            <span
                                                                class="timeline-point timeline-point-info timeline-point-indicator"></span>
                                                            <div class="timeline-event">
                                                                <div
                                                                    class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                    <h6>Brijesh Kumar</h6>
                                                                    <span
                                                                        class="badge rounded-pill badge-light-success">Approved</span>
                                                                </div>
                                                                <h5>(10 min ago)</h5>
                                                                <p>Description will come here</p>
                                                            </div>
                                                        </li>
                                                        <li class="timeline-item">
                                                            <span
                                                                class="timeline-point timeline-point-danger timeline-point-indicator"></span>
                                                            <div class="timeline-event">
                                                                <div
                                                                    class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                    <h6>Deepender Singh</h6>
                                                                    <span
                                                                        class="badge rounded-pill badge-light-success">Approved</span>
                                                                </div>
                                                                <h5>(5 day ago)</h5>
                                                                <p><a href="#"><i data-feather="download"></i></a>
                                                                    Description will come here </p>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>

                                            </div>

                                        </div>

                                        <div class="row">

                                            <div class="col-md-12 revisedvalue mt-2">
                                                <div
                                                    class="newheader d-flex justify-content-between align-items-end mb-1 border-bottom pb-25">
                                                    <div class="header-left">
                                                        <h4 class="card-title text-theme">Loan Detail</h4>
                                                    </div>
                                                </div>

                                            </div>


                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Loan Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" value="" name="loan_amount"
                                                            id="loan_amount" disabled class="form-control">
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Disburse Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" id="dis_amount" name="dis_amount"
                                                            value="" readonly class="form-control">
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Recovered Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" id="rec_amnt" name="rec_amnt"
                                                            value="{{\App\Helpers\Helper::formatIndianNumber(\App\Helpers\Helper::removeCommas($data->rec_amnt))}}" readonly class="form-control">
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Interest Recieved Till Date <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" value="{{\App\Helpers\Helper::formatIndianNumber(\App\Helpers\Helper::removeCommas($data->rec_intrst))}}" id="rec_intrst"
                                                            name="rec_intrst" readonly class="form-control">
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Bal. Loan Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" id="settle_bal_loan_amnnt" readonly
                                                            name="settle_bal_loan_amnnt" value="{{\App\Helpers\Helper::formatIndianNumber(\App\Helpers\Helper::removeCommas($data->settle_bal_loan_amnnt))}}" required
                                                            class="form-control">
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-3" hidden>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Bal. Interest Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input id="settle_intr_bal_amnnt" value="{{\App\Helpers\Helper::formatIndianNumber(\App\Helpers\Helper::removeCommas($data->settle_intr_bal_amnnt))}}" name="settle_intr_bal_amnnt" type="text"
                                                            readonly class="form-control" >
                                                    </div>

                                                </div>

                                            </div>



                                            <div class="col-md-3 finalvalue">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Settlement Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" class="form-control" value="{{\App\Helpers\Helper::formatIndianNumber(\App\Helpers\Helper::removeCommas($data->settle_amnnt))}}" oninput="settle()"
                                                            id="settle_amnnt" name="settle_amnnt">
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-3 finalvalue">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Write off Amt. <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" id="settle_wo_amnnt" name="settle_wo_amnnt"
                                                            readonly value="{{\App\Helpers\Helper::formatIndianNumber(\App\Helpers\Helper::removeCommas($data->settle_wo_amnnt))}}" class="form-control">
                                                    </div>

                                                </div>

                                            </div>



                                        </div>




                                        <div class="row my-2">


                                            <div class="col-md-8 revisedvalue">
                                                <div
                                                    class="newheader d-flex justify-content-between align-items-end mb-1 border-bottom pb-25">
                                                    <div class="header-left">
                                                        <h4 class="card-title text-theme">Settlement Schedule</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>

                                                <div class="table-responsive-md mb-1">


                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Date</th>
                                                                <th>Amount</th>

                                                            </tr>
                                                        </thead>
                                                        <tbody id="table-body-settle">

                                                            @if (isset($data) &&
                                                                    $data->schedules &&
                                                                    $data->schedules->count() > 0)
                                                                @foreach ($data->schedules as $key => $val)
                                                                    <tr>
                                                                        <td>{{ $key + 1 }}</td>
                                                                        <td><input type="date"
                                                                                required name="Settlement[schedule_date][]"
                                                                                value="{{ $val->schedule_date ?? '' }}"
                                                                                class="form-control mw-100 past-date"></td>
                                                                        <td><input type="text"
                                                                                required name="Settlement[schedule_amnt][]"
                                                                                value="{{ \App\Helpers\Helper::formatIndianNumber(\App\Helpers\Helper::removeCommas($val->schedule_amnt)) ?? '' }}"
                                                                                class="form-control mw-100"></td>

                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                        </tbody>


                                                    </table>



                                                </div>
                                            </div>

                                        </div>

                                        <div class="row">

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Upload Document</label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="file" class="form-control">
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-6">
                                                <div class="row  mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Remarks</label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" name="remarks" id="remarks" class="form-control" value="{{$data->remarks??"No Remarks Here.."}}" />
                                                    </div>

                                                </div>


                                            </div>



                                        </div>





                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal to add new record -->

                    </section>
                </div>


            </div>
        </div>
    </div>

    <!-- END: Content-->

<div class="modal fade text-start show" id="postvoucher" tabindex="-1" aria-labelledby="postVoucherModal" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="postVoucherModal"> Voucher Details</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">

                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label">Series <span class="text-danger">*</span></label>
                            <input id = "voucher_book_code" class="form-control" disabled="">
                            <input type="hidden" class="form-control" name="data" id="ldata">
                            <input type="hidden" class="form-control" name="doc" id="doc">
                            <input type="hidden" class="form-control" name="loan_data" id="loan_data">
                            <input type="hidden" class="form-control" name="remakrs" id="remakrs">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label">Voucher No <span class="text-danger">*</span></label>
                            <input id = "voucher_doc_no" class="form-control" disabled="" value="">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label">Voucher Date <span class="text-danger">*</span></label>
                            <input id = "voucher_date" class="form-control" disabled="" value="">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                            <input id = "voucher_currency" class="form-control" disabled="" value="">
                        </div>
                    </div>

                    <div class="col-md-12">


                        <div class="table-responsive">
                            <table
                                class="mt-1 table table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Group</th>
                                        <th>Leadger Code</th>
                                        <th>Leadger Name</th>
                                        <th class="text-end">Debit</th>
                                        <th class="text-end">Credit</th>
                                    </tr>
                                </thead>
                                <tbody id = "posting-table">


                                </tbody>


                            </table>
                        </div>
                    </div>


                </div>
            </div>
            <div class="modal-footer text-end">
                <button onclick = "postVoucher(this);" id = "posting_button" type = "button"
                    class="btn btn-primary btn-sm waves-effect waves-float waves-light"><svg
                        xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="feather feather-check-circle">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg> Submit</button>
            </div>
        </div>
    </div>
</div>


    <div class="modal fade" id="reject" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">
                            Reject Settlement
                        </h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0" id="rc_detail_re"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('loan.settlement.appr_rej') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="st_appr_status" value="reject">
                    <input type="hidden" id="checkedDataVAL" name="checkedData" value="{{ $data->id }}">
                    <div class="modal-body pb-2">

                        <div class="row mt-2">

                            <div class="col-md-12">

                                <div class="mb-1">
                                    <label class="form-label">Upload Document</label>
                                    <input type="file" class="form-control" name="st_appr_doc[]" id="fileInput" />
                                </div>

                                <div class="mb-1">
                                    <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="st_appr_remark" id="re_appr_remark" required></textarea>
                                </div>


                            </div>

                        </div>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal"
                            aria-label="Close">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="approved" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">
                            Approved Settlement
                        </h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0" id="rc_detail_re"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('loan.settlement.appr_rej') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="st_appr_status" value="approve">
                    <input type="hidden" id="checkedDataVAL" name="checkedData" value="{{ $data->id }}">
                    <div class="modal-body pb-2">

                        <div class="row mt-2">

                            <div class="col-md-12">

                                <div class="mb-1">
                                    <label class="form-label">Upload Document</label>
                                    <input type="file" class="form-control" name="st_appr_doc[]" id="fileInput" />



                                    <div class="mb-1">
                                        <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="st_appr_remark" id="re_appr_remark" required></textarea>
                                    </div>


                                </div>

                            </div>
                        </div>

                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal"
                                aria-label="Close">Cancel</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var getSeriesUrl = "{{ url('loan/get-series') }}";
        var getvoucherUrl = "{{ url('/get_voucher_no') }}".trim();
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/js/loan.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

    <script>
        let ballance_amnt = '';

        $('#book_id').on('change', function() {
            var book_id = $(this).val();
            var request = $('#settle_document_no');
            request.val('');
            if (book_id) {
                $.ajax({
                    url: "{{ url('/loan/get-loan-request') }}/" + book_id,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        if (data.requestno == 1) {
                            request.prop('readonly', false);
                        } else {
                            request.prop('readonly', true);
                            request.val(data.requestno);
                        }
                    }
                });
            }
        });






        $('#fileInput').on('change', function() {
            var files = this.files;
            var $fileList = $('#fileList');

            // Loop through selected files
            $.each(files, function(index, file) {
                var fileSize = (file.size / 1024).toFixed(2) + ' KB'; // File size in KB
                var fileName = file.name;
                var fileExtension = fileName.split('.').pop()
                    .toUpperCase(); // Get file extension and make it uppercase

                // Create a new image-uplodasection div
                var $fileDiv = $('<div class="image-uplodasection mb-2"></div>');
                var $fileIcon = $('<i data-feather="file" class="fileuploadicon"></i>');
                var $fileName = $('<span class="file-name d-block"></span>').text(
                    fileExtension + ' file').css('font-size', '10px'); // Display extension
                var $fileInfo = $('<span class="file-info d-block"></span>').text(fileSize).css(
                    'font-size', '10px'); // Display file size on the next line
                var $deleteDiv = $(
                    '<div class="delete-img text-danger"><i data-feather="x"></i></div>');

                $fileDiv.append($fileIcon).append($fileName).append($fileInfo).append(
                    $deleteDiv);
                $fileList.append($fileDiv);
                feather.replace();
            });
        });

        // Optional: Handle delete button click to remove the fileDiv
        $(document).on('click', '.delete-img', function() {
            $(this).closest('.image-uplodasection').remove();
        });
        $(document).on('change', '#settle_application_no', function() {

var customerID = $(this).val();
//console.log(customerID);
if (customerID != "") {

    $.ajax({
        url: '{{ route('loan.settlement.customer') }}',
        data: {
            id: customerID
        },
        dataType: 'json',
        success: function(data) {
            let loanData = 'Loan';
            if (data.customer_record.type == 1) {
                loanData = 'Home ' + loanData;
            } else if (data.customer_record.type == 2) {
                loanData = 'Vehicle ' + loanData;
            } else if (data.customer_record.type == 3) {
                loanData = 'Term ' + loanData;
            }

            let dueDate = data.due_date;
            let recoverySentioned = data.customer_record.recovery_sentioned;

            var totalDisbursement = data.customer_record.loan_disbursements
                .reduce(
                    function(sum, disbursement) {
                        if (disbursement.approvalStatus === "Disbursed") {
                            if (disbursement.actual_dis)
                                return sum + parseFloat(removeCommas(disbursement
                                    .actual_dis));
                            else
                                return sum + parseFloat(removeCommas(disbursement
                                    .dis_amount));
                        }
                        return sum;
                    },
                    0
                );

            var totalInterest = data.customer_record.loan_appraisal.recovery
                .reduce(
                    function(sum, recover) {
                        return sum + parseFloat(removeCommas(recover
                            .interest_amount)
                        ); // Convert to float and accumulate
                    }, 0);
            var totalRecovery = 0;
            var totalIntrestReceived = 0;
            let intrest = parseFloat(removeCommas($('#settle_intr_bal_amnnt').val())) || 0;
            if (data.customer_record.recovery_loan.length != 0) {
                // Find the recovery loan with the maximum ID
                let maxIdRecoveryLoan = data.customer_record.recovery_loan
                    .reduce((max, recovery_lo) =>
                        recovery_lo.id > max.id ? recovery_lo : max
                    );
                console.log(maxIdRecoveryLoan);

                // Calculate total recovery and interest received using the max ID data
                totalRecovery = parseFloat(removeCommas(maxIdRecoveryLoan
                    .rec_principal_amnt));
                totalIntrestReceived = parseFloat(removeCommas(maxIdRecoveryLoan
                    .rec_interest_amnt));

            }
            totalRecovery = parseFloat(removeCommas(data.customer_record.recovery_loan_amount))||0;

            var totalSettlement = 0;
            if (data.customer_record.loan_settlement.length != 0) {
                // Find the recovery loan with the maximum ID
                let maxIdSettled = data.customer_record.loan_settlement
                    .reduce((max, settle_lo) =>
                        settle_lo.id > max.id ? settle_lo : max
                    );


                // Calculate total recovery and interest received using the max ID data
                totalSettlement = parseFloat((maxIdSettled.settle_wo_amount));


            }


            totalInterest = totalInterest - totalIntrestReceived;
            let repayment_dur = data.customer_record.loan_appraisal
                .repayment_start_period;
            let repayment_type = data.customer_record.loan_appraisal
                .repayment_type;
            let rep_month = repayment_dur;


            let settle_bal_loan_amnnt = removeCommas(data.customer_record.loan_appraisal
            .term_loan) - totalRecovery;



            $("#settle_customer").val(data.customer_record.name);
            $("#settle_loan_type").val(loanData);
            //console.log(data.customer_record);

            $("#loan_amount").val(formatIndianNumber(removeCommas(data.customer_record.loan_appraisal
                .term_loan)));
            $("#dis_amount").val(formatIndianNumber(totalDisbursement));



        },
        error: function(xhr, status, error) {
            console.log('AJAX Error:', status, error);
        }
    });
}
});
        $('#settle_application_no').trigger('change');




        $(document).on('input', '.principal-amnt', function() {
            var principalAmount = parseFloat(removeCommas($(this).val()));
            var interestRate = parseFloat(removeCommas($('#data_interest').val()));
            var $row = $(this).closest('tr');

            if (!isNaN(principalAmount) && !isNaN(interestRate)) {
                var interestAmount = (principalAmount * interestRate) / 100;
                var totalAmount = principalAmount + interestAmount;

                $row.find('.interest-rate').val(interestAmount);
                $row.find('.total-amount').val(totalAmount);
            } else {
                $row.find('.interest-rate').val('');
                $row.find('.total-amount').val('');
            }
        });

        function getOrdinalSuffix(i) {
            var j = i % 10,
                k = i % 100;
            if (j == 1 && k != 11) {
                return "st";
            }
            if (j == 2 && k != 12) {
                return "nd";
            }
            if (j == 3 && k != 13) {
                return "rd";
            }
            return "th";
        }
        let tableBody = document.getElementById("table-body-settle");

        function updateRowNumbers() {
            const rows = tableBody.querySelectorAll("tr");
            rows.forEach((row, index) => {
                row.querySelector("td").textContent = index + 1; // Set row number
            });
        }

        // Function to update the total amount based on the values in all amount inputs
        function updateTotalAmount() {
            let total = 0;
            document.querySelectorAll('input[name="Settlement[schedule_amnt][]"]').forEach(input => {
                total += parseFloat(removeCommas(input.value)) || 0;
            });
            // Display or use the total amount as needed
            console.log("Total Amount: ", total.toFixed(
                2)); // You can change this to display in the DOM if needed
        }

        // Function to divide and distribute the settlement amount across all rows
        function checkDividedAmount() {
            let amount = parseFloat(removeCommas($('#settle_amnnt').val()));
            let amountInputs = document.querySelectorAll('input[name="Settlement[schedule_amnt][]"]');
            let dividedAmount = (amount / amountInputs.length).toFixed(2);

            if (dividedAmount <= 100) {
                return false; // Prevents further processing
            }

            return true; // Valid divided amount
        }

        // Function to set the settlement amount across all rows
        function setSettlementAmount() {
            let amount = parseFloat(removeCommas($('#settle_amnnt').val()));
            let amountInputs = document.querySelectorAll('input[name="Settlement[schedule_amnt][]"]');
            let dividedAmount = (amount / amountInputs.length).toFixed(2);

            amountInputs.forEach(input => input.value = dividedAmount);
            updateTotalAmount();
        }
        $('tbody').on('input', 'input[name="Settlement[schedule_amnt][]"]', function() {
            var totalAmount = 0;

            // Loop through all amount fields and sum them
            $('input[name="Settlement[schedule_amnt][]"]').each(function() {
                var value = parseFloat(removeCommas($(this).val()));
                if (!isNaN(value)) {
                    totalAmount += value;
                }
            });

            // Set the total amount in the settle_amnnt field
            $('#settle_amnnt').val(totalAmount.toFixed(2));
        });


        $('tbody').on('click', '#add-bank-row-settle', function(e) {
            e.preventDefault();
            $("#disburs_da").attr('readonly', true);
            var $tbody = $(this).closest('tbody');
            var tbodyId = $tbody.attr('id');
            var clickedClass = $(this).attr('id');
            var $firstTdClass = $(this).closest('tr').find('td:first').attr('id');

            var $currentRow = $(this).closest('tr');
            var $newRow = $currentRow.clone(true, true);

            var isValid = $currentRow.find('input').filter(function() {
                return $(this).val().trim() !== '';
            }).length > 0;

            if (!isValid) {
                alert('At least one field must be filled before adding a new row.');
                return;
            }

            $currentRow.find('input').val('');

            // Update row number for the new row
            var nextIndex = $('#' + tbodyId + ' tr').length + 1;
            $newRow.find('#' + $firstTdClass).text(nextIndex);
            $newRow.find('#' + clickedClass).removeClass(clickedClass).removeAttr('id').removeAttr(
                'data-class').addClass('text-danger delete-item').html(
                '<i data-feather="trash-2"></i>');
            if (checkDividedAmount()) {
                $('#' + tbodyId).append($newRow);
                setSettlementAmount();
                feather.replace();
            } else {
                alert('Setllement Amount is too low!');
                setSettlementAmount();
            }
        });

        $('tbody').on('click', '.delete-item', function(e) {
            e.preventDefault();

            var $tableBody = $(this).closest('tbody');

            $(this).closest('tr').remove();
            setSettlementAmount();

            var $firstTdId = $(this).closest('tr').find('td:first').attr('id');
            $tableBody.find('tr').each(function(index) {
                var $rowNumber = $(this).find('#' + $firstTdId);
                if ($rowNumber.length) {
                    $rowNumber.text(index + 1);
                }

            });
        });

        function settle() {
            // Get settlement and balance loan amounts from input fields
            let settleAmount = parseFloat(removeCommas($('#settle_amnnt').val()));
            let balanceLoanAmount = parseFloat(removeCommas($("#settle_bal_loan_amnnt").val()));

            // Check if balance loan amount is invalid or zero
            if (isNaN(balanceLoanAmount) || balanceLoanAmount === 0) {
                $('#settle_amnnt').val(''); // Clear the settlement amount field
                alert('Please select Bal. Loan Amount First');
                return false;
            }

            // Ensure settlement amount is less than balance loan amount
            if (settleAmount >= balanceLoanAmount) {
                $('#settle_amnnt').val(''); // Clear the settlement amount field
                alert('Settlement amount should be less than Bal. Loan Amount');
                return false;
            }

            // Calculate the write-off amount, round to 2 decimal places, and set it in the appropriate field
            let writeOffAmount = (balanceLoanAmount - settleAmount).toFixed(2);
            $("#settle_wo_amnnt").val(writeOffAmount);
            setSettlementAmount();
        }

        $("#disbursement_amnt").on('change', function() {
            var selectedValue = $(this).val();
            if (selectedValue === "percent") {
                // Make the input field editable
                $("#dis_mile").removeAttr('readonly');
            } else {
                // Make the input field read-only
                $("#dis_mile").attr('readonly', true);
            }
        });

        var baseUrl = "{{ asset('storage/') }}";
        $(document).on('click', '#assess', function() {
            var loanId = $(this).data('loan-id');
            var loanAmnt = $(this).data('loan-amnt');
            var loanName = $(this).data('loan-name') || '-';
            var loanCreatedAt = $(this).data('loan-created-at') || '-';
            var createData = loanCreatedAt.split(' ')[0];
            $("#ass_para").html(`${loanName} | ${loanAmnt} | ${createData}`);

            // Set the loan ID and amount in the form
            $("#id_loan").val(loanId);
            $("#amnt_loan").val(loanAmnt);

            $.ajax({
                url: '{{ route('get.loan.assess') }}',
                data: {
                    id: loanId
                },
                dataType: 'json',
                success: function(data) {
                    if (data.assess) {
                        $("#ass_recom_amnt").val(data.assess.ass_recom_amnt || '');
                        $("#ass_cibil").val(data.assess.ass_cibil || '');
                        $("#ass_remarks").val(data.assess.ass_remarks || '');
                        if (data.assess.ass_doc) {
                            var hiddenInputHtml = '<input type="hidden" name="stored_ass_doc" value="' +
                                data.assess.ass_doc + '" class="form-control" />';
                            $("#hidden_inputs").html(hiddenInputHtml);
                            var docUrl = "{{ asset('storage') }}" + '/' + data.assess.ass_doc;
                            var linkHtml = '<a href="' + docUrl +
                                '" target="_blank">Assessment Doc</a>';
                            $("#doc_link").html(linkHtml);
                        }
                    } else {
                        console.log('No assessment data found.');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', status, error);
                }
            });
        });

        $(document).on('click', '#disburs', function() {
            var loanIdd = $(this).data('loan-id');
            var lloanAmnt = $(this).data('loan-amnt');
            var lloanName = $(this).data('loan-name') || '-';
            var lloanCreatedAt = $(this).data('loan-created-at') || '-';
            var ccreateData = lloanCreatedAt.split(' ')[0];
            $("#dis_para").html(`${lloanName} | ${lloanAmnt} | ${ccreateData}`);

            $("#idd_loan").val(loanIdd);
            $("#lloan_amount").val(lloanAmnt);

            $.ajax({
                url: '{{ route('get.loan.disbursemnt') }}',
                data: {
                    id: loanIdd
                },
                dataType: 'json',
                success: function(data) {
                    try {
                        var disbursal_amnt = data.loan_amount.disbursal_amnt;
                        console.log(disbursal_amnt);
                        $('#disbursement_amnt option').each(function() {
                            if ($(this).val() == disbursal_amnt) {
                                $(this).prop('selected', true);
                            }
                        });
                        $("#table-body-dis").html(data.disburs);
                    } catch (e) {
                        console.error('Error inserting HTML:', e);
                    }
                    feather.replace();
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', status, error);
                }
            });
        });

        $(document).on('click', '#docc', function() {
            var loanIdDoc = $(this).data('loan-id');

            $.ajax({
                url: '{{ route('get.loan.docc') }}',
                data: {
                    id: loanIdDoc
                },
                dataType: 'json',
                success: function(data) {
                    $('#documents-tbody').html(data.doc);
                    feather.replace();
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', status, error);
                }
            });
        });



        function downloadDocumentsZip() {
            var zip = new JSZip();
            var hasDocuments = false;

            // Select all <a> tags inside <tr> > <td> inside #documents-tbody
            var links = document.querySelectorAll('#documents-tbody tr td a');

            if (links.length === 0) {
                alert('No documents available to download.');
                return;
            }

            var linksProcessed = 0;

            links.forEach(function(link, index) {
                if (link.href.length > 0) {
                    hasDocuments = true;
                    // Fetch the document content
                    fetch(link.href)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.blob();
                        })
                        .then(blob => {
                            // Add to zip file
                            var fileName = `document_${index + 1}.${link.href.split('.').pop()}`;
                            zip.file(fileName, blob);

                            linksProcessed++;
                            // Check if all files are added
                            if (linksProcessed === links.length) {
                                zip.generateAsync({
                                        type: 'blob'
                                    })
                                    .then(function(content) {
                                        // Trigger download
                                        saveAs(content, 'documents.zip');
                                    });
                            }
                        })
                        .catch(error => console.error('Error downloading file:', error));
                }
            });

            if (!hasDocuments) {
                alert('No valid documents to download.');
            }
        }



        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })
        $(function() {
            var dt_basic_table = $('.datatables-basic'),
                dt_date_table = $('.dt-date'),
                dt_complex_header_table = $('.dt-complex-header'),
                dt_row_grouping_table = $('.dt-row-grouping'),
                dt_multilingual_table = $('.dt-multilingual'),
                assetPath = '../../../app-assets/';
            if ($('body').attr('data-framework') === 'laravel') {
                assetPath = $('body').attr('data-asset-path');
            }

            // DataTable with buttons
            // --------------------------------------------------------------------

            var keyword = '';
            if (dt_basic_table.length) {
                var dt_basic = dt_basic_table.DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('loan.index') }}",
                        data: function(d) {
                            d.date = $("#fp-range").val(),
                                d.ledger = $("#filter-ledger-name").val(),
                                d.status = $("#filter-status").val(),
                                d.type = $("#filter-ledger-type").val(),
                                d.keyword = keyword
                        }
                    },
                    columns: [{
                            data: null,
                            className: 'dt-center',
                            defaultContent: '<div class="form-check form-check-inline"><input class="form-check-input row-checkbox" type="checkbox"></div>',
                            orderable: false
                        },
                        {
                            data: 'appli_no',
                            name: 'appli_no'
                        },
                        {
                            data: 'ref_no',
                            name: 'ref_no'
                        },
                        {
                            data: 'proceed_date',
                            name: 'proceed_date'
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'email',
                            name: 'email'
                        },
                        {
                            data: 'mobile',
                            name: 'mobile'
                        },
                        {
                            data: 'type',
                            name: 'type'
                        },
                        {
                            data: 'loan_amount',
                            name: 'loan_amount'
                        },
                        {
                            data: 'age',
                            name: 'age'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    drawCallback: function() {
                        feather.replace();
                    },
                    dom: 'Bfrtip',
                    order: [
                        [0, 'desc']
                    ],
                    dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    displayLength: 7,
                    lengthMenu: [7, 10, 25, 50, 75, 100],
                    buttons: [{
                            extend: 'collection',
                            className: 'btn btn-outline-secondary dropdown-toggle',
                            text: feather.icons['share'].toSvg({
                                class: 'font-small-4 mr-50'
                            }) + 'Export',
                            buttons: [{
                                    extend: 'print',
                                    text: feather.icons['printer'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Print',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [3, 4, 5, 6, 7]
                                    }
                                },
                                {
                                    extend: 'csv',
                                    text: feather.icons['file-text'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Csv',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [3, 4, 5, 6, 7]
                                    }
                                },
                                {
                                    extend: 'excel',
                                    text: feather.icons['file'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Excel',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [3, 4, 5, 6, 7]
                                    }
                                },
                                {
                                    extend: 'pdf',
                                    text: feather.icons['clipboard'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Pdf',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [3, 4, 5, 6, 7]
                                    }
                                },
                                {
                                    extend: 'copy',
                                    text: feather.icons['copy'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Copy',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [3, 4, 5, 6, 7]
                                    }
                                }
                            ],
                            init: function(api, node, config) {
                                $(node).removeClass('btn-secondary');
                                $(node).parent().removeClass('btn-group');
                                setTimeout(function() {
                                    $(node).closest('.dt-buttons').removeClass('btn-group')
                                        .addClass('d-inline-flex');
                                }, 50);
                            }
                        },

                    ],
                    language: {
                        paginate: {
                            // remove previous & next text from pagination
                            previous: '&nbsp;',
                            next: '&nbsp;'
                        }
                    }
                });
                $('div.head-label').html('<h6 class="mb-0">Event List</h6>');
            }

            // Flat Date picker
            if (dt_date_table.length) {
                dt_date_table.flatpickr({
                    monthSelectorType: 'static',
                    dateFormat: 'm/d/Y'
                });
            }

            // Filter record
            $(".apply-filter").on("click", function() {
                // Redraw the table
                dt_basic.draw();

                // Remove the custom filter function to avoid stacking filters
                // $.fn.dataTable.ext.search.pop();

                // Hide the modal
                $(".modal").modal("hide");
            })

            // Delete Record
            $('.datatables-basic tbody').on('click', '.delete-record', function() {
                dt_basic.row($(this).parents('tr')).remove().draw();
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Select all input fields of type number
            const numberInputs = document.querySelectorAll('input[type="number"]');

            // Loop through each input field
            numberInputs.forEach(function(input) {
                // Add an input event listener to each number input
                input.addEventListener('input', function() {
                    // If the value is negative, set it to its absolute value
                    if (this.value < 0) {
                        this.value = Math.abs(this.value);
                    }
                });

                // Add a blur event listener to ensure no negative values on losing focus
                input.addEventListener('blur', function() {
                    if (this.value < 0) {
                        this.value = Math.abs(this.value);
                    }
                });
            });
        });

        function fetchSeriesBased(series_id, id) {
            $.ajax({
                url: getvoucherUrl + '/' + series_id,
                method: 'GET',
                success: function(response) {
                    if (response.type == "Auto") {
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

        document.addEventListener('DOMContentLoaded', function() {
            const pastDateInputs = document.querySelectorAll('.past-date');
            const futureDateInputs = document.querySelectorAll('.future-date');

            function readonlyates() {
                const today = new Date().toISOString().split('T')[0];

                pastDateInputs.forEach(input => {
                    input.setAttribute('max', today);
                });

                futureDateInputs.forEach(input => {
                    input.setAttribute('min', today);
                });
            }
            readonlyates();
        });

        document.addEventListener('DOMContentLoaded', function() {
            const appliNoInput = document.getElementById('settle_document_no');
            const errorMessage = document.getElementById('settle_document_no_error_message');
            const appli_span = document.getElementById('settle_document_no_span')

            function validateAppliNo() {
                const value = appliNoInput.value.trim();

                // Check if the string starts with a negative sign
                if (value.startsWith('-')) {
                    appli_span.textContent = '';
                    errorMessage.textContent = 'The Document number must not start with a negative sign.';
                    return false;
                }

                // Check if the string contains only allowed characters (letters, numbers, and dashes)
                const regex = /^[a-zA-Z0-9-_]+$/;
                if (!regex.test(value)) {
                    appli_span.textContent = '';
                    errorMessage.textContent =
                        'The Document number can only contain letters, numbers, dashes and underscores.';
                    return false;
                }

                // If all checks pass, clear the error message
                errorMessage.textContent = '';
                return true;
            }

            // Validate on blur
            appliNoInput.addEventListener('blur', validateAppliNo);
        });

        document.addEventListener('DOMContentLoaded', function() {
            const textInputs = document.querySelectorAll('input[type="text"]');

            textInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    if (this.value.length > 250) {
                        alert(
                            'You have exceeded the 250 character limit. Extra characters will be removed.'
                        );
                        this.value = this.value.substring(0, 250);
                    }
                });

                input.addEventListener('blur', function() {
                    if (this.value.length > 250) {
                        alert(
                            'You have exceeded the 250 character limit. Extra characters will be removed.'
                        );
                        this.value = this.value.substring(0, 250);
                    }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const textInputs = document.querySelectorAll('input[type="number"]');

            textInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    if (this.value.length > 11) {
                        alert(
                            'You have exceeded the 11 character limit. Extra characters will be removed.'
                        );
                        this.value = this.value.substring(0, 11);
                    }
                });

                input.addEventListener('blur', function() {
                    if (this.value.length > 11) {
                        alert(
                            'You have exceeded the 11 character limit. Extra characters will be removed.'
                        );
                        this.value = this.value.substring(0, 11);
                    }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const textareas = document.querySelectorAll('textarea');

            textareas.forEach(function(textarea) {
                function enforceCharacterLimit() {
                    if (this.value.length > 500) {
                        alert(
                            'You have exceeded the 500 character limit. Extra characters will be removed.'
                        );
                        this.value = this.value.substring(0, 500);
                    }
                }

                textarea.addEventListener('input', enforceCharacterLimit);
                textarea.addEventListener('blur', enforceCharacterLimit);
            });

        });
        $('#settle_application_no').trigger('change');
        // Set all input fields to readonly and all select fields to disabled within #settle-add-update
$('#settle-add-update input').prop('readonly', true);
$('#settle-add-update select').prop('disabled', true);
function onPostVoucherOpen(type = "not_posted") {
            resetPostVoucher();

            const apiURL = "{{ route('loan.settlement.getPostingDetails') }}";
            const remarks = $("#remarks").val()||"";
            $.ajax({
                url: apiURL +"?book_id="+"{{ $data->book_id }}" +"&document_id="+"{{ $data->id }}"+"&remarks=" + remarks||"",
                type: "GET",
                dataType: "json",
                success: function(data) {
                    if (!data.data.status) {
                        Swal.fire({
                            title: 'Error!',
                            text: data.data.message,
                            icon: 'error',
                        });
                        return;
                    }
                    const voucherEntries = data.data.data;
                    var voucherEntriesHTML = ``;
                    Object.keys(voucherEntries.ledgers).forEach((voucher) => {
                        voucherEntries.ledgers[voucher].forEach((voucherDetail, index) => {
                            voucherEntriesHTML += `
                    <tr>
                    <td>${voucher}</td>
                    <td class="fw-bolder text-dark">${voucherDetail.ledger_group_code ? voucherDetail.ledger_group_code : ''}</td>
                    <td>${voucherDetail.ledger_code ? voucherDetail.ledger_code : ''}</td>
                    <td>${voucherDetail.ledger_name ? voucherDetail.ledger_name : ''}</td>
                    <td class="text-end">${voucherDetail.debit_amount > 0 ? parseFloat(voucherDetail.debit_amount).toFixed(2) : ''}</td>
                    <td class="text-end">${voucherDetail.credit_amount > 0 ? parseFloat(voucherDetail.credit_amount).toFixed(2) : ''}</td>
					</tr>
                    `
                        });
                    });
                    voucherEntriesHTML += `
            <tr>
                <td colspan="4" class="fw-bolder text-dark text-end">Total</td>
                <td class="fw-bolder text-dark text-end">${voucherEntries.total_debit.toFixed(2)}</td>
                <td class="fw-bolder text-dark text-end">${voucherEntries.total_credit.toFixed(2)}</td>
			</tr>
            `;
                    document.getElementById('posting-table').innerHTML = voucherEntriesHTML;
                    document.getElementById('voucher_doc_no').value = voucherEntries.document_number;
                    document.getElementById('voucher_date').value = moment(voucherEntries.document_date).format('D/M/Y');
                    document.getElementById('voucher_book_code').value = voucherEntries.book_code;
                    document.getElementById('voucher_currency').value = voucherEntries.currency_code;
                    if (type === "posted") {
                        document.getElementById('posting_button').style.display = 'none';
                    } else {
                        document.getElementById('posting_button').style.removeProperty('display');
                    }
                    $('#postvoucher').modal('show');
                }
            });

        }

        function resetPostVoucher() {
            document.getElementById('voucher_doc_no').value = '';
            document.getElementById('voucher_date').value = '';
            document.getElementById('voucher_book_code').value = '';
            document.getElementById('voucher_currency').value = '';
            document.getElementById('posting-table').innerHTML = '';
            document.getElementById('posting_button').style.display = 'none';
        }

        function postVoucher(element) {
            const bookId = "{{ $data->book_id }}";
            const documentId = "{{ $data->id }}";
            const postingApiUrl = "{{ route('loan.settlement.post') }}";
            const remarks = $("#remarks").val()||"";
            console.log(bookId);
            console.log(documentId);
            if (bookId && documentId) {
                $.ajax({
                    url: postingApiUrl,
                    type: "POST",
                    dataType: "json",
                    contentType: "application/json", // Specifies the request payload type
                    data: JSON.stringify({
                        // Your JSON request data here
                        book_id: bookId,
                        document_id: documentId,
                        remarks: remarks||"",
                    }),
                    success: function(data) {
                        const response = data.data;
                        if (response.status) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                            });
                            location.href = '/loan/settlement';
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message,
                                icon: 'error',
                            });
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Some internal error occured',
                            icon: 'error',
                        });
                    }
                });

            }
        }

</script>

@endsection
