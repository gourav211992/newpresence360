@extends('layouts.app')

@section('content')
    @if ((isset($order) && !in_array($order->document_status, [App\Helpers\ConstantHelper::DRAFT,App\Helpers\ConstantHelper::SUBMITTED])))
    
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
								<h2 class="content-header-title float-start mb-0">Bid Details</h2>
								<div class="breadcrumb-wrapper">
									<ol class="breadcrumb">
										<li class="breadcrumb-item"><a href="index.html">Home</a>
										</li>  
										<li class="breadcrumb-item active">View</li>
									</ol>
								</div>
							</div>
						</div>
					</div>
					<div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
						<div class="form-group breadcrumb-right">   
							<button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
                            @if(isset($order) && $order->document_status !=App\Helpers\ConstantHelper::CLOSED)
							<button id="close_bid" data-bs-toggle='modal' data-bs-target="#closeBid" class="btn btn-danger btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Close</button> 
                            @elseif(isset($order) && $order->document_status == App\Helpers\ConstantHelper::CLOSED) 
							<button id="reopen_bid" data-bs-toggle='modal' data-bs-target="#closeBid" class="btn btn-danger btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Reopen</button> 
                             @endif
						</div>
					</div>
				</div>
			</div>
            
            <div class="content-body">
				<section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">  
                            <div class="row">
                                <div class="col-md-8">                    
                                    <div class="card">
                                        <div class="card-body customernewsection-form">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="new-applicayion-help-txt  mb-1 pb-25">
                                                        <h4 class="purchase-head text-start">TripId- {{ $order->document_number }}</h4>
                                                        <h6 class="mt-1 font-small-3"><span style="color: #999"> Bid End On: <span class="badge rounded-pill badge-light-secondary rounded">{{$order->bid_end}}</span></span></h6> 
                                                    </div>
                                                </div>
                                                <div class="col-md-4 text-end d-sm-flex flex-sm-column align-items-sm-end">
                                                    <span class="badge rounded-pill fw-bold px-2 py-50 badge-light-success rounded"><i data-feather="check-circle"></i> {{ $display_status }}</span> 
                                                </div>
                                            </div>
                                            <div class="summary-box mb-1">
                                                <div class="row">  
                                                    <div class="col-md-4 mb-1"> 
                                                        <label class="form-label">Time of Loading</label>
                                                        <h6 class="fw-bolder text-dark">{{ $order->loading_date_time }}</h6>
                                                    </div> 
                                                    <div class="col-md-4 mb-1"> 
                                                        <label class="form-label">Weight</label>
                                                        <h6 class="fw-bolder text-dark">{{ $order->total_weight ." " . $order->uom_code}}</h6>
                                                    </div> 
                                                    <div class="col-md-4 mb-1"> 
                                                        <label class="form-label">Vehicle Type</label>
                                                        <h6 class="fw-bolder text-dark">{{ $order->vehicle->vehicle_type }}</h6>
                                                    </div> 
                                                </div>
                                            </div>   
                                            <div class="text-end mt-2 position-relative" style="z-index: 2">
                                            @if(isset($order) && !in_array($order->document_status,[App\Helpers\ConstantHelper::COMPLETED,App\Helpers\ConstantHelper::SHORTLISTED,App\Helpers\ConstantHelper::CLOSED]) && count($order->bids))
                                                <button data-bs-toggle="modal" onclick='setShortlist()' id='shortlist' data-bs-target="#approved" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Shortlist</button>    
                                            @elseif(isset($order) && in_array($order->document_status,[App\Helpers\ConstantHelper::COMPLETED,App\Helpers\ConstantHelper::CONFIRMED,App\Helpers\ConstantHelper::SHORTLISTED]))
                                            <button data-bs-toggle="modal" onclick='endBid()' id='shortlist' data-bs-target="#approved" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i>End Bid</button>    

                                            @endif
                                            </div>
                                            <ul class="nav nav-tabs border-bottom mb-0" style="margin-top: -25px" >
                                                <li class="nav-item">
                                                    <a class="nav-link active" data-bs-toggle="tab" href="#paymentsc">
                                                        Transporter Applied
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#Collections">
                                                        Pick up
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#Settlement">
                                                        Drop Off
                                                    </a>
                                                </li> 
                                            </ul>
                                            <div class="tab-content mt-1">  
                                                <div class="tab-pane active" id="paymentsc"> 
                                                    <div class="table-responsive">
                                                        <table class="table table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                            <thead>
                                                                <tr>
                                                                    <th width="50px">#</th>
                                                                    <th>Transporter Name</th>
                                                                    <th>Email</th>
                                                                    <th>Contact No.</th>
                                                                    <th>Bid Price</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @php
                                                                    $Lowest_bid_price = 999999999;
                                                                @endphp

                                                                @if(isset($orderbids))
                                                                    @foreach($orderbids as $bid)
                                                                        @php
                                                                            $Lowest_bid_price = $bid->bid_price < $Lowest_bid_price ? $bid->bid_price : $Lowest_bid_price;
                                                                        @endphp
                                                                        <tr>
                                                                            <td>
                                                                                <div class="form-check form-check-inline me-0">
                                                                                    <input class="form-check-input" type="radio" name="podetail" value="{{ $bid->id }}" id="{{ $bid->id }}"
                                                                                        {{ ($bid->id == $order->selected_bid_id) ? "checked" : (($bid->bid_price == $Lowest_bid_price) ? "checked" : "") }}>
                                                                                </div> 
                                                                            </td> 
                                                                            <td>{{ $bid->transporter->company_name }}</td>
                                                                            <td>{{ $bid->transporter->email }}</td>
                                                                            <td>{{ $bid->transporter->whatsapp_number ?? $bid->transporter->mobile ?? $bid->transporter->phone }}</td>
                                                                            <td class="align-right">Rs {{ $bid->bid_price }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                @else
                                                                    <tr>
                                                                        <td colspan="5" class="text-center">No Bids Available</td>
                                                                    </tr>
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                <div class="tab-pane" id="Collections">
                                                    <div class="table-responsive">
                                                        <table class="mt-1 table table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"> 
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th> 
                                                                    <th>Location Name</th>
                                                                    <th>Address</th>
                                                                    <th>City</th>
                                                                    <th>Country</th>
                                                                    <th>State</th> 
                                                                    <th>Pin Code</th> 
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @if(isset($pick_loc))
                                                                @foreach ($pick_loc as $index => $loc)
                                                                <tr id="p_data_{{ $index+1 }}"  data-id="{{ $loc->id }} class="p_data">
                                                                    <td>{{ $index+1 }}</td>
                                                                
                                                                    {{-- Location Name --}}
                                                                    <td data-id="{{ $loc->location_name }}">
                                                                        {{ $loc->location_name }}
                                                                        <input type="hidden" name="location_pick_up[]" value="{{ $loc->location_id }}">
                                                                    </td>

                                                                    {{-- Address --}}
                                                                    <td data-id="{{ $loc->address->address }}">
                                                                        {{ $loc->address->address ?? "" }}
                                                                        <input type="hidden" name="p_address_id[]" value="{{ $loc->address->address }}">
                                                                    </td>

                                                                    {{-- City --}}
                                                                    <td data-id="{{ $loc->address?->city?->id ?? null }}">
                                                                        {{ $loc?->address?->city?->name ?? "" }}
                                                                        <input type="hidden" name="p_city_id[]" value="{{ $loc->address->city->id }}">
                                                                    </td>

                                                                    {{-- Country --}}
                                                                    <td data-id="{{ $loc->address->country->id ?? null }}">
                                                                        {{ $loc->address->country->name ?? "" }}
                                                                        <input type="hidden" name="p_country_id[]" value="{{ $loc->address->country->id }}">
                                                                    </td>

                                                                    {{-- State --}}
                                                                    <td data-id="{{ $loc->address->state->id ?? null }}">
                                                                        {{ $loc->address->state->name ?? "" }}
                                                                        <input type="hidden" name="p_state_id[]" value="{{ $loc->address->state->id }}">
                                                                    </td>

                                                                    {{-- Pincode --}}
                                                                    <td data-id="{{ $loc->address->pincode ?? null }}">
                                                                        {{ $loc->address->pincode ?? "" }}
                                                                        <input type="hidden" name="p_pin_code[]" value="{{ $loc->address->pincode }}">
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                
                                                <div class="tab-pane" id="Settlement">
                                                    <div class="table-responsive">
                                                        <table class="mt-1 table table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"> 
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th> 
                                                                    <th>Location Name</th>
                                                                    <th>Address</th>
                                                                    <th>City</th>
                                                                    <th>Country</th>
                                                                    <th>State</th> 
                                                                    <th>Pin Code</th>  
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @if(isset($drop_loc))
                                                                @foreach ($drop_loc as $index => $loc)
                                                                    <tr id="p_data_{{ $index+1 }}"  data-id="{{ $loc->id }} class="p_data">
                                                                        <td>{{ $index+1 }}</td>
                                                                    
                                                                        {{-- Location Name --}}
                                                                        <td data-id="{{ $loc->location_name ?? null }}">
                                                                            {{ $loc->location_name ?? "" }}
                                                                            <input type="hidden" name="location_pick_up[]" value="{{ $loc->location_id ?? null }}">
                                                                        </td>

                                                                        {{-- Address --}}
                                                                        <td data-id="{{ $loc->address->address ?? null }}">
                                                                            {{ $loc->address->address ?? "" }}
                                                                            <input type="hidden" name="p_address_id[]" value="{{ $loc->address->address ?? null}}">
                                                                        </td>

                                                                        {{-- City --}}
                                                                        <td data-id="{{ $loc->address->city->id ?? null }}">
                                                                            {{ $loc->address->city->name ?? "" }}
                                                                            <input type="hidden" name="p_city_id[]" value="{{ $loc->address->city->id ?? null }}">
                                                                        </td>

                                                                        {{-- Country --}}
                                                                        <td data-id="{{ $loc->address->country->id ?? null}}">
                                                                            {{ $loc->address->country->name ?? "" }}
                                                                            <input type="hidden" name="p_country_id[]" value="{{ $loc->address->country->id ?? null}}">
                                                                        </td>

                                                                        {{-- State --}}
                                                                        <td data-id="{{ $loc->address->state->id ?? null}}">
                                                                            {{ $loc->address->state->name ?? "" }}
                                                                            <input type="hidden" name="p_state_id[]" value="{{ $loc->address->state->id ?? null}}">
                                                                        </td>

                                                                        {{-- Pincode --}}
                                                                        <td data-id="{{ $loc->address->pincode ?? null}}">
                                                                            {{ $loc->address->pincode ?? "" }}
                                                                            <input type="hidden" name="p_pin_code[]" value="{{ $loc->address->pincode ?? null }}">
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">                    
                                    <div class="card">
                                        <div class="card-body customernewsection-form">                                        
                                            <div class="exp-heade-top"><i data-feather="users"></i> <strong>Shortlisted Transporter</strong> <i data-feather="arrow-down" class="float-end"></i></div>
                                            <div class="px-1 mt-2 expempinfodetail">
                                                @if(isset($order) && $order->bid)
                                                    <div class="row">  
                                                        <div class="col-md-8 mb-50"> 
                                                            <label class="form-label">Transporter Name</label>
                                                            <h6 class="fw-bolder text-dark">{{ $order->bid->transporter->company_name }}</h6>
                                                        </div> 
                                                        <div class="col-md-4 mb-50"> 
                                                            <label class="form-label">Price</label>
                                                            <h6 class="fw-bolder text-dark">Rs {{$order->bid->bid_price}}/-</h6>
                                                        </div> 
                                                        <div class="col-md-8 my-50"> 
                                                            <label class="form-label">Driver Name</label>
                                                            <h6 class=" {{ $order->bid->driver_name?"text-dark":"text-danger" }} ">{{ $order->bid->driver_name??"Not Provided" }}</h6>
                                                        </div> 
                                                        <div class="col-md-4 my-50"> 
                                                            <label class="form-label">Vehicle No</label>
                                                            <h6 class="{{ $order->bid->vehicle_number?"text-dark":"text-danger" }}">{{ $order->bid->vehicle_number??"Not Provided" }}</h6>
                                                        </div>
                                                        <div class="col-md-12 my-50"> 
                                                            <label class="form-label">Driver Contact No.</label>
                                                            <h6 class=" {{ $order->bid->driver_contact_no?"text-dark":"text-danger" }} ">{{ $order->bid->driver_contact_no??"Not Provided" }}</h6>
                                                        </div>
                                                    </div>
                                                    @else
                                                    <div class="row">
                                                <h6 class="text-danger">Not Shortlisted Yet</h6>
                                                
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-body">                                        
                                            @if(isset($order))
                                            @include('partials.approval-history', ['document_status' => $order->document_status, 'revision_number' => $order->revision_number, 'colspan' => 12])
                                            @endif
                                            </div>
                                        </div>
                                    </div>
                                     
                                </div> 			 
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>

    <!-- BEGIN: Footer-->
    <!-- END: Footer-->
    <div class="modal fade" id="approved" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <form class="ajax-submit-2" method="POST" action="{{ route('document.approval.transporter') }}" data-redirect="{{ route('transporter.index', ['type' => $type]) }}" enctype='multipart/form-data'>
                @csrf
                <input type="hidden" name="action_type" id="action_type">
                <input type="hidden" name="id" id="id" value="{{isset($order) ? $order -> id : ''}}">
                <input type="hidden" name="bid_id" id="short_list_bid_id" value="">
                <div class="modal-header">
                    <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="approve_reject_heading_label">
                    </h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-2">
                    <div class="row mt-1">
                    <div class="col-md-12">
                        <div class="mb-1">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" id="remarks" class="form-control cannot_disable"></textarea>
                        </div>
                        <div class="row">
                            <div class = "col-md-8">
                                <div class="mb-1">
                                    <label class="form-label">Upload Document</label>
                                    <input type="file" name = "attachments[]" multiple class="form-control cannot_disable" onchange = "addFiles(this, 'approval_files_preview');" max_file_count = "2"/>
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
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button> 
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="closeBid" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <input type="hidden" name="id" id ='bid_id'  value="{{isset($order) ? $order -> id : ''}}">
                <input type="hidden" name="tr_id" value="">
                <div class="modal-header">
                    <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="approve_reject_heading_label">{{ $order->document_status == App\Helpers\ConstantHelper::CLOSED ? 'Reopen Bid' : 'Close Bid' }}</h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-2">
                    <div class="row mt-1">
                    <div class="col-md-12">
                        <div class="mb-1">
                            <label class="form-label">Remarks</label>
                            <textarea name="close_reason" id = 'close_reason' class="form-control cannot_disable"></textarea>
                        </div>
                        <div class="row">
                            <div class = "col-md-8">
                                <div class="mb-1">
                                    <label class="form-label">Upload Document</label>
                                    <input type="file" name = "attachments[]" multiple class="form-control cannot_disable" onchange = "addFiles(this, 'approval_files_preview');" max_file_count = "2"/>
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
                    <button type="reset" class="btn btn-outline-secondary me-1" onclick = "closeModal('closeBid')">Cancel</button> 
                    <button id="confirm_close_bid" onclick="{{ $order->document_status == App\Helpers\ConstantHelper::CLOSED ? 'reOpenBid()' : 'closeBid()' }}" class="btn btn-primary">Submit</button>
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
                        <label class="form-label" for="fp-range">Select Date Range</label>
                        <input type="text" id="fp-range" class="form-control flatpickr-range" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Select Survey Name</label>
                        <select class="form-select select2">
                            <option>Select</option>
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Status</label>
                        <select class="form-select">
                            <option>Select</option>
                            <option>Publish</option>
                            <option>Not Publish</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-start">
                    <button type="button" class="btn btn-primary data-submit mr-1">Apply</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>    
        
    @else
    <!-- BEGIN: Content-->
    <form method="POST" data-completionFunction = "disableHeader" class="ajax-input-form sales_module_form" action = "{{route('transporter.store')}}" data-redirect="{{ route('transporter.index', ['type' => request() -> type]) }}" id = "sale_invoice_form" enctype='multipart/form-data'>

        <div class="app-content content ">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
			    	<div class="row">
                    @php
                        $title = $typeName;
                    @endphp
                    @include('layouts.partials.breadcrumb-add-edit', [
                        'title' => $title, 
                        'menu' => 'Home', 
                        'menu_url' => url('home'),
                        'sub_menu' => 'Add New'
                    ])
                        <input type = "hidden" value = "draft" name = "document_status" id = "document_status" />
					    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
						    <div class="form-group breadcrumb-right" id="buttonsDiv">   
                            @if(!isset(request() -> revisionNumber))
                                <button type = "button" onclick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>  
                                @if (isset($order))
                                @if($buttons['print'])
                                <button class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer">
                                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                        <rect x="6" y="14" width="12" height="8"></rect>
                                    </svg>
                                    Print  <i class="fa-regular fa-circle-down"></i>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    @php
                                        $options = [
                                            'Transporter Request' => 'Transporter Request',
                                            'Credit Note' => 'Credit Note',
                                        ];
                                    @endphp
                                    @foreach ($options as $key => $label)
                                    <li>
                                            <a class="dropdown-item" href="{{ route('transporter.generate-pdf', [$order->id, $key]) }}" target="_blank">{{ $label }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                                @endif
                                @if($buttons['draft'])
                                    <button type="button" onclick = "submitForm('draft');" name="action" value="draft" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0" id="save-draft-button" name="action" value="draft"><i data-feather='save'></i> Save as Draft</button>
                                @endif
                                @if (in_array($order->document_status, [App\Helpers\ConstantHelper::APPROVAL_NOT_REQUIRED, App\Helpers\ConstantHelper::APPROVED]))
                                <button type="button" name="action"  data-bs-toggle="modal" data-bs-target="#bidModal"  class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='minimize'></i>Close Bid</button>
                                @endif
                                @if($buttons['submit'])
                                    <button type="button" onclick = "submitForm('submitted');" name="action" value="submitted" class="btn btn-primary btn-sm" id="submit-button" name="action" value="submitted"><i data-feather="check-circle"></i> Submit</button>
                                @endif
                                @if($buttons['approve'])
                                    <button type="button" id="reject-button" data-bs-toggle="modal" data-bs-target="#approveModal" onclick = "setReject();" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg> Reject</button>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal" onclick = "setApproval();" ><i data-feather="check-circle"></i> Approve</button>
                                @endif
                                @if($buttons['amend'])
                                    <button id = "amendShowButton" type="button" onclick = "openModal('amendmentconfirm')" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i> Amendment</button>
                                @endif
                                @if($buttons['post'])
                                    <button onclick = "onPostVoucherOpen();" id="postButton" type = "button" class="btn btn-warning btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Post</button>
                                @endif
                                @if($buttons['voucher'])
                                    <button type = "button" onclick = "onPostVoucherOpen('posted');" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg> Voucher</button>
                                @endif
                                @if($buttons['revoke'])
                                    <button id = "revokeButton" type="button" onclick = "revokeDocument();" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='rotate-ccw'></i> Revoke</button>
                                @endif
                            @else
                                <button type = "button" name="action" value="draft" id = "save-draft-button" onclick = "submitForm('draft');" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0"><i data-feather='save'></i> Save as Draft</button>  
                                <button type = "button" name="action" value="submitted"  id = "submit-button" onclick = "submitForm('submitted');" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Submit</button> 
                            @endif
                            @endif
						</div>
					</div>
				</div>
			</div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body customernewsection-form"> 
                                    <div class="border-bottom mb-2 pb-25" id ="main_so_form">  
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="newheader">
                                                    <h4 class="card-title text-theme">Basic Information</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                            </div>
                                            @if (isset($order) && isset($docStatusClass))
                                            <div class="col-md-6 text-sm-end">
                                                <span class="badge rounded-pill badge-light-{{$order->display_status === 'Posted' ? 'info' : 'secondary'}} forminnerstatus">
                                                    <span class = "text-dark" >Status</span> : <span class="{{$docStatusClass}}">{{$display_status}}</span>
                                                </span>
                                            </div>
                                            @endif
                                        </div> 
                                    </div>
                                    <div class="row">
                                    <div class="col-md-8"> 
                                            <input type = "hidden" name = "type" id = "type_hidden_input"></input>
                                                @if (isset($order))
                                                <input type = "hidden" value = "{{$order -> id}}" name = "tr_id"></input>
                                                @endif
                                                <div class="row align-items-center mb-1" style="display:none">
                                                    <div class="col-md-3"> 
                                                        <label class="form-label">Document Type <span class="text-danger">*</span></label>  
                                                    </div>
                                                    <div class="col-md-5">  
                                                        <select class="form-select disable_on_edit" disabled id = "service_id_input" {{isset($order) ? 'disabled' : ''}} onchange = "onSeriesChange(this);">
                                                            @foreach ($services as $currentService)
                                                            <option value = "{{$currentService -> alias}}" {{isset($selectedService) ? ($selectedService == $currentService -> alias ? 'selected' : '') : ''}}>{{$currentService -> name}}</option> 
                                                            @endforeach
                                                        </select>
                                                        <input type = "hidden" value = "yes" id = "invoice_to_follow_input" />
                                                    </div>
                                                </div>
                                                
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3"> 
                                                        <label class="form-label">Series <span class="text-danger">*</span></label>  
                                                    </div>
                                                    <div class="col-md-5"> 
                                                        <select class="form-select disable_on_edit" onChange = "getDocNumberByBookId(this);" name = "book_id" id = "series_id_input">
                                                            @foreach ($series as $currentSeries)
                                                            <option value = "{{$currentSeries -> id}}" {{isset($order) ? ($order -> book_id == $currentSeries -> id ? 'selected' : '') : ''}}>{{$currentSeries -> book_code}}</option> 
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <input type = "hidden" name = "book_code" id = "book_code_input" value = "{{isset($order) ? $order -> book_code : ''}}"></input>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3"> 
                                                        <label class="form-label">Trip ID <span class="text-danger">*</span></label>  
                                                    </div>  
                                                    
                                                    <div class="col-md-5"> 
                                                        <input type="text" value = "{{isset($order) ? $order -> document_number : ''}}" class="form-control disable_on_edit" readonly id = "order_no_input" name = "document_no">
                                                    </div> 
                                                </div>
                                                
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3"> 
                                                        <label class="form-label">Date & Time Of Loading <span class="text-danger">*</span></label>  
                                                    </div>  
                                                    
                                                    <div class="col-md-5">
                                                        <input type="hidden" value="{{isset($order) ? $order -> document_date : Carbon\Carbon::now()->format('y-m-d')}}" id="order_date_input" name="document_date"/> 
                                                        <input type="datetime-local" class="form-control " value = "{{isset($order) ? $order -> loading_date_time : Carbon\Carbon::now()->addHours(4)->format('Y-m-d\TH:i')}}" name = "loading_date" id = "order_date_input" oninput = "onDocDateChange();">
                                                    </div> 
                                                </div> 
                                        
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3"> 
                                                <label class="form-label">Vehicle Type<span class="text-danger">*</span></label>  
                                            </div>
                                            <div class="col-md-5"> 
                                                <select class="form-select " name = "vehicle_type" >
                                                @foreach ($vehicle as $veh)
                                                        <option value = "{{$veh->id}}" {{isset($order) ? ($order -> vehicle_type == $veh->id ? 'selected' : '') : ''}}>{{$veh->vehicle_type}}</option> 
                                                        @endforeach
                                                    </select>
                                                </div>       
                                            </div>
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3"> 
                                                <label class="form-label">Weight<span class="text-danger">*</span></label>  
                                            </div> 
                                            <div class="col-md-3"> 
                                                <input type="number" value = "{{isset($order) ? $order -> total_weight : ''}}" class="form-control "  id = "total_weight" name = "weight">
                                            </div>  
                                            <div class="col-md-2"> 
                                                <select class="form-select " name = "uom_id">

                                                    @foreach ($weight as $UOMs)
                                                        <option value = "{{$UOMs -> id}}" {{isset($order) ? ($order -> uom_id == $UOMs -> id ? 'selected' : '') : ''}}>{{$UOMs -> name}}</option> 
                                                    @endforeach
                                                </select>
                                            </div>         
                                        </div>

                                        
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3"> 
                                                <label class="form-label">Bid End Date <span class="text-danger">*</span></label>  
                                            </div>  
                                            
                                            <div class="col-md-5"> 
                                                <input type="datetime-local" value = "{{isset($order) ? $order -> bid_end : Carbon\Carbon::now()->addHours(2)->format('Y-m-d\TH:i')}}" class="form-control" name = "bid_end_date" id = "bid_end_date_input"">
                                            </div> 
                                        </div> 
                                        
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3"> 
                                                <label class="form-label">Transporter </label>  
                                            </div>
                                            <div class="col-md-5"> 
                                                <select multiple class="form-select multiple select2" id="order_no_input" name="transporter_ids[]">
                                                    <!-- <option value="0" {{ empty(old('document_no')) ? 'selected' : '' }}>All</option> -->
                                                    
                                                    @if(isset($vendors) && count($vendors) > 0)
                                                    @foreach($vendors as $transporters)
                                                        <option value="{{ $transporters->id }}" 
                                                            {{ isset($order) && isset($transporter_ids) && in_array($transporters->id, gettype($order->transporter_ids)=='json'?json_decode($order->transporter_ids, true):$order->transporter_ids) ? 'selected' : '' }}>
                                                            {{ $transporters->company_name }}
                                                        </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>   
                                        </div>
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3"> 
                                                <label class="form-label">Remarks </label>  
                                            </div>
                                            <div class="col-md-5"> 
                                                <input type="text" value = "{{isset($order) ? $order -> remarks : ''}}" class="form-control "  id = "order_no_input" name = "remarks">
                                            </div>   
                                        </div>
                                        
                                        </div>
                                        @if(isset($order))
                                        @include('partials.approval-history', ['document_status' => $order->document_status, 'revision_number' => $order->revision_number])
                                        @endif 
                                    </div>
                            </div>
                            <div class="col-md-12" id = "manual_entry_details">
                                <div class="col-md-12 {{(isset($order) && count($order -> dynamic_fields)) > 0 ? '' : 'd-none'}}" id = "dynamic_fields_section">
                    @if (isset($dynamicFieldsUi))
                        {!! $dynamicFieldsUi !!}
                    @endif
                </div>
                                <div class="mt-2">
                                    <div class="step-custhomapp bg-light">
                                        <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-bs-toggle="tab"
                                                href="#PickUp">Pick Up Locations</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab"
                                                href="#DropOff">Drop Off Locations</a>
                                            </li>
                                        </ul>
                                    </div>
                                <div class="tab-content ">
                                    <div class="tab-pane active" id="PickUp">
                                        <div class="table-responsive-md">
                                            <table id="pick_up_table"
                                                class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                <thead>
                                                    <tr>
                                                        <th width = "20px">#</th>
                                                        <th width="300px">Location Name<span
                                                                class="text-danger">*</span></th>
                                                        <th width="300px">Address<span
                                                            class="text-danger">*</span></th>
                                                        <th width="150px">Country/Region<span
                                                            class="text-danger">*</span></th>
                                                        <th width="150px">State</th>
                                                        <th width="150px">City<span
                                                            class="text-danger">*</span></th>
                                                        <th >Pin Code</th>
                                                        <th class = "center-align-content" width = "20px">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="pick_up_body">
                                                    <tr id="pick_up">
                                                        <td>#</td>
                                                        <td>
                                                            <select
                                                                class="form-select mw-100 select2 locationSelect"
                                                                data-id="0" name="location_pick_up[]"
                                                                id="pick_up_location" onchange="storeAddress(this)">
                                                                <option 
                                                                    value="">
                                                                </option>
                                                                @foreach ($stores as $store)
                                                                <option 
                                                                    value="{{$store->id}}">{{$store -> store_name}}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="text"
                                                                class="form-control mw-100 addressSelect"
                                                                data-id="1" name="p_address_id[]"
                                                                id="p_address_id_0"  >
                                                            </input>
                                                        </td>
                                                       
                                                        <td>
                                                            <select
                                                                class="form-select mw-100 select2 countrySelect"
                                                                data-id="0" name="p_country_id[]"
                                                                id="p_country_id_0" oninput="setState(this,'p')" >
                                                                <option
                                                                    value="">
                                                                </option>
                                                                @foreach ($countries as $country)
                                                                    <option
                                                                        value="{{$country->value}}">{{$country->label}}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        
                                                        <td>
                                                            <div class="position-relative">
                                                                <select
                                                                    class="form-select mw-100 select2 stateSelect"
                                                                    data-id="0" name="p_state_id[]"
                                                                    id="p_state_id_0" oninput="setCities(this,'p')" >
                                                                    <option
                                                                        value="">
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <select
                                                                class="form-select mw-100 select2 citySelect"
                                                                data-id="1" name="p_city_id[]"
                                                                id="p_city_id_0"  >
                                                                <option 
                                                                    value="">
                                                                </option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="text"
                                                                id = "p_pin_code_0"
                                                                class="form-control mw-100"
                                                                name="p_pin_code[]"/>
                                                        </td>
                                                        <td class = "center-align-content"><a href="#"
                                                                class="text-primary add_pick_up_location" onclick="addLocationtoDataRow(this,'pick_up')" id="add_pick_up"><i
                                                                    data-feather="plus-square"></i></a>
                                                        </td>
                                                    </tr>
                                                    @if (isset($pick_loc))
                                                    
                                                    @foreach ($pick_loc as $index => $loc)
                                                    <tr id="p_data_{{ $index+1 }}"  data-id="{{ $loc->id }} class="p_data">
                                                        <td>{{ $index+1 }}</td>
                                                    
                                                        {{-- Location Name --}}
                                                        <td data-id="{{ $loc->location_name ?? null }}">
                                                            {{ $loc->location_name  ?? "" }}
                                                            <input type="hidden" name="location_pick_up[]" value="{{ $loc->location_id }}">
                                                        </td>

                                                        {{-- Address --}}
                                                        <td data-id="{{ $loc->address->address ?? null }}">
                                                            {{ $loc->address->address  ?? "" }}
                                                            <input type="hidden" name="p_address_id[]" value="{{ $loc->address->address }}">
                                                        </td>

                                                        
                                                        {{-- Country --}}
                                                        <td data-id="{{ $loc->address->country->id ?? null }}">
                                                            {{ $loc->address->country->name  ?? "" }}
                                                            <input type="hidden" name="p_country_id[]" value="{{ $loc->address->country->id }}">
                                                        </td>

                                                        {{-- State --}}
                                                        <td data-id="{{ $loc->address->state->id ?? null }}">
                                                            {{ $loc->address->state->name  ?? "" }}
                                                            <input type="hidden" name="p_state_id[]" value="{{ $loc->address->state->id }}">
                                                        </td>

                                                        {{-- City --}}
                                                        <td data-id="{{ $loc->address->city->id ?? null }}">
                                                            {{ $loc->address->city->name  ?? "" }}
                                                            <input type="hidden" name="p_city_id[]" value="{{ $loc->address->city->id }}">
                                                        </td>

                                                        {{-- Pincode --}}
                                                        <td data-id="{{ $loc->address->pincode ?? null }}">
                                                            {{ $loc->address->pincode  ?? "" }}
                                                            <input type="hidden" name="p_pin_code[]" value="{{ $loc->address->pincode }}">
                                                        </td>

                                                        {{-- Remove Button --}}
                                                        <td class="center-align-content">
                                                            <a href="#" class="text-primary remove_p_location" 
                                                            onclick="removeLocationtoDataRow('p', {{ $index+1 }})" id="remove_drop_off">
                                                                <i class='text-danger' data-feather="trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="DropOff">
                                        <div class="table-responsive-md">
                                            <table id="drop_off_table"
                                                class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                <thead>
                                                    <tr>
                                                        <th width = "20px">#</th>
                                                        <th width="300px">Location Name<span
                                                            class="text-danger">*</span></th>
                                                        <th width="300px">Address<span
                                                            class="text-danger">*</span></th>
                                                        <th width="150px">Country/Region<span
                                                            class="text-danger">*</span></th>
                                                        <th width="150px">State</th>
                                                        <th width="150px">City<span
                                                        class="text-danger">*</span></th>
                                                        <th >Pin Code</th>
                                                        <th class = "center-align-content" width = "20px">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="drop_off_body">
                                                    <tr id="drop_off">
                                                        <td>$</td>
                                                        <td>
                                                            <input type='text'
                                                                class="form-control mw-100 locationSelect"
                                                                data-id="1" name="location_drop[]"
                                                                id="drop_off_location"  >
                                                            </input>
                                                        </td>
                                                        <td>
                                                            <input type="text"
                                                                class="form-control mw-100 addressSelect"
                                                                data-id="1" name="d_address_id[]"
                                                                id="d_address_id_0"  >
                                                            </input>
                                                        </td>
                                                        
                                                        <td>
                                                            <select
                                                                class="form-select mw-100 select2 countrySelect"
                                                                data-id="0" name="d_country_id[]"
                                                                id="d_country_id_0" oninput="setState(this,'d')" >
                                                                <option
                                                                    value="">
                                                                </option>
                                                                @foreach ($countries as $country)
                                                                    <option 
                                                                        value="{{$country->value}}">{{$country->label}}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <div class="position-relative">
                                                                <select
                                                                    class="form-select mw-100 select2 stateSelect"
                                                                    data-id="0" name="d_state_id[]"
                                                                    id="d_state_id_0" oninput="setCities(this,'d')">
                                                                    <option value="">
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <select
                                                                class="form-select mw-100 select2 citySelect"
                                                                data-id="0" name="d_city_id[]"
                                                                id="d_city_id_0">
                                                                <option 
                                                                    value="">
                                                                </option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="text"
                                                                id = "d_pin_code_0"
                                                                class="form-control mw-100"
                                                                name="d_pin_code[]"
                                                                />
                                                        </td>
                                                        <td class = "center-align-content"><a href="#"
                                                                class="text-primary add_drop_off_location" onclick="addLocationtoDataRow(this,'drop_off')" id="add_drop_off"><i
                                                                    data-feather="plus-square"></i></a>
                                                        </td>
                                                    </tr>
                                                    @if (isset($drop_loc))
                                                    
                                                    @foreach ($drop_loc as $index => $loc)
                                                    <tr id="d_data_{{ $index+1 }}" data-id="{{ $loc->id }} class="d_data">
                                                            <td>{{ $index+1 }}</td>

                                                            {{-- Location Name --}}
                                                            <td data-id="{{ $loc->location_name ?? null }}">
                                                                {{ $loc->location_name  ?? "" }}
                                                                <input type="hidden" name="location_drop[]" value="{{ $loc->location_name ?? null }}">
                                                            </td>

                                                            {{-- Address --}}
                                                            <td data-id="{{ $loc->address->address ?? null }}">
                                                                {{ $loc->address->address  ?? "" }}
                                                                <input type="hidden" name="d_address_id[]" value="{{ $loc->address->address ?? null }}">
                                                            </td>

                                                            
                                                            {{-- Country --}}
                                                            <td data-id="{{ $loc->address->country->id ?? null }}">
                                                                {{ $loc->address->country->name  ?? "" }}
                                                                <input type="hidden" name="d_country_id[]" value="{{ $loc->address->country->id ?? null }}">
                                                            </td>

                                                            {{-- State --}}
                                                            <td data-id="{{ $loc->address->state->id ?? null }}">
                                                                {{ $loc->address->state->name  ?? "" }}
                                                                <input type="hidden" name="d_state_id[]" value="{{ $loc->address->state->id ?? null }}">
                                                            </td>

                                                            {{-- City --}}
                                                            <td data-id="{{ $loc->address->city->id ?? null }}">
                                                                {{ $loc->address->city->name  ?? "" }}
                                                                <input type="hidden" name="d_city_id[]" value="{{ $loc->address->city->id ?? null }}">
                                                            </td>

                                                            {{-- Pincode --}}
                                                            <td data-id="{{ $loc->address->pincode ?? null }}">
                                                                {{ $loc->address->pincode  ?? "" }}
                                                                <input type="hidden" name="d_pin_code[]" value="{{ $loc->address->pincode ?? null }}">
                                                            </td>

                                                            {{-- Remove Button --}}
                                                            <td class="center-align-content">
                                                                <a href="#" class="text-primary remove_d_location" 
                                                                onclick="removeLocationtoDataRow('d', {{ $index+1 }})" id="remove_drop_off">
                                                                    <i class='text-danger' data-feather="trash"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>                                    
                                </div>
                            </div>
                        </div> 
                    </div>
                </section>
            </div>
        <div class="modal fade" id="Remarks" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
            <div class="modal-dialog  modal-dialog-centered" >
                <div class="modal-content">
                    <div class="modal-header p-0 bg-transparent">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-sm-2 mx-50 pb-2">
                        <h1 class="text-center mb-1" id="shareProjectTitle">Add/Edit Remarks</h1>
                        <p class="text-center">Enter the details below.</p>
                        <div class="row mt-2">
                            <div class="col-md-12 mb-1">
                                <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                <textarea class="form-control" current-item = "item_remarks_0" onchange = "changeItemRemarks(this);" id ="current_item_remarks_input" placeholder="Enter Remarks"></textarea>
                            </div> 
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">  
                            <button type="button" class="btn btn-outline-secondary me-1" onclick="closeModal('Remarks');">Cancel</button> 
                        <button type="button" class="btn btn-primary" onclick="closeModal('Remarks');">Submit</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="delivery" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
            <div class="modal-dialog  modal-dialog-centered" >
                <div class="modal-content">
                    <div class="modal-header p-0 bg-transparent">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-sm-2 mx-50 pb-2">
                        <h1 class="text-center mb-1" id="shareProjectTitle">Delivery Schedule</h1>
                        <p class="text-center">Enter the details below.</p>
                        <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail"> 
                            <thead>
                                <tr>
                                    <td>#</td>
                                    <td><input type="text" id = "new_item_delivery_qty_input" class="form-control mw-100" /></td>
                                    <td><input type="date" id = "new_item_delivery_date_input" value="{{Carbon\Carbon::now() -> format('Y-m-d')}}" class="form-control mw-100" /></td>
                                    <td>
                                        <a href="#" onclick = "addDeliveryScheduleRow();" class="text-primary"><i data-feather="plus-square"></i></a>
                                    </td>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            </table>
                        <div class="table-responsive-md customernewsection-form">
                            <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail" id = "delivery_schedule_main_table"> 
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th width="150px">Quantity</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                    </tr>
                                    <tr>
                                        <td class="text-dark"><strong>Total</strong></td>
                                        <td class="text-dark"><strong id = "item_delivery_qty"></strong></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">  
                        <button type="button" class="btn btn-outline-secondary me-1" onclick="closeModal('delivery');">Cancel</button> 
                        <button type="button" class="btn btn-primary" onclick="closeModal('delivery');">Submit</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="attribute" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
            <div class="modal-dialog  modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header p-0 bg-transparent">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-sm-2 mx-50 pb-2">
                        <h1 class="text-center mb-1" id="shareProjectTitle">Select Attribute</h1>
                        <p class="text-center">Enter the details below.</p>

                        <div class="table-responsive-md customernewsection-form">
                                    <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail" id = "attributes_table_modal" item-index = ""> 
                                        <thead>
                                            <tr>  
                                                <th>Attribute Name</th>
                                                <th>Attribute Value</th>
                                            </tr>
                                            </thead>
                                            <tbody id = "attribute_table">	 

                                        </tbody>


                                    </table>
                                </div>
                    </div>
                    
                    <div class="modal-footer justify-content-center">  
                            <button type="button" class="btn btn-outline-secondary me-1" onclick = "closeModal('attribute');">Cancel</button> 
                            <button type="button" class="btn btn-primary" onclick = "closeModal('attribute');">Select</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="amendConfirmPopup" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Amend{{request() -> type == "srdn" ? "Delivery Note CUM Return" : (request() -> type == "dn" ? 'Delivery Note' : 'Transporter Request')}}
                            </h4>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <input type="hidden" name="action_type" id="action_type_main">
                    </div>
                    <div class="modal-body pb-2">
                        <div class="row mt-1">
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="amend_remarks" class="form-control cannot_disable"></textarea>
                                </div>
                                <div class = "row">
                                    <div class = "col-md-8">
                                        <div class="mb-1">
                                            <label class="form-label">Upload Document</label>
                                            <input name = "amend_attachments[]" onchange = "addFiles(this, 'amend_files_preview')" type="file" class="form-control cannot_disable" max_file_count = "2" multiple/>
                                        </div>
                                    </div>
                                    <div class = "col-md-4" style = "margin-top:19px;">
                                        <div class="row" id = "amend_files_preview">
                                        </div>
                                    </div>
                                </div>
                                <span class = "text-primary small">{{__("message.attachment_caption")}}</s>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">  
                        <button type="button" class="btn btn-outline-secondary me-1">Cancel</button> 
                        <button type="button" class="btn btn-primary" onclick = "submitAmend();">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="modal fade" id="bidModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="bidclose" class="ajax-submit-2" method="POST" action="{{ route('document.approval.transporter') }}" data-redirect="{{ route('transporter.index') }}" enctype='multipart/form-data'>
                @csrf
                <input type="hidden" name="action_type" id="action_type" value="completed">
                <input type="hidden" name="id" value="{{isset($order) ? $order -> id : ''}}">
                <div class="modal-header">
                    <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="approve_reject_heading_label">Close Bid
                    </h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-2">
                    <div class="row mt-1">
                    <div class="col-md-12">
                        <div class="mb-1">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control cannot_disable"></textarea>
                        </div>
                        <div class="row">
                            <div class = "col-md-8">
                                <div class="mb-1">
                                    <label class="form-label">Upload Document</label>
                                    <input type="file" name = "attachments[]" multiple class="form-control cannot_disable" onchange = "addFiles(this, 'approval_files_preview');" max_file_count = "2"/>
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
                    <div class="modal-footer justify-content-center">  
                        <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button> 
                        <button type="button" class="btn btn-primary">Submit</button>
                    </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- 
    <div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body alertmsg text-center warning">
                    <i data-feather='alert-circle'></i>
                    <h2>Are you sure?</h2>
                    <p>Are you sure you want to <strong>Amend</strong> this <strong>{{request() -> type == "srdn" ? "Delivery Note CUM Return" : (request() -> type == "dn" ? 'Delivery Note' : 'Sales Return')}}</strong>?</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" data-bs-dismiss="modal" onclick = "amendConfirm();" class="btn btn-primary">Confirm</button>
                </div> 
            </div>
        </div>
    </div>}
    
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form class="ajax-submit-2" method="POST" action="{{ route('document.approval.saleReturn') }}" data-redirect="{{ route('transporter.index', ['type' => $type]) }}" enctype='multipart/form-data'>
                @csrf
                <input type="hidden" name="action_type" id="action_type">
                <input type="hidden" name="id" value="{{isset($order) ? $order -> id : ''}}">
                <div class="modal-header">
                    <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="approve_reject_heading_label">
                        </h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-2">
                    <div class="row mt-1">
                        <div class="col-md-12">
                            <div class="mb-1">
                                <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control cannot_disable"></textarea>
                        </div>
                        <div class="row">
                            <div class = "col-md-8">
                                <div class="mb-1">
                                    <label class="form-label">Upload Document</label>
                                    <input type="file" name = "attachments[]" multiple class="form-control cannot_disable" onchange = "addFiles(this, 'approval_files_preview');" max_file_count = "2"/>
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
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button> 
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
--}}
@endif
    
