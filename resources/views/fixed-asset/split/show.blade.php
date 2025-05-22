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
                                <h2 class="content-header-title float-start mb-0">Split</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">View Detail</li>


                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <a href="{{ route('finance.fixed-asset.split.index') }}"> <button
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
                        <form id="fixed-asset-split-form" >

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
                                                        <input type="date" class="form-control indian-number " id="document_date"
                                                            name="document_date" value="{{ $data->document_date }}" readonly required>
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


                                <div class="row customernewsection-form">
                                    <div class="col-md-12">
                                        <div class="card quation-card">
                                            <div class="card-header newheader">
                                                <div>
                                                    <h4 class="card-title">Old Asset Details</h4>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <!-- Asset Code & Name -->
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="asset_id">Asset Code &
                                                                Name <span class="text-danger">*</span></label>
                                                            <select id="asset_id" name="asset_id"
                                                                class="form-control indian-number  mw-100 p_ledgerselecct" disabled required>
                                                                 <option value="{{ $data->asset_id }}">
                                                                        {{ $data?->asset?->asset_code }} ({{ $data?->asset?->asset_name }})
                                                                    </option>
                                                             
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <!-- Sub-Asset Code -->
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="sub_asset_id">Sub-Asset Code
                                                                <span class="text-danger">*</span></label>
                                                            <select id="sub_asset_id" name="sub_asset_id"
                                                                class="form-control indian-number  mw-100 c_ledgerselecct" disabled required>
                                                                <option value="{{ $data->sub_asset_id }}">
                                                                    {{ $data?->subAsset?->sub_asset_code }}
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <!-- Last Date of Dep. -->
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="last_dep_date">Last Date of
                                                                Dep. <span class="text-danger">*</span></label>
                                                            <input type="date" id="last_dep_date" disabled value="{{$data?->asset?->last_dep_date}}" name="last_dep_date"
                                                                class="form-control indian-number " required />
                                                        </div>
                                                    </div>

                                                    <!-- Current Value -->
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="current_value_asset">Current Value
                                                                <span class="text-danger">*</span></label>
                                                            <input type="text" id="current_value_asset" name="current_value_asset" value="{{$data?->subAsset?->current_value_after_dep}}"
                                                                class="form-control indian-number " disabled required />
                                                        </div>
                                                    </div>
                                                </div>



                                            </div>
                                        </div>

                                    </div>
                                </div>



                                <div class="card">


                                    <div class="card-body customernewsection-form">


                                        <div class="border-bottom mb-2 pb-25">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader ">
                                                        <h4 class="card-title text-theme">New Asset Detail</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                                <div  hidden class="col-md-6 text-sm-end">
                                                    <a href="#" id="delete_new_sub_asset"
                                                        class="btn btn-sm btn-outline-danger me-50">
                                                        <i data-feather="x-circle"></i> Delete</a>
                                                    <a href="#" id= "add_new_sub_asset"
                                                        class="btn btn-sm btn-outline-primary">
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
                                                                <th hidden class="customernewsection-form">
                                                                    <div
                                                                        class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox" disabled class="form-check-input"
                                                                            id="Email">
                                                                        <label class="form-check-label"
                                                                            for="Email"></label>
                                                                    </div>
                                                                </th>
                                                                <th width="200">Asset Code</th>
                                                                <th>Asset Name</th>
                                                                <th width="200">Sub Asset Code</th>
                                                                <th width="100">Quantity</th>
                                                                <th class="text-end">Current Value</th>
                                                                <th class="text-end">Salvage Value</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrntableselectexcel">
                                                            @foreach (json_decode($data->sub_assets) as $subAsset)
                                                            <tr>
                                                                <td  hidden class="customernewsection-form">
                                                                    <div class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox" disabled class="form-check-input row-check">
                                                                        <label class="form-check-label"></label>
                                                                    </div>
                                                                </td>
                                                                <td class="indian-number poprod-decpt">
                                                                    <input type="text" required disabled placeholder="Enter" value="{{$subAsset->asset_code}}" class="form-control indian-number  mw-100 mb-25 asset-code-input" />
                                                                </td>
                                                                <td class="indian-number poprod-decpt">
                                                                    <input type="text" required disabled placeholder="Enter" value="{{$subAsset->asset_name}}" class="form-control indian-number  mw-100 mb-25 asset-name-input" />
                                                                </td>
                                                                <td class="indian-number poprod-decpt">
                                                                    <input type="text" required disabled placeholder="Enter" value="{{$subAsset->sub_asset_id}}" class="form-control indian-number  mw-100 mb-25 sub-asset-code-input" />
                                                                </td>
                                                                <td>
                                                                    <input type="text" required disabled  value="1" class="form-control indian-number  mw-100 quantity-input" />
                                                                </td>
                                                                <td>
                                                                    <input type="text" required disabled class="form-control indian-number  mw-100 text-end current-value-input" value="{{$subAsset->current_value}}" />
                                                                </td>
                                                                <td>
                                                                    <input type="text" required disabled value="{{$subAsset->salvage_value??""}}" class="form-control indian-number  mw-100 text-end salvage-value-input" min="1"  />
                                                                </td>
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
                                                            <label class="form-label">Quantity <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control indian-number " name="quantity"
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
                                                            <input type="date" class="form-control indian-number "
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
                                                                id="depreciation_method" class="form-control indian-number "
                                                                value="{{$data->depreciation_method}}" readonly />
                                                        </div>
                                                    </div>


                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Est. Useful Life (yrs) <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control indian-number " name="useful_life"
                                                                id="useful_life" value="{{$data->useful_life}}" disabled required />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Salvage Value <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control indian-number "
                                                                name="salvage_value" id="salvage_value" readonly
                                                                value="{{$data->salvage_value}}" required />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Dep % <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="number" class="form-control indian-number "
                                                                id="depreciation_rate" value="{{$data->depreciation_percentage}}" name="depreciation_percentage"
                                                                readonly />
                                                            

                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Total Dep. <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="number" id="total_depreciation"
                                                                name="total_depreciation" class="form-control indian-number "
                                                                value="{{$data->total_depreciation}}" readonly />
                                                        </div>
                                                    </div>




                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Current Value <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control indian-number " required
                                                                name="current_value" id="current_value"
                                                                value="{{$data->current_value}}" readonly />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>





                            </div>
                        </form>
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
                                <input id = "voucher_book_code" class="form-control indian-number " disabled="" >
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Voucher No <span class="text-danger">*</span></label>
                                <input id = "voucher_doc_no" class="form-control indian-number " disabled="" value="">
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
              <form class="ajax-input-form" method="POST" action="{{ route('finance.fixed-asset.split.approval') }}" data-redirect="{{ route('finance.fixed-asset.split.index') }}" enctype='multipart/form-data'>
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
                             <textarea name="remarks" class="form-control indian-number "></textarea>
                          </div>
                          <div class="mb-1">
                             <label class="form-label">Upload Document</label>
                             <input type="file" id="ap_file" name="attachment[]" multiple class="form-control indian-number " />
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
  

     <div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body alertmsg text-center warning">
                    <i data-feather='alert-circle'></i>
                    <h2>Are you sure?</h2>
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>Asset Split</strong>? After Amendment this action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
      </div>
    
