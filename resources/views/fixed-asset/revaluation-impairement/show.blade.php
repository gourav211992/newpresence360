@extends('layouts.app')

@section('content')
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Revaluation / Impairement</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">Add New</li>


                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
               
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <a href="{{ route('finance.fixed-asset.revaluation-impairement.index') }}"> <button
                                class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</button>
                        </a>
                             @if($buttons['approve'])
                            <button type="button" class="btn btn-primary btn-sm" id="approved-button" name="action" value="approved"><i data-feather="check-circle"></i> Approve</button>
                            <button type="button" id="reject-button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i data-feather="x-circle"></i> Reject</button>
                    @endif
                    @if($buttons['amend'])
                    <button type="button" data-bs-toggle="modal" data-bs-target="#amendmentconfirm" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i> Amendment</button>
                    @endif
                    @if($buttons['post'])
                        <button id="postButton" onclick="onPostVoucherOpen();" type="button" class="btn btn-warning btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i data-feather="check-circle"></i> Post</button>
                    @endif
                    @if ($buttons['voucher'])
                                    <button type="button" onclick="onPostVoucherOpen('posted');"
                                        class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                                        <i data-feather="file-text"></i> Voucher</button>
                                @endif
                           
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">



                <section id="basic-datatable">
                    <div class="row">
                        <form id="fixed-asset-revaluation-impairement-form" >
     <div class="col-12">


                                <div class="card">
                                    <div class="card-body customernewsection-form">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="newheader border-bottom mb-2 pb-25  ">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h4 class="card-title text-theme">Basic Information</h4>
                                                            <p class="card-text">Fill the details</p>
                                                        </div>


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




                                            <div class="col-md-8">
                                                	<div class="row align-items-center mb-1"> 
															<div class="col-md-3"> 
																<label class="form-label">Type <span class="text-danger">*</span></label>  
															</div> 
                                                        @php
                                                            $selectedType = $data->document_type ?? 'revaluation'; // default to 'revaluation' if not set
                                                        @endphp

                                                        <div class="col-md-8"> 
                                                            <div class="demo-inline-spacing">
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="Revaluation" disabled name="document_type" value="revaluation" class="form-check-input"
                                                                        {{ $selectedType === 'revaluation' ? 'checked' : '' }}>
                                                                    <label class="form-check-label fw-bolder" for="Revaluation">Revaluation</label>
                                                                </div> 
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="Impairement" disabled name="document_type" value="impairement" class="form-check-input"
                                                                        {{ $selectedType === 'impairement' ? 'checked' : '' }}>
                                                                    <label class="form-check-label fw-bolder" for="Impairement">Impairement</label>
                                                                </div>  
                                                                 <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="Writeoff" disabled name="document_type" value="writeoff" class="form-check-input"
                                                                        {{ $selectedType === 'writeoff' ? 'checked' : '' }}>
                                                                    <label class="form-check-label fw-bolder" for="Impairement">Writeoff</label>
                                                                </div>  
                                                            </div>
                                                        </div>

														</div>
                                                     <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="book_id">Series <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" id="book_id" name="book_id" required disabled>
                                                              <option value="{{ $data->book_id }}">{{ $data?->book?->book_code }}
                                                                </option>
                                                         
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="document_number">Doc No <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" id="document_number"
                                                            name="document_number" required disabled value="{{ $data->document_number }}">
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="document_date">Doc Date <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" class="form-control indian-number " id="document_date"
                                                            name="document_date" value="{{ $data->document_date }}" readonly required>
                                                    </div>
                                                </div>
                                                  <div class="row align-items-center mb-1">
                                                     <div class="col-md-3">
                                                      
                                                            <label class="form-label">Category <span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5">
                                                             <select class="form-select select2" name="category_id"
                                                                id="category" required disabled>
                                                                    <option value="{{ $data->category_id }}">
                                                                        {{ $data?->category?->name }}
                                                                    </option>
                                                            </select>
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
                                                            @foreach ($locations as $location)
                                                                <option value="{{ $location->id }}" {{$data->location_id==$location->id?"selected":""}}>
                                                                    {{ $location->store_name }}</option>
                                                            @endforeach
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
                                              
                                            </div>


                                         @include('partials.approval-history', ['document_status' =>$data->document_status, 'revision_number' => $data->revision_number])
                                        
                                        </div>
                                    </div>
                                </div>




                                <div class="card">
                                    <div class="card-body customernewsection-form">


                                        <div class="border-bottom mb-2 pb-25" hidden>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader ">
                                                        <h4 class="card-title text-theme">Select Assets</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-sm-end">
                                                    <a href="#" class="btn btn-sm btn-outline-danger me-50" id="delete">
                                                        <i data-feather="x-circle"></i> Delete</a>
                                                    <a id="addNewRowBtn" class="btn btn-sm btn-outline-primary">
                                                        <i data-feather="plus"></i> Add New</a>
                                                </div>
                                            </div>
                                        </div>





                                        <div class="row">

                                            <div class="col-md-12">


                                                <div class="table-responsive pomrnheadtffotsticky">
                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                        <thead>
                                                            <tr>
                                                                <th width="200px">Asset Name & Code</th>
                                                                <th width="500px">Sub Assets & Code</th>
                                                                <th width="100px">Quantity</th>
                                                                <th class="text-end">Current Value</th>
                                                                <th width="200px">Last Dep. Date</th>
                                                                <th class="text-end">{{ucfirst($data->document_type)}} Amount</th>
                                                                
                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrntableselectexcel">
                                                            @if($data->asset_details)
                                                                @foreach (json_decode($data->asset_details) as $key => $value)
                                                                   
                                                            <tr>
                                                               
                                                                <td class="poprod-decpt">  
                                                                        <input type="text" required class="form-control asset-search-input mw-100" value="{{$value->asset_code??null}}" readonly/>
                                                              
                                                                    </td>
                                                                      
                                                                <td class="poprod-decpt">
                                                                    <input type="text" required class="form-control subasset-search-input mw-100" value="{{$value->sub_asset_code??null}}" readonly/>
                                                                   <input type="hidden" name="sub_asset_id[]" class="sub_asset_id" data-id="1" id="sub_asset_id_1"/> 
                                                                </td>
                                                                <td><input type="number" name="quantity[]" id="quantity_1" readonly data-id="1" value="{{$value->quantity??null}}" readonly
                                                                        class="form-control mw-100 quantity" /></td>
                                                                <td class="text-end"><input type="text" name="currentvalue[]" id="currentvalue_1" data-id="1"
                                                                        class="form-control mw-100 text-end currentvalue indian-number" value="{{$value->currentvalue??null}}" readonly/>
                                                                </td>
                                                                
                                                                <td><input type="date" name="last_dep_date[]" id="last_dep_date_1" data-id="1"
                                                                    class="form-control mw-100 last_dep_date" value="{{$value->last_dep_date??null}}" readonly/>
                                                            </td>
                                                                <td><input type="text" value="{{$value->revaluate??null}}" readonly required name="revaluate_amount[]" id="revaluate_amount_1" data-id="1"
                                                                    class="form-control mw-100 text-end revaluate_amount indian-number" /></td>
                                                            </tr>


                                                            @endforeach
                                                            @endif
                                                        </tbody>


                                                    </table>
                                                </div>
                                            </div>

                                        </div>
                                      <div class="row mt-2"> 
                                                         
												 <div class="col-md-4">
                                                            <label class="form-label">Document</label>

                                                            <div class="d-flex align-items-center gap-2">
                                                                {{-- File input --}}
                                                                <input type="file" name="document" disabled class="form-control" id="documentInput" style="max-width: 85%;" />

                                                                {{-- Preview selected file or existing one --}}
                                                                <div id="filePreview">
                                                                    @if(!empty($data->document))
                                                                        {{-- Existing file icon --}}
                                                                        <div id="existingFilePreview">
                                                                            <a href="{{ asset('documents/' . $data->document) }}" target="_blank">
                                                                                <i data-feather="file-text" class="text-success"></i>
                                                                            </a>
                                                                        </div>
                                                                    @endif

                                                                    {{-- New file preview icon --}}
                                                                    <div id="newFilePreview" style="display: none;">
                                                                        <i data-feather="file" class="text-primary"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>


												<div class="col-md-12">
													<div class="mb-1">  
														<label class="form-label">Final Remarks</label> 
														<textarea type="text" rows="4" name="remarks" class="form-control" placeholder="Enter Remarks here..." readonly>{{$data->remarks??""}}</textarea> 

													</div>
												</div>

										   </div>
                                    </div>
                                </div>




                        </form>


                    </div>
            </div>
            <!-- Modal to add new record -->

            </section>


        </div>
    </div>
    </div>
    <!-- END: Content-->

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>


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
                                <input id = "voucher_date" class="form-control indian-number " disabled="" value="">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Currency <span class="text-danger">*</span></label>
                                <input id = "voucher_currency" class="form-control indian-number " disabled="" value="">
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
              <form class="ajax-input-form" method="POST" action="{{ route('finance.fixed-asset.revaluation-impairement.approval') }}" data-redirect="{{ route('finance.fixed-asset.revaluation-impairement.index') }}" enctype='multipart/form-data'>
                 @csrf
                 <input type="hidden" name="action_type" id="action_type">
                 <input type="hidden" name="id" value="{{$data->id ?? ''}}">
                 <div class="modal-header">
                    <div>
                       <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="popupTitle">
                          <span id="popupTitle"></span> Application
                       </h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                 </div>
                 <div class="modal-body pb-2">
                    <div class="row mt-1">
                       <div class="col-md-12">
                          <div class="mb-1">
                             <label class="form-label">Remarks {{-- <span class="text-danger">*</span> --}}</label>
                             <textarea name="remarks" class="form-control indian-number "></textarea>
                          </div>
                           <div class="row">
                    <div class = "col-md-8">
                        <div class="mb-1">
                            <label class="form-label">Upload Document</label>
                            <input type="file" id="ap_file" name = "attachment[]" multiple class="form-control cannot_disable" onchange = "addFiles(this, 'approval_files_preview');" max_file_count = "2"/>
                        </div>
                    </div>
                    <div class = "col-md-4" style = "margin-top:19px;">
                        <div class = "row" id = "approval_files_preview">

                        </div>
                    </div>
                  </div>
                  <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
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
  

     <div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body alertmsg text-center warning">
                    <i data-feather='alert-circle'></i>
                    <h2>Are you sure?</h2>
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>Asset Revaluation / Impairement</strong>? After Amendment this action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
      </div>
  