@section('scripts')
<script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>



<script>
    // $(window).on('load', function () {
    //         if (feather) {
    //             feather.replace({
    //                 width: 14,
    //                 height: 14
    //             });
    //         }        
    //     })
    //     $(function () {

    //         var dt_basic_table = $('.datatables-basic'),
    //         dt_date_table = $('.dt-date'),
    //             dt_complex_header_table = $('.dt-complex-header'),
    //             dt_row_grouping_table = $('.dt-row-grouping'),
    //             dt_multilingual_table = $('.dt-multilingual'),
    //             assetPath = '../../../app-assets/';

    //         if ($('body').attr('data-framework') === 'laravel') {
    //             assetPath = $('body').attr('data-asset-path');
    //         }

    //         // DataTable with buttons
    //         // --------------------------------------------------------------------

    //         if (dt_basic_table.length) {
    //             var dt_basic = dt_basic_table.DataTable({

    //                 order: [[0, 'asc']],
    //                 dom:
    //                     '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    //                 displayLength: 7,
    //                 lengthMenu: [7, 10, 25, 50, 75, 100],
    //                 buttons: [
    //                     {
    //                         extend: 'collection',
    //                         className: 'btn btn-outline-secondary dropdown-toggle',
    //                         text: feather.icons['share'].toSvg({ class: 'font-small-4 mr-50' }) + 'Export',
    //                         buttons: [
    //                             {
    //                                 extend: 'print',
    //                                 text: feather.icons['printer'].toSvg({ class: 'font-small-4 mr-50' }) + 'Print',
    //                                 className: 'dropdown-item',
    //                                 exportOptions: { columns: [3, 4, 5, 6, 7] }
    //                             },
    //                             {
    //                                 extend: 'csv',
    //                                 text: feather.icons['file-text'].toSvg({ class: 'font-small-4 mr-50' }) + 'Csv',
    //                                 className: 'dropdown-item',
    //                                 exportOptions: { columns: [3, 4, 5, 6, 7] }
    //                             },
    //                             {
    //                                 extend: 'excel',
    //                                 text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + 'Excel',
    //                                 className: 'dropdown-item',
    //                                 exportOptions: { columns: [3, 4, 5, 6, 7] }
    //                             },
    //                             {
    //                                 extend: 'pdf',
    //                                 text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 mr-50' }) + 'Pdf',
    //                                 className: 'dropdown-item',
    //                                 exportOptions: { columns: [3, 4, 5, 6, 7] }
    //                             },
    //                             {
    //                                 extend: 'copy',
    //                                 text: feather.icons['copy'].toSvg({ class: 'font-small-4 mr-50' }) + 'Copy',
    //                                 className: 'dropdown-item',
    //                                 exportOptions: { columns: [3, 4, 5, 6, 7] }
    //                             }
    //                         ],
    //                         init: function (api, node, config) {
    //                             $(node).removeClass('btn-secondary');
    //                             $(node).parent().removeClass('btn-group');
    //                             setTimeout(function () {
    //                                 $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
    //                             }, 50);
    //                         }
    //                     },
                        
    //                 ],

    //                 language: {
    //                     paginate: {
    //                         // remove previous & next text from pagination
    //                         previous: '&nbsp;',
    //                         next: '&nbsp;'
    //                     }
    //                 }
    //             });
    //             $('div.head-label').html('<h6 class="mb-0">Event List</h6>');
    //         }

    //         // Flat Date picker
    //         if (dt_date_table.length) {
    //             dt_date_table.flatpickr({
    //                 monthSelectorType: 'static',
    //                 dateFormat: 'm/d/Y'
    //             });
    //         }
            
    //         // Add New record
    //         // ? Remove/Update this code as per your requirements ?
    //         var count = 101;
    //         $('.data-submit').on('click', function () {
    //             var $new_name = $('.add-new-record .dt-full-name').val(),
    //                 $new_post = $('.add-new-record .dt-post').val(),
    //                 $new_email = $('.add-new-record .dt-email').val(),
    //                 $new_date = $('.add-new-record .dt-date').val(),
    //                 $new_salary = $('.add-new-record .dt-salary').val();

    //             if ($new_name != '') {
    //                 dt_basic.row
    //                     .add({
    //                         responsive_id: null,
    //                         id: count,
    //                         full_name: $new_name,
    //                         post: $new_post,
    //                         email: $new_email,
    //                         start_date: $new_date,
    //                         salary: '$' + $new_salary,
    //                         status: 5
    //                     })
    //                     .draw();
    //                 count++;
    //                 $('.modal').modal('hide');
    //             }
    //         });

    //         // Delete Record
    //         $('.datatables-basic tbody').on('click', '.delete-record', function () {
    //             dt_basic.row($(this).parents('tr')).remove().draw();
    //         });
    //     });
        function onShortListsubmit()
        {
            
            if (selectedBidId) {
                console.log("Selected Bid ID: " + selectedBidId);
                // You can also send it to a backend using AJAX
                $.post('{{route('transporter.shortlist')}}', { tr_id: selectedBidId }, function(response) {
                    console.log(response);
                });
            } else {
                console.log("Please select a bid before shortlisting.");
            }
        }
        function setShortlist()
        {
            var selectedBidId = $("input[name='podetail']:checked").val();
            document.getElementById('action_type').value = "shortlist";
            document.getElementById('short_list_bid_id').value = selectedBidId;

            document.getElementById('approve_reject_heading_label').textContent = "Shortlist " + "{{$typeName}}";

        }
        function endBid()
        {
            var selectedBidId = $("input[name='podetail']:checked").val();
            document.getElementById('action_type').value = "completed";
            document.getElementById('short_list_bid_id').value = selectedBidId;

            document.getElementById('approve_reject_heading_label').textContent = "Shortlist " + "{{$typeName}}";

        }
        function closeBid()
        {
            console.log('check closing');
            let selectedBidId = $('#bid_id').val(); 
            let remarks = $('#close_reason').val(); 
            console.log(selectedBidId);
            $.post('{{route('transporter.closeBid')}}', { tr_id: selectedBidId,remarks:remarks?remarks:"" }, function(response) {
                Swal.fire({
                    title: response.title,
                    text: response.message,
                    icon: response.type,
                });
                location.reload();    
            });
            
        }
        function reOpenBid()
        {
            let selectedBidId = $('#bid_id').val(); 
            let remarks = $('#close_reason').val(); 
            $.post('{{route('transporter.reOpenBid')}}', { tr_id: selectedBidId,remarks:remarks?remarks:"" }, function(response) {
                    console.log(response);
                    Swal.fire({
                    title: response.title,
                    text: response.message,
                    icon: response.type,
                });
                location.reload();    
            });
        }
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        });
        // $(document).ready(function() {
        //     $('.select2').select2(); // Initialize Select2

        //     $('.select2').on('select2:open', function() {
        //         setTimeout(() => {
        //             document.querySelector('.select2-search__field').focus();
        //         }, 100);
        //     });
        // });

        $('#issues').on('change', function() {
            var issue_id = $(this).val();
            var seriesSelect = $('#series');

            seriesSelect.empty(); // Clear any existing options
            seriesSelect.append('<option value="">Select</option>');

            if (issue_id) {
                $.ajax({
                    url: "{{ url('get-series') }}/" + issue_id,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $.each(data, function(key, value) {
                            seriesSelect.append('<option value="' + key + '">' + value + '</option>');
                        });
                    }
                });
            }
        });

        $('#series').on('change', function() {
            var book_id = $(this).val();
            var request = $('#requestno');

            request.val(''); // Clear any existing options
            
            if (book_id) {
                $.ajax({
                    url: "{{ url('get-request') }}/" + book_id,
                    type: "GET",
                    dataType: "json",
                    success: function(data) 
                        {
                            if (data.requestno) {
                            request.val(data.requestno);
                        }
                    }
                });
            }
        });

        function onChangeSeries(element)
        {
            document.getElementById("order_no_input").value = 12345;
        }
        
        function storeAddress(element)
        {
            value = element.value;
            index = element.getAttribute('data-id');
            country_ele =$('#p_country_id_'+index);
            state_ele =$('#p_state_id_'+index);
            console.log(country_ele[0]);
            console.log(value);
            $.ajax({
                url: "{{ route('transporter.get-address')}}",
                type: "GET",
                dataType: "json",
                data:{
                    store_id : value,
                },
                success: function(data) {
                    console.log(data);
                    $(`#p_address_id_${index}`).val(data.address);
                    // $(`#p_city_id_${index}`).val(data.city);
                    $(`#p_country_id_${index}`).val(data?.s_address?.country_id ?? "").trigger('change');
                    $(`#p_pin_code_${index}`).val(data?.s_address?.pincode);
                    console.log(data?.ss_address?.state_id);
                    setState(country_ele[0],"p",data?.s_address?.state_id??null);
                    setCities(state_ele[0],"p",data?.s_address?.city_id??null,data?.s_address?.state_id??null);

                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });

        }
        function setState(element,type,state_id=null)
        {
            console.log(element);
            let value = element.value;
            index = element.getAttribute('data-id');
            console.log(value);
            $.ajax({
                url: "{{ route('transporter.get-state')}}",
                type: "GET",
                dataType: "json",
                data:{
                    country_id : value,
                },
                success: function(data) {
                    console.log(data,index);
                    let selectElement = $(`#${type}_state_id_${index}`); // Target the select element
                    console.log(state_id);
                    console.log(selectElement, 'GGG');

                    selectElement.empty(); // Clear previous options
                    selectElement.append('<option value=""></option>'); // Default option
                    // Loop through the response and append options dynamically
                    $.each(data.states, function(index, item) {
                    var additional = "";

                        if(item.id==state_id){
                            additional = 'selected';
                        }
                        selectElement.append(`<option value="${item.id}" ${additional}>${item.name}</option>`);
                    });
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }
        function setCities(element,type,city_id=null,s_id=null)
        {
            console.log(element);
            let value = element.value;
            index = element.getAttribute('data-id');
            console.log(value);
            $.ajax({
                url: "{{ route('transporter.get-city')}}",
                type: "GET",
                dataType: "json",
                data:{
                    state_id : s_id??value,
                },
                success: function(data) {
                    console.log(data,index);
                    let selectElement = $(`#${type}_city_id_${index}`); // Target the select element
                    console.log(selectElement,type,index, 'DDD');
                    console.log(city_id);
                    selectElement.empty(); // Clear previous options
                    selectElement.append('<option value=""></option>'); // Default option
                    // Loop through the response and append options dynamically
                    $.each(data.cities, function(index, item) {
                    var additional = "";

                        if(item.id==city_id){
                            additional = 'selected';
                        }
                        selectElement.append(`<option value="${item.id}" ${additional}>${item.name}</option>`);
                    });
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }
        function addLocationtoDataRow(element,type){
            console.log(element);
            table_row = document.getElementById(type);
            let error =false;
            let loc_data ='';
            const table_index = document.getElementsByClassName(`${type}_data`).length+1 || 1;
            let newRow = `<tr id = '${type}_data_${table_index}' class = '${type}_data'><td>${table_index}</td>`;
            Array.from(table_row.cells).forEach((td,index) => {
                let a = $(td).find('select').val() || $(td).find('input').val();
                let name = $(td).find('input,select').attr('name');
                if($(td).find('select').length){
                    if(a && a.length>0 ){
                        console.log($(td).find('select'));
                        let b = $(td).find('select option:selected');

                        loc_data=$(td).find('select').attr("id");
                        req_name=$(td).find('select').attr("req_name");
                        var x =`<td data-id=${a}>${b.text()}<input type='hidden' value=${a} name = ${name}></td>`; 
                        if ($(td).find('select').attr("id") === `pick_up_location`) {
                            $(b).prop("disabled", true); // Better for form elements
                            console.log('abc');
                        }
                    }
                    else{
                        Swal.fire({
                            title: 'Error!',
                            text: 'Please Fill All the Required Data',
                            icon: 'error',
                        });
                        error = true;
                    }
                }
                else if(a && a.length>0){
                    var x = `<td>${a}<input type='hidden' value="${a}" name = ${name}></td>`; 
                }   
                newRow+=x;
            });
            if(error){
                Swal.fire({
                            title: 'Error!',
                            text: 'Please Fill All the Required Data',
                            icon: 'error',
                        });
                return false;
            }
            newRow+=`<td class = "center-align-content"><a href="#"
                    class="text-primary remove_${type}_location" onclick="removeLocationtoDataRow('${type}',${table_index},${loc_data})" id="remove_drop_off"><i class='text-danger'
                        data-feather="trash"></i></a>
            </td></tr>`;
            // let rowData = Array.from(table_row.cells).map(cell => cell.textContent.trim());
        
        
            $(`#${type}_table`).append(newRow); 
            renderIcons();
            let tr = document.querySelector("tr"); // Get the row

            table_row.querySelectorAll("td").forEach(td => {

                // Reset inputs, selects, and textareas
                td.querySelectorAll("input, select, textarea").forEach(el => {
                    if (el.tagName == "SELECT") {
                        $(el).val('').trigger('change'); // Reset dropdowns to first option
                    } else {
                        el.value = ""; // Clear text inputs and textareas
                    }
                });
            });


        }

        function reRenderLocationsData(type) {
            table_rows = document.getElementsByClassName(`${type}_data`);
            for (let index = 0; index < table_rows.length; index++) {
                table_rows[index].id=`${type}_data_${index}`;
                $('#'+table_rows[index].id).find('td:first').text(index+1);
            }
        }
        function removeLocationtoDataRow(type,index,loc_val){
            var deletedLocationIds = JSON.parse(localStorage.getItem(`deleted${type}LocationIds`));

            table_ele=$(this).closest('tr');
            console.log(table_ele);
            console.log(type,index);
            ele=document.getElementById(`${type}_data_${index}`);
            $("#pick_up_location").find(`option[value="${$(ele).find('td[data-id]:first').attr('data-id')}"]`).prop('disabled',false);
            ele.remove();
            if (table_ele.attr('data-id')) {
                deletedLocationIds.push(table_ele.attr('data-id'));
            }
            localStorage.setItem(`deleted${type}LocationIds`, JSON.stringify(deletedItemIds));
            reRenderLocationsData(type);

        }
        // editscript();
        // function editscript()
        // {
        //     localStorage.setItem('deletedpLocationIds', JSON.stringify([]));
        //     localStorage.setItem('deleteddLocationIds', JSON.stringify([]));

        // }
        function onChangeCustomer(selectElementId) 
        {
            const selectedOption = document.getElementById(selectElementId);
            const paymentTermsDropdown = document.getElementById('payment_terms_dropdown');
            const currencyDropdown = document.getElementById('currency_dropdown');
            //Set Currency
            const currencyId = selectedOption.getAttribute('currency_id');
            const currency = selectedOption.getAttribute('currency');
            const currencyCode = selectedOption.getAttribute('currency_code');
            if (currencyId && currency) {
                const newCurrencyValues = `
                    <option value = '${currencyId}' > ${currency} </option>
                `;
                currencyDropdown.innerHTML = newCurrencyValues;
                $("#currency_code_input").val(currencyCode);
            }
            else {
                currencyDropdown.innerHTML = '';
                $("#currency_code_input").val("");
            }
            //Set Payment Terms
            const paymentTermsId = selectedOption.getAttribute('payment_terms_id');
            const paymentTerms = selectedOption.getAttribute('payment_terms');
            const paymentTermsCode = selectedOption.getAttribute('payment_terms_code');
            if (paymentTermsId && paymentTerms) {
                const newPaymentTermsValues = `
                    <option value = '${paymentTermsId}' > ${paymentTerms} </option>
                `;
                paymentTermsDropdown.innerHTML = newPaymentTermsValues;
                $("#payment_terms_code_input").val(paymentTermsCode);
            }
            else {
                paymentTermsDropdown.innerHTML = '';
                $("#payment_terms_code_input").val("");
            }
            //Get Addresses (Billing + Shipping)
            changeDropdownOptions(document.getElementById('customer_id_input'), ['billing_address_dropdown','shipping_address_dropdown'], ['billing_addresses', 'shipping_addresses'], '/customer/addresses/', 'vendor_dependent');
        }

        function changeDropdownOptions(mainDropdownElement, dependentDropdownIds, dataKeyNames, routeUrl, resetDropdowns = null, resetDropdownIdsArray = [])
        {
            const mainDropdown = mainDropdownElement;
            const secondDropdowns = [];
            const dataKeysForApi = [];
            if (Array.isArray(dependentDropdownIds)) {
                dependentDropdownIds.forEach(elementId => {
                    if (elementId.type && elementId.type == "class") {
                        const multipleUiDropDowns = document.getElementsByClassName(elementId.value);
                        const secondDropdownInternal = [];
                        for (let idx = 0; idx < multipleUiDropDowns.length; idx++) {
                            secondDropdownInternal.push(document.getElementById(multipleUiDropDowns[idx].id));
                        }
                        secondDropdowns.push(secondDropdownInternal);
                    } else {
                        secondDropdowns.push(document.getElementById(elementId));
                    }
                });
            } else {
                secondDropdowns.push(document.getElementById(dependentDropdownIds))
            }

            if (Array.isArray(dataKeyNames)) {
                dataKeyNames.forEach(key => {
                    dataKeysForApi.push(key);
                })
            } else {
                dataKeysForApi.push(dataKeyNames);
            }

            if (dataKeysForApi.length !== secondDropdowns.length) {
                console.log("Dropdown function error");
                return;
            }

            if (resetDropdowns) {
                const resetDropdownsElement = document.getElementsByClassName(resetDropdowns);
                for (let index = 0; index < resetDropdownsElement.length; index++) {
                    resetDropdownsElement[index].innerHTML = `<option value = '0'>Select</option>`;
                }
            }

            if (resetDropdownIdsArray) {
                if (Array.isArray(resetDropdownIdsArray)) {
                    resetDropdownIdsArray.forEach(elementId => {
                        let currentResetElement = document.getElementById(elementId);
                        if (currentResetElement) {
                            currentResetElement.innerHTML = `<option value = '0'>Select</option>`;
                        }
                    });
                } else {
                    const singleResetElement = document.getElementById(resetDropdownIdsArray);
                    if (singleResetElement) {
                        singleResetElement.innerHTML = `<option value = '0'>Select</option>`;
                    }            
                }
            }

            const apiRequestValue = mainDropdown?.value;
            const apiUrl = routeUrl + apiRequestValue;
            fetch(apiUrl, {
                method : "GET",
                headers : {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            }).then(response => response.json()).then(data => {
                if (mainDropdownElement.id == "customer_id_input") {
                    if (data?.data?.currency_exchange?.status == false || data?.data?.error_message) {
                        Swal.fire({
                            title: 'Error!',
                            text: data?.data?.currency_exchange?.message ? data?.data?.currency_exchange?.message : data?.data?.error_message,
                            icon: 'error',
                        });
                        mainDropdownElement.value = "";
                        document.getElementById('currency_dropdown').innerHTML = "";
                        document.getElementById('currency_dropdown').value = "";
                        document.getElementById('payment_terms_dropdown').innerHTML = "";
                        document.getElementById('payment_terms_dropdown').value = "";
                        document.getElementById('current_billing_address_id').value = "";
                        document.getElementById('current_shipping_address_id').value = "";
                        document.getElementById('current_billing_address').textContent = "";
                        document.getElementById('current_shipping_address').textContent = "";
                        document.getElementById('customer_id_input').value = "";
                        return;
                    }
                    
                }
                // console.clear();
                // console.log(data);
                // return false;
                secondDropdowns.forEach((currentElement, idx) => {
                    if (Array.isArray(currentElement)) {
                        currentElement.forEach(currentElementInternal => {
                            currentElementInternal.innerHTML = `<option value = '0'>Select</option>`;
                            const response = data.data;
                            response?.[dataKeysForApi[idx]]?.forEach(item => {
                                const option = document.createElement('option');
                                option.value = item.value;
                                option.textContent = item.label;
                                currentElementInternal.appendChild(option);
                            })
                        });
                    } else {
                        
                        currentElement.innerHTML = `<option value = '0'>Select</option>`;
                        const response = data.data;
                        response?.[dataKeysForApi[idx]]?.forEach((item, idxx) => {
                            if (idxx == 0) {
                                if (currentElement.id == "billing_address_dropdown") {
                                    document.getElementById('current_billing_address').textContent = item.label;
                                    document.getElementById('current_billing_address_id').value = item.id;
                                    // $('#billing_country_id_input').val(item.country_id).trigger('change');
                                    // changeDropdownOptions(document.getElementById('billing_country_id_input'), ['billing_state_id_input'], ['states'], '/states/', null, ['billing_city_id_input']);
                                }
                                if (currentElement.id == "shipping_address_dropdown") {
                                    document.getElementById('current_shipping_address').textContent = item.label;
                                    document.getElementById('current_shipping_address_id').value = item.id;
                                    document.getElementById('current_shipping_country_id').value = item.country_id;
                                    document.getElementById('current_shipping_state_id').value = item.state_id;
                                    // $('#shipping_country_id_input').val(item.country_id).trigger('change');
                                    // changeDropdownOptions(document.getElementById('shipping_country_id_input'), ['shipping_state_id_input'], ['states'], '/states/', null, ['shipping_city_id_input']);
                                }
                                // if (currentElement.id == "billing_state_id_input") {
                                //     changeDropdownOptions(document.getElementById('billing_state_id_input'), ['billing_city_id_input'], ['cities'], '/cities/', null, []);
                                //     $('#billing_state_id_input').val(item.state_id).trigger('change');
                                //     console.log("STATEID", item);

                                // }
                                // if (currentElement.id == "shipping_state_id_input") {
                                //     changeDropdownOptions(document.getElementById('shipping_state_id_input'), ['shipping_city_id_input'], ['cities'], '/cities/', null, []);
                                //     $('#shipping_state_id_input').val(item.state_id).trigger('change');
                                //     console.log("STATEID", item);

                                // }
                            }
                            const option = document.createElement('option');
                            option.value = item.value;
                            option.textContent = item.label;
                            if (idxx == 0 && (currentElement.id == "billing_address_dropdown" || currentElement.id == "shipping_address_dropdown")) {
                                option.selected = true;
                            }
                            currentElement.appendChild(option);
                        })
                    }
                });
            }).catch(error => {
                console.log("Error : ", error);
            })
        }

        function itemOnChange(selectedElementId, index, routeUrl) // Retrieve element and set item attiributes
        {
            const selectedElement = document.getElementById(selectedElementId);
            const ItemIdDocument = document.getElementById(selectedElementId + "_value");
            if (selectedElement && ItemIdDocument) {
                ItemIdDocument.value = selectedElement.dataset?.id;
                const apiRequestValue = selectedElement.dataset?.id;
                const apiUrl = routeUrl + apiRequestValue;
                fetch(apiUrl, {
                    method : "GET",
                    headers : {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                }).then(response => response.json()).then(data => {
                    const response = data.data;
                    selectedElement.setAttribute('attribute-array', JSON.stringify(response.attributes));
                    selectedElement.setAttribute('item-name', response.item.item_name);
                    selectedElement.setAttribute('hsn_code', (response.item_hsn));
                    document.getElementById('items_name_' + index).value = response.item.item_name;
                    setItemAttributes('items_dropdown_' + index, index);

                    onItemClick(index);
                    
                }).catch(error => {
                    console.log("Error : ", error);
                })
            }
        }

        function setItemAttributes(elementId, index, disabled=false)
        {
            document.getElementById('attributes_table_modal').setAttribute('item-index',index);
            var elementIdForDropdown = elementId;
            const dropdown = document.getElementById(elementId);
            const attributesTable = document.getElementById('attribute_table');
            if (dropdown) {
                const attributesJSON = JSON.parse(dropdown.getAttribute('attribute-array'));
                var innerHtml = ``;
                attributesJSON.forEach((element, index) => {
                    var optionsHtml = ``;
                    console.log(element,'eleeele');
                    console.log(disabled);
                    element.values_data.forEach(value => {
                        optionsHtml += `
                        <option value = '${value.id}' ${value.selected ? 'selected' : ''}>${value.value}</option>
                        `
                    });
                    innerHtml += `
                    <tr>
                    <td>
                    ${element.group_name}
                    </td>
                    <td>
                    <select ${disabled ? 'disabled' : ''} class="form-select select2 disable_on_edit" id = "attribute_val_${index}" style = "max-width:100% !important;" onchange = "changeAttributeVal(this, ${elementIdForDropdown}, ${index});">
                        <option>Select</option>
                        ${optionsHtml}
                    </select> 
                    </td>
                    </tr>
                    `
                });
                attributesTable.innerHTML = innerHtml;
                if (attributesJSON.length == 0) {
                    document.getElementById('item_qty_' + index).focus();
                    document.getElementById('attribute_button_' + index).disabled = true;
                } else {
                    $("#attribute").modal("show");
                    document.getElementById('attribute_button_' + index).disabled = false;
                }
            }

        }

        function changeAttributeVal(selectedElement, elementId, index)
        {
            const attributesJSON = JSON.parse(elementId.getAttribute('attribute-array'));
            const selectedVal = selectedElement.value;
            attributesJSON.forEach((element, currIdx) => {
                if (currIdx == index) {
                    element.values_data.forEach(value => {
                    if (value.id == selectedVal) {
                        value.selected = true;
                    }
                });
                }
            });
            elementId.setAttribute('attribute-array', JSON.stringify(attributesJSON));
        }
        function addItemRow()
        {
            // var docType = $("#service_id_input").val();
            // var invoiceToFollow = $("#service_id_input").val() == "yes";
            const tableElementBody = document.getElementById('item_header');
            const previousElements = document.getElementsByClassName('item_header_rows');
            const newIndex = previousElements.length ? previousElements.length : 0;
            if (newIndex == 0) {
                let addRow = $('#series_id_input').val() && $("#order_no_input").val() &&  $('#order_no_input').val() && $('#order_date_input').val();
                if (!addRow) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please fill all the header details first',
                    icon: 'error',
                });
                return;
                }
            } else {
                let addRow = $('#items_dropdown_' + (newIndex - 1)).val() &&  parseFloat($('#item_qty_' + (newIndex - 1)).val()) > 0;
                if (!addRow) {
                    Swal.fire({
                    title: 'Error!',
                    text: 'Please fill all the previous item details first',
                    icon: 'error',
                });
                return;
                }
            }
            const newItemRow = document.createElement('tr');
            newItemRow.className = 'item_header_rows';
            newItemRow.id = "item_row_" + newIndex;
            newItemRow.onclick = function () {
                onItemClick(newIndex);
            };
            var headerStoreId = $("#store_id_input").val();
            var headerStoreCode = $("#store_id_input").attr("data-name");
            
            newItemRow.innerHTML = `
            <tr id = "item_row_${newIndex}">
                <td class="customernewsection-form">
                   <div class="form-check form-check-primary custom-checkbox">
                       <input type="checkbox" class="form-check-input item_row_checks" id="item_row_check_${newIndex}" del-index = "${newIndex}">
                       <label class="form-check-label" for="Email"></label>
                   </div> 
                </td>
                <td class="poprod-decpt"> 
                   
                   <input type="text" id = "items_dropdown_${newIndex}" data-index = '${newIndex}' name="item_code[${newIndex}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input" autocomplete="off" data-name="" data-code="" data-id="" hsn_code = "" item_name = "" attribute-array = "[]" specs = "[]" item-locations = "[]">
                   <input type = "hidden" name = "item_id[]" id = "items_dropdown_${newIndex}_value"></input>

                </td>
               
                <td class="poprod-decpt">
                    <input type="text" id = "items_name_${newIndex}" name = "item_name[${newIndex}]" class="form-control mw-100"   value = "" readonly>
                </td>
                <td class="poprod-decpt"> 
                   <button id = "attribute_button_${newIndex}" type = "button" data-bs-toggle="modal" onclick = "setItemAttributes('items_dropdown_${newIndex}', ${newIndex});" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                   <input type = "hidden" name = "attribute_value_${newIndex}" />
                </td>
                <td>
                   <select class="form-select" name = "uom_id[]" id = "uom_dropdown_${newIndex}">
                       
                   </select> 
                </td>
                <td><input type="text" id = "item_qty_${newIndex}" name = "item_qty[${newIndex}]" oninput = "changeItemQty(this, ${newIndex});" class="form-control item_store_locations mw-100 text-end" onblur = "setFormattedNumericValue(this);"/></td>
                <td>
                   <input type="text" id = "item_remarks_${newIndex}" name = "item_remarks[]" class="form-control mw-100"   value = "" />
                </td>
             </tr>
            `;
            tableElementBody.appendChild(newItemRow);
            initializeAutocomplete1("items_dropdown_" + newIndex, newIndex);
            renderIcons();
            disableHeader();

            const qtyInput = document.getElementById('item_qty_' + newIndex);
        }

        function deleteItemRows()
        {
            var deletedItemIds = JSON.parse(localStorage.getItem('deletedpwoItemIds'));
            const allRowsCheck = document.getElementsByClassName('item_row_checks');
            let deleteableElementsId = [];
            for (let index = allRowsCheck.length - 1; index >= 0; index--) {  // Loop in reverse order
                if (allRowsCheck[index].checked) {
                    const currentRowIndex = allRowsCheck[index].getAttribute('del-index');
                    const currentRow = document.getElementById('item_row_' + index);
                    if (currentRow) {
                        if (currentRow.getAttribute('data-id')) {
                            deletedItemIds.push(currentRow.getAttribute('data-id'));
                        }
                        deleteableElementsId.push('item_row_' + currentRowIndex);
                    }
                }
            }
            for (let index = 0; index < deleteableElementsId.length; index++) {
                document.getElementById(deleteableElementsId[index])?.remove();
            }
            console.log(deletedItemIds);
            localStorage.setItem('deletedpwoItemIds', JSON.stringify(deletedItemIds));
            const allRowsNew = document.getElementsByClassName('item_row_checks');
            if (allRowsNew.length > 0) {
                for (let idx = 0; idx < allRowsNew.length; idx++) {
                    const currentRowIndex = allRowsCheck[idx].getAttribute('del-index');
                }
                disableHeader();
            } else {
                setAllTotalFields();
                enableHeader();
            }
            
        }

        function setItemRemarks(elementId) {
            const currentRemarksValue = document.getElementById(elementId).value;
            const modalInput = document.getElementById('current_item_remarks_input');
            modalInput.value = currentRemarksValue;
            modalInput.setAttribute('current-item', elementId);
        }

        function changeItemRemarks(element)
        {
            const elementToBeChanged = document.getElementById(element.getAttribute('current-item'));
            if (elementToBeChanged) {
                elementToBeChanged.value = element.value;
            }
        }

        function changeItemValue(index) // Single Item Value
        {
            const currentElement = document.getElementById('item_value_' + index);
            if (currentElement) {
                const currentQty = document.getElementById('item_qty_' + index).value;
                const currentRate = document.getElementById('item_rate_' + index).value;
                currentElement.value = (parseFloat(currentRate ? currentRate : 0) * parseFloat(currentQty ? currentQty : 0)).toFixed(2);
            }
            changeItemTotal(index);
            changeAllItemsTotalTotal();
        }

        function changeItemTotal(index) //Single Item Total
        {
            const currentElementValue = document.getElementById('item_value_' + index).value;
            const currentElementDiscount = document.getElementById('item_discount_' + index).value;
            const newItemTotal = (parseFloat(currentElementValue ? currentElementValue : 0) - parseFloat(currentElementDiscount ? currentElementDiscount : 0)).toFixed(2);
            document.getElementById('item_total_' + index).value = newItemTotal;

        }

        function changeAllItemsValue()
        {

        }

        function changeAllItemsTotal() //All items total value
        {
            const elements = document.getElementsByClassName('item_values_input');
            var totalValue = 0;
            for (let index = 0; index < elements.length; index++) {
                totalValue += parseFloat(elements[index].value ? elements[index].value : 0);
            }
            document.getElementById('all_items_total_value').innerText = (totalValue).toFixed(2);
            document.getElementById('all_items_total_value').innerText = (totalValue) ;
        }
        function changeAllItemsDiscount() //All items total discount
        {
            const elements = document.getElementsByClassName('item_discounts_input');
            var totalValue = 0;
            for (let index = 0; index < elements.length; index++) {
                totalValue += parseFloat(elements[index].value ? elements[index].value : 0);
            }
            document.getElementById('all_items_total_discount').innerText = (totalValue).toFixed(2);
            changeAllItemsTotalTotal();
        }
        function changeAllItemsTotalTotal() //All items total
        {
            const elements = document.getElementsByClassName('item_totals_input');
            var totalValue = 0;
            for (let index = 0; index < elements.length; index++) {
                totalValue += parseFloat(elements[index].value ? elements[index].value : 0);
            }
            const totalElements = document.getElementsByClassName('all_tems_total_common');
            for (let index = 0; index < totalElements.length; index++) {
                totalElements[index].innerText = (totalValue).toFixed(2);
            }
        }

        function changeItemQty(element, index)
        {
            var inputNumValue = parseFloat(element.value ? element.value  : 0);
            var itemAndAttrCheck = checkSelectedItemAndAttributes(index);
            if (itemAndAttrCheck) {
                Swal.fire({
                    title: 'Error!',
                    text: itemAndAttrCheck,
                    icon: 'error',
                });
                element.value = 0;
                return;
            }
            if (element.hasAttribute('max'))
            {
                var maxInputVal = parseFloat(element.getAttribute('max'));
                console.log(inputNumValue,maxInputVal,"vaaaaaalllluuuueeee");
                if (inputNumValue > maxInputVal) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Quantity cannot be greater than ' + maxInputVal,
                        icon: 'error',
                    });
                    element.value = (parseFloat(maxInputVal ? maxInputVal  : 0)).toFixed(2)
                    return;
                }
            }
        }

        function checkSelectedItemAndAttributes(index)
        {
            var itemCode = document.getElementById('items_dropdown_' + index).value;
            if (!itemCode) {
                return "Please select an item first";
            }
            var itemAttributes = JSON.parse(document.getElementById('items_dropdown_' + index).getAttribute('attribute-array'));
            var itemAttributesString = "";
            if (itemAttributes.length > 0) {
                var allAttributesSelected = true;
                itemAttributes.forEach((itemAttr, itemAttrIndex) => {
                    var currentItemSelected = false;
                    itemAttr.values_data.forEach(valData => {
                        if (valData.selected) {
                            currentItemSelected = true;
                            itemAttributesString += (itemAttrIndex == 0 ? '' : ',')  + itemAttr.id + ":" + valData.id;
                        }
                    });
                    if (!currentItemSelected) {
                        allAttributesSelected = false;
                    }
                });
                if (!allAttributesSelected) {
                    return "Please select item attributes first";
                }
            }
            //Check if same item with same attributes already exists
            var allItemRows = document.getElementsByClassName('item_header_rows');
            console.log(allItemRows, "ALL ITEMS ROW");
            var sameItemExists = false;
            for (let itemIndex = 0; itemIndex < allItemRows.length; itemIndex++) {
                if (index != allItemRows[itemIndex].getAttribute('data-index')) {
                    var currentItemCodeElement = document.getElementById('items_dropdown_' + itemIndex);
                    if (currentItemCodeElement) {
                        if (currentItemCodeElement.value == itemCode) { //Item Code matched
                            //Check same attributes
                            var currentItemAttributes = JSON.parse(currentItemCodeElement.getAttribute("attribute-array"));
                            var currentItemAttributesString = '';
                            currentItemAttributes.forEach((currentItemAttribute, currentItemAttributeIndex) => {
                                currentItemAttribute.values_data.forEach(valData => {
                                    if (valData.selected) {
                                        currentItemAttributesString += (currentItemAttributeIndex == 0 ? '' : ',') + currentItemAttribute.id + ":" + valData.id;
                                    }
                                });
                            });
                            if (itemAttributesString == currentItemAttributesString) {
                                sameItemExists = true;
                            }
                        }
                    }
                }
            }
            console.log(itemAttributesString, currentItemAttributesString, "SALE ORDER ATRR");
            if (sameItemExists) {
                return "Item with same attributes already exists";
            }
            //Item Type
            return null;
        }

        function addHiddenInput(id, val, name, classname, docId)
        {
            const newHiddenInput = document.createElement("input");
            newHiddenInput.setAttribute("type", "hidden");
            newHiddenInput.setAttribute("name", name);
            newHiddenInput.setAttribute("id", id);
            newHiddenInput.setAttribute("value", val);
            newHiddenInput.setAttribute("class", classname);
            document.getElementById(docId).appendChild(newHiddenInput);
        }

        function renderIcons()
        {
            feather.replace()
        }

        function onItemClick(itemRowId)
        {
            if(itemRowId >= 0 ){

                const docType = "{{$type ? $type : 'si'}}";
                
                const hsn_code = document.getElementById('items_dropdown_'+ itemRowId).getAttribute('hsn_code');
                const item_name = document.getElementById('items_dropdown_'+ itemRowId).getAttribute('item-name');
                const attributes = JSON.parse(document.getElementById('items_dropdown_'+ itemRowId).getAttribute('attribute-array'));
                const specs = JSON.parse(document.getElementById('items_dropdown_'+ itemRowId).getAttribute('specs'));
                console.log(specs);
                // document.getElementById('current_item_name').textContent = item_name;
                
                const qtDetailsRow = document.getElementById('current_item_qt_no_row');
                const qtDetails = document.getElementById('current_item_qt_no');
                
                let qtDocumentNo = document.getElementById('qt_document_no_'+ itemRowId);
                let qtBookCode = document.getElementById('qt_book_code_'+ itemRowId);
                let qtDocumentDate = document.getElementById('qt_document_date_'+ itemRowId);

                qtDocumentNo = qtDocumentNo?.value ? qtDocumentNo.value : '';
                qtBookCode = qtBookCode?.value ? qtBookCode.value : '';
                qtDocumentDate = qtDocumentDate?.value ? qtDocumentDate.value : '';
                referenceNo = document.getElementById('reference_no_input').value;
                console.log(referenceNo,'reference');
                if (qtDocumentNo && qtBookCode && qtDocumentDate) {
                    qtDetailsRow.style.display = "table-row";
                    qtDetails.innerHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Reference From</strong>:<span class="badge rounded-pill badge-light-primary"><strong>Bid No: </strong>: ${qtBookCode + "-" + qtDocumentNo}</span><span class="badge rounded-pill badge-light-primary"><strong>Date & Time Of Loading: </strong>: ${qtDocumentDate}</span>`;
                    if (referenceNo.length > 0) {
                        qtDetails.innerHTML += `<span class="badge rounded-pill badge-light-primary"><strong>Reference No:</strong> ${referenceNo}</span>`;
                    }
                } else {
                    qtDetailsRow.style.display = "none";
                    qtDetails.innerHTML = ``;
                }
                // document.getElementById('current_item_hsn_code').innerText = hsn_code;
                var innerHTMLAttributes = ``;
                console.log(attributes);
                attributes.forEach(element => {
                    var currentOption = '';
                    console.log(element,'element');
                    element.values_data.forEach(subElement => {
                        if (subElement.selected) {
                            currentOption = subElement.value;
                        }
                    });
                    innerHTMLAttributes +=  `<span class="badge rounded-pill badge-light-primary"><strong>${element.group_name}</strong>: ${currentOption}</span>`;
                });
                var specsInnerHTML = ``;
                specs.forEach(spec => {
                    specsInnerHTML +=  `<span class="badge rounded-pill badge-light-primary "><strong>${spec.specification_name}</strong>: ${spec.value}</span>`;
                });

                document.getElementById('current_item_attributes').innerHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Attributes</strong>:` + innerHTMLAttributes;
                if (innerHTMLAttributes) {
                    document.getElementById('current_item_attribute_row').style.display = "table-row";
                } else {
                    document.getElementById('current_item_attribute_row').style.display = "none";
                }
                document.getElementById('current_item_specs').innerHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Specifications</strong>:` + specsInnerHTML;
                if (specsInnerHTML) {
                    document.getElementById('current_item_specs_row').style.display = "table-row";
                } else {
                    document.getElementById('current_item_specs_row').style.display = "none";
                }
                const remarks = document.getElementById('item_remarks_' + itemRowId).value;
                if (specsInnerHTML) {
                    document.getElementById('current_item_specs_row').style.display = "table-row";
                } else {
                    document.getElementById('current_item_specs_row').style.display = "none";
                }
                // document.getElementById('current_item_description').textContent = remarks;
                // if (remarks) {
                //     document.getElementById('current_item_description_row').style.display = "table-row";
                // } else {
                    //     document.getElementById('current_item_description_row').style.display = "none";
                    // }
                    
                let itemAttributes = JSON.parse(document.getElementById(`items_dropdown_${itemRowId}`).getAttribute('attribute-array'));
                let selectedItemAttr = [];
                if (itemAttributes && itemAttributes.length > 0) {
                    itemAttributes.forEach(element => {
                        element.values_data.forEach(subElement => {
                            if (subElement.selected) {
                                selectedItemAttr.push(subElement.id);
                            }
                        });
                    });
                }
                $.ajax({
                    url: "{{route('get_item_inventory_details')}}",
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        quantity: document.getElementById('item_qty_' + itemRowId).value,
                        item_id: document.getElementById('items_dropdown_'+ itemRowId + '_value').value,
                        uom_id : document.getElementById('uom_dropdown_' + itemRowId).value,
                        selectedAttr : selectedItemAttr
                    },
                    success: function(data) {
                        if (data.inv_qty && data.inv_uom)
                        document.getElementById('current_item_inventory_details').innerHTML = `
                        <span class="badge rounded-pill badge-light-primary"><strong>Inv. UOM</strong>: ${data.inv_uom}</span>
                        <span class="badge rounded-pill badge-light-primary"><strong>Qty in ${data.inv_uom}</strong>: ${data.inv_qty}</span>
                        `;
                        if (data?.item && data?.item?.category && data?.item?.sub_category) {
                            document.getElementById('current_item_cat_hsn').innerHTML = `
                            <span class="badge rounded-pill badge-light-primary"><strong>Category</strong>: <span id = "item_category">${ data?.item?.category?.name}</span></span>
                            <span class="badge rounded-pill badge-light-primary"><strong>Sub Category</strong>: <span id = "item_sub_category">${ data?.item?.sub_category?.name}</span></span>
                            <span class="badge rounded-pill badge-light-primary"><strong>HSN</strong>: <span id = "current_item_hsn_code">${hsn_code}</span></span>
                            `;
                        }
                        //Stocks
                        
                            document.getElementById('current_item_stocks_row').style.display = "table-row";
                            document.getElementById('current_item_stocks').innerHTML = `
                            <span class="badge rounded-pill badge-light-primary"><strong>Confirmed Stock</strong>: <span id = "item_sub_category">${data?.stocks?.confirmedStocks}</span></span>
                            <span class="badge rounded-pill badge-light-primary"><strong>Pending Stock</strong>: <span id = "item_category">${data?.stocks?.pendingStocks}</span></span>
                            `;
                            var inputQtyBox = document.getElementById('item_qty_' + itemRowId);
                    },
                    error: function(xhr) {
                        console.error('Error fetching customer data:', xhr.responseText);
                    }
                });

                var rateInput = document.getElementById('item_rate_'+ itemRowId);
                var qtyInput = document.getElementById('item_qty_'+ itemRowId);
            }
            else{
                return;
            }
        }

        function openDeliverySchedule(itemRowIndex)
        {
            document.getElementById('delivery_schedule_main_table').setAttribute('item-row-index', itemRowIndex);
            renderPreviousDeliverySchedule(itemRowIndex);
        }

        function renderPreviousDeliverySchedule(itemRowIndex)
        {
                const previousHiddenQtyFields = document.getElementsByClassName('delivery_schedule_qties_hidden_' + itemRowIndex);
                const previousHiddenDateFields = document.getElementsByClassName('delivery_schedule_dates_hidden_' + itemRowIndex);
                    
                    const oldDelivery = document.getElementsByClassName('item_deliveries');
                    if (oldDelivery && oldDelivery.length > 0)
                    {
                        while (oldDelivery.length > 0) {
                            oldDelivery[0].remove();
                        }
                    }
                var isNew = true;
                var newData = ``;
                for (let index = 0; index < previousHiddenQtyFields.length; index++) {
                    const newHTML = document.getElementById('delivery_schedule_main_table').insertRow(index + 2);
                    newHTML.id = "item_delivery_schedule_modal_" + index;
                    newHTML.className = "item_deliveries";
                    newData = `
                        <td>${index+1}</td>
                        <td>${previousHiddenQtyFields[index].value}</td>
                        <td>${previousHiddenDateFields[index].value}</td>
                        <td>
                            <a href="#" class="text-danger" onclick = "removeDeliverySchedule(${index}, ${itemRowIndex});"><i data-feather="trash-2"></i></a>
                        </td>
                    `;
                    newHTML.innerHTML = newData;
                    isNew = false;
                }

                document.getElementById('new_item_delivery_date_input').value = "{{Carbon\Carbon::now() -> format('Y-m-d')}}";

                if (isNew) {
                    document.getElementById('new_item_delivery_qty_input').value = document.getElementById("item_qty_"+itemRowIndex).value;
                } else {
                    document.getElementById('new_item_delivery_qty_input').value = "";
                }
                renderIcons();
        }

        function removeDeliverySchedule(index, itemIndex)
        {
            const removableElement = document.getElementById('item_delivery_schedule_modal_' + index);
            if (removableElement) {
                removableElement.remove();
            }
            document.getElementById("item_delivery_schedule_qty_" + itemIndex + "_" + index)?.remove();
            document.getElementById("item_delivery_schedule_date_" + itemIndex + "_" + index)?.remove();

            renderPreviousDeliverySchedule(itemIndex);
        }

        function addDeliveryScheduleRow()
        {
            const deliveryQty = document.getElementById('new_item_delivery_qty_input').value;
            const deliverySchedule = document.getElementById('new_item_delivery_date_input').value;
            if (deliveryQty && deliverySchedule) //All fields filled
            {
                const ItemRowIndexVal = document.getElementById('delivery_schedule_main_table').getAttribute('item-row-index');

                const previousHiddenFields = document.getElementsByClassName('delivery_schedule_qties_hidden_' + ItemRowIndexVal);

                addDeliveryHiddenInput(ItemRowIndexVal, previousHiddenFields.length ? previousHiddenFields.length : 0);
                
                
            }
        }

        function addDeliveryHiddenInput(itemRow, deliveryIndex)
        {
            addHiddenInput("item_delivery_schedule_qty_" + itemRow + "_" + deliveryIndex, document.getElementById('new_item_delivery_qty_input').value, `item_delivery_schedule_qty[${itemRow}][${deliveryIndex}]`, 'delivery_schedule_qties_hidden_' + itemRow, "item_row_" + itemRow);
            addHiddenInput("item_delivery_schedule_date" + itemRow + "_" + deliveryIndex, document.getElementById('new_item_delivery_date_input').value, `item_delivery_schedule_date[${itemRow}][${deliveryIndex}]`, 'delivery_schedule_dates_hidden_' + itemRow, "item_row_" + itemRow);

            addDeliveryScheduleInTable(itemRow);
        }

        function addDeliveryScheduleInTable(itemRowIndex)
        {
                const previousHiddenQtyFields = document.getElementsByClassName('delivery_schedule_qties_hidden_' + itemRowIndex);
                const previousHiddenDateFields = document.getElementsByClassName('delivery_schedule_dates_hidden_' + itemRowIndex);

                const newIndex = previousHiddenQtyFields.length ? previousHiddenQtyFields.length : 0;

                var newData = ``;
                for (let index = newIndex- 1; index < previousHiddenQtyFields.length; index++) {
                    const newHTML = document.getElementById('delivery_schedule_main_table').insertRow(index + 2);
                    newHTML.className = "item_deliveries";
                    newHTML.id = "item_delivery_schedule_modal_" + newIndex;
                    newData = `
                        <td>${index+1}</td>
                        <td>${previousHiddenQtyFields[index].value}</td>
                        <td>${previousHiddenDateFields[index].value}</td>
                        <td>
                            <a href="#" class="text-danger" onclick = "removeDeliverySchedule(${newIndex}, ${itemRowIndex});"><i data-feather="trash-2"></i></a>
                        </td>
                    `;
                    newHTML.innerHTML = newData;
                }
                
                document.getElementById('new_item_delivery_date_input').value = "{{Carbon\Carbon::now() -> format('Y-m-d')}}";
                document.getElementById('new_item_delivery_qty_input').value = "";
                renderIcons();
        }

        function openModal(id)
        {
            $('#' + id).modal('show');
        }

        function closeModal(id)
        {
            $('#' + id).modal('hide');
            
        }

        function submitForm(status) {
            // Create FormData object
            enableHeader();
        }

        function initializeAutocomplete1(selector, index) {
            let modalId = '#'+$("#" + selector).closest('.modal').attr('id');
            console.log(selector);
            console.log(selector,modalId,index);
            $("#" + selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type:'work_order_items',
                            header_book_id : $("#series_id_input").val()
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: `${item.item_name} (${item.item_code})`,
                                    code: item.item_code || '', 
                                    item_id: item.id,
                                    uom : item.uom,
                                    alternateUoms : item.alternate_u_o_ms,
                                    specifications : item.specifications
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching customer data:', xhr.responseText);
                        }
                    });
                },
                minLength: 0,
                appendTo : modalId,
                select: function(event, ui) {
                    var $input = $(this);
                    var itemCode = ui.item.code;
                    var itemName = ui.item.value;
                    var itemId = ui.item.item_id;

                    $input.attr('data-name', itemName);
                    $input.attr('data-code', itemCode);
                    $input.attr('data-id', itemId);
                    $input.attr('specs', JSON.stringify(ui.item.specifications));
                    $input.val(itemCode);

                    const uomDropdown = document.getElementById('uom_dropdown_' + index);
                    var uomInnerHTML = ``;
                    if (uomDropdown) {
                        uomInnerHTML += `<option value = '${ui.item.uom.id}'>${ui.item.uom.alias}</option>`;
                    }
                    if (ui.item.alternateUoms && ui.item.alternateUoms.length > 0) {
                        var selected = false;
                        ui.item.alternateUoms.forEach((saleUom) => {
                            if (saleUom.is_selling) {
                                uomInnerHTML += `<option value = '${saleUom.uom?.id}' ${selected == false ? "selected" : ""}>${saleUom.uom?.alias}</option>`;
                                selected = true;
                            }
                        });
                    }
                    uomDropdown.innerHTML = uomInnerHTML;
                    document.getElementById('')

                    itemOnChange(selector, index, '/item/attributes/');
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        // $('#itemId').val('');
                        $(this).attr('data-name', '');
                        $(this).attr('data-code', '');
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
    }
    // initializeAutocomplete1("items_dropdown_0", 0);
    

    function disableHeader()
    {
        const disabledFields = document.getElementsByClassName('disable_on_edit');
            for (let disabledIndex = 0; disabledIndex < disabledFields.length; disabledIndex++) {
                disabledFields[disabledIndex].disabled = true;
            }
    }

    function enableHeader()
    {
        const disabledFields = document.getElementsByClassName('disable_on_edit');
            for (let disabledIndex = 0; disabledIndex < disabledFields.length; disabledIndex++) {
                disabledFields[disabledIndex].disabled = false;
            }
    }

    //Function to set values for edit form
    // function editScript()
    // {
    //     localStorage.setItem('deletedpwoItemIds', JSON.stringify([]));
    //     localStorage.setItem('deletedAttachmentIds', JSON.stringify([]));
    //     const order = @json(isset($order) ? $order : null);
    //     if (order) {
    //         //Disable header fields which cannot be changed
    //         disableHeader();
    //         //Item Discount
    //         order.items.forEach((item, itemIndex) => {
    //             itemUomsHTML = ``;
    //             if (item.item.uom && item.item.uom.id) {
    //                 itemUomsHTML += `<option value = '${item.item.uom.id}' ${item.item.uom.id == item.uom_id ? "selected" : ""}>${item.item.uom.alias}</option>`;
    //             }
    //             item.item.alternate_uoms.forEach(singleUom => {
    //                 if (singleUom.is_selling) {
    //                     itemUomsHTML += `<option value = '${singleUom.uom.id}' ${singleUom.uom.id == item.uom_id ? "selected" : ""} >${singleUom.uom?.alias}</option>`;
    //                 }
    //             });
    //             document.getElementById('uom_dropdown_' + itemIndex).innerHTML = itemUomsHTML;
    //         });
    //         order.media_files.forEach((mediaFile, mediaIndex) => {
    //             appendFilePreviews(mediaFile.file_url, 'main_order_file_preview', mediaIndex, mediaFile.id, order.document_status == 'draft' ? false : true);
    //         });
    //         renderIcons();
    //         onItemClick(0);

    //         let finalAmendSubmitButton = document.getElementById("amend-submit-button");

    //         viewModeScript(finalAmendSubmitButton ? false : true);

    //     }
    // }

    document.addEventListener('DOMContentLoaded', function() {
        const order = @json(isset($order) ? $order : null);
        let x= document.getElementById('service_id_input');
        if(x){
            onSeriesChange(x, order ? false : true);
        }

        // getDocNumberByBookId(document.getElementById('series_id_input'), order ? false : true);
    });

    function resetParametersDependentElements()
    {
        var selectionSection = document.getElementById('selection_section');
        if (selectionSection) {
            selectionSection.style.display = "none";
        }
        var selectionSectionSO = document.getElementById('sales_order_selection');
        if (selectionSectionSO) {
            selectionSectionSO.style.display = "none";
        }
        var selectionSectionSI = document.getElementById('sales_order_selection');
        if (selectionSectionSI) {
            selectionSectionSI.style.display = "none";
        }
        var selectionSectionSR = document.getElementById('sales_return_selection');
        if (selectionSectionSR) {
            selectionSectionSR.style.display = "none";
        }
        var selectionSectionDN = document.getElementById('delivery_note_selection');
        if (selectionSectionDN) {
            selectionSectionDN.style.display = "none";
        }
        var selectionSectionLease = document.getElementById('land_lease_selection');
        if (selectionSectionLease) {
            selectionSectionLease.style.display = "none";
        }
        // document.getElementById('add_item_section').style.display = "none";
        $("#order_date_input").attr('max', "<?php echo date('Y-m-d'); ?>");
        $("#order_date_input").attr('min', "<?php echo date('Y-m-d'); ?>");
        $("#order_date_input").off('input');
        if ("{{!isset($order)}}") {
            $("#order_date_input").val(moment().format("YYYY-MM-DD"));

        }
        $('#order_date_input').on('input', function() {
            restrictBothFutureAndPastDates(this);
        });
    }

    function getDocNumberByBookId(element, reset = true) 
    {
        resetParametersDependentElements();
        let bookId = element.value;
        console.log(bookId);
        let actionUrl = '{{route("book.get.doc_no_and_parameters")}}'+'?book_id='+bookId + "&document_date=" + $("#order_date_input").val();
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                if (data.status == 200) {
                  $("#book_code_input").val(data.data.book_code);
                  if(!data.data.doc.document_number) {
                    if (reset) {
                        $("#order_no_input").val('');
                    }
                  }
                  if (reset) {
                      $("#order_no_input").val(data.data.doc.document_number);
                  }
                  if(data.data.doc.type == 'Manually') {
                     $("#order_no_input").attr('readonly', false);
                  } else {
                     $("#order_no_input").attr('readonly', true);
                  }
                  enableDisableQtButton();
                  if (data.data.parameters)
                  {
                    implementBookParameters(data.data.parameters);
                  }
                   if (reset) {
                      implementBookDynamicFields(data.data.dynamic_fields_html, data.data.dynamic_fields);
                  }
                }
                if(data.status == 404) {
                    if (reset) {
                        $("#book_code_input").val('');
                    }
                    alert(data.message);
                    enableDisableQtButton();
                }
            });
        }); 
    }

    function implementBookDynamicFields(html, data)
    {
        let dynamicBookSection = document.getElementById('dynamic_fields_section');
        dynamicBookSection.innerHTML = html;
        if (data && data.length > 0) {
            dynamicBookSection.classList.remove('d-none');
        } else {
            dynamicBookSection.classList.add('d-none');
        }
    }
    function onDocDateChange()
    {
        let bookId = $("#series_id_input").val();
        let actionUrl = '{{route("book.get.doc_no_and_parameters")}}'+'?book_id='+bookId + "&document_date=" + $("#order_date_input").val();
        console.log(actionUrl);
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                if (data.status == 200) {
                  $("#book_code_input").val(data.data.book_code);
                  if(!data.data.doc.document_number) {
                     $("#order_no_input").val('');
                  }
                  $("#order_no_input").val(data.data.doc.document_number);
                  if(data.data.doc.type == 'Manually') {
                     $("#order_no_input").attr('readonly', false);
                  } else {
                     $("#order_no_input").attr('readonly', true);
                  }
                }
                if(data.status == 404) {
                    $("#book_code_input").val("");
                    alert(data.message);
                }
            });
        });
    }

    function implementBookParameters(paramData)
    {
        var selectedRefFromServiceOption = paramData.reference_from_service;
        var selectedBackDateOption = paramData.back_date_allowed;
        var selectedFutureDateOption = paramData.future_date_allowed;
        // Reference From
        if (selectedRefFromServiceOption) {
            var selectVal = selectedRefFromServiceOption;
            if (selectVal && selectVal.length > 0) {
                selectVal.forEach(selectSingleVal => {
                    if (selectSingleVal == 'so') {
                        var selectionSectionElement = document.getElementById('selection_section');
                        if (selectionSectionElement) {
                            selectionSectionElement.style.display = "";
                        }
                        var selectionPopupElement = document.getElementById('sales_order_selection');
                        if (selectionPopupElement)
                        {
                            selectionPopupElement.style.display = ""
                        }
                    }
                    if (selectSingleVal == 'd') {
                        // document.getElementById('add_item_section').style.display = "";
                    }
                    if (selectSingleVal == 'sr') {
                        var selectionSectionElement = document.getElementById('selection_section');
                        if (selectionSectionElement) {
                            selectionSectionElement.style.display = "";
                        }
                        var selectionPopupElement = document.getElementById('sales_return_selection');
                        if (selectionPopupElement)
                        {
                            selectionPopupElement.style.display = ""
                        }
                    }
                    if (selectSingleVal == 'dnote') {
                        var selectionSectionElement = document.getElementById('selection_section');
                        if (selectionSectionElement) {
                            selectionSectionElement.style.display = "";
                        }
                        var selectionPopupElement = document.getElementById('delivery_note_selection');
                        if (selectionPopupElement)
                        {
                            selectionPopupElement.style.display = ""
                        }
                    }
                    if (selectSingleVal == 'land-lease') {
                        var selectionSectionElement = document.getElementById('selection_section');
                        if (selectionSectionElement) {
                            selectionSectionElement.style.display = "";
                        }
                        var selectionPopupElement = document.getElementById('land_lease_selection');
                        if (selectionPopupElement)
                        {
                            selectionPopupElement.style.display = ""
                        }
                    }
                    
                });
            }
        }

        var backDateAllow = false;
        var futureDateAllow = false;

        //Back Date Allow
        if (selectedBackDateOption) {
            var selectVal = selectedBackDateOption;
            if (selectVal && selectVal.length > 0) {
                if (selectVal[0] == "yes") {
                    backDateAllow = true;
                } else {
                    backDateAllow = false;
                }
            }
        }

        //Future Date Allow
        if (selectedFutureDateOption) {
            var selectVal = selectedFutureDateOption;
            if (selectVal && selectVal.length > 0) {
                if (selectVal[0] == "yes") {
                    futureDateAllow = true;
                } else {
                    futureDateAllow = false;
                }
            }
        }

        if (backDateAllow && futureDateAllow) { // Allow both ways (future and past)
            $("#order_date_input").removeAttr('max');
            $("#order_date_input").removeAttr('min');
            $("#order_date_input").off('input');
        } 
        if (backDateAllow && !futureDateAllow) { // Allow only back date
            $("#order_date_input").removeAttr('min');
            $("#order_date_input").attr('max', "<?php echo date('Y-m-d'); ?>");
            $("#order_date_input").off('input');
            $('#order_date_input').on('input', function() {
                restrictFutureDates(this);
            });
        } 
        if (!backDateAllow && futureDateAllow) { // Allow only future date
            $("#order_date_input").removeAttr('max');
            $("#order_date_input").attr('min', "<?php echo date('Y-m-d'); ?>");
            $("#order_date_input").off('input');
            $('#order_date_input').on('input', function() {
                restrictPastDates(this);
            });
        }
    }

    function enableDisableQtButton()
    {
        const bookId = document.getElementById('series_id_input').value;
        const bookCode = document.getElementById('book_code_input').value;
        const documentDate = document.getElementById('order_date_input').value;    }

    // editScript();

    function setApproval()
    {
        document.getElementById('action_type').value = "approve";
        document.getElementById('approve_reject_heading_label').textContent = "Approve " + "{{$typeName}}";

    }
    function setReject()
    {
        document.getElementById('action_type').value = "reject";
        document.getElementById('approve_reject_heading_label').textContent = "Reject " + "{{$typeName}}";
    }
    function setFormattedNumericValue(element)
    {
        element.value = (parseFloat(element.value ? element.value  : 0)).toFixed(2)
    }
    
    function initializeAutocompleteQt(selector, selectorSibling, typeVal, labelKey1, labelKey2 = "") {
        let modalId = '#'+$("#" + selector).closest('.modal').attr('id');
            $("#" + selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type: typeVal,
                            header_book_id : $("#series_id_input").val()
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: `${item[labelKey1]} (${item[labelKey2] ? item[labelKey2] : ''})`,
                                    code: item[labelKey1] || '', 
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching customer data:', xhr.responseText);
                        }
                    });
                },
                minLength: 0,
                appendTo : modalId,
                select: function(event, ui) {
                    var $input = $(this);
                    $input.val(ui.item.label);
                    $("#" + selectorSibling).val(ui.item.id);
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#" + selectorSibling).val("");
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
    }
    var openPullType = "so";

    //Disable form submit on enter button
    document.querySelector("form").addEventListener("keydown", function(event) {
        if (event.key === "Enter") {
            event.preventDefault();  // Prevent form submission
        }
    });
    $("input[type='text']").on("keydown", function(event) {
        if (event.key === "Enter") {
            event.preventDefault();  // Prevent form submission
        }
    });

    $(document).ready(function() {
        // Event delegation to handle dynamically added input fields
        $(document).on('input', '.decimal-input', function() {
            // Allow only numbers and a single decimal point
            this.value = this.value.replace(/[^0-9.]/g, ''); // Remove non-numeric characters
            
            // Prevent more than one decimal point
            if ((this.value.match(/\./g) || []).length > 1) {
                this.value = this.value.substring(0, this.value.length - 1);
            }

            // Optional: limit decimal places to 2
            if (this.value.indexOf('.') !== -1) {
                this.value = this.value.substring(0, this.value.indexOf('.') + 3);
            }
        });
    });

    
var currentRevNo = $("#revisionNumber").val();

// # Revision Number On Change
$(document).on('change', '#revisionNumber', (e) => {
    e.preventDefault();
    let actionUrl = location.pathname + '?type=' + "{{request() -> type ?? 'si'}}" + '&revisionNumber=' + e.target.value;
    $("#revisionNumber").val(currentRevNo);
    window.open(actionUrl, '_blank'); // Opens in a new tab
});

$(document).on('submit', '.ajax-submit-2', function (e) {
    e.preventDefault();
    var submitButton = (e.originalEvent && e.originalEvent.submitter) || $(this).find(':submit');
    var submitButtonHtml = submitButton.innerHTML; 
    submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
    submitButton.disabled = true;
    var method = $(this).attr('method');
    var url = $(this).attr('action');
    var redirectUrl = $(this).data('redirect');
    var data = new FormData($(this)[0]);

    var formObj = $(this);
    
    $.ajax({
        url,
        type: method,
        data,
        contentType: false,
        processData: false,
        success: function (res) {
            submitButton.disabled = false;
            submitButton.innerHTML = submitButtonHtml;
            $('.ajax-validation-error-span').remove();
            $(".is-invalid").removeClass("is-invalid");
            $(".help-block").remove();
            $(".waves-ripple").remove();
            Swal.fire({
                title: 'Success!',
                text: res.message,
                icon: 'success',
            });
            setTimeout(() => {
                if (res.store_id) {
                    location.href = `/stores/${res.store_id}/edit`;
                } else if (redirectUrl) {
                    location.href = redirectUrl;
                } else {
                    location.reload();
                }
            }, 1500);
            
        },
        error: function (error) {
            submitButton.disabled = false;
            submitButton.innerHTML = submitButtonHtml;
            $('.ajax-validation-error-span').remove();
            $(".is-invalid").removeClass("is-invalid");
            $(".help-block").remove();
            $(".waves-ripple").remove();
            let res = error.responseJSON || {};
            if (error.status === 422 && res.errors) {
                if (
                    Object.size(res) > 0 &&
                    Object.size(res.errors) > 0
                ) {
                    show_validation_error(res.errors);
                }
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: res.message || 'An unexpected error occurred.',
                    icon: 'error',
                });
            }
        }
    });
});

function showLocation(element, type) {
    let locations = $(element).attr('location-ids');

    $.ajax({
        url: '{{ route('transporter.get-locations') }}',
        type: "POST",
        data: { location_ids: locations },
        success: function(response) {
            // Set the modal title

            // Build the table structure
            let tableHtml = `
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Location</th>
                            <th>Address</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            // Loop through the response (assuming it's an array of arrays)
            response.data.forEach(location => {
                tableHtml += `
                    <tr>
                        <td class='no-wrap'>${location[0]}</td>
                        <td class='no-wrap'>${location[1]}</td>
                    </tr>
                `;
            });

            tableHtml += `</tbody></table>`;

            // Append the table to the modal body
            $('#locationModalTitle').text(`${type} Location Details`);                            
            $('.modal-body').html(tableHtml);
            $('#location').modal('show');

        },
        error: function(xhr) {
            $(".modal-body").html(`<div class="alert alert-danger">Error: ${xhr.responseText}</div>`);
        }
    });
}

function viewModeScript(disable = true)
{
    const currentOrder = @json(isset($order) ? $order : null);
    const editOrder = "{{( isset($buttons) && ($buttons['draft'] || $buttons['submit'])) ? false : true}}";
    const revNoQuery = "{{ isset(request() -> revisionNumber) ? true : false }}";

    if ((editOrder || revNoQuery) && currentOrder) {
        document.querySelectorAll('input, textarea, select').forEach(element => {
            if (element.id !== 'revisionNumber' && element.type !== 'hidden' && !element.classList.contains('cannot_disable')) {
                // element.disabled = disable;
                element.style.pointerEvents = disable ? "none" : "auto";
                if (disable) {
                    element.setAttribute('readonly', true);
                } else {
                    element.removeAttribute('readonly');
                }
            }
        });
        //Disable all submit and cancel buttons
        document.querySelectorAll('.can_hide').forEach(element => {
            element.style.display = disable ? "none" : "";
        });
        //Remove add delete button
        document.getElementById('add_delete_item_section').style.display = disable ? "none" : "";
    } else {
        return;
    }
}

function amendConfirm()
{
    viewModeScript(false);
    disableHeader();
    const amendButton = document.getElementById('amendShowButton');
    if (amendButton) {
        amendButton.style.display = "none";
    }
    //disable other buttons
    var printButton = document.getElementById('dropdownMenuButton');
    if (printButton) {
        printButton.style.display = "none";
    }   
    var postButton = document.getElementById('postButton');
    if (postButton) {
        postButton.style.display = "none";
    }
    const buttonParentDiv = document.getElementById('buttonsDiv');
    const newSubmitButton = document.createElement('button');
    newSubmitButton.type = "button";
    newSubmitButton.id = "amend-submit-button";
    newSubmitButton.className = "btn btn-primary btn-sm mb-50 mb-sm-0";
    newSubmitButton.innerHTML = `<i data-feather="check-circle"></i> Submit`;
    newSubmitButton.onclick = function() {
        openAmendConfirmModal();
    };

    if (buttonParentDiv) {
        buttonParentDiv.appendChild(newSubmitButton);
    }

    if (feather) {
        feather.replace({
            width: 14,
            height: 14
        });
    }

}

function openAmendConfirmModal()
{
    $("#amendConfirmPopup").modal("show");
}

function submitAmend()
{
    enableHeader();
    let remark = $("#amendConfirmPopup").find('[name="amend_remarks"]').val();
    $("#action_type_main").val("amendment");
    $("#amendConfirmPopup").modal('hide');
    $("#sale_invoice_form").submit();
}

const maxNumericLimit = 9999999;

document.addEventListener('input', function (e) {
        if (e.target.classList.contains('text-end')) {
            let value = e.target.value;

            // Remove invalid characters (anything other than digits and a single decimal)
            value = value.replace(/[^0-9.]/g, '');

            // Prevent more than one decimal point
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts[1];
            }

            // Prevent starting with a decimal (e.g., ".5" -> "0.5")
            if (value.startsWith('.')) {
                value = '0' + value;
            }

            // Limit to 2 decimal places
            if (parts[1]?.length > 2) {
                value = parts[0] + '.' + parts[1].substring(0, 2);
            }

            // Prevent exceeding the max limit
            if (value && Number(value) > maxNumericLimit) {
                value = maxNumericLimit.toString();
            }

            e.target.value = value;
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.target.classList.contains('text-end')) {
            if ( e.key === 'Tab' ||
                ['Backspace', 'ArrowLeft', 'ArrowRight', 'Delete', '.'].includes(e.key) || 
                /^[0-9]$/.test(e.key)
            ) {
                // Allow numbers, navigation keys, and a single decimal point
                return;
            }
            e.preventDefault(); // Block everything else
        }
    });
    function resetSeries()
    {
        let x=document.getElementById('series_id_input');
        if(x){
            x.innerHTML = ''
        } 
    }

    function implementSeriesChange(val)
    {
        //COMMON CHANGES
        document.getElementById("type_hidden_input").value = val;
        const breadCrumbHeading = document.getElementById('breadcrumb-document-heading');

        breadCrumbHeading.textContent = "Transporter Request";
    }

    
    function onSeriesChange(element, reset = true)
    {
        resetSeries();
        implementSeriesChange(element.value);
        let x =document.getElementById('series_id_input');
        if(x){
        $.ajax({
            url: "{{route('book.service-series.get')}}",
            method: 'GET',
            dataType: 'json',
            data: {
                menu_alias: "{{request() -> segments()[0]}}",
                service_alias: element.value,
                book_id : reset ? null : "{{isset($order) ? $order -> book_id : null}}"
            },
            success: function(data) {
                let newSeriesHTML = ``;
                if (data.status == 'success') {
                        data.data.forEach((book, bookIndex) => {
                            newSeriesHTML += `<option value = "${book.id}" ${bookIndex == 0 ? 'selected' : ''} >${book.book_code}</option>`;
                        });
                        console.log("DATA book", newSeriesHTML);
                        x.innerHTML = newSeriesHTML;
                        x.value=data.data[0]?.id;
                        getDocNumberByBookId(x, reset);
                    } else {
                        x.innerHTML = '';
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching customer data:', xhr.responseText);
                    document.getElementById('series_id_input').innerHTML = '';
                }
            });
        }
    }

    function revokeDocument()
    {
        const orderId = "{{isset($order) ? $order -> id : null}}";
        if (orderId) {
            $.ajax({
            url: "{{route('sale.return.revoke')}}",
            method: 'POST',
            dataType: 'json',
            data: {
                id : orderId
            },
            success: function(data) {
                if (data.status == 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                    });
                    location.reload();
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                    });
                    window.location.href = "{{$redirect_url}}";
                }
            },
            error: function(xhr) {
                console.error('Error fetching customer data:', xhr.responseText);
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
@endsection