@endsection




@section('scripts')
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

        function genereateSubAssetRow(code) {
            let Current = $('#current_value_asset').val();
            let subAssetId = $('#sub_asset_id').val();
            let assetId = $('#asset_id').val();
            let newRow = '';
            newRow = `
                <tr class="trselected">
                <td class="indian-number customernewsection-form">
                    <div class="form-check form-check-primary custom-checkbox">
                        <input type="checkbox" disabled class="form-check-input row-check">
                        <label class="form-check-label"></label>
                    </div>
                </td>
                <td class="indian-number poprod-decpt">
                    <input type="text" required placeholder="Enter" class="form-control indian-number  mw-100 mb-25 asset-code-input" />
                </td>
                <td class="indian-number poprod-decpt">
                    <input type="text" required placeholder="Enter" class="form-control indian-number  mw-100 mb-25 asset-name-input" />
                </td>
                <td class="indian-number poprod-decpt">
                    <input type="text" required placeholder="Enter" disabled class="form-control indian-number  mw-100 mb-25 sub-asset-code-input" />
                </td>
                <td>
                    <input type="text" required disabled value="1" class="form-control indian-number  mw-100 quantity-input" />
                </td>
                <td>
                    <input type="text" required class="form-control indian-number  mw-100 text-end current-value-input" max="${Current}" />
                </td>
            </tr>


                `;
                    $(".mrntableselectexcel tr").removeClass('trselected');
                    $('.mrntableselectexcel').append(newRow);
                    updateSubAssetCodes();
        }


        $('#delete_new_sub_asset').on('click', function() {
            $('.mrntableselectexcel .row-check:checked').closest('tr').remove();
            updateSubAssetCodes();
        });

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
                        showToast('error', data.message);
                    }
                });
            });
        });
       // $('#book_id').trigger('change');

        $('#fixed-asset-split-form').on('submit', function(e) {
    e.preventDefault(); // Always prevent default first

    collectSubAssetDataToJson();
    document.getElementById('document_status').value = 'submitted';

    let currentValueAsset = parseFloat($('#current_value_asset').val()) || 0;
    let totalCurrentValue = parseFloat($('#current_value').val()) || 0;

    if (totalCurrentValue > currentValueAsset) {
        showToast('error', 'Total Current Value cannot be greater than Asset Current Value.');
        return false;
    } else if (totalCurrentValue <= 0) {
        showToast('error', 'Total Current Value must be greater than 0.');
        return false;
    }

    // Submit form manually if validation passes
    this.submit();
});


        $(document).ready(function() {

            // On Asset change, get sub-assets
            $('#asset_id').on('change', function() {
                let assetId = $(this).val();
                $('#sub_asset_id').html('<option value="">Loading...</option>');

                $.ajax({
                    url: '{{ route('finance.fixed-asset.sub_asset') }}', // Update this route
                    type: 'GET',
                    data: {
                        id: assetId
                    },
                    success: function(response) {
                        $('#sub_asset_id').html('<option value="">Select</option>');
                        $.each(response, function(key, subAsset) {
                            $('#sub_asset_id').append(
                                '<option value="' + subAsset.id + '">' + subAsset
                                .sub_asset_code + '</option>'
                            );
                        });

                        $('#category').val(response[0].asset.category_id).trigger('change');
                        $('#ledger').val(response[0].asset.ledger_id).trigger('change');
                        $('#ledger_group').val(response[0].asset.ledger_group_id).trigger(
                            'change');
                        $('#last_dep_date').val(response[0].asset.last_dep_date);
                        let lastDepDate = new Date(response[0].asset.last_dep_date);

                        // Add 1 day
                        lastDepDate.setDate(lastDepDate.getDate() + 1);

                        // Format as YYYY-MM-DD
                        let nextDate = lastDepDate.toISOString().split('T')[0];

                        $('#last_dep_date').val(response[0].asset.last_dep_date);
                        $('#capitalize_date').val(nextDate);
                        $('#depreciation_rate').val(response[0].asset.depreciation_percentage);
                        $('#depreciation_rate_year').val(response[0].asset
                            .depreciation_percentage_year);
                        $('#useful_life').val(response[0].asset.useful_life);
                        $('#maintenance_schedule').val(response[0].asset.maintenance_schedule);
                        $('#salvage_value').val(response[0].asset.salvage_value);
                    },
                    error: function() {
                        showToast('error', 'Failed to load sub-assets.');
                    }
                });
                $('.mrntableselectexcel').empty();
                   
            });

            // On Sub-Asset change, get value and last dep date
            $('#sub_asset_id').on('change', function() {
                let subAssetId = $(this).val();
                let assetId = $('#asset_id').val();

                $.ajax({
                    url: '{{ route('finance.fixed-asset.sub_asset_details') }}', // Update this route
                    type: 'GET',
                    data: {
                        id: assetId,
                        sub_asset_id: subAssetId
                    },
                    success: function(response) {
                        $('#current_value_asset').val(response.current_value_after_dep);
                        $('#last_dep_date').val(response.asset.last_dep_date);
                        
                    },
                    error: function() {
                        showToast('error', 'Failed to load sub-asset details.');
                    }
                });
                $('.mrntableselectexcel').empty();
                    
            });

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

        // Function to update sub-asset codes based on current asset codes in all rows
        function updateSubAssetCodes() {
    const assetCodeCounts = {};
    const assetCodeToName = {}; // Store the first encountered name for each asset code

    let totalQuantity = 0;
    let totalCurrentValue = 0;

    $('.mrntableselectexcel tr').each(function() {
        const $row = $(this);

        const assetCode = $row.find('.asset-code-input').val().trim();
        const $assetNameInput = $row.find('.asset-name-input');
        const $subAssetInput = $row.find('.sub-asset-code-input');

        const quantity = parseFloat($row.find('.quantity-input').val()) || 0;
        const currentValue = parseFloat($row.find('.current-value-input').val()) || 0;

        if (assetCode !== '') {
            // Count sub-assets per asset code
            assetCodeCounts[assetCode] = (assetCodeCounts[assetCode] || 0) + 1;
            const subAssetCode = `${assetCode}-${String(assetCodeCounts[assetCode]).padStart(2, '0')}`;
            $subAssetInput.val(subAssetCode);

            // Handle asset name consistency
            const currentAssetName = $assetNameInput.val().trim();

            if (!assetCodeToName[assetCode] && currentAssetName !== '') {
                // First time seeing this asset code — store its name
                assetCodeToName[assetCode] = currentAssetName;
            } else if (assetCodeToName[assetCode]) {
                // Set name from previously stored value
                $assetNameInput.val(assetCodeToName[assetCode]);
            }
        } else {
            $subAssetInput.val('');
        }

        // Accumulate totals
        totalQuantity += quantity;
        totalCurrentValue += currentValue;
    });

    $('#quantity').val(totalQuantity);
    
    let currentValueAsset = parseFloat($('#current_value_asset').val()) || 0;
    if (totalCurrentValue > currentValueAsset) {
        showToast('error', 'Total Current Value cannot be greater than Asset Current Value.');
    }

    $('#current_value').val(totalCurrentValue.toFixed(2));
}

        $('#ledger').change(function() {
            let groupDropdown = $('#ledger_group');
            $.ajax({
                url: '{{ route('finance.fixed-asset.getLedgerGroups') }}',
                method: 'GET',
                data: {
                    ledger_id: $(this).val(),
                    _token: $('meta[name="csrf-token"]').attr(
                        'content') // CSRF token
                },
                success: function(response) {
                    groupDropdown.empty(); // Clear previous options

                    response.forEach(item => {
                        groupDropdown.append(
                            `<option value="${item.id}">${item.name}</option>`
                        );
                    });

                },
                error: function() {
                    showToast('error', 'Error fetching group items.');
                }
            });

        });
        $('#category').on('change', function() {

            var category_id = $(this).val();
            if (category_id) {
                $.ajax({
                    type: "GET",
                    url: "{{ route('finance.fixed-asset.setup.category') }}?category_id=" + category_id,
                    success: function(res) {
                        if (res) {
                            $('#ledger').val(res.ledger_id).select2();
                            $('#ledger').trigger('change');
                            $('#ledger_group').val(res.ledger_group_id).select2();
                            $('#maintenance_schedule').val(res.maintenance_schedule);
                            $('#useful_life').val(res.expected_life_years);
                        }
                    }
                });
            }
        });

        function collectSubAssetDataToJson() {
            const subAssetData = [];

            $('.mrntableselectexcel tr').each(function() {
                const $row = $(this);

                const assetCode = $row.find('.asset-code-input').val()?.trim() || '';
                const assetName = $row.find('td:eq(2) input').val()?.trim() || '';
                const subAssetCode = $row.find('.sub-asset-code-input').val()?.trim() || '';
                const quantity = parseFloat($row.find('.quantity-input').val()) || 0;
                const currentValue = parseFloat($row.find('.current-value-input').val()) || 0;
               
                if (assetCode !== '') {
                    subAssetData.push({
                        asset_code: assetCode,
                        asset_name: assetName,
                        sub_asset_id: subAssetCode,
                        quantity: quantity,
                        current_value: currentValue,
                    });
                }
            });

            $('#sub_assets').val(JSON.stringify(subAssetData));
        }

        $(document).on('input change', '.asset-code-input,.asset-name-input, .quantity-input, .current-value-input', updateSubAssetCodes);
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
            const apiURL = "{{route('finance.fixed-asset.split.posting.get')}}";
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

        function postVoucher(element)
        {
            const bookId = "{{isset($data) ? $data -> book_id : ''}}";
            const documentId = "{{isset($data) ? $data -> id : ''}}";
            const postingApiUrl = "{{route('finance.fixed-asset.split.post')}}"
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
                    }),
                    success: function(data) {
                        const response = data.data;
                        if (response.status) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                            });
                            location.reload();
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
        $('#ap_file').prop('disabled', false).prop('readonly', false);
        $('#revisionNumber').prop('disabled', false).prop('readonly', false);
        
$(document).on('click', '#amendmentSubmit', (e) => {
let actionUrl = "{{ route('finance.fixed-asset.split.amendment', $data->id) }}";
fetch(actionUrl).then(response => {
    return response.json().then(data => {
        if (data.status == 200) {
            Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success'
                }).then(() => {
                    window.location.href = "{{ route('finance.fixed-asset.split.edit', $data->id) }}";
                });
   
        } else {
            Swal.fire({
                title: 'Error!',
                text: data.message,
                icon: 'error'
            });
            $('#amendmentconfirm').modal('hide');
        }
    });
});
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

    </script>
    <!-- END: Content-->
@endsection