@endsection




@section('scripts')
  <script src="{{asset('assets/js/fileshandler.js')}}"></script>
    <script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })



        $(".mrntableselectexcel tr").click(function() {
            $(this).addClass('trselected').siblings().removeClass('trselected');
            value = $(this).find('td:first').html();
        });

        $(document).on('keydown', function(e) {
            if (e.which == 38) {
                $('.trselected').prev('tr').addClass('trselected').siblings().removeClass('trselected');
            } else if (e.which == 40) {
                $('.trselected').next('tr').addClass('trselected').siblings().removeClass('trselected');
            }
            $('.mrntableselectexcel').scrollTop($('.trselected').offset().top - 40);
        });

        $('#add_new_sub_asset').on('click', function() {
            const subAssetCode = $('#sub_asset_id').val();
            genereateSubAssetRow(subAssetCode);
        });



$('#fixed-asset-revaluation-impairement-form').on('submit', function(e) {
     document.getElementById('document_status').value = 'submitted';
            e.preventDefault(); // Always prevent default first
             updateJsonData();
                if(validateRevaluationAmounts())
                this.submit();
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

function initializeAssetAutocomplete(selector) {
    $(selector).autocomplete({
        source: function (request, response) {
            const category = $('#category').val();

            if (!category) {
                response([]); // Return an empty list to autocomplete
                return;
            }

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("finance.fixed-asset.asset-search") }}',
                type: 'POST',
                dataType: 'json',
                data: {
                    q: request.term,
                    ids:getAllAssetIds(),
                    category:category,
                },
                success: function (data) {
                    response(data.map(function (item) {
                        return {
                            label: item.asset_code + ' (' + item.asset_name + ')',
                            value: item.id,
                            asset: item
                        };
                    }));
                },
                error: function () {
                    response([]);
                }
            });
        },
        minLength: 0,
        select: function (event, ui) {
                const row = $(this).closest('tr');
        
             row.find('.sub_asset_id').val();
            row.find('.subasset-search-input').val('');
        row.find('.quantity').val('');
                row.find('.currentvalue').val('');
                row.find('.last_dep_date').val('');
               
            const asset = ui.item.asset;
            const rowId = row.data('id'); // assuming you set `data-id` on the <tr>

            // Set visible label and hidden ID
            $(this).val(ui.item.label);
            row.find('.asset_id').val(ui.item.value);

            return false;
        },
        change: function (event, ui) {
            const row = $(this).closest('tr');
            if (!ui.item) {

                $(this).val('');
                row.find('.asset_id').val('');
               
                row.find('.sub_asset_id').val();
                row.find('.subasset-search-input').val('');
                row.find('.quantity').val('');
                row.find('.currentvalue').val('');
                row.find('.last_dep_date').val('');
                refreshAssetSelects();
            }
        }
    }).focus(function () {
        if (this.value === '') {
            $(this).autocomplete('search');
        }
    });
}
function initializeSubAssetAutocomplete(selector) {
    $(selector).autocomplete({
        source: function (request, response) {
            let row = $(this.element).closest('tr'); 
            let assetId = row.find('.asset_id').val(); 
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("finance.fixed-asset.sub_asset_search") }}',
                type: 'POST',
                dataType: 'json',
                data: {
                    id: assetId,
                    q: request.term
                },
                success: function (data) {
                    response(data.map(function (item) {
                        return {
                            label: item.sub_asset_code,
                            value: item.id,
                            asset: item.asset,
                            sub_asset: item
                        };
                    }));
                },
                error: function () {
                    response([]);
                }
            });
        },
        minLength: 0,
        select: function (event, ui) {
            let row = $(this).closest('tr');
            let subAssetId = row.find('.sub_asset_id');
            let lastdep = row.find('.last_dep_date');

            const asset = ui.item.asset;
            const sub_asset = ui.item.sub_asset;

            $(this).val(ui.item.label);
            subAssetId.val(ui.item.value);
            lastdep.val("");

            if (asset.last_dep_date !== asset.capitalize_date) {
                let lastDepDate = new Date(asset.last_dep_date);
                lastDepDate.setDate(lastDepDate.getDate() - 1);
                let formattedDate = lastDepDate.toISOString().split('T')[0];
                lastdep.val(formattedDate);
            }
            row.find('.quantity').val(1);
            row.find('.currentvalue').val(sub_asset.current_value_after_dep);

            return false;
        },
        change: function (event, ui) {
            let row = $(this).closest('tr');
            let subAssetId = row.find('.sub_asset_id');
            let lastdep = row.find('.last_dep_date');

            if (!ui.item) {
                $(this).val('');
                subAssetId.val("");
                lastdep.val("");
                row.find('.quantity').val('');
                row.find('.currentvalue').val('');
            }
        },
        focus: function () {
            return false;
        }
    }).focus(function () {
        if (this.value === '') {
            $(this).autocomplete('search');
        }
    });
}



   initializeAssetAutocomplete('.asset-search-input');
   initializeSubAssetAutocomplete('.subasset-search-input');
        
 $('.select2').select2();

            
 
