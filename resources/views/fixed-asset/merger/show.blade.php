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
                                <h2 class="content-header-title float-start mb-0">Merger</h2>
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
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <div class="form-group breadcrumb-right">
                                <a href="{{ route('finance.fixed-asset.merger.index') }}"> <button
                                    class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</button>
                            </a>
                                @if($buttons['approve'])
                                <button type="button" class="btn btn-primary btn-sm" id="approved-button" name="action" value="approved"><i data-feather="check-circle"></i> Approve</button>
                                <button type="button" id="reject-button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg> Reject</button>
                        @endif
                        @if($buttons['amend'])
                        <button type="button" data-bs-toggle="modal" data-bs-target="#amendmentconfirm" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i> Amendment</button>
                        @endif
                        @if($buttons['post'])
                        <button id="postButton" onclick="onPostVoucherOpen();" type="button" class="btn btn-warning btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Post</button>
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
                        <form>

                            @csrf
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
                                                        <input type="date" class="form-control indian-number" id="document_date"
                                                            name="document_date" value="{{ $data->document_date }}" readonly required>
                                                    </div>
                                                </div>
                                                  <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Category <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="old_category_id" disabled
                                                            id="old_category" required>
                                                            @foreach ($categories as $category)
                                                                <option value="{{ $category->id }}"
                                                                    {{$data->old_category_id == $category->id ? 'selected' : '' }}>
                                                                    {{ $category->name }}
                                                                </option>
                                                            @endforeach
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


                                        <div class="border-bottom mb-2 pb-25">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader ">
                                                        <h4 class="card-title text-theme">Select Assets</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                                <div hidden class="col-md-6 text-sm-end">
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
                                                                <th class="text-end">Salvage Value</th>
                                                                <th width="200px">Last Dep. Date</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrntableselectexcel">
                                                            @foreach(json_decode($data->asset_details) as $key => $assetRow)
                                                            <tr>
                                                                <td class="indian-number poprod-decpt">
                                                                    <select name="asset_id[]" class="form-control indian-number select2 asset_id" required disabled data-id="{{ $key }}">
                                                                        <option value="">Select</option>
                                                                        @foreach ($assets as $asset)
                                                                            <option value="{{ $asset->id }}" 
                                                                                {{ $asset->id == $assetRow->asset_id ? 'selected' : '' }}>
                                                                                {{ $asset->asset_code }} ({{ $asset->asset_name }})
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td class="indian-number poprod-decpt">
                                                                    <select name="sub_asset_id[{{ $key }}][]" class="form-select select2 sub_asset_id" multiple disabled required data-id="{{ $key }}">
                                                                        @php
                                                                            $selectedSubAssets = $assetRow->sub_asset_code ?? [];
                                                                        @endphp
                                                                        @foreach ($selectedSubAssets as $subAsset)
                                                                            <option selected>
                                                                                {{ $subAsset }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>{{ $assetRow->quantity }}</td>
                                                                <td class="indian-number text-end">{{ $assetRow->currentvalue }}</td>
                                                                <td class="indian-number text-end">{{ $assetRow->salvagevalue ??"" }}</td>
                                                                
                                                                <td>{{ $assetRow->last_dep_date ? \Carbon\Carbon::parse($assetRow->last_dep_date)->format('d-m-Y') : '' }}</td>
                                                            </tr>
                                                            @endforeach
                                                            </tbody>
                                                            

                                                    </table>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>



                                <div class="row customernewsection-form">
                                    <div class="col-md-12">
                                        <div class="card quation-card">
                                            <div class="card-header newheader">
                                                <div>
                                                    <h4 class="card-title">Asset Details</h4>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Category <span
                                                                    class="text-danger">*</span></label>
                                                                    <select class="form-select select2" name="category_id"
                                                                    id="category" required disabled>
                                                                        <option value="{{ $data->category_id }}">
                                                                            {{ $data?->category?->name }}
                                                                        </option>
                                                                </select>
                                                                    </div>
                                                    </div>
                                                     <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">IT Act Category <span
                                                                    class="text-danger"></span></label>
                                                            <select class="form-select select2" name="it_category_id" disabled
                                                                id="it_category">
                                                                <option value=""
                                                                    {{ old('it_category') ? '' : 'selected' }}>
                                                                    Select</option>
                                                                @foreach ($it_categories as $it_category)
                                                                    <option value="{{ $it_category->id }}"
                                                                        {{ $data->it_category_id == $it_category->id ? 'selected' : '' }}>
                                                                        {{ $it_category->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Asset Name <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control indian-number" name="asset_name" readonly
                                                                id="asset_name"
                                                                value="{{ $data->asset_name }}" required />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Asset Code <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="asset_code"
                                                                id="asset_code" value="{{$data->asset_code}}" readonly
                                                                required />
                                                        </div>
                                                    </div>


                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Quantity <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="quantity"
                                                                id="quantity" value="{{$data->quantity}}" readonly />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Ledger <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select select2" name="ledger_id"
                                                                id="ledger" required disabled>
                                                                    <option value="{{ $data->ledger_id }}">
                                                                        {{ $data?->ledger?->name }}
                                                                    </option>
                                                            </select>

                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Ledger Group <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select select2" name="ledger_group_id"
                                                                id="ledger_group" required disabled>
                                                                    <option value="{{ $data->ledger_group_id }}">
                                                                        {{ $data?->ledgerGroup?->name }}
                                                                    </option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Capitalize Date <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="date" class="form-control indian-number"
                                                                name="capitalize_date" id="capitalize_date"
                                                                value="{{$data->capitalize_date}}"  readonly required />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Maint. Schedule <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select" name="maintenance_schedule"
                                                                id="maintenance_schedule" disabled required>
                                                                <option value=""
                                                                    {{ $data->maintenance_schedule == '' ? 'selected' : '' }}>
                                                                    Select</option>
                                                                <option value="weekly"
                                                                    {{ $data->maintenance_schedule == 'weekly' ? 'selected' : '' }}>
                                                                    Weekly</option>
                                                                <option value="monthly"
                                                                    {{ $data->maintenance_schedule == 'monthly' ? 'selected' : '' }}>
                                                                    Monthly</option>
                                                                <option value="quarterly"
                                                                    {{ $data->maintenance_schedule == 'quarterly' ? 'selected' : '' }}>
                                                                    Quarterly</option>
                                                                <option value="semi-annually"
                                                                    {{ $data->maintenance_schedule == 'semi-annually' ? 'selected' : '' }}>
                                                                    Semi-Annually</option>
                                                                <option value="annually"
                                                                    {{ $data->maintenance_schedule == 'annually' ? 'selected' : '' }}>
                                                                    Annually</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Dep. Method <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" name="depreciation_method"
                                                                id="depreciation_method" class="form-control indian-number"
                                                                value="{{$data->depreciation_method}}" readonly />
                                                        </div>
                                                    </div>


                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Est. Useful Life (yrs) <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="useful_life"
                                                                id="useful_life" value="{{$data->useful_life}}" disabled required />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Salvage Value <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control indian-number"
                                                                name="salvage_value" id="salvage_value" readonly
                                                                value="{{$data->salvage_value}}" required />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Dep % <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="number" class="form-control indian-number"
                                                                id="depreciation_rate" value="{{$data->depreciation_percentage}}" name="depreciation_percentage"
                                                                readonly />
                                                            

                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Total Dep. <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="number" id="total_depreciation"
                                                                name="total_depreciation" class="form-control indian-number"
                                                                value="{{$data->total_depreciation}}" readonly />
                                                        </div>
                                                    </div>




                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Current Value <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control indian-number" required
                                                                name="current_value" id="current_value"
                                                                value="{{$data->current_value}}" readonly />
                                                        </div>
                                                    </div>
                                               
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
                                <input id = "voucher_book_code" class="form-control indian-number" disabled="" >
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
                                <input id = "voucher_date" class="form-control indian-number" disabled="" value="">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Currency <span class="text-danger">*</span></label>
                                <input id = "voucher_currency" class="form-control indian-number" disabled="" value="">
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
              <form class="ajax-input-form" method="POST" action="{{ route('finance.fixed-asset.merger.approval') }}" data-redirect="{{ route('finance.fixed-asset.merger.index') }}" enctype='multipart/form-data'>
                 @csrf
                 <input type="hidden" name="action_type" id="action_type">
                 <input type="hidden" name="id" value="{{$data->id ?? ''}}">
                 <div class="modal-header">
                    <div>
                       <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="popupTitle">
                          <span id="action"></span> Application
                       </h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                 </div>
                 <div class="modal-body pb-2">
                    <div class="row mt-1">
                       <div class="col-md-12">
                          <div class="mb-1">
                             <label class="form-label">Remarks {{-- <span class="text-danger">*</span> --}}</label>
                             <textarea name="remarks" class="form-control indian-number"></textarea>
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
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>Asset Merger</strong>? After Amendment this action cannot be undone.</p>
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
 $('.select2').select2();
 $(document).on('click', '#approved-button', (e) => {
            let actionType = 'approve';
            $("#approveModal").find("#action_type").val(actionType);
            $("#approveModal").find("#action").text("Approve");
           
            $("#approveModal").modal('show');
            });

            $(document).on('click', '#reject-button', (e) => {
            let actionType = 'reject';
            $("#approveModal").find("#action_type").val(actionType);
            $("#approveModal").find("#action").text("Reject");
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
            const apiURL = "{{route('finance.fixed-asset.merger.posting.get')}}";
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
            const postingApiUrl = "{{ route('finance.fixed-asset.merger.post') }}";

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
                            location.href = '{{route("finance.fixed-asset.merger.index")}}';
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
   const amendmentRoute = "{{ route('finance.fixed-asset.merger.edit',$data->id) }}";
              
$(document).on('click', '#amendmentSubmit', (e) => {
            // let actionUrl = "{{ route('finance.fixed-asset.merger.amendment', $data->id) }}";
            // fetch(actionUrl).then(response => {
            //     return response.json().then(data => {
            //         if (data.status == 200) {
            //             Swal.fire({
            //                     title: 'Success!',
            //                     text: data.message,
            //                     icon: 'success'
            //                 }).then(() => {
            //                     window.location.href = "{{ route('finance.fixed-asset.merger.edit', $data->id) }}";
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
                $('#cost_center').empty();
            }
        });
    } else {
        $('#cost_center').empty();
    }
});

$('#location').trigger('change');

        $(document).on('click', '.mrntableselectexcel tr', function() {
    $(this).addClass('trselected').siblings().removeClass('trselected');
});


    </script>
    <!-- END: Content-->
@endsection
