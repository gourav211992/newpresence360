@extends('layouts.app')
@section('content')
    <!-- BEGIN: Content-->
     <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-6  mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Depreciation</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="file:///C|/Users/NGARG/AppData/Local/Temp/Adobe/Dreamweaver 2021/index.html">Home</a></li> 
                                    <li class="breadcrumb-item active"><a href="file:///C|/Users/NGARG/AppData/Local/Temp/Adobe/Dreamweaver 2021/index.html">Fixed Assets</a></li> 
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
				<div class="content-header-right text-md-end col-md-6 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">  
                        <a href="{{route('finance.fixed-asset.depreciation.index')}}" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</a>
                     
                        @if($buttons['approve'])
                        <button type="button" class="btn btn-primary btn-sm" id="approved-button" name="action" value="approved"><i data-feather="check-circle"></i> Approve</button>
                        <button type="button" id="reject-button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg> Reject</button>
                
                    @endif
                    @if($buttons['post'])
                        <button id="postButton" onclick="onPostVoucherOpen();" type="button" class="btn btn-warning btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Post</button>
                    @endif
                    @if($buttons['voucher'])
                        <button type="button" onclick="onPostVoucherOpen('posted');" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg> Voucher</button>
                    @endif
                    
                    {{-- @if($buttons['amend'])
                    <button type="button" data-bs-toggle="modal" data-bs-target="#amendmentconfirm" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i> Amendment</button>
                    @endif --}}
                      
                </div>
                </div>
            </div>
            <div class="content-body"> 
                 <section id="basic-datatable">
                    <div class="row">
                        <form id="fixed-asset-depreciation-form">
                        <input type ="hidden" name="book_code" id ="book_code_input">
                        <input type="hidden" name="doc_number_type" id="doc_number_type">
                        <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern">
                        <input type="hidden" name="doc_prefix" id="doc_prefix">
                        <input type="hidden" name="doc_suffix" id="doc_suffix">
                        <input type="hidden" name="doc_no" id="doc_no">
                        <input type="hidden" name="document_status" id="document_status" value="">

                        <div class="col-12">
                            
                            
                            <div class="card">
								 <div class="card-body customernewsection-form">  
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div
                                                class="newheader d-flex justify-content-between border-bottom mb-2 pb-25">
                                                <div>
                                                    <h4 class="card-title text-theme">Basic Information</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                                <div class="header-right">
                                                    @php
                                                        use App\Helpers\Helper;
                                                    @endphp
                                                   <div class="col-md-6 text-sm-end">
                                                    <span class="badge rounded-pill {{App\Helpers\ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$data->document_status] ?? ''}} forminnerstatus">
                                                        <span class="text-dark">Status</span>
                                                         : <span class="{{App\Helpers\ConstantHelper::DOCUMENT_STATUS_CSS[$data->document_status] ?? ''}}">
                                                            @if ($data->document_status == App\Helpers\ConstantHelper::APPROVAL_NOT_REQUIRED)
                                                            Approved
                                                        @else
                                                            {{ ucfirst($data->document_status) }}
                                                        @endif
                                                    </span>
                                                    </span>        
                                            </div>
                                         </div>
                                            </div>
                                        </div>
                                    </div> 
                                    <div class="row">
                                          

                                            <div class="col-md-8">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label for="book_id" class="form-label">Series <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select disabled id="book_id" name="book_id" class="form-select" required>
                                                            @foreach($series as $book)
                                                                <option value="{{ $book->id }}" {{ ($data->book_id ?? '') == $book->id ? 'selected' : '' }}>
                                                                    {{ $book->book_code }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label for="document_number" class="form-label">Document No <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input disabled type="text" id="document_number" name="document_number" class="form-control"
                                                               value="{{ $data->document_number ?? '' }}" required>
                                                    </div>
                                                </div>
                                            
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label for="document_date" class="form-label">Date <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input disabled type="date" id="document_date" name="document_date" class="form-control"
                                                               value="{{ isset($data->document_date) ? $data->document_date : date('Y-m-d') }}" required>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Location <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select id="location" class="form-select" disabled
                                                            name="location_id" required>
                                                            @if($data->location_id!=null)
                                                            @foreach ($locations as $location)
                                                                <option value="{{ $location->id }}" {{$data->location_id==$location->id?"selected":""}}>
                                                                    {{ $location->store_name }}</option>
                                                            @endforeach
                                                            @else
                                                            <option value=""></option>
                                                            @endif
                                                        </select>
                                                    </div>

                                                </div>
                                                <div class="row align-items-center mb-1 cost_center">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Cost Center <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select id="cost_center" class="form-select"
                                                            name="cost_center_id" required disabled>
                                                        </select>
                                                    </div>

                                                </div>

                                            
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label for="period" class="form-label">Period <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select disabled id="period" name="period" class="form-select" required>
                                                             <option value="{{ $data->period }}">
                                                                    {{ (new DateTime(explode(" to ", $data->period)[1]))->format('jS F Y') }}
                                                                </option>
                                                         
                                                        </select>
                                                    </div>
                                                </div>
                                            
                                            </div>
                                            @include('partials.approval-history', ['document_status' => $data->document_status, 'revision_number' => $data->revision_number])
                                         
                                        </div> 
                                </div>
                            </div>
                            
                              
							
                            <div class="card">
								 <div class="card-body customernewsection-form"> 
                                     
                                     
                                            <div class="border-bottom pb-25">
                                                     <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="newheader "> 
                                                                <h4 class="card-title text-theme">Category Wise Detail</h4>
                                                                <p class="card-text">View the details below</p>
                                                            </div>
                                                        </div> 
                                                    </div> 
                                             </div>
											 
											 
											  
  
											
											<div class="row"> 
                                                
                                                 <div class="col-md-12" id="category_wise_detail">
                                                     
                                                     
                                                    <div class="col-md-12 earn-dedtable">
                                                        <table class="table table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                                  <thead>
                                                                    <tr>
                                                                       <th>#</th>
                                                                       <th>Category</th>
                                                                       <th>Asset Code</th>
                                                                       <th>Asset Name</th>
                                                                       <th>Sub Asset Code</th>
                                                                       <th>Ledger Name</th>
                                                                       <th>FY</th>
                                                                       <th>From Date</th>
                                                                       <th>To Date</th>
                                                                       <th>Posted Days</th>
                                                                       <th>Days</th>
                                                                       <th hidden class="text-end">Current Value</th>
                                                                       <th class="text-end">Return Down Value</th>
                                                                       <th class="text-end">Dep. Amount</th>
                                                                       <th class="text-end">After Dep. Value</th>
                                                                     
                                                                     </tr>
                                                                   </thead>
                                                                <tbody id="assetTableBody">
                                                                    @foreach($assetDetails as $index => $asset)
                                                                    <tr>
                                                                        <td>{{ $index + 1 }}</td>
                                                                        <td>{{ $asset['category'] ?? '' }}</td>
                                                                        <td>{{ $asset['asset_code'] ?? '' }}</td>
                                                                        <td>{{ $asset['asset_name'] ?? '' }}</td>
                                                                        <td>{{ $asset['sub_asset_code'] ?? '' }}</td>
                                                                        <td>{{ $asset['ledger_name'] ?? '' }}</td>
                                                                        <td>{{ $asset['fy'] ?? '' }}</td>
                                                                        <td>{{ $asset['from_date'] ?? '' }}</td>
                                                                        <td>{{ $asset['to_date'] ?? '' }}</td>
                                                                        <td>{{ $asset['posted_days'] ?? 0 }}</td>
                                                                        <td>{{ $asset['days'] ?? '' }}</td>
                                                                        <td hidden class="text-end">{{ number_format((float)$asset['current_value'] ?? 0, 2) }}</td>
                                                                        <td class="text-end">{{ number_format((float)$asset['current_value_after_dep'] ?? 0, 2) }}</td>
                                                                        <td class="text-end">{{ number_format((float)$asset['dep_amount'] ?? 0, 2) }}</td>
                                                                        <td class="text-end">{{ number_format((float)$asset['after_dep_value'] ?? 0, 2) }}</td>
                                                                    </tr>
                                                                @endforeach    
                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                   
                                                                        <td colspan="11" class="text-center">Grand Total</td>
                                                                        <td hidden id="grand_total_current" class="text-end">{{number_format($data->grand_total_current_value,2)}}</td>
                                                                        <td id="grand_total_current_after_dep" class="text-end">{{number_format($data->grand_total_current_value_after_dep,2)}}</td>
                                                                        <td id="grand_total_dep" class="text-end">{{number_format($data->grand_total_dep_amount,2)}}</td>
                                                                        <td id="grand_total_after_dep" class="text-end">{{number_format($data->grand_total_after_dep_value,2)}}</td>
                                                                    </tr>
                                                                    
                                                                </tfoot>
                                                              

                                                        </table>
                                                    </div>
                                                      
                                                      
												
                                                 
                                             </div> 
								</div>
                            </div>
                             
                            
                        </div>
                    </div>
                        </form>
                    <!-- Modal to add new record -->
                     
                </section> 

                 

            </div>
        </div>
    </div>
    <div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body alertmsg text-center warning">
                    <i data-feather='alert-circle'></i>
                    <h2>Are you sure?</h2>
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>Depreciation</strong>? After Amendment this action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
      </div>
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
                                <input id = "voucher_book_code" class="form-control" disabled="" >
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
								<table class="mt-1 table table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
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
									<tbody id="posting-table"></tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<div class="text-end">
					<button style="margin: 1%;" onclick = "postVoucher(this);" id="posting_button" type = "button" class="btn btn-primary btn-sm waves-effect waves-float waves-light">Submit</button>
				</div>
			</div>
		</div>
	</div>
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
           <div class="modal-content">
              <form class="ajax-input-form" method="POST" action="{{ route('finance.fixed-asset.depreciation.approval') }}" data-redirect="{{ route('finance.fixed-asset.depreciation.index') }}" enctype='multipart/form-data'>
                 @csrf
                 <input type="hidden" name="action_type" id="action_type">
                 <input type="hidden" name="id" value="{{$data->id ?? ''}}">
                 <div class="modal-header">
                    <div>
                       <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="popupTitle">
                          Approve Application
                       </h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                 </div>
                 <div class="modal-body pb-2">
                    <div class="row mt-1">
                       <div class="col-md-12">
                          <div class="mb-1">
                             <label class="form-label">Remarks {{-- <span class="text-danger">*</span> --}}</label>
                             <textarea name="remarks" class="form-control"></textarea>
                          </div>
                          <div class="mb-1">
                             <label class="form-label">Upload Document</label>
                             <input type="file" name="attachment[]" multiple class="form-control" />
                          </div>
                       </div>
                    </div>
                 </div>
                 <div class="modal-footer justify-content-center">  
                    <button type="reset" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button> 
                    <button type="submit" class="btn btn-primary">Submit</button>
                 </div>
              </form>
           </div>
        </div>
     </div>
    
    @section('scripts')
        <script>
            $(document).on('click', '#approved-button', (e) => {
   let actionType = 'approve';
   $("#approveModal").find("#action_type").val(actionType);
   $("#approveModal").modal('show');
});

$(document).on('click', '#reject-button', (e) => {
   let actionType = 'reject';
   $("#approveModal").find("#action_type").val(actionType);
   $("#approveModal").modal('show');
});

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
            showToast('error',
                "@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach"
            );
        @endif
       $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })
        function resetParametersDependentElements(data) {
            let backDateAllowed = false;
            let futureDateAllowed = false;

            if (data != null) {
                console.log(data.parameters.back_date_allowed);
                if (Array.isArray(data?.parameters?.back_date_allowed)) {
                    for (let i = 0; i < data.parameters.back_date_allowed.length; i++) {
                        if (data.parameters.back_date_allowed[i].trim().toLowerCase() === "yes") {
                            backDateAllowed = true;
                            break; // Exit the loop once we find "yes"
                        }
                    }
                }
                if (Array.isArray(data?.parameters?.future_date_allowed)) {
                    for (let i = 0; i < data.parameters.future_date_allowed.length; i++) {
                        if (data.parameters.future_date_allowed[i].trim().toLowerCase() === "yes") {
                            futureDateAllowed = true;
                            break; // Exit the loop once we find "yes"
                        }
                    }
                }
                //console.log(backDateAllowed, futureDateAllowed);

            }

            const dateInput = document.getElementById("document_date");

            // Determine the max and min values for the date input
            const today = moment().format("YYYY-MM-DD");

            if (backDateAllowed && futureDateAllowed) {
                dateInput.removeAttribute("min");
                dateInput.removeAttribute("max");
            } else if (backDateAllowed) {
                dateInput.setAttribute("max", today);
                dateInput.removeAttribute("min");
            } else if (futureDateAllowed) {
                dateInput.setAttribute("min", today);
                dateInput.removeAttribute("max");
            } else {
                dateInput.setAttribute("min", today);
                dateInput.setAttribute("max", today);
            }
        }

        $('#book_id').on('change', function() {
            resetParametersDependentElements(null);
            let currentDate = new Date().toISOString().split('T')[0];
            let document_date = $('#document_date').val();
            let bookId = $('#book_id').val();
            let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId +
                "&document_date=" + document_date;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        resetParametersDependentElements(data.data);
                        $("#book_code_input").val(data.data.book_code);
                        if (!data.data.doc.document_number) {
                            $("#document_number").val('');
                            $('#doc_number_type').val('');
                            $('#doc_reset_pattern').val('');
                            $('#doc_prefix').val('');
                            $('#doc_suffix').val('');
                            $('#doc_no').val('');
                        } else {
                            $("#document_number").val(data.data.doc.document_number);
                            $('#doc_number_type').val(data.data.doc.type);
                            $('#doc_reset_pattern').val(data.data.doc.reset_pattern);
                            $('#doc_prefix').val(data.data.doc.prefix);
                            $('#doc_suffix').val(data.data.doc.suffix);
                            $('#doc_no').val(data.data.doc.doc_no);
                        }
                        if (data.data.doc.type == 'Manually') {
                            $("#document_number").attr('readonly', false);
                        } else {
                            $("#document_number").attr('readonly', true);
                        }

                    }
                    if (data.status == 404) {
                        $("#document_number").val('');
                        $('#doc_number_type').val('');
                        $('#doc_reset_pattern').val('');
                        $('#doc_prefix').val('');
                        $('#doc_suffix').val('');
                        $('#doc_no').val('');
                        alert(data.message);
                    }
                });
            });
        });

        function resetPostVoucher()
        {
            document.getElementById('voucher_doc_no').value = '';
            document.getElementById('voucher_date').value = '';
            document.getElementById('voucher_book_code').value = '';
            document.getElementById('voucher_currency').value = '';
            document.getElementById('posting-table').innerHTML = '';
            document.getElementById('posting_button').style.display = 'none';
        }

        function onPostVoucherOpen(type = "not_posted")
        {
            // resetPostVoucher();
            const apiURL = "{{route('finance.fixed-asset.depreciation.posting.get')}}";
            $.ajax({
                url: apiURL + "?book_id=" + $("#book_id").val() + "&document_id=" + "{{isset($data) ? $data -> id : ''}}",
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
                            <td class="text-end indian-number">${voucherDetail.debit_amount > 0 ? parseFloat(voucherDetail.debit_amount).toFixed(2) : ''}</td>
                            <td class="text-end indian-number">${voucherDetail.credit_amount > 0 ? parseFloat(voucherDetail.credit_amount).toFixed(2) : ''}</td>
                            </tr>
                            `
                        });
                    });
                    voucherEntriesHTML+= `
                    <tr>
                        <td colspan="4" class="fw-bolder text-dark text-end">Total</td>
                        <td class="fw-bolder text-dark text-end indian-number">${voucherEntries.total_debit.toFixed(2)}</td>
                        <td class="fw-bolder text-dark text-end indian-number">${voucherEntries.total_credit.toFixed(2)}</td>
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

                              function postVoucher(element) {
    Swal.fire({
        title: 'Are you sure?',
        text: " Note: Once Submit the Voucher you are not able to redo the entry.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, post it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const bookId = "{{ isset($data) ? $data->book_id : '' }}";
            const documentId = "{{ isset($data) ? $data->id : '' }}";
            const postingApiUrl = "{{ route('finance.fixed-asset.depreciation.post') }}";

            if (bookId && documentId) {
                $.ajax({
                    url: postingApiUrl,
                    type: "POST",
                    dataType: "json",
                    contentType: "application/json",
                    data: JSON.stringify({
                        book_id: bookId,
                        document_id: documentId,
                    }),
                    success: function (data) {
                        const response = data.data;
                        if (response.status) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                            });
                            location.href = '{{route("finance.fixed-asset.depreciation.index")}}';
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message,
                                icon: 'error',
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Some internal error occurred',
                            icon: 'error',
                        });
                    }
                });
            }
        }
    });
}

  $('#location').on('change', function () {
    var locationId = $(this).val();

    if (locationId) {
        // Build the route manually
        var url = '{{ route("cost-center.get-cost-center", ":id") }}'.replace(':id', locationId);
        var selectedCostCenterId = '{{ $data->cost_center_id ?? '' }}'; // Use null coalescing for safety

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if(data.length==0){
                    $('#cost_center').empty(); 
                $('#cost_center').prop('required', false);
                $('.cost_center').hide();
                }
                else{
                    $('.cost_center').show();
                    $('#cost_center').prop('required', true);
                $('#cost_center').empty(); // Clear previous options
                $.each(data, function (key, value) {
                        let selected = (value.id == selectedCostCenterId) ? 'selected' : '';
                        $('#cost_center').append('<option value="' + value.id + '" ' + selected + '>' + value.name + '</option>');
                    });
            }
            },
            error: function () {
                $('.cost_center').hide();
                $('#cost_center').empty();
            }
        });
    } else {
        $('.cost_center').hide();
        $('#cost_center').empty();
    }
});

$('#location').trigger('change');


    </script>
@endsection
<!-- END: Body-->

@endsection