let rowCount = 1;

$('#addNewRowBtn').on('click', function () {
    rowCount++;
    let newRow = `
    <tr>
        <td class="customernewsection-form">
            <div class="form-check form-check-primary custom-checkbox">
                <input type="checkbox" class="form-check-input row-check" id="Email_${rowCount}">
                <label class="form-check-label" for="Email_${rowCount}"></label>
            </div>
        </td>
        <td class="poprod-decpt">   
            <input type="text" class="form-control asset-search-input mw-100" required />
            <input type="hidden" name="asset_id[]" class="asset_id" data-id="${rowCount}" id="asset_id_${rowCount}"/> 
         </td>
        <td class="poprod-decpt">
            <input type="text" required class="form-control subasset-search-input mw-100"/>
            <input type="hidden" name="sub_asset_id[]" class="sub_asset_id" data-id="${rowCount}" id="sub_asset_id_${rowCount}"/> 
        </td>
        <td><input type="number" name="quantity[]" id="quantity_${rowCount}" readonly data-id="${rowCount}"
            class="form-control mw-100 quantity" /></td>
        <td><input type="text" name="currentvalue[]" id="currentvalue_${rowCount}" data-id="${rowCount}"
            class="form-control mw-100 text-end currentvalue" readonly /></td>
          
        <td><input type="date" name="last_dep_date[]" id="last_dep_date_${rowCount}" data-id="${rowCount}"
            class="form-control mw-100 last_dep_date" readonly /></td>
             <td><input type="number" step="2" required name="revaluate_amount[]" id="revaluate_amount_${rowCount}" data-id="${rowCount}"
            class="form-control mw-100 text-end revaluate_amount"/></td>
    </tr>
    `;

    $('.mrntableselectexcel').append(newRow);
    $(".select2").select2();
    refreshAssetSelects();
    initializeAssetAutocomplete('.asset-search-input');
    initializeSubAssetAutocomplete('.subasset-search-input');
});
function refreshAssetSelects() {
    let selectedAssets = [];

// Collect all selected asset values
$('.asset_id').each(function () {
    let val = $(this).val();
    if (val) {
        selectedAssets.push(val);
    }
});

// Disable already selected options in other selects
$('.asset_id').each(function () {
    let currentSelect = $(this);
    let currentVal = currentSelect.val();
   currentSelect.find('option').each(function () {
        let optionVal = $(this).val();
        if (optionVal === "") return; // skip placeholder
        if (selectedAssets.includes(optionVal) && optionVal !== currentVal) {
            $(this).prop('disabled', true);
        } else {
            $(this).prop('disabled', false);
        }
    });
});

}

$('#delete').on('click', function () {
    let $rows = $('.mrntableselectexcel tr');
    let $checked = $rows.find('.row-check:checked');

    // Prevent deletion if only one row exists
    if ($rows.length <= 1) {
        showToast('error','At least one row is required.');
        return;
    }

    // Prevent deletion if checked rows would remove all
    if ($rows.length - $checked.length < 1) {
        showToast('error','You must keep at least one row.');
        return;
    }

    // Remove only the checked rows
    $checked.closest('tr').remove();

});
$('#checkAll').on('change', function () {
    let isChecked = $(this).is(':checked');
    $('.mrntableselectexcel .row-check').prop('checked', isChecked);
});

$('#location').on('change', function () {
   // add_blank();
    var locationId = $(this).val();

    if (locationId) {
                var selectedCostCenterId = '{{ $data->cost_center_id ?? '' }}'; // Use null coalescing for safety

        // Build the route manually
        var url = '{{ route("cost-center.get-cost-center", ":id") }}'.replace(':id', locationId);

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
               // $('#cost_center').trigger('change'); // Trigger change to load categories
                
                
                
            }
            },
            error: function () {
                $('#cost_center').empty();
            }
        });
    } else {
        $('#cost_center').empty();
    }
});
$('#cost_center').on('change', function () {
  //  add_blank(); // Custom function, assuming you're resetting rows

    var costCenterId = $(this).val();
    var locationId = $('#location').val();

    if (locationId && costCenterId) {
        // Use Blade to render the correct route with parameters
        var url = '{{ route("finance.fixed-asset.get-categories") }}';

        $.ajax({
            url: url,
            type: 'GET',
            data: {
                cost_center_id: costCenterId,
                location_id: locationId
            },
            dataType: 'json',
            success: function (data) {
                $('#category').empty().append('<option value="">Select Category</option>');
                $.each(data, function (key, value) {
                    $('#category').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            },
            error: function () {
                $('#category').empty();
            }
        });
    } else {
        $('#category').empty();
    }
});

$('#location').trigger('change');

function getAllAssetIds() {
    let assetIds = [];

    $('.asset_id').each(function () {
        let val = $(this).val();
        if (val) {
            assetIds.push(parseFloat(val));
        }
    });

    return assetIds;
}
    function updateSelectedRadioLabel() {
            const selected = document.querySelector('input[name="document_type"]:checked');
            if (selected) {
                const label = document.querySelector(`label[for="${selected.id}"]`);
                if (label) {
                    document.getElementById("selectedRadioText").textContent = label.textContent.trim();
                    if (label.textContent.trim() == "Writeoff") {
                        $('.revaluate_amount').each(function() {
                            $(this).val(0).prop('readonly', true);
                        });
                    } else {
                        $('.revaluate_amount').each(function() {
                            $(this).val(0).prop('readonly', false);
                        });
                    }
                }
            }
        }
    // On radio change
    document.querySelectorAll('input[name="document_type"]').forEach(radio => {
        radio.addEventListener('change', updateSelectedRadioLabel);
    });

    // Initial update on page load
    document.addEventListener("DOMContentLoaded", updateSelectedRadioLabel);
     function getSelectedDocumentType() {
        const selected = document.querySelector('input[name="document_type"]:checked');
        return selected ? selected.value : null;
    }
function validateRevaluationAmounts(showErrors = true) {
    const documentType = getSelectedDocumentType();
    let isValid = true;

    document.querySelectorAll('.revaluate_amount').forEach(input => {
        const row = input.closest('tr');
        const currentValueInput = row.querySelector('.currentvalue');

        if (currentValueInput.value.trim() === "" && input.value.trim() === "") return;

        const currentVal = parseFloat(currentValueInput.value) || 0;
        const revalVal = parseFloat(input.value) || 0;

        //input.classList.remove('is-invalid');

        if (documentType === 'revaluation' && revalVal <= currentVal) {
            isValid = false;
            //input.classList.add('is-invalid');
            if (showErrors) {
                showToast('error', 'Revaluation amount must be greater than current value.');
            }
        } else if (documentType === 'impairement' && revalVal >= currentVal) {
            isValid = false;
           // input.classList.add('is-invalid');
            if (showErrors) {
                showToast('error', 'Impairement amount must be less than current value.');
            }
        }
    });

    return isValid;
}

  
    function updateJsonData(){
          const allRows = [];

    $('.mrntableselectexcel tr').each(function () {
        const row = $(this);
        const rowId = row.find('.asset_id').attr('data-id');
        let sub_asset_codes = [];
        row.find(`#sub_asset_id_${rowId} option:selected`).each(function () {
            sub_asset_codes.push($(this).text());
        });

        const rowData = {
            asset_id: row.find(`#asset_id_${rowId}`).val(),
            sub_asset_id: row.find(`#sub_asset_id_${rowId}`).val(), // array from select2
            quantity: row.find(`#quantity_${rowId}`).val(),
            sub_asset_code :sub_asset_codes,
            currentvalue: row.find(`#currentvalue_${rowId}`).val(),
            revaluate: row.find(`#revaluate_amount${rowId}`).val(),
            last_dep_date: row.find(`#last_dep_date_${rowId}`).val(),
        };

        allRows.push(rowData);
    });

    $('#asset_details').val(JSON.stringify(allRows));
    }
    $('#category').on('change', function() {
        //add_blank();
        });
        function add_blank(){
    $('.mrntableselectexcel').empty();
                let blank_row = `<tr class="trselected" data-id="${rowCount}">
        <td class="customernewsection-form">
            <div class="form-check form-check-primary custom-checkbox">
                <input type="checkbox" class="form-check-input row-check" id="Email_${rowCount}">
                <label class="form-check-label" for="Email_${rowCount}"></label>
            </div>
        </td>
        <td class="poprod-decpt">   
            <input type="text" class="form-control asset-search-input mw-100" required />
            <input type="hidden" name="asset_id[]" class="asset_id" data-id="${rowCount}" id="asset_id_${rowCount}"/> 
         </td>
        <td class="poprod-decpt">
            <input type="text" required class="form-control subasset-search-input mw-100"/>
            <input type="hidden" name="sub_asset_id[]" class="sub_asset_id" data-id="${rowCount}" id="sub_asset_id_${rowCount}"/> 
        </td>
        <td><input type="number" name="quantity[]" id="quantity_${rowCount}" readonly data-id="${rowCount}"
            class="form-control mw-100 quantity" /></td>
        <td><input type="text" name="currentvalue[]" id="currentvalue_${rowCount}" data-id="${rowCount}"
            class="form-control mw-100 text-end currentvalue" readonly /></td>
  
        <td><input type="date" name="last_dep_date[]" id="last_dep_date_${rowCount}" data-id="${rowCount}"
            class="form-control mw-100 last_dep_date" readonly /></td>
             <td><input type="number" step="2" required name="revaluate_amount[]" id="revaluate_amount_${rowCount}" data-id="${rowCount}"
            class="form-control mw-100 text-end revaluate_amount"/></td>
    </tr>`;
    $('.mrntableselectexcel').append(blank_row);
     initializeAssetAutocomplete('.asset-search-input');
   initializeSubAssetAutocomplete('.subasset-search-input');

}
$(document).on('input change', '.revaluate_amount', function() {
    validateRevaluationAmounts();
});
$(document).on('input change', '[name="document_type"]', function() {
    validateRevaluationAmounts();
});
    $(document).on('input change', '.currentvalue', function() {
        validateRevaluationAmounts();
    });
     $(document).on('click', '#approved-button', (e) => {
            let actionType = 'approve';
            $("#approveModal").find("#action_type").val(actionType);
            $("#approveModal").find("#popupTitle").text('Approve');
            $("#approveModal").modal('show');
            });

            $(document).on('click', '#reject-button', (e) => {
            let actionType = 'reject';
            $("#approveModal").find("#action_type").val(actionType);
            $("#approveModal").find("#popupTitle").text('Reject');
            $("#approveModal").modal('show');
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
            const apiURL = "{{route('finance.fixed-asset.revaluation-impairement.posting.get')}}";
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
                            <td class="indian-number fw-bolder text-dark">${voucherDetail.ledger_group_code ? voucherDetail.ledger_group_code : ''}</td>
                            <td>${voucherDetail.ledger_code ? voucherDetail.ledger_code : ''}</td>
                            <td>${voucherDetail.ledger_name ? voucherDetail.ledger_name : ''}</td>
                            <td class="indian-number text-end">${voucherDetail.debit_amount > 0 ? parseFloat(voucherDetail.debit_amount).toFixed(2) : ''}</td>
                            <td class="indian-number text-end">${voucherDetail.credit_amount > 0 ? parseFloat(voucherDetail.credit_amount).toFixed(2) : ''}</td>
                            </tr>
                            `
                        });
                    });
                    voucherEntriesHTML+= `
                    <tr>
                        <td colspan="4" class="fw-bolder text-dark text-end">Total</td>
                        <td class="indian-number fw-bolder text-dark text-end">${voucherEntries.total_debit.toFixed(2)}</td>
                        <td class="indian-number fw-bolder text-dark text-end">${voucherEntries.total_credit.toFixed(2)}</td>
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
            const postingApiUrl = "{{ route('finance.fixed-asset.revaluation-impairement.post') }}";

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
                            location.href = '{{route("finance.fixed-asset.revaluation-impairement.index")}}';
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


$('#ap_file').prop('disabled', false).prop('readonly', false);
        $('#revisionNumber').prop('disabled', false).prop('readonly', false);
        const amendmentRoute = "{{ route('finance.fixed-asset.revaluation-impairement.edit',$data->id) }}";
        
$(document).on('click', '#amendmentSubmit', (e) => {
            // let actionUrl = "{{ route('finance.fixed-asset.revaluation-impairement.amendment', $data->id) }}";
            // fetch(actionUrl).then(response => {
            //     return response.json().then(data => {
            //         if (data.status == 200) {
            //             Swal.fire({
            //                     title: 'Success!',
            //                     text: data.message,
            //                     icon: 'success'
            //                 }).then(() => {
            //                     window.location.href = "{{ route('finance.fixed-asset.revaluation-impairement.edit', $data->id) }}";
            //                 });
            
            //         } else {
            //             Swal.fire({
            //                 title: 'Error!',
            //                 text: data.message,
            //                 icon: 'error'
            //             });
            //             $('#amendmentconfirm').modal('hide');
            //         }
            //     });
            // });
                e.preventDefault();
                $('.preloader').show();
                let url = new URL(amendmentRoute, window.location.origin); // full absolute URL
                url.searchParams.set('amendment', 1);
                window.location.href = url.toString();

});
// # Revision Number On Chage
$(document).on('change', '#revisionNumber', (e) => {
    let actionUrl = location.pathname + '?revisionNumber='+e.target.value;
    let revision_number = Number("{{$revision_number}}");
    let revisionNumber = Number(e.target.value);
    if(revision_number == revisionNumber) {
        location.href = actionUrl;
    } else {
        window.open(actionUrl, '_blank');
    }
});
    </script>
    <!-- END: Content-->
@endsection
